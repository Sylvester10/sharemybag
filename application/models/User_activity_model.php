<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Account history is append-only. Sensitive credentials never enter snapshots. */
class User_activity_model extends CI_Model
{
    public const FIELDS = array(
        'firstname' => 'First name', 'middlename' => 'Middle name', 'lastname' => 'Last name',
        'email' => 'Email', 'number' => 'Phone', 'country' => 'Country',
        'address' => 'Address', 'state' => 'City', 'post_code' => 'Postal code',
        'email_verified_at' => 'Email verified at', 'verified_phone_e164' => 'Verified phone',
        'phone_verified_at' => 'Phone verified at', 'phone_signin_enabled' => 'Phone sign-in',
        'is_verified' => 'Identity verification', 'account_status' => 'Account status',
        'id_type' => 'ID type', 'platform' => 'Platform', 'socials' => 'Social profile',
        'selfie' => 'Selfie', 'id_card' => 'Identity document', 'utility' => 'Address document',
        'verification_rejection_reason' => 'Verification rejection reason',
        'verification_rejection_note' => 'Verification rejection note',
        'verification_rejected_at' => 'Verification rejected at',
        'verification_rejected_by' => 'Verification rejected by',
    );

    public function actor($type, $id)
    {
        $type = $type === 'admin' ? 'admin' : 'user';
        // Admin impersonation must never be attributed to the account owner.
        if ($type === 'user' && (int) $this->session->userdata('user_impersonator_id') > 0) {
            $id = (int) $this->session->userdata('user_impersonator_id');
            $type = 'admin';
        }
        $row = $this->db->where('id', (int) $id)->get($type === 'admin' ? 'admins' : 'users')->row();
        $name = $row ? trim($row->name ?? (($row->firstname ?? '') . ' ' . ($row->lastname ?? ''))) : '';
        return array('type' => $type, 'id' => (int) $id,
            'name' => $name !== '' ? $name : ($row->email ?? ucfirst($type)));
    }

    /** Lock the account before editing so the before/after record is accurate. */
    public function updateDetails($userId, array $data, array $actor, $event = 'details_updated', $challengeId = null)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            return $this->saveDetails($userId, $data, $actor, $event, $challengeId);
        } finally {
            $this->db->db_debug = $debug;
        }
    }

    private function saveDetails($userId, array $data, array $actor, $event, $challengeId)
    {
        if (!$this->db->table_exists('user_activity_logs') || (int) $actor['id'] <= 0) {
            return false;
        }
        $this->db->trans_begin();
        $before = $this->db->query('SELECT * FROM users WHERE id = ? FOR UPDATE', array((int) $userId))->row();
        if (!$before) {
            $this->db->trans_rollback();
            return false;
        }
        if ($event === 'phone_verified') {
            // A reset may have happened while Twilio was checking the code.
            $challenge = $this->db->query('SELECT * FROM auth_login_challenges WHERE id = ? FOR UPDATE', array((int) $challengeId))->row();
            if (!empty($before->phone_verified_at) || !$challenge
                || (int) $challenge->user_id !== (int) $userId || $challenge->purpose !== 'profile_phone'
                || $challenge->consumed_at !== null || strtotime($challenge->expires_at) <= time()
                || !hash_equals($challenge->destination_hash, hash('sha256', strtolower((string) $data['number'])))) {
                $this->db->trans_rollback();
                return false;
            }
        }
        if ($event === 'phone_signin_updated' && !empty($data['phone_signin_enabled'])
            && (empty($before->phone_verified_at) || empty($before->verified_phone_e164))) {
            $this->db->trans_rollback();
            return false;
        }
        $data = array_intersect_key($data, self::FIELDS);
        $phoneChanged = array_key_exists('number', $data) && (string) $data['number'] !== (string) $before->number;
        $emailChanged = array_key_exists('email', $data) && strtolower((string) $data['email']) !== strtolower((string) $before->email);
        $phoneReset = $event === 'phone_cleared' || ($phoneChanged && $event !== 'phone_verified');
        if ($phoneReset) {
            $data['verified_phone_e164'] = null;
            $data['phone_verified_at'] = null;
            $data['phone_signin_enabled'] = 0;
        }
        if ($emailChanged && property_exists($before, 'email_verified_at')) {
            $data['email_verified_at'] = null;
        }
        $changes = array();
        foreach ($data as $field => $value) {
            if ((string) ($before->$field ?? '') !== (string) $value) {
                $changes[$field] = array('before' => $before->$field ?? null, 'after' => $value);
            }
        }
        if (!$changes && !$phoneReset) {
            $this->db->trans_commit();
            return true;
        }
        $ok = $this->db->where('id', (int) $userId)->update('users', $data);
        if ($phoneReset || $emailChanged || (isset($changes['phone_signin_enabled']) && empty($data['phone_signin_enabled']))) {
            if ($this->db->table_exists('auth_login_challenges')) {
                $channels = array();
                if ($phoneReset || isset($changes['phone_signin_enabled'])) { $channels = array('sms', 'whatsapp'); }
                if ($emailChanged) { $channels[] = 'email'; }
                $ok = $this->db->where('user_id', (int) $userId)->where_in('delivery_channel', $channels)
                    ->where('consumed_at IS NULL', null, false)
                    ->update('auth_login_challenges', array('consumed_at' => date('Y-m-d H:i:s'))) && $ok;
            }
        }
        if ($event === 'phone_verified') {
            $ok = $this->db->where('id', (int) $challengeId)->update('auth_login_challenges',
                array('consumed_at' => date('Y-m-d H:i:s'))) && $ok;
        }
        $ok = $this->record($userId, $event, $actor, $changes) && $ok;
        if (!$ok || !$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }
        return $this->db->trans_commit();
    }

    public function record($userId, $event, array $actor, array $changes = array(), $method = null)
    {
        if (!$this->db->table_exists('user_activity_logs')) { return false; }
        $changes = array_intersect_key($changes, self::FIELDS);
        $json = $changes ? json_encode($changes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        if ($json === false) { return false; }
        $name = (string) $actor['name'];
        $name = function_exists('mb_substr') ? mb_substr($name, 0, 255, 'UTF-8') : $name;
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            return $this->db->insert('user_activity_logs', array(
            'user_id' => (int) $userId, 'actor_type' => $actor['type'],
            'actor_id' => (int) $actor['id'], 'actor_name' => $name,
            'event' => $event, 'method' => $method, 'changes' => $json,
            'date_added' => date('Y-m-d H:i:s'),
            ));
        } finally {
            $this->db->db_debug = $debug;
        }
    }

    public function history($userId, $type, $page)
    {
        $page = max(1, (int) $page);
        if (!$this->db->table_exists('user_activity_logs')) {
            return array('rows' => array(), 'page' => $page, 'more' => false, 'available' => false);
        }
        $this->db->where('user_id', (int) $userId);
        if ($type === 'signin') {
            $this->db->where_in('event', array('signed_in', 'signed_out', 'admin_access'));
        } else {
            $this->db->where_not_in('event', array('signed_in', 'signed_out', 'admin_access'));
        }
        $rows = $this->db->order_by('id', 'DESC')->limit(21, ($page - 1) * 20)->get('user_activity_logs')->result();
        $more = count($rows) > 20;
        return array('rows' => array_slice($rows, 0, 20), 'page' => $page, 'more' => $more, 'available' => true);
    }
}
