<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Gateway delivery works without a customer session; both endpoints require a valid signature. */
class Booking_webhooks extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_once FCPATH.'vendor/autoload.php';
        $this->load->database();
        $this->load->helper(['url','app','email','my','sk']);
        require_once APPPATH.'core/Constants.php';
        $this->load->model('booking_checkout_model');
        $this->load->library(['booking_payment_gateway','booking_payment_reconciler']);
    }

    public function stripe()
    {
        $raw = $this->payload();
        if ($raw === null) { return; }
        $secret = ENVIRONMENT === 'production' ? ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? '') : ($_ENV['STRIPE_TEST_WEBHOOK_SECRET'] ?? '');
        if (!$secret) { $this->respond(503); return; }
        try {
            $event = \Stripe\Webhook::constructEvent($raw,(string)$this->input->get_request_header('Stripe-Signature'),$secret);
        } catch (Throwable $error) { $this->respond(400); return; }
        if (!in_array($event->type,['checkout.session.completed','checkout.session.async_payment_succeeded','checkout.session.expired'],true)) {
            $this->respond(200); return;
        }
        if (!$this->booking_checkout_model->ready()) { $this->respond(503); return; }
        $data = $event->data->object->toArray();
        $reference = $data['metadata']['reference'] ?? '';
        $attempt = $this->booking_checkout_model->attempt_by_reference($reference);
        if (!$attempt || $attempt->provider !== 'stripe') { $this->respond(200); return; }
        // Recover a lost initialization acknowledgement only from a signed, fully bound provider object.
        if (empty($attempt->provider_session_id)) {
            $candidate = clone $attempt;
            $candidate->provider_session_id = $data['id'] ?? '';
            if (!$this->booking_payment_gateway->matches($candidate,$this->booking_payment_gateway->stripe_result($data))) { $this->respond(400); return; }
            if (!$this->booking_checkout_model->attach_gateway($attempt->id,$candidate->provider_session_id,$data['url'] ?? null)) {
                $this->respond(503); return;
            }
            $attempt = $this->booking_checkout_model->attempt_by_id($attempt->id);
        }
        $this->reconcile($attempt);
    }

    public function paystack()
    {
        $raw = $this->payload();
        if ($raw === null) { return; }
        $secret = get_paystack_secret_key();
        $signature = (string)$this->input->get_request_header('X-Paystack-Signature');
        if (!$secret || !$signature || !hash_equals(hash_hmac('sha512',$raw,$secret),$signature)) { $this->respond(400); return; }
        $event = json_decode($raw,true);
        if (!is_array($event)) { $this->respond(400); return; }
        if (($event['event'] ?? '') !== 'charge.success') { $this->respond(200); return; }
        if (!$this->booking_checkout_model->ready()) { $this->respond(503); return; }
        $attempt = $this->booking_checkout_model->attempt_by_reference($event['data']['reference'] ?? '');
        if (!$attempt || $attempt->provider !== 'paystack') { $this->respond(200); return; }
        $this->reconcile($attempt);
    }

    private function reconcile($attempt)
    {
        $result = $this->booking_payment_reconciler->reconcile($attempt,false);
        $this->respond(!empty($result['status']) || !empty($result['review']) ? 200 : 503);
    }

    private function payload()
    {
        if ($this->input->method() !== 'post') { $this->respond(405); return null; }
        $raw = $this->input->raw_input_stream;
        if (!is_string($raw) || strlen($raw)>262144) { $this->respond(413); return null; }
        return $raw;
    }

    private function respond($status)
    {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode(['received'=>$status===200]));
    }
}
