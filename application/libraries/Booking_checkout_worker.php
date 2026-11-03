<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Invoked by the existing CLI mail worker, under its own nonblocking reconciliation lock. */
class Booking_checkout_worker
{
    protected function lock_path() { return APPPATH.'cache/booking_checkout_worker.lock'; }
    public function run($limit = 5)
    {
        $CI =& get_instance();
        $CI->load->model('booking_checkout_model');
        $result = ['checked'=>0,'confirmed'=>0,'review'=>0,'pending'=>0,'errors'=>0];
        if (!$CI->booking_checkout_model->ready()) { return $result + ['schema_ready'=>false]; }
        $handle = @fopen($this->lock_path(),'c');
        if (!$handle) { $result['errors']++; return $result; }
        if (!flock($handle,LOCK_EX|LOCK_NB)) { fclose($handle); return $result + ['busy'=>true]; }
        try {
            $CI->load->library('booking_payment_reconciler');
            foreach ($CI->booking_checkout_model->reconcilable_attempts(min(20,max(1,(int)$limit))) as $attempt) {
                $state = $CI->booking_payment_reconciler->reconcile($attempt,strtotime($attempt->hold_until.' UTC') <= time());
                $result['checked']++;
                if (!empty($state['review'])) { $result['review']++; }
                elseif (!empty($state['paid'])) { $result['confirmed']++; }
                elseif (($state['outcome'] ?? '') === 'pending') { $result['pending']++; }
                elseif (empty($state['status']) && ($state['outcome'] ?? '') !== 'open') { $result['errors']++; }
                $CI->db->where('id',$attempt->id)->where_in('status',['initializing','open','pending'])
                    ->update('booking_payment_attempts',['updated_at'=>gmdate('Y-m-d H:i:s')]);
            }
        } catch (Throwable $error) {
            $result['errors']++; log_message('error','Booking checkout reconciliation interrupted.');
        } finally { flock($handle,LOCK_UN); fclose($handle); }
        return $result;
    }
}
