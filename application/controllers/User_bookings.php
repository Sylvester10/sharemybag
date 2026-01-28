<?php
defined('BASEPATH') or die('Direct access not allowed');


class User_bookings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('common_model');
        $this->load->model('user_bookings_model');
        $this->load->model('travellers_model');
        $this->user_details = $this->common_model->get_user_details($this->session->email);
        $this->traveller_details = $this->common_model->get_traveller_details_by_id($this->session->id);
    }



    public function index()
    {
        $this->dashboard_header('Search Travellers');
        $id = $this->user_details->id;
        $data['is_verified'] = $this->user_details->is_verified;
        $data['account_status'] = $this->user_details->account_status;
        $data['user_details'] = $this->user_details;
        $data['is_profile_complete'] = $this->users_model->is_profile_complete($id);
        $this->load->view('users/search_travellers', $data);
        $this->dashboard_footer();
    }


    // Search
    public function search()
    {
        $destination = $this->input->post('destination');
        $this->load->model('common_model', 'common');
        $travellers = $this->common_model->get_travellers_by_destination($destination);
        $is_verified = $this->user_details->is_verified;

        if (count($travellers) > 0) {
            $data = array();
            foreach ($travellers as $traveller) {
                // Recalculate bag space before anything else
                $this->travellers_model->update_traveller_space($traveller->id);

                // Re-fetch traveller after updating space
                $traveller = $this->common_model->get_traveller_details_by_id($traveller->id);

                $days = get_date_difference(date('Y-m-d H:i:s'), $traveller->travel_date);
                $days = !$days ? 'Today' : ($days > 1 ? "$days Days" : "$days Day");
                $location = ($traveller->destination == 'Nigeria') ? $traveller->location : $traveller->current_state;

                // Check if profile is complete
                $profile_completed = (
                    empty($this->user_details->number) ||
                    empty($this->user_details->address) ||
                    empty($this->user_details->state) ||
                    empty($this->user_details->post_code)
                ) ? 0 : 1;

                $data[] = array(
                    'travel_date' => x_date($traveller->travel_date),
                    'days_remaining' => $days,
                    'current_state' => $traveller->current_state,
                    'departure_state' => $traveller->departure_state,
                    'arrival_airport' => $traveller->arrival_airport,
                    'arrival_state' => $traveller->arrival_state,
                    'available_space' => $traveller->available_space,
                    'hash' => $traveller->hash,
                    'bag_locked' => $traveller->bag_locked,
                    'is_verified' => (int)$this->user_details->is_verified,
                    'profile_completed' => $profile_completed,
                    'destination' => $destination,
                );
            }
            echo json_encode(array('status' => true, 'travellers' => $data));
        } else {
            echo json_encode(array('status' => false, 'msg' => 'No Traveller Available'));
        }
    }

    public function buy_bag_space($hash)
    {
        $this->dashboard_header('Buy Space');
        $traveller = $this->common_model->get_traveller_details_by_hash($hash);
        $data['user_details'] = $this->user_details;
        $data['user_id'] = $this->user_details->id;
        $data['traveller_details'] = $traveller;

        // Canada users use CAD ($)
        if ($this->user_details->country == 'Canada') {
            $data['currency'] = 'dollars'; // Used for JS logic
            $data['symbol'] = '$';         // CAD symbol
        } else {
            // Nigeria and United Kingdom users use GBP (£)
            $data['currency'] = 'pounds'; // Used for JS logic
            $data['symbol'] = '&pound;';   // GBP symbol
        }

        // Safely retrieve exchange rates
        $cad_rate_obj = $this->common_model->get_most_recent_cad_exchange_rate();
        $pound_rate_obj = $this->common_model->get_most_recent_pound_exchange_rate();

        // Assign rates, using 0 as a safe fallback if the rate object is null
        $data['one_pound'] = $pound_rate_obj ? $pound_rate_obj->rate : 0; // GBP to NGN rate
        $data['one_dollar'] = $cad_rate_obj ? $cad_rate_obj->rate : 0;   // CAD to NGN rate

        $this->load->view('users/book_space', $data);
        $this->dashboard_footer();
    }


    public function get_traveling_available_space($id = false)
    {
        if (!$id) {
            echo 0;
            return;
        }
        $this->travellers_model->update_traveller_space($id);
        $traveller = $this->common_model->get_traveller_details_by_id($id);
        echo !$traveller ? 0 : $traveller->available_space;
    }


    /* ========== Add Booking ========== */
    public function add_booking_ajax()
    {
        // Traveller details validation
        $this->form_validation->set_rules('traveller_id', 'Traveller ID', 'trim');
        $this->form_validation->set_rules('traveller_name', 'Traveller Name', 'trim');
        $this->form_validation->set_rules('traveller_email', 'Traveller Email', 'trim');
        $this->form_validation->set_rules('traveller_contact', 'Traveller contact', 'trim');
        $this->form_validation->set_rules('available_space', 'Available Space', 'trim');
        $this->form_validation->set_rules('traveller_travel_date', 'Traveller travel date', 'trim');
        $this->form_validation->set_rules('traveller_departure_date', 'Traveller departure date', 'trim');
        $this->form_validation->set_rules('traveller_arrival_date', 'Traveller arrival date', 'trim');
        $this->form_validation->set_rules('traveller_drop_address1', '1st drop address', 'trim');
        $this->form_validation->set_rules('traveller_drop_date1', '1st drop date', 'trim');
        $this->form_validation->set_rules('traveller_drop_address2', '2nd drop address', 'trim');
        $this->form_validation->set_rules('traveller_drop_date2', '2nd drop date', 'trim');
        $this->form_validation->set_rules('traveller_departure_state', 'Traveller departure', 'trim');

        $this->form_validation->set_rules('traveller_current_state', 'Traveller current state', 'trim');
        $this->form_validation->set_rules('traveller_arrival_airport', 'Traveller arrival Airport', 'trim');
        $this->form_validation->set_rules('traveller_arrival_state', 'Traveller Arrival State', 'trim');

        // Form validation for booking details
        $this->form_validation->set_rules('insurance', 'Insurance', 'trim');
        $this->form_validation->set_rules('need_help', 'Need Help', 'trim');
        $this->form_validation->set_rules('agent_phone', 'agent mobile', 'trim|required', array('required' => 'Please enter agent number'));
        $this->form_validation->set_rules('agent_email', 'agent email', 'trim|valid_email|required', array('required' => 'Please enter agent number', 'valid_email' => 'Please enter a valid email'));
        $this->form_validation->set_rules('agent_address', 'agent Address', 'trim|required', array('required' => 'Please enter agent address'));
        $this->form_validation->set_rules('agent_locality', 'agent Local', 'trim|required', array('required' => 'Please enter agent locale'));
        $this->form_validation->set_rules('agent_postcode', 'agent Postcode', 'trim', array('required' => 'Please enter agent postcode'));
        $this->form_validation->set_rules('receiver_phone', 'receiver Mobile', 'trim|required', array('required' => 'Please enter receiver number'));
        $this->form_validation->set_rules('receiver_email', 'receiver email', 'trim|valid_email|required', array('required' => 'Please enter receiver number', 'valid_email' => 'Please enter a valid email'));
        $this->form_validation->set_rules('receiver_address', 'receiver Address', 'trim|required', array('required' => 'Please enter receiver address'));
        $this->form_validation->set_rules('receiver_locality', 'receiver Local', 'trim|required', array('required' => 'Please enter receiver locale'));
        $this->form_validation->set_rules('receiver_postcode', 'receiver Postcode', 'trim', array('required' => 'Please enter receiver postcode'));
        $this->form_validation->set_rules('payment_method', 'Payment Method', 'trim', array('required' => 'Please select a payment method'));

        $payment_method = $this->input->post('payment_method');

        // Check if form validation passes
        if ($this->form_validation->run()) {

            // Get user details
            $user_id = $this->user_details->id;
            $fullname = $this->user_details->firstname . ' ' . $this->user_details->lastname;
            $email = $this->user_details->email;
            $number = $this->user_details->number;
            $user_country = $this->user_details->country;
            // get agent and receiver details
            $agent_name = $this->input->post('agent_name');
            $agent_phone = $this->input->post('agent_phone');
            $agent_email = $this->input->post('agent_email');
            $receiver_name = $this->input->post('receiver_name');
            $traveller_id = $this->input->post('traveller_id');
            $traveller_destination = $this->input->post('traveller_destination');

            // Agent cannot be the same as receiver
            if ($agent_name == $receiver_name) {
                $res = [
                    'status' => false,
                    'msg' => 'Agent cannot be same as Receiver.',
                    'title' => 'Booking Error',
                    'msg_timeout' => 6000,
                ];
                echo json_encode($res);
                return;
            };

            if ($user_country === $traveller_destination) {
                if ($agent_name === $fullname || $agent_email === $email || $agent_phone === $number) {
                    $res = [
                        'status' => false,
                        'msg' => 'You cannot be the Agent when sending to your current country.',
                        'title' => 'Booking Error',
                        'msg_timeout' => 6000,
                    ];
                    echo json_encode($res);
                    return;
                }
            };

            // Calculate Traveller Commission and inject into POST data ---
            $traveller_details = $this->common_model->get_traveller_details_by_id($traveller_id);
            $destination_country = $traveller_details->destination ?? '';
            $calculations = json_decode($this->input->post('price_calculations'));
            $selected_space = $calculations->selectedSpace; // Use selectedSpace from calculations for accuracy

            // Base commission calculation logic NG -> UK (4.50 for Nigeria, 5.00 for UK/CA per KG)
            $ng_uk_base_commission_rate = ($destination_country == 'Nigeria') ? 4.50 : 5.00;
            $ng_uk_traveller_commission = $ng_uk_base_commission_rate * (float)$selected_space;

            // Base commission calculation logic NG -> CA
            $ng_ca_base_commission_rate = 10.00;
            $ng_ca_traveller_commission = $ng_ca_base_commission_rate * (float)$selected_space;

            $is_ng_uk_route =
                ($traveller_details->location === 'United Kingdom' && $traveller_details->destination === 'Nigeria') ||
                ($traveller_details->location === 'Nigeria' && $traveller_details->destination === 'United Kingdom');

            $is_ng_ca_route =
                ($traveller_details->location === 'Canada' && $traveller_details->destination === 'Nigeria') ||
                ($traveller_details->location === 'Nigeria' && $traveller_details->destination === 'Canada');

            $traveller_commission = ($is_ng_uk_route) ? $ng_uk_traveller_commission : $ng_ca_traveller_commission;

            // Get selected items to check for premium items (for commission increase)
            if ($is_ng_uk_route) {
                $items_json = $this->input->post('items');
                if ($items_json) {
                    $decoded_items = json_decode($items_json);
                    if (is_array($decoded_items)) {
                        foreach ($decoded_items as $item) {
                            if (isset($item->category)) {
                                if ($item->category === 'Documents/Electronics') {
                                    $traveller_commission += 10.00; // £10 commission
                                } elseif ($item->category === 'Fish/Medicine') {
                                    $traveller_commission += 10.00; // £10 commission
                                } elseif ($item->category === 'Duty Free') {
                                    $traveller_commission += 6.50;  // £6.50 commission
                                }
                            }
                        }
                    }
                }
            }

            $traveller_commission = round($traveller_commission, 2);

            // Manually inject the calculated commission into $_POST so it is picked up by the model's extractKeys
            $_POST['traveller_commission'] = $traveller_commission;

            $booking = $this->users_model->add_booking_to_db($user_id, $fullname, $email);

            // Safely retrieve exchange rates
            $cad_rate_obj = $this->common_model->get_most_recent_cad_exchange_rate();
            $pound_rate_obj = $this->common_model->get_most_recent_pound_exchange_rate();

            $cad_rate = $cad_rate_obj ? $cad_rate_obj->rate : 0;
            $pound_rate = $pound_rate_obj ? $pound_rate_obj->rate : 0;

            // Get traveller details (only needed for route title)
            $traveller = $this->common_model->get_traveller_details_by_id($booking->traveller_id);

            // UPDATED CURRENCY LOGIC BASED ON USER'S COUNTRY (ORIGIN) ---
            $is_canada_user = ($this->user_details->country == 'Canada');

            if ($booking) {
                // Set currency and amount variables based on USER's country
                if ($is_canada_user) {
                    $currency = 'cad'; // Charge in CAD
                    $exchange_rate = $cad_rate; // CAD to NGN rate
                    $charge_amount = (float)$booking->total_amount; // Amount is in CAD
                    $title_route = $traveller->location . ' - ' . $traveller->destination;
                } else {
                    $currency = 'gbp'; // Charge in GBP (for UK and Nigerian users)
                    $exchange_rate = $pound_rate; // GBP to NGN rate
                    $charge_amount = (float)$booking->total_amount; // Amount is in GBP
                    $title_route = $traveller->location . ' - ' . $traveller->destination;
                }

                $ngn_amount = $charge_amount * $exchange_rate;
                $title = 'Purchasing ' . $booking->selected_space . 'KG Bag Space ' . $title_route;

                if ($payment_method === 'stripe') {
                    // Create Stripe Checkout session
                    try {
                        // Verify Stripe payment using secret key
                        $stripeSecretKey = get_stripe_secret_key();
                        \Stripe\Stripe::setApiKey($stripeSecretKey);

                        $checkout_session = \Stripe\Checkout\Session::create([
                            'line_items' => [[
                                'price_data' => array(
                                    'currency' => $currency, // Dynamically set currency (gbp or cad)
                                    'unit_amount' => $charge_amount * 100, // Amount to charge
                                    'product_data' => array(
                                        'name' => $title,
                                        // 'description' => 'This is the test description.',
                                        // 'images' => [''],
                                    ),

                                ),
                                'quantity' => 1,
                            ]],
                            'mode' => 'payment',
                            'success_url' => base_url() . 'user_bookings/stripe/' . $booking->hash . '/1',
                            'cancel_url' => base_url() . 'user_bookings/stripe/' . $booking->hash . '/0',
                            'automatic_tax' => [
                                'enabled' => true,
                            ],
                        ]);

                        // Return Stripe session ID to the frontend
                        $res = [
                            'status' => true,
                            'msg' => 'Redirecting to Stripe to complete your payment.',
                            'title' => 'Booking Initialized',
                            'msg_timeout' => 5000,
                            'redirect' => $checkout_session->url
                        ];

                        echo json_encode($res);
                        return;
                    } catch (Exception $e) {
                        // Handle Stripe errors
                        $res = [
                            'status' => false,
                            // 'msg' => 'Stripe error: ' . $e->getMessage(),
                            'msg' => 'Unable to process payments. Try again later.',
                            'title' => 'Payment Error.',
                            'msg_timeout' => 7000,
                        ];
                        echo json_encode($res);
                        return;
                    }
                } elseif ($payment_method === 'paystack') {
                    // Create Paystack Checkout session (Always NGN amount for Paystack)
                    try {
                        // Verify Paystack payment using secret key
                        $paystackSecretKey = get_paystack_secret_key();

                        $reference = 'SMB' . uniqid(); // Unique reference for transaction
                        $callback_url = base_url() . 'user_bookings/paystack/' . $booking->hash . '?reference=' . $reference;

                        $fields = [
                            'email' => $email,
                            'amount' => $ngn_amount * 100, // Convert NGN amount to kobo
                            'reference' => $reference,
                            'callback_url' => $callback_url,
                            'metadata' => [
                                'booking_id' => $booking->id,
                                'user_id' => $user_id,
                                'cancel_action' => base_url('user_bookings/paystack_cancel/' . $booking->hash)
                                // 'cancel_action' => base_url('history')
                            ]
                        ];

                        $fields_string = http_build_query($fields);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            "Authorization: Bearer {$paystackSecretKey}",
                            "Cache-Control: no-cache",
                        ));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        $response = json_decode($result);

                        if ($response && $response->status) {
                            // Save reference and set initial payment status for your tracking
                            $this->db->where('id', $booking->id);
                            $this->db->update('bookings', [
                                'paystack_ref' => $reference,
                                'payment_status' => 'canceled'
                            ]);

                            $res = [
                                'status' => true,
                                'msg' => 'Redirecting to Paystack to complete your payment.',
                                'title' => 'Booking Initialized',
                                'msg_timeout' => 5000,
                                'redirect' => $response->data->authorization_url
                            ];
                            echo json_encode($res);
                            return;
                        } else {
                            throw new Exception($response->message ?? 'Failed to initialize Paystack payment.');
                        }
                    } catch (Exception $e) {
                        // Handle Paystack errors
                        $res = [
                            'status' => false,
                            // 'msg' => 'Paystack error: ' . $e->getMessage(),
                            'msg' => 'Unable to process payments. Try again later.',
                            'title' => 'Payment Error',
                            'msg_timeout' => 6000,
                        ];
                        echo json_encode($res);
                        return;
                    }
                } else {
                    $res = [
                        'status' => false,
                        'msg' => 'Invalid payment method selected.',
                        'title' => 'Payment Error.',
                        'msg_timeout' => 6000,
                    ];
                    echo json_encode($res);
                    return;
                }
            }
        } else {

            // Show validation errors
            $res = [
                'status' => false,
                'msg' => validation_errors(),
                'title' => 'Booking Error.',
                'msg_timeout' => 6000,
            ];
            echo json_encode($res);
            return;
        }
    }


    public function stripe($hash = false, $status = 'No status')
    {
        $booking = $this->user_bookings_model->dataByHash($hash);
        $traveller_id = $booking->traveller_id;
        $email = $booking->agent_email;

        if ($booking) {
            $status = $status == '1' ? 'completed' : 'canceled';
            $data['payment_status'] = $status;

            $this->db->where('id', $booking->id);
            $this->db->update('bookings', $data);

            if ($status == 'completed') {
                $data['new'] = 0;

                $this->db->where('id', $booking->id);
                $this->db->update('bookings', $data);

                $data['tracking_id'] = $booking->tracking_id;
                $data['total_amount'] = $booking->total_amount;
                $data['agent_name'] = $booking->agent_name;
                $data['date_added'] = x_date($booking->date_added);
                $data['items'] = $booking->items;
                $data['insurance'] = $booking->insurance;
                $data['traveller_name'] = $booking->traveller_name;
                $data['traveller_contact'] = $booking->traveller_contact;
                $data['traveller_departure_state'] = $booking->traveller_departure_state;
                $data['traveller_drop_address1'] = $booking->traveller_drop_address1;
                $data['traveller_drop_date1'] = x_date($booking->traveller_drop_date1);
                $data['traveller_drop_address2'] = $booking->traveller_drop_address2 == '' ? 'N/A' : $booking->traveller_drop_address2;
                $data['traveller_drop_date2'] = $booking->traveller_drop_date2 == '' ? 'N/A' : $booking->traveller_drop_date2;
                $data['traveller_departure_date'] = x_date($booking->traveller_departure_date);
                $data['traveller_arrival_date'] = $booking->traveller_arrival_date == '' ? 'N/A' : x_date($booking->traveller_arrival_date);

                $data['traveller_current_state'] = $booking->traveller_current_state;
                $data['traveller_arrival_airport'] = $booking->traveller_arrival_airport;
                $data['traveller_arrival_state'] = $booking->traveller_arrival_state;
                $data['currency'] = ($booking->currency == 'dollars') ? '$' : '£';

                //Update the tracking ID, used space and available space in the traveller table
                $this->travellers_model->update_traveller_space($traveller_id);

                // Send email to Admin and User
                send_email_notification($this, 'customers@sharemybag.co.uk', 'New Booking', $data, 'admin_booking_notification_email');
                send_email_notification($this, $email, 'Booking Successful', $data, 'user_booking_notification_email');

                //set success booking notification
                $res = [
                    'status' => true,
                    'msg' => 'Please check your email for details.',
                    'title' => 'Booking Successful.',
                    'msg_timeout' => 7000
                ];
                $this->session->set_flashdata('status_success', $res['msg']);
                $this->session->set_flashdata('title', $res['title']);
                $this->session->set_flashdata('msg_timeout', $res['msg_timeout']);

                // Redirect to booking history where the flash message will be shown
                redirect('booking-success');
            } else {
                // Send email to User
                // send_email_notification($this, $email, 'Booking Canceled', $data, 'user_booking_notification_email');

                // set cancel booking notification
                $res = [
                    'status' => false,
                    'msg' => 'You canceled the payment.',
                    'title' => 'Booking Canceled.',
                    'msg_timeout' => 7000
                ];
                $this->session->set_flashdata('status_error', $res['msg']);
                $this->session->set_flashdata('title', $res['title']);
                $this->session->set_flashdata('msg_timeout', $res['msg_timeout']);

                // Redirect to buy bag page where the flash message will be shown
                redirect('history');
            }
        } else {

            //set invalid booking notification
            $res = [
                'status' => false,
                'msg' => 'This booking was invalid.',
                'title' => 'Booking Invalid.',
                'msg_timeout' => 6000
            ];
            $this->session->set_flashdata('status_error', $res['msg']);
            $this->session->set_flashdata('title', $res['title']);
            $this->session->set_flashdata('msg_timeout', $res['msg_timeout']);

            // Redirect to booking history where the flash message will be shown
            redirect('history');
        }

        // set cancel booking notification
        $res = [
            'status' => false,
            'msg' => 'You canceled the payment.',
            'title' => 'Booking Canceled.',
            'msg_timeout' => 7000
        ];
        $this->session->set_flashdata('status_error', $res['msg']);
        $this->session->set_flashdata('title', $res['title']);
        $this->session->set_flashdata('msg_timeout', $res['msg_timeout']);

        // Redirect to booking history where the flash message will be shown
        redirect('history');
    }


    public function paystack($hash = false)
    {
        $reference = $this->input->get('reference');
        $booking = $this->user_bookings_model->dataByHash($hash);
        $traveller_id = $booking->traveller_id;
        $email = $booking->agent_email;

        if (!$reference || !$booking) {
            $this->session->set_flashdata('status_error', 'This booking was invalid or missing reference.');
            $this->session->set_flashdata('title', 'Booking Invalid.');
            $this->session->set_flashdata('msg_timeout', 7000);
            redirect('history');
        }

        if ($booking) {

            // Verify Paystack payment using secret key
            $secretKey = get_paystack_secret_key();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . $reference);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer {$secretKey}",
                "Cache-Control: no-cache",
            ));
            $result = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($result);
            $status = ($response && $response->status && $response->data->status === 'success') ? 'completed' : 'canceled';

            // Update bookings table
            $this->db->where('id', $booking->id);
            $this->db->update('bookings', ['payment_status' => $status, 'new' => 0]);

            if ($status === 'completed') {
                $data = [
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
                ];

                $this->travellers_model->update_traveller_space($traveller_id);

                send_email_notification($this, 'customers@sharemybag.co.uk', 'New Booking', $data, 'admin_booking_notification_email');
                send_email_notification($this, $email, 'Booking Successful', $data, 'user_booking_notification_email');

                $this->session->set_flashdata('status_success', 'Booking Successful. Check your email for details.');
                $this->session->set_flashdata('title', 'Booking Successful.');
                $this->session->set_flashdata('msg_timeout', 7000);
                redirect('booking-success');
            } else {
                // Optional: log that user cancelled via $response->data->gateway_response
                $this->session->set_flashdata('status_error', 'Payment was cancelled or failed.');
                $this->session->set_flashdata('title', 'Booking Cancelled.');
                $this->session->set_flashdata('msg_timeout', 7000);
                redirect('history');
            }
        }
    }

    public function paystack_cancel($hash = false)
    {
        if ($hash) {
            $booking = $this->user_bookings_model->dataByHash($hash);
            if ($booking) {
                $this->db->where('id', $booking->id);
                $this->db->update('bookings', ['payment_status' => 'canceled']);
            }
        }

        $this->session->set_flashdata('status_error', 'You canceled the payment.');
        $this->session->set_flashdata('title', 'Booking Canceled.');
        $this->session->set_flashdata('msg_timeout', 7000);
        redirect('history');
    }


    public function booking_success()
    {
        $this->dashboard_header('Booking Successful');
        $this->load->view('users/booking_success');
        $this->dashboard_footer();
    }


    public function check()
    {
        $this->load->view('users/check');
    }
}
