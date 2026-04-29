<?php
defined('BASEPATH') or exit('Direct access to script not allowed');


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
        $verification_code = generate_verification_code();
        $pass_reset_code = generate_verification_code();
        $ref_num = mt_rand(11111, 99999);
        $firstname = trim($this->input->post('firstname', TRUE));
        $username = strtolower($firstname) . $ref_num;

        $email = trim($this->input->post('email', TRUE));
        $number = $this->input->post('country_code', TRUE) . $this->input->post('phone', TRUE);
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
            'verification_code' => $verification_code,
            'pass_reset_code'   => $pass_reset_code,
            'account_status'    => 1, // Set to 1 explicitly
            'is_verified'       => VERIFY_NONE,
        );

        $this->db->insert('users', $data);
        $user_id = $this->db->insert_id();
        $this->user_read_model->clearUserCountCaches();

        send_email_notification($this, $email, 'Email Address Verification', $data, 'user_code_verification_email');

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
        send_email_notification($this, $email, 'Email Address Verification', $data, 'user_code_verification_email');

        return true;
    }


    public function get_user_by_verification_code($verification_code)
    {
        $query = $this->db->where(['verification_code' => $verification_code])->where('deleted_at IS NULL', null, false)->get('users');
        return $query->row();
    }


    public function update_user_verification($user_id, $password)
    {
        $this->db->where('id', $user_id);
        $this->db->update('users', [
            'password' => $password,
            'verification_code' => NULL // Clear old code after verification
        ]);
    }


    public function update_user_verification_code($user_id, $new_code)
    {
        $this->db->where('id', $user_id);
        $this->db->update('users', ['verification_code' => $new_code]);
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

        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
        $this->user_read_model->clearUserCountCaches();

        return ($this->db->affected_rows() > 0) ? $user_id : false;
    }


    public function update_profile_to_db($id)
    {
        $data = array(
            'state'     => $this->input->post('state', TRUE),
            'post_code' => $this->input->post('post_code', TRUE),
            'address'   => $this->input->post('address', TRUE),
            'number'    => $this->input->post('number', TRUE),
        );

        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }


    public function add_booking_to_db($user_id, $fullname, $email)
    {
        // Generate server-side identifiers
        $tracking_id = generate_unique_tracking_id('bookings', 'tracking_id', 7);
        $hash = $this->generate_hash(200);

        // ── SERVER-SIDE PRICE RECALCULATION (SEC-008) ──
        // We still read client-submitted calculations for comparison, but
        // the authoritative totals come from server-side logic in the controller.
        // The controller already computed and injected traveller_commission into $_POST.
        $calculations = json_decode($this->input->post('price_calculations'));
        $traveller_commission = (float) $this->input->post('traveller_commission', TRUE);
        $payment_method = payment_method_normalize($this->input->post('payment_method', TRUE));

        // Use the server-validated client summary as the VAT-exclusive booking base,
        // then apply the Paystack-only VAT rule here so storage stays authoritative.
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
            'currency'              => $this->input->post('currency', TRUE),

            // ── Traveller details (safe user input) ──
            'traveller_id'              => (int) $this->input->post('traveller_id', TRUE),
            'traveller_name'            => $this->input->post('traveller_name', TRUE),
            'traveller_email'           => $this->input->post('traveller_email', TRUE),
            'traveller_contact'         => $this->input->post('traveller_contact', TRUE),
            // 'traveller_travel_date'     => $this->input->post('traveller_travel_date', TRUE),
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
            'agent_phone'     => $this->input->post('agent_phone', TRUE),
            'agent_email'     => $this->input->post('agent_email', TRUE),
            'agent_address'   => $this->input->post('agent_address', TRUE),
            'agent_locality'  => $this->input->post('agent_locality', TRUE),
            'agent_postcode'  => $this->input->post('agent_postcode', TRUE),

            // ── Receiver details ──
            'receiver_name'      => $this->input->post('receiver_name', TRUE),
            'receiver_phone'     => $this->input->post('receiver_phone', TRUE),
            'receiver_email'     => $this->input->post('receiver_email', TRUE),
            'receiver_address'   => $this->input->post('receiver_address', TRUE),
            'receiver_locality'  => $this->input->post('receiver_locality', TRUE),
            'receiver_postcode'  => $this->input->post('receiver_postcode', TRUE),

            // ── Booking extras ──
            'payment_method'  => $payment_method,
            'items'           => $this->input->post('items', TRUE),
            'need_help'       => $this->input->post('need_help', TRUE),
        );

        $this->db->insert('bookings', $data);
        $this->finance_read_model->clearFinanceSummaryCaches();
        $this->booking_read_model->clearBookingCountCaches();

        return $this->db->where(array('hash' => $hash))->where('deleted_at IS NULL', null, false)->get('bookings')->row();
    }


    public function calculate_traveller_commission($traveller, $selected_space, $items_json = null)
    {
        $selected_space = (float) $selected_space;
        $route_key = booking_route_key($traveller->location, $traveller->destination);
        $route_pricing = booking_route_pricing($traveller->location, $traveller->destination);

        if (in_array($route_key, ['ng_uk', 'ca_ng'], true) && $items_json) {
            $decoded_items = json_decode($items_json);
            $traveller_commission = 0.0;

            if (is_array($decoded_items)) {
                foreach ($decoded_items as $item) {
                    if (!isset($item->category)) {
                        continue;
                    }

                    $item_size = isset($item->size) ? (float) $item->size : 0;
                    if ($item_size <= 0) {
                        continue;
                    }

                    if ($item->category === 'Documents/Electronics' || $item->category === 'Gold') {
                        $traveller_commission += $route_pricing['premium_payout_rate'] * $item_size;
                    } elseif (
                        $item->category === 'Fish/Medicine' ||
                        $item->category === 'Fish/Meat' ||
                        $item->category === 'Medication'
                    ) {
                        $traveller_commission += $route_pricing['special_payout_rate'] * $item_size;
                    } else {
                        $traveller_commission += $route_pricing['normal_payout_rate'] * $item_size;
                    }
                }
            }

            return round($traveller_commission, 2);
        }

        $ng_uk_base_commission_rate = ($traveller->destination == 'Nigeria') ? 4.50 : 5.00;
        $ng_uk_traveller_commission = $ng_uk_base_commission_rate * $selected_space;

        $ng_ca_base_commission_rate = 10.00;
        $ng_ca_traveller_commission = $ng_ca_base_commission_rate * $selected_space;

        $is_ng_uk_route =
            ($traveller->location === 'United Kingdom' && $traveller->destination === 'Nigeria') ||
            ($traveller->location === 'Nigeria' && $traveller->destination === 'United Kingdom');

        $traveller_commission = $is_ng_uk_route ? $ng_uk_traveller_commission : $ng_ca_traveller_commission;

        if ($is_ng_uk_route && $items_json) {
            $decoded_items = json_decode($items_json);
            if (is_array($decoded_items)) {
                foreach ($decoded_items as $item) {
                    if (!isset($item->category)) {
                        continue;
                    }

                    if (
                        $item->category === 'Documents/Electronics' ||
                        $item->category === 'Gold' ||
                        $item->category === 'Fish/Medicine' ||
                        $item->category === 'Fish/Meat' ||
                        $item->category === 'Medication'
                    ) {
                        $traveller_commission += 10.00;
                    } elseif ($item->category === 'Duty Free') {
                        $traveller_commission += 6.50;
                    }
                }
            }
        }

        return round($traveller_commission, 2);
    }


    public function mark_paystack_initialized($booking_id, $reference)
    {
        $this->db->where('id', $booking_id);
        $updated = $this->db->update('bookings', [
            'paystack_ref' => $reference,
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
            'currency' => currency_symbol_text($booking->currency),
        ];
    }

    public function add_offline_booking_to_db($id)
    {
        $y = $this->traveller_read_model->get_traveller_details_by_id($id);
        $user = $this->user_read_model->get_user_details_by_id($this->input->post('user_id'));
        $selected_space = (float) $this->input->post('selected_space');
        $route_pricing = booking_route_pricing($y->location, $y->destination);
        $currency = in_array(booking_route_key($y->location, $y->destination), array('ng_ca', 'ca_ng'), true) ? 'CAD' : 'GBP';
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
            'traveller_travel_date' => $y->travel_date,
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
            'agent_phone' => $this->input->post('agent_phone'),
            'agent_address' => $this->input->post('agent_address'),
            'agent_locality' => $this->input->post('agent_locality'),
            'agent_postcode' => $this->input->post('agent_postcode'),

            // Receiver Details
            'receiver_name' => $this->input->post('receiver_name'),
            'receiver_email' => $this->input->post('receiver_email'),
            'receiver_phone' => $this->input->post('receiver_phone'),
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
        $bookings = $this->db
            ->select('id, traveller_id, traveller_current_state, traveller_destination, traveller_arrival_state, selected_space, payment_method, payment_status, currency, selected_price, service_charge, sub_total, traveller_commission, vat, total_amount, items, tracking_id')
            ->from('bookings')
            ->group_start()
                ->where('LOWER(COALESCE(payment_method, "")) IN ("offline", "bank")', null, false)
                ->or_where('payment_method IS NULL', null, false)
            ->group_end()
            ->where('LOWER(COALESCE(payment_status, "")) IN ("completed","complete","success","paid")', null, false)
            ->get()
            ->result();

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
            $destination = $traveller && !empty($traveller->destination)
                ? trim((string) $traveller->destination)
                : trim((string) ($booking->traveller_destination ?: $booking->traveller_arrival_state));

            $routePricing = booking_route_pricing($origin, $destination);
            $currency = in_array(booking_route_key($origin, $destination), array('ng_ca', 'ca_ng'), true) ? 'CAD' : 'GBP';
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
            if (empty($booking->traveller_destination) && $destination !== '') {
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


    public function is_profile_complete($id)
    {
        $user_details = $this->user_read_model->get_user_details_by_id($id);

        // Check if any of the fields are null or empty
        $is_user_complete = (
            $user_details->number == '' ||
            $user_details->address == '' ||
            $user_details->state == '' ||
            $user_details->post_code == ''
        );

        return $is_user_complete;
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
