<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/booking_capacity_helper.php';

/** Payment attempts and capacity leases. Network calls and notifications stay outside transactions. */
class Booking_checkout_model extends CI_Model
{
    private const ACTIVE = ['initializing', 'open', 'pending'];
    private const TERMINAL = ['failed', 'canceled', 'expired'];
    private const FORM_FIELDS = [
        'items',
        'category',
        'item',
        'size',
        'insurance',
        'parcel_status',
        'receiver_status',
        'agent_name',
        'agent_country_code',
        'agent_phone',
        'agent_email',
        'agent_address',
        'agent_locality',
        'agent_postcode',
        'receiver_name',
        'receiver_country_code',
        'receiver_phone',
        'receiver_email',
        'receiver_address',
        'receiver_locality',
        'receiver_postcode',
        'payment_method',
        '_booking_step'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function ready()
    {
        return $this->db->table_exists('booking_checkouts') && $this->db->table_exists('booking_payment_attempts');
    }

    public function capture($booking, array $form)
    {
        if (!$this->ready() || !$booking || (int) $booking->user_id <= 0 || $this->is_paid($booking)) {
            return false;
        }
        $safe = [];
        foreach (self::FORM_FIELDS as $field) {
            if (isset($form[$field]) && is_scalar($form[$field])) {
                $safe[$field] = (string) $form[$field];
            }
        }
        $json = json_encode($safe, JSON_UNESCAPED_UNICODE);
        if ($json === false || strlen($json) > 131072) {
            return false;
        }
        $existing = $this->checkout($booking->id, $booking->user_id);
        if ($existing) {
            return $this->db->where('booking_id', (int) $booking->id)->where('user_id', (int) $booking->user_id)
                ->update('booking_checkouts', ['form_data' => $json, 'updated_at' => $this->now()]);
        }
        return $this->db->insert('booking_checkouts', [
            'booking_id' => (int) $booking->id,
            'user_id' => (int) $booking->user_id,
            'form_data' => $json,
            'resume_until' => null,
            'request_key' => isset($form['_request_key']) && preg_match('/^[a-f0-9]{64}$/', (string) $form['_request_key']) ? $form['_request_key'] : null,
            'created_at' => $this->now(),
            'updated_at' => $this->now()
        ]);
    }

    public function checkout_by_request($request_key, $user_id)
    {
        if (!$this->ready() || !is_string($request_key) || !preg_match('/^[a-f0-9]{64}$/', $request_key)) {
            return null;
        }
        return $this->db->where('request_key', $request_key)->where('user_id', (int) $user_id)->get('booking_checkouts')->row();
    }

    public function checkout($booking_id, $user_id)
    {
        if (!$this->ready()) {
            return null;
        }
        return $this->db->where('booking_id', (int) $booking_id)->where('user_id', (int) $user_id)->get('booking_checkouts')->row();
    }

    public function latest_attempt($booking_id)
    {
        if (!$this->ready()) {
            return null;
        }
        return $this->db->where('booking_id', (int) $booking_id)->order_by('id', 'DESC')->limit(1)->get('booking_payment_attempts')->row();
    }

    public function attempt_by_reference($reference)
    {
        if (!$this->ready() || !is_string($reference) || strlen($reference) > 80) {
            return null;
        }
        return $this->db->where('reference', $reference)->get('booking_payment_attempts')->row();
    }

    public function attempt_by_id($id)
    {
        if (!$this->ready()) {
            return null;
        }
        return $this->db->where('id', (int) $id)->get('booking_payment_attempts')->row();
    }

    public function resume_state($booking, $user_id)
    {
        $state = [
            'resume_available' => false,
            'checking_payment' => false,
            'review_required' => false,
            'recovery_expired' => false,
            'expiry_checking_payment' => false,
            'message' => '',
            'resume_until' => 0
        ];
        if (
            !$this->ready() || !$booking || (int) $booking->user_id !== (int) $user_id
            || !empty($booking->deleted_at) || !empty($booking->cancelled_at) || $this->is_paid($booking)
            || !$this->checkout($booking->id, $user_id)
        ) {
            return $state;
        }
        $checkout = $this->checkout($booking->id, $user_id);
        $state['resume_until'] = $checkout->resume_until ? strtotime($checkout->resume_until . ' UTC') : strtotime($checkout->created_at . ' UTC') + 900;
        $state['recovery_expired'] = $state['resume_until'] <= time();
        if ($this->has_review($booking->id)) {
            $state['review_required'] = true;
            $state['message'] = 'This payment needs review. Please contact support before paying again.';
            return $state;
        }
        $attempt = $this->latest_attempt($booking->id);
        $state['expiry_checking_payment'] = $attempt && in_array($attempt->status, self::ACTIVE, true);
        if ($attempt && in_array($attempt->status, ['initializing', 'pending'], true)) {
            $state['checking_payment'] = true;
            return $state;
        }
        $result = $this->assert_resumable($booking, $user_id);
        if ($attempt && $attempt->status === 'open') {
            $state['resume_available'] = $result['status'];
            $state['checking_payment'] = !$result['status'];
        } else {
            $state['resume_available'] = $result['status'];
            if (!$result['status'] && !$state['recovery_expired']) {
                $state['message'] = $result['msg'];
            }
        }
        return $state;
    }

    public function assert_resumable($booking, $user_id)
    {
        if (
            !$this->ready() || !$booking || (int) $booking->user_id !== (int) $user_id || !empty($booking->deleted_at)
            || !empty($booking->cancelled_at) || $this->is_paid($booking)
        ) {
            return $this->failure('This booking is not available to resume.');
        }
        $checkout = $this->checkout($booking->id, $user_id);
        if (!$checkout) {
            return $this->failure('This booking is not available to resume.');
        }
        $until = $checkout->resume_until ?: gmdate('Y-m-d H:i:s', strtotime($checkout->created_at . ' UTC') + 900);
        if ($until <= $this->now()) {
            return $this->failure('This booking can no longer be resumed.');
        }
        $latest = $this->latest_attempt($booking->id);
        if ($this->has_review($booking->id)) {
            return $this->failure('This payment needs review. Please contact support before paying again.');
        }
        $traveller = $this->db->where('id', (int) $booking->traveller_id)->get('travellers')->row();
        $trip_error = $this->trip_error($traveller);
        if ($trip_error) {
            return $this->failure($trip_error);
        }
        $capacity = booking_capacity_snapshot($this->db, $traveller, $latest && in_array($latest->status, self::ACTIVE, true) ? $latest->id : 0);
        if (!$capacity || $capacity['available'] + 0.0005 < (float) $booking->selected_space) {
            return $this->failure('This traveller no longer has enough space for this booking.');
        }
        return ['status' => true, 'msg' => ''];
    }

    public function create_attempt($booking, $user_id, $provider, $amount_minor, $currency, $exchange_rate, array $quote)
    {
        if (
            !$this->ready() || !$booking || !in_array($provider, ['stripe', 'paystack'], true)
            || !is_int($amount_minor) || $amount_minor <= 0 || !preg_match('/^[A-Z]{3}$/', (string) $currency)
        ) {
            return $this->failure('We could not prepare this payment.');
        }
        return $this->transaction(function () use ($booking, $user_id, $provider, $amount_minor, $currency, $exchange_rate, $quote) {
            $current = $this->lock_booking($booking->id);
            if (
                !$current || (int) $current->user_id !== (int) $user_id || !empty($current->deleted_at)
                || !empty($current->cancelled_at) || $this->is_paid($current)
            ) {
                return $this->failure('This booking is not available for payment.');
            }
            $traveller = booking_lock_traveller($this->db, $current->traveller_id);
            $trip_error = $this->trip_error($traveller);
            if ($trip_error) {
                return $this->failure($trip_error);
            }
            $user = $this->db->where('id', (int) $user_id)->get('users')->row();
            if (!$user || (int) $user->account_status !== 1 || !empty($user->deleted_at)) {
                return $this->failure('This account cannot start a payment.');
            }
            $latest = $this->latest_attempt($current->id);
            if ($this->has_review($current->id)) {
                return $this->failure('This payment needs review. Please contact support before paying again.');
            }
            if ($latest && in_array($latest->status, self::ACTIVE, true)) {
                return [
                    'status' => false,
                    'reuse' => true,
                    'attempt' => $latest,
                    'msg' => 'We are checking your previous payment. Please do not pay again.'
                ];
            }
            if ($latest) {
                $resumable = $this->assert_resumable($current, $user_id);
                if (!$resumable['status']) {
                    return $resumable;
                }
            }
            $capacity = booking_capacity_snapshot($this->db, $traveller);
            $space = (float) $current->selected_space;
            if (!$capacity || $space <= 0 || $capacity['available'] + 0.0005 < $space) {
                return $this->failure('This traveller no longer has enough space for this booking.');
            }
            $now = $this->now();
            $row = [
                'booking_id' => (int) $current->id,
                'user_id' => (int) $user_id,
                'traveller_id' => (int) $current->traveller_id,
                'provider' => $provider,
                'reference' => 'SMB' . bin2hex(random_bytes(24)),
                'provider_session_id' => null,
                'authorization_url' => null,
                'status' => 'initializing',
                'amount_minor' => (int) $amount_minor,
                'currency' => $currency,
                'exchange_rate' => (float) $exchange_rate,
                'quote_data' => json_encode($quote, JSON_UNESCAPED_UNICODE),
                'hold_space' => $space,
                'hold_until' => gmdate('Y-m-d H:i:s', time() + 900),
                'created_at' => $now,
                'updated_at' => $now,
                'confirmed_at' => null,
                'review_reason' => null
            ];
            if (!$this->db->insert('booking_payment_attempts', $row)) {
                return $this->failure('We could not prepare this payment.');
            }
            $row['id'] = (int) $this->db->insert_id();
            if (!$this->db->where('id', (int) $current->id)->update('bookings', ['payment_status' => 'pending'])) {
                return $this->failure('We could not prepare this booking.');
            }
            if (!$this->write_capacity($traveller)) {
                return $this->failure('We could not reserve bag space.');
            }
            return ['status' => true, 'attempt' => (object) $row, 'msg' => ''];
        });
    }

    public function attach_gateway($id, $session_id, $url = null)
    {
        $attempt = $this->attempt_by_id($id);
        if (!$attempt || ($url !== null && (!filter_var($url, FILTER_VALIDATE_URL) || stripos($url, 'https://') !== 0))) {
            return false;
        }
        if ($attempt->provider === 'stripe' && (!is_string($session_id) || $session_id === '' || strlen($session_id) > 255)) {
            return false;
        }
        if ($url === null && ($attempt->provider !== 'stripe' || !is_string($session_id) || $session_id === '' || strlen($session_id) > 255)) {
            return false;
        }
        if ($session_id !== null && strlen((string) $session_id) > 255) {
            return false;
        }
        if (!empty($attempt->provider_session_id) && !hash_equals($attempt->provider_session_id, (string) $session_id)) {
            return false;
        }
        return $this->db->where('id', (int) $id)->where_in('status', ['initializing', 'pending'])
            ->update('booking_payment_attempts', [
                'provider_session_id' => $session_id ?: null,
                'authorization_url' => $url ?? $attempt->authorization_url,
                'status' => 'open',
                'updated_at' => $this->now()
            ]);
    }

    public function mark_unknown($id, $reason = '')
    {
        $snapshot = $this->attempt_by_id($id);
        if (!$snapshot) {
            return false;
        }
        $result = $this->transaction(function () use ($snapshot, $reason) {
            $booking = $this->lock_booking($snapshot->booking_id);
            if (!$booking) {
                return $this->failure('Booking not found.');
            }
            $traveller = booking_lock_traveller($this->db, $snapshot->traveller_id);
            $attempt = $this->lock_attempt($snapshot->id);
            if (!$attempt || !in_array($attempt->status, self::ACTIVE, true)) {
                return ['status' => true];
            }
            if (!$this->db->where('id', (int) $attempt->id)->where_in('status', self::ACTIVE)
                ->update('booking_payment_attempts', [
                    'status' => 'pending',
                    'updated_at' => $this->now(),
                    'review_reason' => substr((string) $reason, 0, 255)
                ])) {
                return $this->failure('Unable to update payment.');
            }
            // Elapsed leases no longer consume capacity, even when the provider still needs checking.
            if ($traveller && empty($traveller->deleted_at) && !$this->write_capacity($traveller)) {
                return $this->failure('Unable to refresh bag space.');
            }
            return ['status' => true];
        });
        return $result['status'];
    }

    /** Only call after provider verification establishes an unpaid terminal outcome. */
    public function end_attempt($id, $status, $reason = '')
    {
        if (!$this->ready() || !in_array($status, self::TERMINAL, true)) {
            return false;
        }
        $snapshot = $this->attempt_by_id($id);
        if (!$snapshot) {
            return false;
        }
        $result = $this->transaction(function () use ($snapshot, $status, $reason) {
            $booking = $this->lock_booking($snapshot->booking_id);
            if (!$booking) {
                return $this->failure('Booking not found.');
            }
            $traveller = booking_lock_traveller($this->db, $snapshot->traveller_id);
            $attempt = $this->lock_attempt($snapshot->id);
            if (!$attempt || !in_array($attempt->status, self::ACTIVE, true)) {
                return ['status' => true, 'msg' => ''];
            }
            if (!$this->db->where('id', $attempt->id)->where_in('status', self::ACTIVE)
                ->update('booking_payment_attempts', [
                    'status' => $status,
                    'updated_at' => $this->now(),
                    'review_reason' => substr((string) $reason, 0, 255)
                ])) {
                return $this->failure('Unable to update payment status.');
            }
            $deadline = min(time() + 900, strtotime($attempt->created_at . ' UTC') + 1800);
            if (!$this->db->where('booking_id', $booking->id)->where('resume_until IS NULL', null, false)
                ->update('booking_checkouts', ['resume_until' => gmdate('Y-m-d H:i:s', $deadline), 'updated_at' => $this->now()])) {
                return $this->failure('Unable to update recovery window.');
            }
            if ($traveller && empty($traveller->deleted_at) && !$this->write_capacity($traveller)) {
                return $this->failure('Unable to update bag space.');
            }
            return ['status' => true, 'msg' => ''];
        });
        return $result['status'];
    }

    public function apply_verified($id, array $verified)
    {
        $snapshot = $this->attempt_by_id($id);
        if (!$snapshot) {
            return $this->failure('Payment not found.');
        }
        return $this->transaction(function () use ($snapshot, $verified) {
            $booking = $this->lock_booking($snapshot->booking_id);
            if (!$booking) {
                return $this->failure('Booking not found.');
            }
            $traveller = booking_lock_traveller($this->db, $snapshot->traveller_id);
            $attempt = $this->lock_attempt($snapshot->id);
            if (!$attempt || !$this->matches_verified($attempt, $verified)) {
                return $this->failure('We could not verify this payment.');
            }
            if ($attempt->status === 'paid') {
                $still_booked = $this->is_paid($booking) && empty($booking->deleted_at) && empty($booking->cancelled_at);
                return [
                    'status' => true,
                    'paid' => $still_booked,
                    'newly_paid' => false,
                    'booking_id' => (int) $booking->id,
                    'msg' => $still_booked ? 'Your booking payment is confirmed.' : 'This payment has already been recorded.'
                ];
            }
            if ($attempt->status === 'review') {
                return [
                    'status' => true,
                    'paid' => false,
                    'newly_paid' => false,
                    'review' => true,
                    'newly_reviewed' => false,
                    'booking_id' => (int) $booking->id,
                    'msg' => 'Your payment needs review. Please contact support before paying again.'
                ];
            }
            $latest = $this->latest_attempt($booking->id);
            $capacity = $traveller ? booking_capacity_snapshot($this->db, $traveller, $attempt->id) : false;
            $invalid = !$latest || (int) $latest->id !== (int) $attempt->id || $this->is_paid($booking) || $this->has_review($booking->id)
                || !empty($booking->deleted_at) || !empty($booking->cancelled_at) || $this->trip_error($traveller)
                || !in_array($attempt->status, self::ACTIVE, true) || $attempt->hold_until <= $this->now()
                || (int) $booking->traveller_id !== (int) $attempt->traveller_id
                || abs((float) $booking->selected_space - (float) $attempt->hold_space) > 0.0005
                || !$capacity || $capacity['available'] + 0.0005 < (float) $attempt->hold_space;
            if ($invalid) {
                if (!$this->db->where('id', $attempt->id)->update('booking_payment_attempts', [
                    'status' => 'review',
                    'confirmed_at' => $this->now(),
                    'updated_at' => $this->now(),
                    'review_reason' => 'Payment received outside its valid allocation; reconciliation/refund review required.'
                ])) {
                    return $this->failure('Unable to record payment review.');
                }
                if ($traveller && empty($traveller->deleted_at) && !$this->write_capacity($traveller)) {
                    return $this->failure('Payment needs review.');
                }
                return [
                    'status' => true,
                    'paid' => false,
                    'newly_paid' => false,
                    'review' => true,
                    'newly_reviewed' => true,
                    'booking_id' => (int) $booking->id,
                    'msg' => 'Your payment needs review. Please contact support before paying again.'
                ];
            }
            if (!$this->db->where('id', $attempt->id)->where_in('status', self::ACTIVE)
                ->update('booking_payment_attempts', [
                    'status' => 'paid',
                    'confirmed_at' => $this->now(),
                    'updated_at' => $this->now(),
                    'review_reason' => null
                ])) {
                return $this->failure('Unable to confirm payment.');
            }
            if (!$this->db->where('id', $booking->id)->where("LOWER(TRIM(COALESCE(payment_status,''))) NOT IN ('completed','complete','success','paid')", null, false)
                ->update('bookings', ['payment_status' => 'completed', 'new' => 0]) || $this->db->affected_rows() !== 1) {
                return $this->failure('Unable to confirm booking.');
            }
            if (!$this->write_capacity($traveller)) {
                return $this->failure('Unable to confirm bag space.');
            }
            return [
                'status' => true,
                'paid' => true,
                'newly_paid' => true,
                'booking_id' => (int) $booking->id,
                'msg' => 'Your booking payment is confirmed.'
            ];
        });
    }

    public function expired_attempts($limit = 20)
    {
        if (!$this->ready()) {
            return [];
        }
        return $this->db->where_in('status', self::ACTIVE)->where('hold_until <=', $this->now())
            ->order_by('updated_at', 'ASC')->order_by('id', 'ASC')->limit(min(100, max(1, (int) $limit)))->get('booking_payment_attempts')->result();
    }

    public function reconcilable_attempts($limit = 20)
    {
        if (!$this->ready()) {
            return [];
        }
        return $this->db->where_in('status', self::ACTIVE)->where('updated_at <=', gmdate('Y-m-d H:i:s', time() - 30))
            ->order_by('updated_at', 'ASC')->order_by('id', 'ASC')->limit(min(100, max(1, (int) $limit)))
            ->get('booking_payment_attempts')->result();
    }

    private function matches_verified($attempt, array $verified)
    {
        if (($verified['outcome'] ?? '') !== 'paid' || ($verified['provider'] ?? '') !== $attempt->provider
            || !hash_equals($attempt->reference, (string) ($verified['reference'] ?? ''))
            || (int) ($verified['booking_id'] ?? 0) !== (int) $attempt->booking_id
            || (int) ($verified['user_id'] ?? 0) !== (int) $attempt->user_id
            || !isset($verified['amount_minor']) || !is_numeric($verified['amount_minor'])
            || (int) $verified['amount_minor'] !== (int) $attempt->amount_minor
            || (float) $verified['amount_minor'] !== (float) $attempt->amount_minor
            || ($verified['currency'] ?? '') !== $attempt->currency
        ) {
            return false;
        }
        return $attempt->provider !== 'stripe' || (!empty($attempt->provider_session_id)
            && hash_equals($attempt->provider_session_id, (string) ($verified['provider_session_id'] ?? '')));
    }

    private function has_review($booking_id)
    {
        return $this->db->where('booking_id', (int) $booking_id)->where('status', 'review')->count_all_results('booking_payment_attempts') > 0;
    }

    private function trip_error($traveller)
    {
        if (
            !$traveller || !empty($traveller->deleted_at) || strcasecmp(trim((string) $traveller->status), 'Approved') !== 0
            || (int) ($traveller->bag_locked ?? 0) === 1
        ) {
            return 'This traveller is no longer available for booking.';
        }
        if (!traveller_is_bookable_by_cutoff($traveller->travel_date)) {
            return 'Bookings for this departure have closed.';
        }
        return '';
    }

    private function write_capacity($traveller)
    {
        $snapshot = booking_capacity_snapshot($this->db, $traveller);
        return $snapshot && !$snapshot['overcommitted'] && $this->db->where('id', $traveller->id)->update(
            'travellers',
            ['used_space' => $snapshot['used'], 'available_space' => $snapshot['available']]
        );
    }

    private function lock_booking($id)
    {
        $query = $this->db->query('SELECT * FROM bookings WHERE id = ?' . $this->lock_sql(), [(int) $id]);
        return $query ? $query->row() : null;
    }

    private function lock_attempt($id)
    {
        $query = $this->db->query('SELECT * FROM booking_payment_attempts WHERE id = ?' . $this->lock_sql(), [(int) $id]);
        return $query ? $query->row() : null;
    }

    private function lock_sql()
    {
        return $this->db->dbdriver === 'sqlite3' ? '' : ' FOR UPDATE';
    }
    private function now()
    {
        return gmdate('Y-m-d H:i:s');
    }
    private function is_paid($booking)
    {
        return in_array(strtolower(trim((string) $booking->payment_status)), ['completed', 'complete', 'success', 'paid'], true);
    }
    private function failure($message)
    {
        return ['status' => false, 'msg' => $message];
    }

    private function transaction(callable $operation)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            if (!$this->db->trans_begin()) {
                return $this->failure('Payment processing is temporarily unavailable.');
            }
            $result = $operation();
            if (empty($result['status'])) {
                $this->db->trans_rollback();
                return $result;
            }
            if (!$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $this->failure('Payment processing is temporarily unavailable.');
            }
            if (!$this->db->trans_commit()) {
                $this->db->trans_rollback();
                return $this->failure('Payment processing is temporarily unavailable.');
            }
            return $result;
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            log_message('error', 'Booking checkout transaction failed.');
            return $this->failure('Payment processing is temporarily unavailable.');
        } finally {
            $this->db->db_debug = $debug;
        }
    }
}
