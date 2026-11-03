<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Serialize mailbox-wide reservations before SMTP. No message bodies or raw recipients in audit. */
class Mail_safety_model extends CI_Model
{
    public $settings;
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->config('mail_safety', true);
        $this->settings = $this->config->item('mail_safety');
    }
    public function reserve($recipient, $template, $bulk = false)
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) || !is_array($this->settings)
            || !$this->db->table_exists('mail_safety_state') || !$this->db->table_exists('mail_send_events')) {
            return ['status'=>false, 'outcome'=>'unavailable'];
        }
        $sender = hash('sha256', strtolower(trim($_ENV['SMTP_USER'] ?? 'unconfigured')));
        $recipient_key = hash('sha256', strtolower(trim($recipient)));
        $now = time(); $day = gmdate('Y-m-d', $now); $minute = (int) floor($now / 60);
        $debug = $this->db->db_debug; $this->db->db_debug = false;
        try {
            $insert = $this->db->dbdriver === 'sqlite3' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
            if (!$this->db->query($insert . ' INTO mail_safety_state (sender_key) VALUES (?)', [$sender])) {
                return ['status'=>false, 'outcome'=>'unavailable'];
            }
            if (!$this->db->trans_begin()) { return ['status'=>false, 'outcome'=>'unavailable']; }
            $lock = $this->db->dbdriver === 'sqlite3' ? '' : ' FOR UPDATE';
            $query = $this->db->query('SELECT * FROM mail_safety_state WHERE sender_key = ?' . $lock, [$sender]);
            $row = $query ? $query->row() : null;
            if (!$row) { $this->db->trans_rollback(); return ['status'=>false, 'outcome'=>'unavailable']; }
            $used = $row->day_bucket === $day ? (int) $row->day_used : 0;
            $bulk_used = $row->day_bucket === $day ? (int) $row->bulk_day_used : 0;
            $minute_used = (int) $row->minute_bucket === $minute ? (int) $row->minute_used : 0;
            $recipient_used = $this->db->where('sender_key',$sender)->where('recipient_key',$recipient_key)
                ->where('created_at >=',$day.' 00:00:00')->count_all_results('mail_send_events');
            if ($used >= $this->settings['daily_limit'] || $minute_used >= $this->settings['minute_limit']
                || $recipient_used >= $this->settings['recipient_daily_limit']
                || ($bulk && ($bulk_used >= $this->settings['bulk_daily_limit'] || $now - (int)$row->bulk_last_at < $this->settings['bulk_interval_seconds']))) {
                $this->db->trans_rollback(); return ['status'=>false,'outcome'=>'rate_limited'];
            }
            $updated = $this->db->where('sender_key',$sender)->update('mail_safety_state', [
                'day_bucket'=>$day,'day_used'=>$used+1,'minute_bucket'=>$minute,'minute_used'=>$minute_used+1,
                'bulk_day_used'=>$bulk_used+($bulk?1:0),'bulk_last_at'=>$bulk?$now:(int)$row->bulk_last_at]);
            $logged = $this->db->insert('mail_send_events', ['sender_key'=>$sender,'recipient_key'=>$recipient_key,
                'template'=>substr((string)$template,0,80),'status'=>'reserved','created_at'=>gmdate('Y-m-d H:i:s',$now)]);
            $id = $this->db->insert_id();
            if (!$updated || !$logged || !$this->db->trans_status()) {
                $this->db->trans_rollback(); return ['status'=>false,'outcome'=>'unavailable'];
            }
            if (!$this->db->trans_commit()) {
                $this->db->trans_rollback();
                return ['status'=>false,'outcome'=>'unavailable'];
            }
            return ['status'=>true,'id'=>(int)$id];
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error','Mail safety reservation failed.');
            return ['status'=>false,'outcome'=>'unavailable'];
        } finally { $this->db->db_debug = $debug; }
    }
    public function finish($id, $sent)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            $updated = $this->db->where('id',(int)$id)->where('status','reserved')->update('mail_send_events',
                ['status'=>$sent?'sent':'failed','finished_at'=>gmdate('Y-m-d H:i:s')]);
            if ($updated) { return true; }
        } catch (Throwable $error) {
            // Audit persistence cannot change a transport result or cause a duplicate retry.
        } finally { $this->db->db_debug = $debug; }
        log_message('error','Could not finish mail send audit ID '.(int)$id);
        return false;
    }
}
