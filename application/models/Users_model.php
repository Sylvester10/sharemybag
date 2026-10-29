<?php
defined('BASEPATH') or exit('Direct access to script not allowed');
require_once APPPATH . 'helpers/signin_code_helper.php';
require_once APPPATH . 'helpers/registration_helper.php';


class Users_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->load->model('user_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('booking_read_model');
        $this->load->model('travellers_model');
        $this->load->model('finance_read_model');
        $this->load->model('shipping_read_model');
        $this->primary_cols = array('id');

        // Don't load traveller details on every single model instantiation unless needed.
        if ($this->session->userdata('id')) {
            $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
        }
    }



    // public function add_new_user()
    // {
    //     // Generate and update verification code
    //     $verification_code = generate_verification_code();
    //     $pass_reset_code = generate_verification_code();
    //     $ref_num = mt_rand(11111, 99999);
    //     $username = strtolower($this->input->post('firstname', TRUE)) . $ref_num;

    //     $data = extractKeys($this->input->post(), $this->getColumns());
    //     $referral_link = base_url('travellers' . '/' . '?refer=' . $username);
    //     $email = $this->input->post('email', TRUE);

    //     $country_code = $this->input->post('country_code', TRUE);
    //     $phone = $this->input->post('phone', TRUE);

    //     $number = $country_code . "" . $phone;

    //     $data = array(
    //         'verification_code' => $verification_code,
    //         'pass_reset_code' => $pass_reset_code,
    //         'account_status' => 0,
    //         'is_verified' => 0,
    //         'username' => $username,
    //         'firstname' => $this->input->post('firstname', TRUE),
    //         'lastname' => $this->input->post('lastname', TRUE),
    //         'email' => $email,
    //         'number' => $number,
    //         'country' => $this->input->post('country', TRUE),
    //         'referral_link' => $referral_link,
    //         'account_status' => 1,
    //     );


    //     $this->db->insert('users', $data);

    //     // Get the last inserted user ID
    //     $user_id = $this->db->insert_id();

    //     // Send email to User
    //     send_email_notification($this, $email, 'Email Address Verification', $data, 'user_code_verification_email');

    //     return $user_id; // Return user ID
    // }

    public function add_new_user()
    {
        $pass_reset_code = generate_verification_code();
        $ref_num = mt_rand(11111, 99999);
        $firstname = trim($this->input->post('firstname', TRUE));
        $username = strtolower($firstname) . $ref_num;

        $email = strtolower(trim($this->input->post('email', TRUE)));
        $number = normalize_phone_number(
            $this->input->post('country_code', TRUE),
            $this->input->post('phone', TRUE)
        );
        $referral_link = base_url('travellers/?refer=' . $username);

        // SECURITY: Explicitly define ONLY the fields you want users to submit.
        $data = array(
            'firstname'         => $firstname,
            'lastname'          => trim($this->input->post('lastname', TRUE)),
            'email'             => $email,
            'number'            => $number,
            'country'           => trim($this->input->post('country', TRUE)),
            'username'          => $username,
            'referral_link'     => $referral_link,
            'verification_code' => null,
            'pass_reset_code'   => $pass_reset_code,
            'account_status'    => 1, // Set to 1 explicitly
            'is_verified'       => VERIFY_NONE,
        );

        if ($this->db->field_exists('verification_code_expires_at', 'users')) {
            $data['verification_code_expires_at'] = null;
        }
        if ($this->db->field_exists('verification_attempts', 'users')) {
            $data['verification_attempts'] = 0;
        }
        if ($this->db->field_exists('verification_locked_until', 'users')) {
            $data['verification_locked_until'] = null;
        }

        if (!$this->db->insert('users', $data)) { return false; }
        $user_id = $this->db->insert_id();
        $this->user_read_model->clearUserCountCaches();

        return $user_id;
    }


    public function resend_verification_code($user_id)
    {
        // Get user data for sending the email
        $user = $this->user_read_model->get_user_details_by_id($user_id);
        if (!$user) {
            return false; // Handle case where user is not found
        }

        $verification_code = $user->verification_code; // Fetch the existing verification code
        $email = $user->email;

        // Prepare email data
        $data = [
            'verification_code' => $verification_code,
            'firstname' => $user->firstname,
        ];

        // Send email to User
        return send_email_notification($this, $email, 'Email Address Verification', $data, 'user_code_verification_email');
    }


    public function get_user_by_verification_code($verification_code)
    {
        $query = $this->db->where(['verification_code' => $verification_code])->where('deleted_at IS NULL', null, false)->get('users');
        return $query->row();
    }


    public function update_user_verification($user_id, $password, $verification_code, $resume_token, $email = null)
    {
        if ((string) $verification_code === '' || (string) $resume_token === '') {
            return false;
        }
        $data = [
            'password' => $password,
            'verification_code' => NULL // Clear old code after verification
        ];
        if ($this->db->field_exists('email_verified_at', 'users')) {
            $data['email_verified_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('verification_code_expires_at', 'users')) {
            $data['verification_code_expires_at'] = null;
        }
        if ($this->db->field_exists('verification_attempts', 'users')) {
            $data['verification_attempts'] = 0;
        }
        if ($this->db->field_exists('verification_locked_until', 'users')) {
            $data['verification_locked_until'] = null;
        }
        if ($this->db->field_exists('signup_resume_token', 'users')) {
            $data['signup_resume_token'] = null;
        }
        if ($this->db->field_exists('signup_resume_expires_at', 'users')) {
            $data['signup_resume_expires_at'] = null;
        }
        // Atomically consume this pending signup. Only the winning request may send welcome.
        $this->db->where('id', $user_id);
        $this->db->where('verification_code', $verification_code);
        $this->db->where('signup_resume_token', $resume_token);
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('account_status', 1);
        if ($email !== null) { $this->db->where('email', $email); }
        $this->db->group_start()->where('password IS NULL', null, false)->or_where('password', '')->group_end();
        if ($this->db->field_exists('verification_code_expires_at', 'users')) {
            $this->db->group_start()->where('verification_code_expires_at IS NULL', null, false)
                ->or_where('verification_code_expires_at >', date('Y-m-d H:i:s'))->group_end();
        }
        if ($this->db->field_exists('signup_resume_expires_at', 'users')) {
            $this->db->where('signup_resume_expires_at >', date('Y-m-d H:i:s'));
        }
        if ($this->db->field_exists('verification_locked_until', 'users')) {
            $this->db->group_start()->where('verification_locked_until IS NULL', null, false)
                ->or_where('verification_locked_until <=', date('Y-m-d H:i:s'))->group_end();
        }
        $updated = $this->db->update('users', $data);
        return $updated && $this->db->affected_rows() === 1;
    }


    public function update_user_verification_code($user_id, $new_code)
    {
        $data = ['verification_code' => $new_code];
        if ($this->db->field_exists('verification_code_expires_at', 'users')) {
            $data['verification_code_expires_at'] = date('Y-m-d H:i:s', time() + 900);
        }
        if ($this->db->field_exists('verification_attempts', 'users')) {
            $data['verification_attempts'] = 0;
        }
        if ($this->db->field_exists('verification_locked_until', 'users')) {
            $data['verification_locked_until'] = null;
        }
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
    }


    public function clear_verification_security_state($user_id)
    {
        $data = [];
        if ($this->db->field_exists('verification_code_expires_at', 'users')) {
            $data['verification_code_expires_at'] = null;
        }
        if ($this->db->field_exists('verification_attempts', 'users')) {
            $data['verification_attempts'] = 0;
        }
        if ($this->db->field_exists('verification_locked_until', 'users')) {
            $data['verification_locked_until'] = null;
        }

        if (!empty($data)) {
            $this->db->where('id', $user_id);
            $this->db->update('users', $data);
        }
    }

    public function issue_signup_resume_token($user_id, $ttl_seconds = 86400)
    {
        if (!$this->db->field_exists('signup_resume_token', 'users') || !$this->db->field_exists('signup_resume_expires_at', 'users')) {
            return null;
        }

        $user = $this->user_read_model->get_user_details_by_id($user_id);
        if (!registration_can_resume($user)) { return null; }
        $token = !empty($user->signup_resume_token) && strtotime((string) $user->signup_resume_expires_at) > time()
            ? $user->signup_resume_token : bin2hex(random_bytes(32));
        $this->pending_signup_where($user_id);
        $this->db->where('signup_resume_token', $user->signup_resume_token);
        $ok = $this->db->update('users', [
            'signup_resume_token' => $token,
            'signup_resume_expires_at' => date('Y-m-d H:i:s', time() + $ttl_seconds),
        ]);
        // A concurrent request may have already issued a different token. Return persisted truth.
        $current = $this->user_read_model->get_user_details_by_id($user_id);
        return $ok && registration_can_resume($current) ? $current->signup_resume_token : null;
    }

    private function pending_signup_where($user_id)
    {
        $this->db->where('id', (int) $user_id)->where('account_status', 1)
            ->where('deleted_at IS NULL', null, false)
            ->group_start()->where('password IS NULL', null, false)->or_where('password', '')->group_end();
    }

    public function get_signup_resume_user($token)
    {
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) { return null; }
        $user = $this->user_read_model->get_user_by_signup_resume_token($token);
        return registration_can_resume($user) && strtotime((string) $user->signup_resume_expires_at) > time() ? $user : null;
    }

    /** Claim issuance before SMTP so concurrent requests cannot send competing codes. */
    public function send_signup_verification_code($user_id, $token)
    {
        $failure = ['status'=>false, 'msg'=>'Your details are saved, but we could not send a verification code. Please try again.'];
        $user = $this->get_signup_resume_user($token);
        if (!$user || (int) $user->id !== (int) $user_id) { return $failure; }
        foreach (['verification_code_expires_at', 'verification_attempts', 'verification_locked_until'] as $field) {
            if (!$this->db->field_exists($field, 'users')) { return $failure; }
        }
        if (!empty($user->verification_locked_until) && strtotime($user->verification_locked_until) > time()) {
            return ['status'=>false, 'msg'=>'Too many incorrect verification attempts. Please try again after the verification lock expires.'];
        }
        $keys = ['registration-code:user:' . (int) $user_id => 3, 'registration-code:ip:' . get_user_ip() => 15];
        foreach ($keys as $key=>$max) {
            $limit = auth_throttle_check($key, $max, 900);
            if (!$limit['allowed']) { return ['status'=>false, 'msg'=>auth_throttle_message($limit['retry_after'], 'verification code request')]; }
        }
        $now = time();
        $code = generate_verification_code();
        $this->pending_signup_where($user_id);
        $this->db->where('email', $user->email)->where('signup_resume_token', $token)
            ->where('signup_resume_expires_at >', date('Y-m-d H:i:s', $now))
            ->group_start()->where('verification_locked_until IS NULL', null, false)->or_where('verification_locked_until <=', date('Y-m-d H:i:s', $now))->group_end()
            ->group_start()->where('verification_code_expires_at IS NULL', null, false)->or_where('verification_code_expires_at <=', date('Y-m-d H:i:s', $now + 870))->group_end();
        $claimed = $this->db->update('users', ['verification_code'=>$code,
            'verification_code_expires_at'=>date('Y-m-d H:i:s', $now + 900), 'verification_attempts'=>0, 'verification_locked_until'=>null]);
        if (!$claimed || $this->db->affected_rows() !== 1) {
            return ['status'=>false, 'msg'=>'Please wait 30 seconds before requesting another code.'];
        }
        foreach ($keys as $key=>$max) { auth_throttle_hit($key, $max, 900); }
        if (!send_email_notification($this, $user->email, 'Email Address Verification',
            ['firstname'=>$user->firstname, 'verification_code'=>$code,
             'resume_link'=>production_url('verify-email/' . rawurlencode($token))], 'user_code_verification_email')) {
            $this->db->where('id', (int) $user_id)->where('verification_code', $code)
                ->update('users', ['verification_code'=>null, 'verification_code_expires_at'=>null]);
            log_message('error', 'Registration verification email failed for user ID ' . (int) $user_id);
            return $failure;
        }
        return ['status'=>true, 'msg'=>'A verification code has been sent to your email.', 'cooldown_seconds'=>30];
    }

    public function send_registration_continuation($user_id, array $actor)
    {
        $failure = ['status'=>false, 'msg'=>'A registration email could not be sent. Please try again.'];
        $user = $this->user_read_model->get_user_details_by_id($user_id);
        if (!registration_can_resume($user) || ($actor['type'] ?? '') !== 'admin'
            || (int) ($actor['id'] ?? 0) <= 0 || !$this->db->table_exists('user_activity_logs')) {
            return ['status'=>false, 'msg'=>'Registration recovery is unavailable for this account.'];
        }
        $keys = ['registration-email:user:' . (int) $user_id => [3,900],
            'registration-email:cooldown:' . (int) $user_id => [1,30]];
        foreach ($keys as $key=>$settings) {
            $limit = auth_throttle_check($key, $settings[0], $settings[1]);
            if (!$limit['allowed']) { return ['status'=>false, 'msg'=>auth_throttle_message($limit['retry_after'], 'registration email')]; }
        }
        $token = $this->issue_signup_resume_token($user_id);
        $current = $token ? $this->get_signup_resume_user($token) : null;
        if (!$current || $current->email !== $user->email) { return $failure; }
        foreach ($keys as $key=>$settings) { auth_throttle_hit($key, $settings[0], $settings[1]); }
        $sent = send_email_notification($this, $current->email, 'Complete your ShareMyBag registration',
            ['firstname'=>$current->firstname, 'resume_link'=>production_url('continue-registration/' . rawurlencode($token))],
            'user_registration_continuation_email');
        $this->load->model('user_activity_model');
        $audited = $this->user_activity_model->record($user_id,
            $sent ? 'registration_email_sent' : 'registration_email_failed', $actor);
        if (!$audited) { log_message('error', 'Registration email audit failed for user ID ' . (int) $user_id); }
        if (!$sent) { return $failure; }
        return ['status'=>true, 'msg'=>$audited ? 'Registration email sent.' : 'Registration email sent. Its activity record could not be saved.',
            'sent_at'=>date('Y-m-d H:i:s')];
    }

    public function last_registration_email($user_id)
    {
        if (!$this->db->table_exists('user_activity_logs')) { return null; }
        $row = $this->db->where('user_id', (int) $user_id)->where('event', 'registration_email_sent')
            ->order_by('id', 'DESC')->limit(1)->get('user_activity_logs')->row();
        return $row ? $row->date_added : null;
    }


    public function record_signup_login($user)
    {
        if (!$this->db->table_exists('user_activity_logs')) { return; }
        $this->load->model('user_activity_model');
        if (!$this->user_activity_model->record($user->id, 'signed_in',
            ['type'=>'user', 'id'=>(int) $user->id, 'name'=>trim($user->firstname . ' ' . ($user->lastname ?? ''))], [], 'registration')) {
            log_message('error', 'Registration sign-in audit failed for user ID ' . (int) $user->id);
        }
    }

    public function record_verification_failure($user_id, $max_attempts = 5, $lock_minutes = 15, $code = null, $token = null)
    {
        if (!$this->db->field_exists('verification_attempts', 'users')
            || !$this->db->field_exists('verification_locked_until', 'users')) { return; }
        $this->pending_signup_where($user_id);
        if ($code !== null) { $this->db->where('verification_code', $code); }
        if ($token !== null) { $this->db->where('signup_resume_token', $token); }
        $this->db->group_start()->where('verification_locked_until IS NULL', null, false)
            ->or_where('verification_locked_until <=', date('Y-m-d H:i:s'))->group_end();
        // Set the lock first: MySQL evaluates assignments left-to-right.
        $this->db->set('verification_locked_until', 'CASE WHEN COALESCE(verification_attempts, 0) + 1 >= ' . (int) $max_attempts
            . ' THEN ' . $this->db->escape(date('Y-m-d H:i:s', time() + (int) $lock_minutes * 60))
            . ' ELSE verification_locked_until END', false);
        $this->db->set('verification_attempts', 'COALESCE(verification_attempts, 0) + 1', false);
        $this->db->update('users');
    }


    public function verify_document($user_id, $id_photo, $selfie, $utility)
    {
        // SECURITY FIX: Explicitly map the fields. Do not use extractKeys on POST data here.
        $data = [
            'id_type'     => $this->input->post('id_type', TRUE),
            'platform'    => $this->input->post('platform', TRUE),
            'socials'     => $this->input->post('socials', TRUE),
            'id_card'     => $id_photo,
            'selfie'      => $selfie,
            'utility'     => $utility,
            'is_verified' => VERIFY_PENDING,
        ];

        if ($this->db->field_exists('verification_rejection_reason', 'users')) {
            $data['verification_rejection_reason'] = null;
        }
        if ($this->db->field_exists('verification_rejection_note', 'users')) {
            $data['verification_rejection_note'] = null;
        }
        if ($this->db->field_exists('verification_rejected_at', 'users')) {
            $data['verification_rejected_at'] = null;
        }
        if ($this->db->field_exists('verification_rejected_by', 'users')) {
            $data['verification_rejected_by'] = null;
        }

        $this->load->model('user_activity_model');
        $ok = $this->user_activity_model->updateDetails($user_id, $data,
            $this->user_activity_model->actor('user', $user_id), 'identity_updated');
        if ($ok) { $this->user_read_model->clearUserCountCaches(); }
        return $ok ? $user_id : false;
    }


    public function update_profile_to_db($id)
    {
        $this->load->model('user_activity_model');
        return $this->user_activity_model->updateDetails($id, array(
            'state' => $this->input->post('state', TRUE),
            'post_code' => $this->input->post('post_code', TRUE),
            'address' => $this->input->post('address', TRUE),
        ), $this->user_activity_model->actor('user', $id));
    }

    public function mark_phone_verified($userId, $phone, $challengeId = null)
    {
        $this->load->model('user_activity_model');
        return $this->user_activity_model->updateDetails($userId, array(
            'number' => $phone, 'verified_phone_e164' => $phone,
            'phone_verified_at' => date('Y-m-d H:i:s'), 'phone_signin_enabled' => 0,
        ), $this->user_activity_model->actor('user', $userId), 'phone_verified', $challengeId);
    }

    public function set_signin_code_methods($userId, $methods)
    {
        $mask = signin_code_methods_mask($methods);
        if ($mask === null) { return false; }
        $this->load->model('user_activity_model');
        return $this->user_activity_model->updateDetails($userId,
            array('signin_code_methods' => $mask),
            $this->user_activity_model->actor('user', $userId), 'signin_methods_updated');
    }

    public function set_phone_signin_enabled($userId, $enabled, $channel = null)
    {
        if ($channel !== null && !in_array($channel, array('sms', 'whatsapp'), true)) { return false; }
        $channel = $channel ?? 'sms';
        return $this->set_signin_code_methods($userId, $enabled ? array('email', $channel) : array('email'));
    }


    public function add_booking_to_db($user_id, $fullname, $email)
    {
        // Generate server-side identifiers
        $tracking_id = generate_unique_tracking_id('bookings', 'tracking_id', 7);
        $hash = $this->generate_hash(200);
        $traveller_id = (int) $this->input->post('traveller_id', TRUE);
        $traveller = $this->traveller_read_model->get_traveller_details_by_id($traveller_id);
        $route_origin = $traveller && !empty($traveller->location)
            ? $traveller->location
            : $this->input->post('traveller_current_state', TRUE);
        $route_destination = $traveller && !empty($traveller->destination)
            ? $traveller->destination
            : $this->input->post('traveller_destination', TRUE);
        $route_currency = booking_route_currency($route_origin, $route_destination);

        // ── SERVER-SIDE PRICE RECALCULATION (SEC-008) ──
        // We still read client-submitted calculations for comparison, but
        // the authoritative totals come from server-side logic in the controller.
        // The controller already computed and injected traveller_commission into $_POST.
        $calculations = json_decode($this->input->post('price_calculations'));
        $traveller_commission = (float) $this->input->post('traveller_commission', TRUE);
        $payment_method = payment_method_normalize($this->input->post('payment_method', TRUE));

        // Use the server-validated client summary as the VAT-exclusive booking base.
        // The shared pricing helper applies the current VAT rate (suspended for now).
        $selected_space = isset($calculations->selectedSpace) ? (float) $calculations->selectedSpace : 0;
        $selected_price = isset($calculations->selectedPrice) ? (float) $calculations->selectedPrice : 0;
        $sub_total = isset($calculations->subTotal) ? (float) $calculations->subTotal : 0;
        $service_charge = isset($calculations->serviceCharge) ? (float) $calculations->serviceCharge : 0;
        $insurance_val = isset($calculations->insurance) ? (float) $calculations->insurance : 0;
        $base_total = isset($calculations->totalAmount) ? (float) $calculations->totalAmount : ($sub_total + $insurance_val);
        $pricing = booking_price_breakdown($base_total, $traveller_commission, $service_charge, $insurance_val, $payment_method);

        // ── EXPLICIT FIELD ALLOWLIST (SEC-007) ──
        // Only these fields are written. Server-controlled fields (payment_status,
        // status, delivery_status, hash, tracking_id, user_id) are NEVER from POST.
        $data = array(
            // ── Server-controlled (never from POST) ──
            'payment_status'    => payment_status_normalize('canceled'),
            'status'            => booking_status_normalize('Pending'),
            'delivery_status'   => delivery_status_normalize('Pending'),
            'tracking_id'       => $tracking_id,
            'user_id'           => $user_id,
            'user_fullname'     => $fullname,
            'user_email'        => $email,
            'hash'              => $hash,

            // ── Financial (server-calculated) ──
            'total_amount'          => $pricing['total_amount'],
            'sub_total'             => round($sub_total, 2),
            'service_charge'        => round($service_charge, 2),
            'vat'                   => $pricing['vat'],
            'insurance'             => round($insurance_val, 2),
            'selected_space'        => $selected_space,
            'selected_price'        => round($selected_price, 2),
            'traveller_commission'  => round($traveller_commission, 2),
            'currency'              => $route_currency,

            // ── Traveller details (safe user input) ──
            'traveller_id'              => $traveller_id,
            'traveller_name'            => $this->input->post('traveller_name', TRUE),
            'traveller_email'           => $this->input->post('traveller_email', TRUE),
            'traveller_contact'         => $this->input->post('traveller_contact', TRUE),
            'traveller_departure_date'  => $this->input->post('traveller_departure_date', TRUE),
            'traveller_arrival_date'    => $this->input->post('traveller_arrival_date', TRUE),
            'traveller_drop_address1'   => $this->input->post('traveller_drop_address1', TRUE),
            'traveller_drop_date1'      => $this->input->post('traveller_drop_date1', TRUE),
            'traveller_drop_address2'   => $this->input->post('traveller_drop_address2', TRUE),
            'traveller_drop_date2'      => $this->input->post('traveller_drop_date2', TRUE),
            'traveller_departure_state' => $this->input->post('traveller_departure_state', TRUE),
            'traveller_current_state'   => $this->input->post('traveller_current_state', TRUE),
            'traveller_arrival_airport' => $this->input->post('traveller_arrival_airport', TRUE),
            'traveller_arrival_state'   => $this->input->post('traveller_arrival_state', TRUE),
            'traveller_destination'     => $this->input->post('traveller_destination', TRUE),

            // ── Agent details ──
            'agent_name'      => $this->input->post('agent_name', TRUE),
            'agent_phone'     => normalize_phone_number(
                $this->input->post('agent_country_code', TRUE),
                $this->input->post('agent_phone', TRUE)
            ),
            'agent_email'     => $this->input->post('agent_email', TRUE),
            'agent_address'   => $this->input->post('agent_address', TRUE),
            'agent_locality'  => $this->input->post('agent_locality', TRUE),
            'agent_postcode'  => $this->input->post('agent_postcode', TRUE),

            // ── Receiver details ──
            'receiver_name'      => $this->input->post('receiver_name', TRUE),
            'receiver_phone'     => normalize_phone_number(
                $this->input->post('receiver_country_code', TRUE),
                $this->input->post('receiver_phone', TRUE)
            ),
            'receiver_email'     => $this->input->post('receiver_email', TRUE),
            'receiver_address'   => $this->input->post('receiver_address', TRUE),
            'receiver_locality'  => $this->input->post('receiver_locality', TRUE),
            'receiver_postcode'  => $this->input->post('receiver_postcode', TRUE),

            // ── Booking extras ──
            'payment_method'  => $payment_method,
            'items'           => $this->input->post('items', TRUE),
        );

        $this->db->insert('bookings', $data);
        $this->finance_read_model->clearFinanceSummaryCaches();
        $this->booking_read_model->clearBookingCountCaches();

        return $this->db->where(array('hash' => $hash))->where('deleted_at IS NULL', null, false)->get('bookings')->row();
    }


    public function calculate_traveller_commission($traveller, $selected_space, $items_json = null)
    {
        $route_pricing = booking_route_pricing($traveller->location, $traveller->destination);
        return booking_calculate_traveller_commission($route_pricing, $selected_space, $items_json);
    }


    public function mark_paystack_initialized($booking_id, $reference, $exchange_rate)
    {
        $this->db->where('id', $booking_id);
        $updated = $this->db->update('bookings', [
            'paystack_ref' => $reference,
            'paystack_exchange_rate' => round((float) $exchange_rate, 4),
            'payment_status' => payment_status_normalize('canceled')
        ]);
        if ($updated) {
            $this->finance_read_model->clearFinanceSummaryCaches();
            $this->booking_read_model->clearBookingCountCaches();
        }
        return $updated;
    }


    public function cancel_booking_payment_by_hash($hash)
    {
        $booking = $this->booking_read_model->dataByHash($hash);
        if (!$booking) {
            return false;
        }

        $this->db->where('id', $booking->id);
        $updated = $this->db->update('bookings', [
            'payment_status' => payment_status_normalize('canceled')
        ]);
        if ($updated) {
            $this->finance_read_model->clearFinanceSummaryCaches();
            $this->booking_read_model->clearBookingCountCaches();
        }
        return $updated;
    }


    public function finalize_booking_payment_by_hash($hash, $is_completed)
    {
        $booking = $this->booking_read_model->dataByHash($hash);
        if (!$booking) {
            return [
                'status' => false,
                'title' => 'Booking Invalid.',
                'msg' => 'This booking was invalid.',
                'msg_timeout' => 6000,
                'redirect' => 'history',
            ];
        }

        $payment_status = payment_status_normalize($is_completed ? 'completed' : 'canceled');

        $this->db->where('id', $booking->id);
        $this->db->update('bookings', [
            'payment_status' => $payment_status,
            'new' => $is_completed ? 0 : $booking->new,
        ]);
        $this->finance_read_model->clearFinanceSummaryCaches();
        $this->booking_read_model->clearBookingCountCaches();

        if (!$is_completed) {
            return [
                'status' => false,
                'title' => 'Booking Canceled.',
                'msg' => 'You canceled the payment.',
                'msg_timeout' => 7000,
                'redirect' => 'history',
            ];
        }

        $this->travellers_model->update_traveller_space($booking->traveller_id);

        $email_data = $this->build_booking_notification_data($booking);
        send_email_notification($this, $_ENV['ADMIN_NOTIFICATION_EMAIL'] ?? 'customers@sharemybag.co.uk', 'New Booking', $email_data, 'admin_booking_notification_email');
        send_email_notification($this, $booking->agent_email, 'Booking Successful', $email_data, 'user_booking_notification_email');

        return [
            'status' => true,
            'title' => 'Booking Successful.',
            'msg' => 'Please check your email for details.',
            'msg_timeout' => 7000,
            'redirect' => 'booking-success',
            'booking_id' => (int) $booking->id,
        ];
    }


    private function build_booking_notification_data($booking)
    {
        return [
            'tracking_id' => $booking->tracking_id,
            'total_amount' => $booking->total_amount,
            'agent_name' => $booking->agent_name,
            'date_added' => x_date($booking->date_added),
            'items' => $booking->items,
            'insurance' => $booking->insurance,
            'user_fullname' => $booking->user_fullname,
            'traveller_name' => $booking->traveller_name,
            'traveller_contact' => $booking->traveller_contact,
            'traveller_departure_state' => $booking->traveller_departure_state,
            'traveller_drop_address1' => $booking->traveller_drop_address1,
            'traveller_drop_date1' => x_date($booking->traveller_drop_date1),
            'traveller_drop_address2' => $booking->traveller_drop_address2 ?: 'N/A',
            'traveller_drop_date2' => $booking->traveller_drop_date2 ? x_date($booking->traveller_drop_date2) : 'N/A',
            'traveller_departure_date' => x_date($booking->traveller_departure_date),
            'traveller_arrival_date' => $booking->traveller_arrival_date ? x_date($booking->traveller_arrival_date) : 'N/A',
            'traveller_current_state' => $booking->traveller_current_state,
            'traveller_arrival_airport' => $booking->traveller_arrival_airport,
            'traveller_arrival_state' => $booking->traveller_arrival_state,
            'traveller_destination' => isset($booking->traveller_destination) ? $booking->traveller_destination : $booking->traveller_arrival_state,
            'currency' => currency_symbol_text($booking->currency),
            'currency_code' => currency_code_normalize($booking->currency),
        ];
    }

    public function add_offline_booking_to_db($id)
    {
        $y = $this->traveller_read_model->get_traveller_details_by_id($id);
        $user = $this->user_read_model->get_user_details_by_id($this->input->post('user_id'));
        $selected_space = (float) $this->input->post('selected_space');
        $route_pricing = booking_route_pricing($y->location, $y->destination);
        $currency = booking_route_currency($y->location, $y->destination);
        $selected_price = round($route_pricing['normal_rate'] * $selected_space, 2);
        $service_charge = round((float) $route_pricing['service_charge'], 2);
        $traveller_commission = $this->calculate_traveller_commission($y, $selected_space, null);
        $sub_total = round($selected_price + $service_charge, 2);
        $pricing = booking_price_breakdown($sub_total, $traveller_commission, $service_charge, 0, 'offline');
        $tracking_id = generate_unique_tracking_id('bookings', 'tracking_id', 7);
        $items = json_encode(array(
            (object) array(
                'item_name' => 'Offline Bag Space',
                'category' => 'Normal',
                'size' => $selected_space,
                'price' => $selected_price,
            )
        ));

        $data = [
            // Traveller Info
            'traveller_id' => $y->id,
            'traveller_name' => $y->fullname,
            'traveller_contact' => $y->phone,
            'traveller_email' => $y->email,
            'traveller_departure_date' => $y->travel_date,
            'traveller_arrival_date' => $y->arrival_date,
            'traveller_departure_state' => $y->departure_state,
            'traveller_current_state' => $y->current_state,
            'traveller_arrival_state' => $y->arrival_state,
            'traveller_arrival_airport' => $y->arrival_airport,
            'traveller_destination' => $y->destination,
            'traveller_drop_address1' => $y->drop_address1,
            'traveller_drop_date1' => $y->drop_date1,
            'traveller_drop_address2' => $y->drop_address2,
            'traveller_drop_date2' => $y->drop_date2,

            // Linked user (smb user)
            'user_id' => $this->input->post('user_id'),
            'user_email' => $user->email,
            'user_fullname' => $user->firstname . ' ' . $user->lastname,

            // Agent Details
            'agent_name' => $this->input->post('agent_name'),
            'agent_email' => $this->input->post('agent_email'),
            'agent_phone' => normalize_phone_number(
                $this->input->post('agent_country_code', TRUE),
                $this->input->post('agent_phone', TRUE)
            ),
            'agent_address' => $this->input->post('agent_address'),
            'agent_locality' => $this->input->post('agent_locality'),
            'agent_postcode' => $this->input->post('agent_postcode'),

            // Receiver Details
            'receiver_name' => $this->input->post('receiver_name'),
            'receiver_email' => $this->input->post('receiver_email'),
            'receiver_phone' => normalize_phone_number(
                $this->input->post('receiver_country_code', TRUE),
                $this->input->post('receiver_phone', TRUE)
            ),
            'receiver_address' => $this->input->post('receiver_address'),
            'receiver_locality' => $this->input->post('receiver_locality'),
            'receiver_postcode' => $this->input->post('receiver_postcode'),

            // Booking Info
            'tracking_id' => $tracking_id,
            'selected_space' => $selected_space,
            'selected_price' => $selected_price,
            'sub_total' => $sub_total,
            'service_charge' => $service_charge,
            'traveller_commission' => round((float) $traveller_commission, 2),
            'vat' => $pricing['vat'],
            'insurance' => 0.00,
            'total_amount' => $pricing['total_amount'],
            'currency' => $currency,
            'items' => $items,
            'status' => booking_status_normalize('Approved'),
            'delivery_status' => delivery_status_normalize('Pending'),
            'new' => 0,
            'payment_method' => 'offline',
            'payment_status' => payment_status_normalize('completed'),
            'hash' => $this->generate_hash(200),
        ];

        $this->db->trans_start();
        $inserted = $this->db->insert('bookings', $data);
        if ($inserted) {
            $this->travellers_model->update_traveller_space($data['traveller_id']);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed in ' . __METHOD__);
            return false;
        }

        if ($inserted) {
            $this->finance_read_model->clearFinanceSummaryCaches();
            $this->booking_read_model->clearBookingCountCaches();
        }
        return $inserted;
    }

    public function backfill_offline_booking_financials()
    {
        $fields = array(
            'id',
            'traveller_id',
            'traveller_current_state',
            'traveller_arrival_state',
            'selected_space',
            'payment_method',
            'payment_status',
            'currency',
            'selected_price',
            'service_charge',
            'sub_total',
            'traveller_commission',
            'vat',
            'total_amount',
            'items',
            'tracking_id',
        );

        if ($this->db->field_exists('traveller_destination', 'bookings')) {
            $fields[] = 'traveller_destination';
        }

        $this->db->select(implode(', ', $fields));
        $this->db->from('bookings');
        $this->db->group_start();
        $this->db->where("(payment_method IS NULL OR LOWER(payment_method) IN ('offline','bank'))", null, false);
        $this->db->group_end();
        $this->db->where("LOWER(COALESCE(payment_status, '')) IN ('completed','complete','success','paid')", null, false);
        $query = $this->db->get();

        if ($query === false) {
            log_message('error', 'Offline booking backfill query failed: ' . $this->db->last_query());
            return 0;
        }

        $bookings = $query->result();

        $updated = 0;

        foreach ($bookings as $booking) {
            $selectedSpace = (float) $booking->selected_space;
            if ($selectedSpace <= 0) {
                continue;
            }

            $traveller = $this->traveller_read_model->get_traveller_details_by_id((int) $booking->traveller_id);
            $origin = $traveller && !empty($traveller->location)
                ? trim((string) $traveller->location)
                : trim((string) $booking->traveller_current_state);
            $bookingDestination = property_exists($booking, 'traveller_destination') ? $booking->traveller_destination : '';
            $destination = $traveller && !empty($traveller->destination)
                ? trim((string) $traveller->destination)
                : trim((string) ($bookingDestination ?: $booking->traveller_arrival_state));

            $routePricing = booking_route_pricing($origin, $destination);
            $currency = booking_route_currency($origin, $destination);
            $selectedPrice = round($routePricing['normal_rate'] * $selectedSpace, 2);
            $serviceCharge = round((float) $routePricing['service_charge'], 2);
            $travellerCommission = round($routePricing['normal_payout_rate'] * $selectedSpace, 2);
            $subTotal = round($selectedPrice + $serviceCharge, 2);
            $pricing = booking_price_breakdown($subTotal, $travellerCommission, $serviceCharge, 0, 'offline');
            $items = json_encode(array(
                (object) array(
                    'item_name' => 'Offline Bag Space',
                    'category' => 'Normal',
                    'size' => $selectedSpace,
                    'price' => $selectedPrice,
                )
            ));

            $data = array();

            if (empty($booking->tracking_id)) {
                $data['tracking_id'] = generate_unique_tracking_id('bookings', 'tracking_id', 7);
            }
            if ($this->db->field_exists('traveller_destination', 'bookings') && empty($bookingDestination) && $destination !== '') {
                $data['traveller_destination'] = $destination;
            }
            if (empty($booking->currency)) {
                $data['currency'] = $currency;
            }
            if ((float) $booking->selected_price <= 0) {
                $data['selected_price'] = $selectedPrice;
            }
            if ((float) $booking->service_charge <= 0) {
                $data['service_charge'] = $serviceCharge;
            }
            if ((float) $booking->sub_total <= 0) {
                $data['sub_total'] = $subTotal;
            }
            if ((float) $booking->traveller_commission <= 0) {
                $data['traveller_commission'] = $travellerCommission;
            }
            if ($booking->vat === null || $booking->vat === '') {
                $data['vat'] = $pricing['vat'];
            }
            if ((float) $booking->total_amount <= 0) {
                $data['total_amount'] = $pricing['total_amount'];
            }
            if (empty($booking->items)) {
                $data['items'] = $items;
            }
            if (empty($booking->payment_method)) {
                $data['payment_method'] = 'offline';
            }

            if (!empty($data)) {
                $this->db->where('id', (int) $booking->id)->update('bookings', $data);
                $updated++;
            }
        }

        if ($updated > 0) {
            $this->finance_read_model->clearFinanceSummaryCaches();
            $this->booking_read_model->clearBookingCountCaches();
        }

        return $updated;
    }


    public function is_profile_incomplete($id)
    {
        $user_details = $this->user_read_model->get_user_details_by_id($id);

        return (
            empty($user_details->number) ||
            empty($user_details->address) ||
            empty($user_details->state) ||
            empty($user_details->post_code)
        );
    }

    public function is_profile_complete($id)
    {
        return !$this->is_profile_incomplete($id);
    }


    public function change_password($id)
    {
        $y = $this->user_read_model->get_user_details_by_id($id);
        $password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);
        $data = array(
            'password' => $password,
        );
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }
}
