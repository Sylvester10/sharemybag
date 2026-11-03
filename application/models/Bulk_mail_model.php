<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Admin reminders are queued; uncertain SMTP outcomes always need manual review. */
class Bulk_mail_model extends CI_Model
{
    public function enqueue_users(array $users, $subject, $body, $actor_id)
    {
        $result = ['status'=>true, 'queued'=>0, 'skipped'=>0, 'errors'=>0];
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            if (!$this->db->table_exists('mail_bulk_queue') || (int) $actor_id <= 0
                || trim((string) $subject) === '' || strlen((string) $subject) > 255
                || preg_match('/[\r\n]/', (string) $subject) || strlen((string) $body) > 65536) {
                return ['status'=>false, 'queued'=>0, 'skipped'=>0, 'errors'=>max(1,count($users))];
            }
            $seen = [];
            $index = 0;
            foreach ($users as $user) {
                $email = strtolower(trim((string) ($user->email ?? '')));
                if ($index++ >= 1000 || !$this->eligible($user) || strlen($email) > 254 || !filter_var($email,FILTER_VALIDATE_EMAIL) || isset($seen[$email])) {
                    $result['skipped']++; continue;
                }
                $seen[$email] = true;
                $dedupe = hash('sha256',gmdate('Y-m-d') . "\0" . $email . "\0" . $subject . "\0" . $body);
                if ($this->db->where('dedupe_key',$dedupe)->get('mail_bulk_queue')->row()) {
                    $result['skipped']++; continue;
                }
                $saved = $this->db->insert('mail_bulk_queue',[
                        'recipient'=>$email, 'subject'=>$subject, 'body'=>$body,
                        'template'=>'admin_general_notification_email', 'actor_id'=>(int) $actor_id,
                        'dedupe_key'=>$dedupe, 'status'=>'pending', 'created_at'=>gmdate('Y-m-d H:i:s'),
                    ]);
                if ($saved) { $result['queued']++; }
                elseif ($this->db->where('dedupe_key',$dedupe)->get('mail_bulk_queue')->row()) { $result['skipped']++; }
                else { $result['errors']++; $result['status'] = false; }
            }
            return $result;
        } catch (Throwable $error) {
            $result['status'] = false;
            $result['errors'] = max(1, count($users) - $result['queued'] - $result['skipped']);
            return $result;
        } finally { $this->db->db_debug = $debug; }
    }

    private function eligible($user)
    {
        if (!$user || !empty($user->deleted_at) || (int) ($user->account_status ?? 0) !== 1 || empty($user->password)) { return false; }
        // Older completed accounts predate email_verified_at; consumed signup code is their legacy proof.
        return !empty($user->email_verified_at) || (property_exists($user,'verification_code')
            && ($user->verification_code === null || $user->verification_code === ''));
    }

    private function recipient_is_eligible($email)
    {
        $users = $this->db->where('LOWER(email)',strtolower($email))->where('account_status',1)
            ->where('deleted_at IS NULL',null,false)->get('users')->result();
        foreach ($users as $user) { if ($this->eligible($user)) { return true; } }
        return false;
    }

    protected function worker_lock_path()
    {
        return APPPATH . 'cache/bulk_mail_worker.lock';
    }

    public function process($limit = 5)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try { return $this->process_queue($limit); }
        finally { $this->db->db_debug = $debug; }
    }

    private function process_queue($limit)
    {
        $result = ['sent'=>0,'failed'=>0,'skipped'=>0,'review'=>0,'deferred'=>0,'errors'=>0,'busy'=>false];
        if (!$this->db->table_exists('mail_bulk_queue')) { $result['errors']++; return $result; }
        $handle = @fopen($this->worker_lock_path(),'c');
        if (!$handle) { $result['errors']++; return $result; }
        if (!flock($handle,LOCK_EX|LOCK_NB)) { fclose($handle); $result['busy'] = true; return $result; }
        try {
            // A crashed worker might have sent the message. Never replay its ambiguous claim.
            if (!$this->db->where('status','sending')->group_start()->where('claimed_at IS NULL',null,false)
                ->or_where('claimed_at <',gmdate('Y-m-d H:i:s',time()-900))->group_end()
                ->update('mail_bulk_queue',['status'=>'review','last_error'=>'Previous delivery outcome unknown; review before retrying.'])) {
                $result['errors']++; return $result;
            }
            $result['review'] += $this->db->affected_rows();
            for ($index=0;$index<min(5,max(1,(int)$limit));$index++) {
                $row = $this->claim();
                if ($row === false) { $result['errors']++; break; }
                if (!$row) { break; }
                if (!$this->recipient_is_eligible($row->recipient) || $row->template !== 'admin_general_notification_email') {
                    if (!$this->finish($row,['status'=>'skipped','last_error'=>'Recipient or template is no longer eligible.'])) { $result['errors']++; break; }
                    $result['skipped']++; continue;
                }
                $outcome = null;
                try {
                    $sent = send_email_notification($this,$row->recipient,$row->subject,
                        ['message'=>$row->body],$row->template,$outcome,true);
                } catch (Throwable $error) {
                    if ($this->finish($row,['status'=>'review','last_error'=>'Delivery outcome unknown; review before retrying.'])) { $result['review']++; }
                    else { $result['errors']++; }
                    break;
                }
                if (!$sent && in_array($outcome,['rate_limited','unavailable'],true)) {
                    if (!$this->finish($row,['status'=>'pending','claimed_at'=>null,'last_error'=>'Sending deferred by application safety controls.'])) { $result['errors']++; }
                    $result['deferred']++; break;
                }
                $data = $sent ? ['status'=>'sent','sent_at'=>gmdate('Y-m-d H:i:s'),'last_error'=>null]
                    : ['status'=>'failed','last_error'=>'SMTP send failed; review before any manual retry.'];
                if (!$this->finish($row,$data)) { $result['errors']++; break; }
                $result[$sent?'sent':'failed']++;
                if (!$sent) { break; }
            }
        } catch (Throwable $error) {
            // Leave any claimed row for stale-claim review; its delivery may be uncertain.
            $result['errors']++;
        } finally { flock($handle,LOCK_UN); fclose($handle); }
        return $result;
    }

    private function claim()
    {
        if (!$this->db->trans_begin()) { return false; }
        $lock = $this->db->dbdriver === 'sqlite3' ? '' : ' FOR UPDATE';
        $query = $this->db->query("SELECT * FROM mail_bulk_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 1" . $lock);
        if (!$query) { $this->db->trans_rollback(); return false; }
        $row = $query->row();
        if (!$row) { $this->db->trans_commit(); return null; }
        $claimed_at = gmdate('Y-m-d H:i:s');
        $ok = $this->db->where('id',$row->id)->where('status','pending')
            ->update('mail_bulk_queue',['status'=>'sending','claimed_at'=>$claimed_at]);
        if (!$ok || $this->db->affected_rows() !== 1 || !$this->db->trans_status()) { $this->db->trans_rollback(); return false; }
        if (!$this->db->trans_commit()) { return false; }
        $row->claimed_at = $claimed_at;
        return $row;
    }

    private function finish($row,array $data)
    {
        $ok = $this->db->where('id',$row->id)->where('status','sending')->where('claimed_at',$row->claimed_at)
            ->update('mail_bulk_queue',$data);
        return $ok && $this->db->affected_rows() === 1;
    }
}
