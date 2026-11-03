<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Application flow behind the existing booking form; no new UI surface. */
class Booking_checkout_flow
{
    private $CI;
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('booking_checkout');
        $this->CI->load->model(['booking_checkout_model','traveller_read_model','travellers_model','users_model','finance_read_model']);
        $this->CI->load->library(['booking_payment_gateway','booking_payment_reconciler']);
    }

    public function intent()
    {
        $token = (string)$this->CI->session->userdata('booking_checkout_token');
        if (strlen($token) !== 64) { $token = bin2hex(random_bytes(32)); $this->CI->session->set_userdata('booking_checkout_token',$token); }
        return $token;
    }

    public function valid_intent(array $post)
    {
        $expected = (string)$this->CI->session->userdata('booking_checkout_token');
        return strlen($expected) === 64 && is_string($post['booking_checkout_token'] ?? null)
            && hash_equals($expected,$post['booking_checkout_token']);
    }

    public function new_request($traveller_id)
    {
        $requests = $this->CI->session->userdata('booking_checkout_requests');
        $requests = is_array($requests) ? $requests : [];
        $nonce = bin2hex(random_bytes(24));
        $requests[$nonce] = ['traveller_id'=>(int)$traveller_id,'created_at'=>time()];
        $requests = array_slice($requests,-25,null,true);
        $this->CI->session->set_userdata('booking_checkout_requests',$requests);
        return $nonce;
    }

    public function owned_booking($hash, $user_id)
    {
        if (!is_string($hash) || $hash === '' || strlen($hash) > 255) { return null; }
        return $this->CI->db->where('hash',$hash)->where('user_id',(int)$user_id)
            ->where('deleted_at IS NULL',null,false)->get('bookings')->row();
    }

    public function restore($booking, $user_id)
    {
        if (!$booking || (int)$booking->user_id !== (int)$user_id) { return $this->failure('This booking is unavailable.'); }
        $check = $this->CI->booking_checkout_model->assert_resumable($booking,$user_id);
        if (empty($check['status'])) { return $check; }
        $checkout = $this->CI->booking_checkout_model->checkout($booking->id,$user_id);
        $form = $checkout ? json_decode($checkout->form_data,true) : null;
        return is_array($form) ? ['status'=>true,'form'=>booking_checkout_form($form)] : $this->failure('This booking cannot be resumed.');
    }

    public function resume(array $post, $user)
    {
        if (!$this->valid_intent($post)) { return $this->failure('Reload History and try again.'); }
        $mode = $post['mode'] ?? 'check';
        if (!in_array($mode,['check','resume'],true)) { return $this->failure('Open History and use the booking action to continue.'); }
        $model = $this->CI->booking_checkout_model;
        if (!$model->ready()) { return $this->failure('Booking recovery is temporarily unavailable.'); }
        $booking = $this->owned_booking($post['booking_hash'] ?? '',$user->id);
        if (!$booking) { return $this->failure('This booking is unavailable.'); }
        if (payment_status_normalize($booking->payment_status) === 'completed') { return $this->paid_result($booking); }
        $state = $model->resume_state($booking,$user->id);
        if (!empty($state['review_required'])) {
            return $this->failure('This payment needs review. Please contact support before paying again.')
                + ['review_required'=>true,'resume_available'=>false,'checking_payment'=>false];
        }
        if ($state['recovery_expired']) {
            return $this->failure($state['checking_payment'] ? 'We are checking this payment.' : 'This booking is canceled.')
                + ['recovery_expired'=>true,'resume_available'=>false,'checking_payment'=>$state['checking_payment'],
                    'resume_until'=>$state['resume_until']];
        }
        $attempt = $model->latest_attempt($booking->id);
        if ($attempt && in_array($attempt->status,['initializing','open','pending'],true)) {
            $result = $this->CI->booking_payment_reconciler->reconcile($attempt,$mode === 'resume');
            if (!empty($result['paid'])) { return $this->paid_result($booking); }
            if (!empty($result['review'])) { return $result + ['review_required'=>true,'resume_available'=>false,'checking_payment'=>false]; }
            if (($result['outcome'] ?? '') === 'open') {
                $state = $model->resume_state($booking,$user->id);
                return $result + ['resume_available'=>$state['resume_available'],'checking_payment'=>!$state['resume_available'],
                    'resume_until'=>$state['resume_until'],'recovery_expired'=>$state['recovery_expired']];
            }
            if (($result['outcome'] ?? '') !== 'unpaid') {
                $state = $model->resume_state($booking,$user->id);
                return $result + ['checking_payment'=>true,'resume_available'=>false,
                    'resume_until'=>$state['resume_until'],'recovery_expired'=>$state['recovery_expired']];
            }
        }
        $check = $model->assert_resumable($booking,$user->id);
        if (empty($check['status'])) {
            $state = $model->resume_state($booking,$user->id);
            return $check + ['resume_available'=>false,'recovery_expired'=>$state['recovery_expired'],'resume_until'=>$state['resume_until']];
        }
        $traveller = $this->CI->travellers_model->update_traveller_space($booking->traveller_id,true);
        if (!$traveller || !$this->CI->traveller_read_model->is_bookable_by_cutoff($traveller)
            || $traveller->status !== 'Approved' || !empty($traveller->bag_locked)
            || (float)$traveller->available_space < (float)$booking->selected_space) {
            return $this->failure('This traveller no longer has enough available space for this booking.')
                + ['resume_available'=>false];
        }
        return ['status'=>true,'redirect'=>base_url('buy-bag-space/'.$traveller->hash.'?resume='.rawurlencode($booking->hash))];
    }

    public function submit(array $post, $user)
    {
        $model = $this->CI->booking_checkout_model;
        if (!$this->valid_intent($post)) { return $this->failure('Reload the booking page and try again.'); }
        if (!$model->ready()) { return $this->failure('Checkout is temporarily unavailable. Please try again later.'); }
        $requests = $this->CI->session->userdata('booking_checkout_requests');
        if (!is_string($post['booking_request_id'] ?? null) || !preg_match('/^[a-f0-9]{48}$/',$post['booking_request_id'])
            || !is_scalar($post['traveller_id'] ?? null) || !ctype_digit((string)$post['traveller_id'])) {
            return $this->failure('Reload the booking page and try again.');
        }
        $request = is_array($requests) ? ($requests[$post['booking_request_id']] ?? null) : null;
        $traveller_id = (int)$post['traveller_id'];
        if (!$request || (int)$request['traveller_id'] !== $traveller_id || $request['created_at'] < time()-86400) {
            return $this->failure('Reload the booking page and try again.');
        }
        $request_key = hash_hmac('sha256',$post['booking_request_id'],$this->intent());
        $booking = !empty($post['resume_booking_hash']) ? $this->owned_booking($post['resume_booking_hash'],$user->id) : null;
        if (!empty($post['resume_booking_hash']) && (!$booking || (int)$booking->traveller_id !== $traveller_id)) { return $this->failure('This booking is unavailable.'); }
        if (!$booking) {
            $checkout = $model->checkout_by_request($request_key,$user->id);
            if ($checkout) { $booking = $this->CI->db->where('id',$checkout->booking_id)->where('user_id',(int)$user->id)->get('bookings')->row(); }
        }
        if ($booking && payment_status_normalize($booking->payment_status) === 'completed') { return $this->paid_result($booking); }
        if ($booking && !empty($model->resume_state($booking,$user->id)['review_required'])) {
            return $this->failure('This payment needs review. Please contact support before paying again.');
        }
        try {
            $form = booking_checkout_form($post);
            $traveller = $this->CI->traveller_read_model->get_traveller_details_by_id($traveller_id);
            if (!$traveller || $traveller->status !== 'Approved' || !empty($traveller->bag_locked)
                || !$this->CI->traveller_read_model->is_bookable_by_cutoff($traveller)) {
                return $this->failure('Bookings for this traveller are no longer available.');
            }
            $currency = booking_route_currency($traveller->location,$traveller->destination);
            $rate_row = $currency === 'CAD' ? $this->CI->finance_read_model->get_most_recent_cad_exchange_rate()
                : $this->CI->finance_read_model->get_most_recent_pound_exchange_rate();
            $quote = booking_checkout_quote($traveller,$form,$rate_row ? $rate_row->rate : 0);
            $quote_token = booking_checkout_quote_token($traveller_id,$form,$quote,$this->intent());
            $posted_quote = is_string($post['price_calculations'] ?? null) ? json_decode($post['price_calculations'],true) : null;
            $quoted_match = is_array($posted_quote)
                && is_numeric($posted_quote['totalAmount'] ?? null) && is_numeric($posted_quote['selectedSpace'] ?? null)
                && is_string($post['currency'] ?? null) && currency_code_normalize($post['currency']) === $quote['currency']
                && abs((float)($posted_quote['totalAmount'] ?? -1)-$quote['total_amount']) < 0.005
                && abs((float)($posted_quote['selectedSpace'] ?? -1)-$quote['selected_space']) < 0.0005
                && ($form['payment_method'] !== 'paystack' || (is_numeric($post['rate'] ?? null)
                    && abs((float)$post['rate']-$quote['exchange_rate']) < 0.000001));
            if (!$quoted_match && !(is_string($post['booking_quote_token'] ?? null) && hash_equals($quote_token,$post['booking_quote_token']))) {
                return ['status'=>false,'quote_changed'=>true,'msg'=>'Review the updated total, then continue to payment.',
                    'pricing'=>$quote,'quote_token'=>$quote_token];
            }
            $previous = $booking ? $model->latest_attempt($booking->id) : null;
            if ($previous && in_array($previous->status,['initializing','open','pending'],true)) {
                return $this->existing_attempt_result($previous,$booking,$form,$quote);
            }
            if ($booking) {
                $check = $model->assert_resumable($booking,$user->id);
                if (empty($check['status'])) { return $check; }
            }
            $db = $this->CI->db;
            if (!$db->trans_begin()) { return $this->failure('Checkout is unavailable. Please try again.'); }
            try {
                // Existing booking rows are locked first; new rows do not yet contend with other bookings.
                if ($booking) { $db->query('SELECT id FROM bookings WHERE id = ?'.$this->lock(),[$booking->id]); }
                $locked_query = $db->query('SELECT * FROM travellers WHERE id = ?'.$this->lock(),[$traveller_id]);
                $locked_traveller = $locked_query ? $locked_query->row() : null;
                if (!$locked_traveller || $locked_traveller->status !== 'Approved' || !empty($locked_traveller->bag_locked)
                    || !$this->CI->traveller_read_model->is_bookable_by_cutoff($locked_traveller)) {
                    $db->trans_rollback(); return $this->failure('Bookings for this traveller are no longer available.');
                }
                $locked_currency = booking_route_currency($locked_traveller->location,$locked_traveller->destination);
                $locked_rate = $locked_currency === 'CAD' ? $this->CI->finance_read_model->get_most_recent_cad_exchange_rate()
                    : $this->CI->finance_read_model->get_most_recent_pound_exchange_rate();
                $locked_quote = booking_checkout_quote($locked_traveller,$form,$locked_rate ? $locked_rate->rate : 0);
                if ($locked_quote != $quote) {
                    $db->trans_rollback();
                    return ['status'=>false,'quote_changed'=>true,'msg'=>'Review the updated total, then continue to payment.',
                        'pricing'=>$locked_quote,'quote_token'=>booking_checkout_quote_token($traveller_id,$form,$locked_quote,$this->intent())];
                }
                $traveller = $locked_traveller;
                if (!$booking) {
                    $existing = $model->checkout_by_request($request_key,$user->id);
                    if ($existing) {
                        $db->trans_rollback();
                        return $this->failure('This checkout is already starting. Open History to check its payment.');
                    }
                } else {
                    $active = $model->latest_attempt($booking->id);
                    if ($active && in_array($active->status,['initializing','open','pending','paid','review'],true)) {
                        $db->trans_rollback(); return $this->failure('Check the existing payment in History before continuing.');
                    }
                }
                // The legacy writer now receives only trusted financial and traveller fields.
                $this->trusted_post($post,$traveller,$quote);
                $booking = $this->CI->users_model->add_booking_to_db($user->id,trim($user->firstname.' '.$user->lastname),$user->email,$booking ? $booking->id : 0);
                if (!$booking) { throw new RuntimeException('Booking write failed.'); }
                $form['_request_key'] = $request_key;
                if (!$model->capture($booking,$form) || !$db->trans_status()) { throw new RuntimeException('Checkout snapshot failed.'); }
                $claim = $model->create_attempt($booking,$user->id,$form['payment_method'],$quote['charge_minor'],$quote['charge_currency'],$quote['exchange_rate'],$quote);
                if (empty($claim['status']) || empty($claim['attempt']) || !$db->trans_status()) {
                    $db->trans_rollback(); return array_merge($claim,['status'=>false,'msg'=>$claim['msg'] ?? 'Space is no longer available for this booking.']);
                }
                if (!$db->trans_commit()) { throw new RuntimeException('Checkout commit failed.'); }
            } catch (Throwable $error) {
                $db->trans_rollback(); log_message('error','Booking checkout transaction could not complete.');
                return $this->failure('We could not start checkout. Please try again.');
            }
            $attempt = $claim['attempt'];
            $gateway = $this->CI->booking_payment_gateway->initialize($attempt,$booking);
            if (empty($gateway['status'])) {
                if (($gateway['outcome'] ?? '') === 'not_created') {
                    $model->end_attempt($attempt->id,'failed','Gateway did not create a payment.');
                    return $this->failure('We could not start payment. Please try again or select the other payment method.');
                }
                $model->mark_unknown($attempt->id,'Gateway initialization outcome is unknown.');
                return $this->failure('We could not open the payment page. Check this payment in History before trying again.');
            }
            if (!$model->attach_gateway($attempt->id,$gateway['session_id'],$gateway['url'])) {
                $model->mark_unknown($attempt->id,'Gateway opened but local acknowledgement failed.');
                return $this->failure('We are checking this payment. Open History before trying again.');
            }
            return ['status'=>true,'redirect'=>$gateway['url'],'msg'=>'Opening payment.'];
        } catch (InvalidArgumentException $error) { return $this->failure($error->getMessage()); }
    }

    private function existing_attempt_result($attempt, $booking, array $form, array $quote)
    {
        $checkout = $this->CI->booking_checkout_model->checkout($booking->id,$booking->user_id);
        $original = $checkout ? json_decode($checkout->form_data,true) : [];
        foreach (['_booking_step','category','item','size','booking_request_id'] as $key) { unset($form[$key],$original[$key]); }
        if ($attempt->status === 'open' && strtotime($attempt->hold_until.' UTC') > time()
            && $attempt->provider === ($form['payment_method'] ?? '') && (int)$attempt->amount_minor === $quote['charge_minor']
            && $form == $original && !empty($attempt->authorization_url)) {
            return ['status'=>true,'redirect'=>$attempt->authorization_url];
        }
        return $this->failure('An earlier payment needs to be checked. Open History before continuing.');
    }

    private function trusted_post(array $post, $traveller, array $quote)
    {
        foreach ($post as $key=>$value) { if (is_scalar($value)) { $_POST[$key]=$value; } }
        $_POST['items'] = json_encode($quote['items']);
        $_POST['price_calculations'] = json_encode(['selectedSpace'=>$quote['selected_space'],'selectedPrice'=>$quote['selected_price'],
            'subTotal'=>$quote['sub_total'],'serviceCharge'=>$quote['service_charge'],'insurance'=>$quote['insurance'],
            'totalAmount'=>$quote['base_total']]);
        $_POST['traveller_commission'] = $quote['traveller_commission'];
        foreach (['name'=>'fullname','email'=>'email','contact'=>'phone','departure_date'=>'travel_date','arrival_date'=>'arrival_date',
            'departure_state'=>'departure_state','drop_address1'=>'drop_address1','drop_date1'=>'drop_date1',
            'drop_address2'=>'drop_address2','drop_date2'=>'drop_date2','current_state'=>'current_state',
            'arrival_airport'=>'arrival_airport','arrival_state'=>'arrival_state','destination'=>'destination'] as $field=>$property) {
            $_POST['traveller_'.$field] = $traveller->$property ?? '';
        }
    }

    private function lock() { return $this->CI->db->dbdriver === 'sqlite3' ? '' : ' FOR UPDATE'; }
    private function failure($message) { return ['status'=>false,'msg'=>$message]; }
    private function paid_result($booking)
    {
        $this->CI->session->set_flashdata('booking_success_id',(int)$booking->id);
        return ['status'=>true,'paid'=>true,'redirect'=>base_url('booking-success')];
    }
}
