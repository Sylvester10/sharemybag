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
        $data['user_details'] = $this->users_model->is_profile_complete($id);
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
        $data['currency'] = 'pounds';
        $data['symbol'] = '&pound;';
        $data['one_naira'] = $this->common_model->one_naira();
        $data['one_pound'] = $this->common_model->one_pound();
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
        $this->form_validation->set_rules('traveller_arrival_state', 'Arrival State', 'trim');

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
            $address = $this->user_details->address;

            if ($this->input->post('agent_name') == $this->input->post('receiver_name')) {
                $res = [
                    'status' => false,
                    'msg' => 'Agent cannot be same as receiver',
                    'title' => 'Booking Error.',
                    'msg_timeout' => 6000,
                ];
                echo json_encode($res);
                return;
            };

            $booking = $this->users_model->add_booking_to_db($user_id, $fullname, $email);
            $rate = $this->common_model->get_most_recent_exchange_rate()->rate;

            if ($booking) {
                $currency = 'gbp';
                $exchange_rate = $rate; // You can fetch this from your DB or API if dynamic
                $gbp_amount = (float)$booking->total_amount;
                $ngn_amount = $gbp_amount * $exchange_rate;
                $title = 'Purchasing ' . $booking->selected_space . 'KG Bag Space United Kingdom - Nigeria';

                if ($payment_method === 'stripe') {
                    // Create Stripe Checkout session
                    try {

                        $stripeSecretKey = '';
                        \Stripe\Stripe::setApiKey($stripeSecretKey); // Use your Stripe secret key

                        $checkout_session = \Stripe\Checkout\Session::create([
                            'line_items' => [[
                                'price_data' => array(
                                    'currency' => $currency,
                                    'unit_amount' => $gbp_amount * 100,
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
                    // Create Paystack Checkout session
                    try {
                        $paystackSecretKey = ''; // Replace with your Paystack test secret key

                        $reference = 'SMB' . uniqid(); // Unique reference for transaction
                        $callback_url = base_url() . 'user_bookings/paystack/' . $booking->hash . '?reference=' . $reference;

                        $fields = [
                            'email' => $email,
                            'amount' => $ngn_amount * 100, // Convert to kobo
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

            // Verify Paystack payment
            $secretKey = ''; // Replace with your test key in production

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
            $this->db->update('bookings', ['payment_status' => $status]);

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


    public function checks()
    {
        var_dump($this->travellers_model->dataById(71));
        return;

        $stripeSecretKey = '';
        \Stripe\Stripe::setApiKey($stripeSecretKey); // Use your Stripe secret key

        $YOUR_DOMAIN = 'http://localhost/smb/';

        $stripe = new \Stripe\StripeClient($stripeSecretKey);



        // $product = $stripe->products->create(
        //     [
        //         'name' => 'SMB-Checkout',
        //         'description' => 'This is the test description.',
        //         'images' => ['https://www.foodiesfeed.com/wp-content/uploads/2023/06/burger-with-melted-cheese.jpg'],
        //     ]
        // );

        // $price = $stripe->prices->create([
        //     'product' => $product->id,
        //     'unit_amount' => 1200000,
        //     'currency' => 'ngn',
        // ]);

        // print_p($price);
        // return;

        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
                # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
                'price_data' => array(
                    'currency' => 'gbp',
                    'unit_amount' => 10500,
                    'product_data' => array(
                        'name' => '2kg Bag Space NG - UK',
                        'description' => 'This is the test description.',
                        'images' => ['https://m.media-amazon.com/images/I/61UDx9jF0mL._AC_SL1315_.jpg'],
                    ),

                ),
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . 'user_bookings/stripe/SMBXHZK52/1',
            'cancel_url' => $YOUR_DOMAIN . 'user_bookings/stripe/cancel',
            'automatic_tax' => [
                'enabled' => true,
            ],
        ]);

        print_p($checkout_session->url);
    }


    public function check()
    {
        $this->load->view('users/check');
    }
}
