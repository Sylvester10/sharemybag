<?php
defined('BASEPATH') or die('Direct access not allowed');


class User_bookings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('finance_read_model');
        $this->load->model('user_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('user_bookings_model');
        $this->load->model('travellers_model');
        $this->user_details = $this->user_read_model->get_user_details($this->session->email);
        $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
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
        $travellers = $this->traveller_read_model->get_travellers_by_destination($destination);
        $is_verified = $this->user_details->is_verified;
        $csrf_hash = $this->security->get_csrf_hash();

        if (count($travellers) > 0) {
            $data = array();
            foreach ($travellers as $traveller) {
                $traveller = $this->travellers_model->update_traveller_space($traveller->id, true);
                if (!$traveller) {
                    continue;
                }

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
                    'area' => $traveller->area,
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
                    'csrf_hash' => $csrf_hash,
                );
            }
            echo json_encode(array('status' => true, 'travellers' => $data, 'csrf_hash' => $csrf_hash));
        } else {
            echo json_encode(array('status' => false, 'msg' => 'No travellers are available for that route right now.', 'csrf_hash' => $csrf_hash));
        }
    }


    public function buy_bag_space($hash)
    {
        $this->dashboard_header('Buy Space');
        $traveller = $this->traveller_read_model->get_traveller_details_by_hash($hash);
        $data['user_details'] = $this->user_details;
        $data['user_id'] = $this->user_details->id;
        $data['traveller_details'] = $traveller;
        $route_currency = booking_route_currency($traveller->location, $traveller->destination);
        $data['currency'] = $route_currency;
        $data['symbol'] = currency_symbol($route_currency);

        // Safely retrieve exchange rates
        $cad_rate_obj = $this->finance_read_model->get_most_recent_cad_exchange_rate();
        $pound_rate_obj = $this->finance_read_model->get_most_recent_pound_exchange_rate();

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
        $traveller = $this->travellers_model->update_traveller_space($id, true);
        echo !$traveller ? 0 : $traveller->available_space;
    }


    /* ========== Add Booking ========== */
    public function add_booking_ajax()
    {
        $csrf_hash = $this->security->get_csrf_hash();
        // Traveller details validation
        $this->form_validation->set_rules('traveller_id', 'Traveller ID', 'trim|is_natural_no_zero');
        $this->form_validation->set_rules('traveller_name', 'Traveller Name', 'trim');
        $this->form_validation->set_rules('traveller_email', 'Traveller Email', 'trim|valid_email');
        $this->form_validation->set_rules('traveller_contact', 'Traveller contact', 'trim');
        $this->form_validation->set_rules('available_space', 'Available Space', 'trim|numeric|greater_than[0]');
        // $this->form_validation->set_rules('traveller_travel_date', 'Traveller travel date', 'trim');
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
        $this->form_validation->set_rules('payment_method', 'Payment Method', 'trim|required|in_list[stripe,paystack]', array('required' => 'Please select a payment method', 'in_list' => 'Invalid payment method selected.'));

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
                    'msg' => 'Enter different details for the agent and receiver.',
                    'title' => 'Booking Error',
                    'msg_timeout' => 6000,
                    'csrf_hash' => $csrf_hash,
                ];
                echo json_encode($res);
                return;
            };

            if ($user_country === $traveller_destination) {
                if ($agent_name === $fullname || $agent_email === $email || $agent_phone === $number) {
                    $res = [
                        'status' => false,
                        'msg' => 'Use a different agent for deliveries to your current country.',
                        'title' => 'Booking Error',
                        'msg_timeout' => 6000,
                        'csrf_hash' => $csrf_hash,
                    ];
                    echo json_encode($res);
                    return;
                }
            };

            $traveller_details = $this->traveller_read_model->get_traveller_details_by_id($traveller_id);
            $calculations = json_decode($this->input->post('price_calculations'));
            $selected_space = $calculations->selectedSpace; // Use selectedSpace from calculations for accuracy
            $_POST['traveller_commission'] = $this->users_model->calculate_traveller_commission(
                $traveller_details,
                $selected_space,
                $this->input->post('items')
            );

            $booking = $this->users_model->add_booking_to_db($user_id, $fullname, $email);

            // Safely retrieve exchange rates
            $cad_rate_obj = $this->finance_read_model->get_most_recent_cad_exchange_rate();
            $pound_rate_obj = $this->finance_read_model->get_most_recent_pound_exchange_rate();

            $cad_rate = $cad_rate_obj ? $cad_rate_obj->rate : 0;
            $pound_rate = $pound_rate_obj ? $pound_rate_obj->rate : 0;

            // Get traveller details (only needed for route title)
            $traveller = $this->traveller_read_model->get_traveller_details_by_id($booking->traveller_id);

            if ($booking) {
                $currency = currency_code_normalize($booking->currency);
                $exchange_rate = ($currency === 'CAD') ? $cad_rate : $pound_rate;
                $charge_amount = (float) $booking->total_amount;
                $title_route = $traveller->location . ' - ' . $traveller->destination;

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
                                    'currency' => strtolower($currency),
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
                            'redirect' => $checkout_session->url,
                            'csrf_hash' => $csrf_hash,
                        ];

                        echo json_encode($res);
                        return;
                    } catch (Exception $e) {
                        // Handle Stripe errors
                        $res = [
                            'status' => false,
                            'msg' => 'We could not start your payment right now. Please try again.',
                            'title' => 'Payment Error.',
                            'msg_timeout' => 7000,
                            'csrf_hash' => $csrf_hash,
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
                            $this->users_model->mark_paystack_initialized($booking->id, $reference);

                            $res = [
                                'status' => true,
                                'msg' => 'Redirecting to Paystack to complete your payment.',
                                'title' => 'Booking Initialized',
                                'msg_timeout' => 5000,
                                'redirect' => $response->data->authorization_url,
                                'csrf_hash' => $csrf_hash,
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
                            'msg' => 'We could not start your payment right now. Please try again.',
                            'title' => 'Payment Error',
                            'msg_timeout' => 6000,
                            'csrf_hash' => $csrf_hash,
                        ];
                        echo json_encode($res);
                        return;
                    }
                } else {
                    $res = [
                        'status' => false,
                        'msg' => 'Select a valid payment method to continue.',
                        'title' => 'Payment Error.',
                        'msg_timeout' => 6000,
                        'csrf_hash' => $csrf_hash,
                    ];
                    echo json_encode($res);
                    return;
                }
            }
        } else {

            // Show validation errors
            $res = [
                'status' => false,
                'msg' => first_validation_error('Please complete the booking form and try again.'),
                'title' => 'Booking Error.',
                'msg_timeout' => 6000,
                'csrf_hash' => $csrf_hash,
            ];
            echo json_encode($res);
            return;
        }
    }


    public function stripe($hash = false, $status = 'No status')
    {
        $result = $this->users_model->finalize_booking_payment_by_hash($hash, $status == '1');
        $this->set_booking_flash($result);
        redirect($result['redirect']);
    }


    public function paystack($hash = false)
    {
        $reference = $this->input->get('reference');
        $booking = $this->user_bookings_model->dataByHash($hash);

        if (!$reference || !$booking) {
            $this->session->set_flashdata('status_error', 'This payment link is no longer valid.');
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
            $status = payment_status_normalize(($response && $response->status && $response->data->status === 'success') ? 'completed' : 'canceled');

            if ($status === 'completed') {
                $result = $this->users_model->finalize_booking_payment_by_hash($hash, true);
                $this->set_booking_flash($result);
                redirect($result['redirect']);
            } else {
                $result = [
                    'status' => false,
                    'msg' => 'Your payment was not completed.',
                    'title' => 'Booking Cancelled.',
                    'msg_timeout' => 7000,
                    'redirect' => 'history',
                ];
                $this->set_booking_flash($result);
                redirect($result['redirect']);
            }
        }
    }


    public function paystack_cancel($hash = false)
    {
        if ($hash) {
            $this->users_model->cancel_booking_payment_by_hash($hash);
        }

        $result = [
            'status' => false,
            'msg' => 'You cancelled the payment.',
            'title' => 'Booking Canceled.',
            'msg_timeout' => 7000,
            'redirect' => 'history',
        ];
        $this->set_booking_flash($result);
        redirect($result['redirect']);
    }


    private function set_booking_flash($result)
    {
        $flash_key = !empty($result['status']) ? 'status_success' : 'status_error';
        $this->session->set_flashdata($flash_key, $result['msg']);
        $this->session->set_flashdata('title', $result['title']);
        $this->session->set_flashdata('msg_timeout', $result['msg_timeout']);
    }


    public function booking_success()
    {
        $this->dashboard_header('Booking Successful');
        $this->load->view('users/booking_success');
        $this->dashboard_footer();
    }
}
