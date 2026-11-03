<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Shared by authenticated return/resume, signed webhooks, and the CLI expiry worker. */
class Booking_payment_reconciler
{
    private $CI;
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model(['booking_checkout_model','users_model','booking_read_model']);
        $this->CI->load->library('booking_payment_gateway');
    }

    public function reconcile($attempt, $close = false)
    {
        $model = $this->CI->booking_checkout_model;
        $verified = $this->CI->booking_payment_gateway->verify($attempt,$close);
        if (($verified['outcome'] ?? '') === 'paid') {
            $result = $model->apply_verified($attempt->id,$verified);
            if (!empty($result['newly_paid'])) {
                // Financial confirmation remains valid if a notification fails.
                $this->CI->users_model->send_booking_payment_notifications($attempt->booking_id);
            }
            if (!empty($result['review'])) { log_message('error','Booking payment requires refund/reconciliation review; attempt ID '.(int)$attempt->id); }
            if (!empty($result['newly_reviewed'])) {
                $message = 'Payment requires reconciliation/refund review for booking #'.(int)$attempt->booking_id
                    .', attempt #'.(int)$attempt->id.', reference '.html_escape($attempt->reference).'. '
                    .'Do not ask the customer to pay again. Check provider records before allocating space or refunding.';
                send_email_notification($this->CI,$_ENV['ADMIN_NOTIFICATION_EMAIL'] ?? 'customers@sharemybag.co.uk',
                    'Booking payment requires review',['message'=>$message],'admin_general_notification_email');
                $latest = $model->latest_attempt($attempt->booking_id);
                if ($latest && (int)$latest->id !== (int)$attempt->id && in_array($latest->status,['initializing','open','pending'],true)) {
                    // A late earlier charge makes another checkout unsafe. Close it where supported, without recursion.
                    $other = $this->CI->booking_payment_gateway->verify($latest,true);
                    if (($other['outcome'] ?? '') === 'unpaid') { $model->end_attempt($latest->id,'canceled','An earlier payment requires review.'); }
                    elseif (($other['outcome'] ?? '') === 'paid') { $model->apply_verified($latest->id,$other); }
                    else { $model->mark_unknown($latest->id,'An earlier payment requires review.'); }
                }
            }
            return $result;
        }
        if (($verified['outcome'] ?? '') === 'unpaid') {
            $ended = $model->end_attempt($attempt->id,$close?'canceled':'failed');
            return ['status'=>(bool)$ended,'paid'=>false,'outcome'=>'unpaid','msg'=>'Payment was not completed.'];
        }
        if (!$close && ($verified['provider'] ?? '') === 'stripe' && ($verified['gateway_status'] ?? '') === 'open') {
            $model->attach_gateway($attempt->id,$attempt->provider_session_id,$attempt->authorization_url);
            return ['status'=>false,'paid'=>false,'outcome'=>'open','msg'=>'Payment has not been completed.'];
        }
        $model->mark_unknown($attempt->id, !empty($verified['binding_mismatch']) ? 'Provider data did not match this payment.' : 'Payment confirmation is pending.');
        return ['status'=>false,'paid'=>false,'outcome'=>'pending','msg'=>'We are checking this payment. Please check again before trying to pay.'];
    }
}
