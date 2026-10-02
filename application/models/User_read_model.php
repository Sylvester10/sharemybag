<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

class User_read_model extends \MY_Model
{
    const USER_COUNT_CACHE_TTL = 300;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->primary_cols = array('id');
    }

    public function get_user_details($email)
    {
        $this->db->where('email', $email);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    public function get_user_details_by_id($id)
    {
        return $this->dataById($id);
    }

    public function get_user_by_signup_resume_token($token)
    {
        if (!$this->db->field_exists('signup_resume_token', $this->table)) {
            return null;
        }

        $this->db->where('signup_resume_token', $token);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    public function users()
    {
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function get_users_phone($phone)
    {
        $this->db->where('number', $phone);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    public function get_user_by_verified_phone($phone)
    {
        if (!$this->db->field_exists('verified_phone_e164', $this->table)
            || !$this->db->field_exists('phone_signin_enabled', $this->table)) {
            return null;
        }

        $this->db->where('verified_phone_e164', $phone);
        $this->db->where('phone_verified_at IS NOT NULL', null, false);
        $this->db->where('phone_signin_enabled', 1);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    public function get_login_user($identifier)
    {
        $identifier = trim((string) $identifier);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->get_user_details(strtolower($identifier));
        }

        $phone = normalize_phone_number('', $identifier);
        return strpos($phone, '+') === 0 ? $this->get_user_by_verified_phone($phone) : null;
    }

    public function verified_phone_belongs_to_another_user($phone, $userId)
    {
        if (!$this->db->field_exists('verified_phone_e164', $this->table)) {
            return false;
        }

        $this->db->where('verified_phone_e164', $phone);
        $this->db->where('id !=', (int) $userId);
        $this->applyNotDeleted();
        return $this->db->count_all_results($this->table) > 0;
    }

    public function get_approved_users()
    {
        $this->db->where('is_verified', VERIFY_APPROVED);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function get_pending_users()
    {
        $this->db->where('is_verified', VERIFY_PENDING);
        $this->applyNotDeleted();
        return $this->db->get($this->table)->result();
    }

    public function count_approved_users()
    {
        return $this->getUserCountSummary()->approved_users;
    }

    public function count_pending_users()
    {
        return $this->getUserCountSummary()->pending_users;
    }

    public function count_users()
    {
        return $this->getUserCountSummary()->total_users;
    }

    public function clearUserCountCaches()
    {
        $this->forgetCache('users.summary.counts');
    }

    private function getUserCountSummary()
    {
        return $this->rememberCache('users.summary.counts', self::USER_COUNT_CACHE_TTL, function () {
            $this->db->select("
                COUNT(*) AS total_users,
                SUM(CASE WHEN is_verified = " . (int) VERIFY_APPROVED . " THEN 1 ELSE 0 END) AS approved_users,
                SUM(CASE WHEN is_verified = " . (int) VERIFY_PENDING . " THEN 1 ELSE 0 END) AS pending_users
            ", false);
            $this->applyNotDeleted();
            $row = $this->db->get($this->table)->row();

            return (object) array(
                'total_users' => $row ? (int) $row->total_users : 0,
                'approved_users' => $row ? (int) $row->approved_users : 0,
                'pending_users' => $row ? (int) $row->pending_users : 0,
            );
        });
    }
}
