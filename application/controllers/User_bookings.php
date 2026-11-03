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
        $this->load->model('booking_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('user_bookings_model');
        $this->load->model('travellers_model');
        $this->load->library('booking_checkout_flow');
        $this->user_details = $this->user_read_model->get_user_details($this->session->email);
        $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
    }



    public function index()
    {
        $from = $this->input->get('from_booking',true);
        if ($from) {
            $booking = $this->booking_checkout_flow->owned_booking($from,$this->user_details->id);
            $this->load->model('booking_checkout_model');
            $checkout = $booking && $this->booking_checkout_model->ready() ? $this->booking_checkout_model->checkout($booking->id,$this->user_details->id) : null;
            $form = $checkout ? json_decode($checkout->form_data,true) : null;
            if (is_array($form)) {
                $this->session->set_userdata('booking_prefill',['form'=>booking_checkout_form($form),'expires'=>time()+900]);
            }
        }
        $this->dashboard_header('Search Travellers');
        $id = $this->user_details->id;
        $data['is_verified'] = $this->user_details->is_verified;
        $data['account_status'] = $this->user_details->account_status;
        $data['user_details'] = $this->user_details;
        $data['is_profile_incomplete'] = $this->users_model->is_profile_incomplete($id);
        $this->load->view('users/search_travellers', $data);
        $this->dashboard_footer();
    }


    // Search
    public function search()
    {
        $location = trim((string) $this->input->post('location', true));
        $destination = trim((string) $this->input->post('destination', true));
        $csrf_hash = $this->security->get_csrf_hash();
        $supported_countries = countries();

        if (
            !in_array($location, $supported_countries, true) ||
            !in_array($destination, $supported_countries, true) ||
            $location === $destination
        ) {
            echo json_encode(array(
                'status' => false,
                'msg' => 'Select two different route locations.',
                'csrf_hash' => $csrf_hash,
            ));
            return;
        }

        $travellers = $this->traveller_read_model->get_travellers_by_route($location, $destination);

        if (count($travellers) > 0) {
            $data = array();
            foreach ($travellers as $traveller) {
                $traveller = $this->travellers_model->update_traveller_space($traveller->id, true);
                if (!$traveller) {
                    continue;
                }

                $days = get_date_difference(date('Y-m-d H:i:s'), $traveller->travel_date);
                $days = !$days ? 'Today' : ($days > 1 ? "$days Days" : "$days Day");
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
                    'destination_area' => isset($traveller->destination_area) ? $traveller->destination_area : '',
                    'available_space' => $traveller->available_space,
                    'hash' => $traveller->hash,
                    'bag_locked' => $traveller->bag_locked,
                    'is_verified' => (int)$this->user_details->is_verified,
                    'profile_completed' => $profile_completed,
                    'destination' => $traveller->destination,
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
        $traveller = $this->traveller_read_model->get_traveller_details_by_hash($hash);
        if (
            !$traveller ||
            $traveller->status !== 'Approved' ||
            !$this->traveller_read_model->is_bookable_by_cutoff($traveller)
        ) {
            $this->session->set_flashdata('status_error', 'This traveller is no longer available for booking.');
            $this->session->set_flashdata('title', 'Traveller Unavailable');
            redirect('user_bookings');
            return;
        }

        $resume_hash = $this->input->get('resume', true);
        $data['booking_restore'] = [];
        $data['resume_booking'] = null;
        if ($resume_hash) {
            $booking = $this->booking_checkout_flow->owned_booking($resume_hash,$this->user_details->id);
            $restore = $this->booking_checkout_flow->restore($booking,$this->user_details->id);
            if (empty($restore['status']) || (int)$booking->traveller_id !== (int)$traveller->id) {
                $this->session->set_flashdata('status_error',$restore['msg'] ?? 'This booking is unavailable.');
                redirect('history'); return;
            }
            $data['booking_restore'] = $restore['form'];
            $data['resume_booking'] = $booking;
            $this->output->set_header('Cache-Control: no-store');
        } else {
            $prefill = $this->session->userdata('booking_prefill');
            if (is_array($prefill) && ($prefill['expires'] ?? 0) > time()) {
                $data['booking_restore'] = $prefill['form'];
            }
        }
        $data['resume_token'] = $this->booking_checkout_flow->intent();
        $data['booking_request_id'] = $this->booking_checkout_flow->new_request($traveller->id);
        $this->dashboard_header('Buy Space');
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
        echo (!$traveller || !$this->traveller_read_model->is_bookable_by_cutoff($traveller))
            ? 0
            : $traveller->available_space;
    }


    /* ========== Add Booking ========== */
    public function add_booking_ajax()
    {
        $csrf_hash = $this->security->get_csrf_hash();
        $post = $this->input->post(null,true);
        if ($this->input->method() !== 'post' || !is_array($post) || !$this->booking_checkout_flow->valid_intent($post)) {
            $this->checkout_json(['status'=>false,'msg'=>'Reload the booking page and try again.']); return;
        }
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
        $this->form_validation->set_rules('agent_country_code', 'agent country code', 'trim|required', array('required' => 'Please select agent country code'));
        $this->form_validation->set_rules('agent_phone', 'agent mobile', 'trim|required', array('required' => 'Please enter agent number'));
        $this->form_validation->set_rules('agent_email', 'agent email', 'trim|valid_email|required', array('required' => 'Please enter agent number', 'valid_email' => 'Please enter a valid email'));
        $this->form_validation->set_rules('agent_address', 'agent Address', 'trim|required', array('required' => 'Please enter agent address'));
        $this->form_validation->set_rules('agent_locality', 'agent Local', 'trim|required', array('required' => 'Please enter agent locale'));
        $this->form_validation->set_rules('agent_postcode', 'agent Postcode', 'trim');
        $this->form_validation->set_rules('receiver_country_code', 'receiver country code', 'trim|required', array('required' => 'Please select receiver country code'));
        $this->form_validation->set_rules('receiver_phone', 'receiver Mobile', 'trim|required', array('required' => 'Please enter receiver number'));
        $this->form_validation->set_rules('receiver_email', 'receiver email', 'trim|valid_email|required', array('required' => 'Please enter receiver number', 'valid_email' => 'Please enter a valid email'));
        $this->form_validation->set_rules('receiver_address', 'receiver Address', 'trim|required', array('required' => 'Please enter receiver address'));
        $this->form_validation->set_rules('receiver_locality', 'receiver Local', 'trim|required', array('required' => 'Please enter receiver locale'));
        $this->form_validation->set_rules('receiver_postcode', 'receiver Postcode', 'trim');
        $this->form_validation->set_rules('payment_method', 'Payment Method', 'trim|required|in_list[stripe,paystack]', array('required' => 'Please select a payment method', 'in_list' => 'Invalid payment method selected.'));

        if (!$this->form_validation->run()) {
            $this->checkout_json(['status'=>false,'msg'=>first_validation_error('Please complete the booking form.')]); return;
        }
        if (trim((string)($post['agent_name'] ?? '')) === trim((string)($post['receiver_name'] ?? ''))) {
            $this->checkout_json(['status'=>false,'msg'=>'Enter different details for the agent and receiver.']); return;
        }
        $this->checkout_json($this->booking_checkout_flow->submit($post,$this->user_details));
    }

    public function resume_ajax()
    {
        $post = $this->input->post(null,true);
        if ($this->input->method() !== 'post' || !is_array($post)) {
            $this->checkout_json(['status'=>false,'msg'=>'Open History and use the booking action to continue.']); return;
        }
        $this->checkout_json($this->booking_checkout_flow->resume($post,$this->user_details));
    }

    private function checkout_json(array $result)
    {
        $result['csrf_hash'] = $this->security->get_csrf_hash();
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function payment_return($reference = '') { $this->finish_payment_return($reference,false); }
    public function payment_cancel($reference = '') { $this->finish_payment_return($reference,true); }

    private function finish_payment_return($reference, $close)
    {
        $this->load->model('booking_checkout_model');
        $attempt = $this->booking_checkout_model->ready() ? $this->booking_checkout_model->attempt_by_reference($reference) : null;
        if (!$attempt || (int)$attempt->user_id !== (int)$this->user_details->id) {
            $this->set_booking_flash(['status'=>false,'msg'=>'This payment is unavailable.','title'=>'Payment unavailable','redirect'=>'history']);
            redirect('history'); return;
        }
        $this->load->library('booking_payment_reconciler');
        $result = $this->booking_payment_reconciler->reconcile($attempt,$close);
        $paid = !empty($result['paid']);
        $this->set_booking_flash(['status'=>$paid,'booking_id'=>$attempt->booking_id,
            'msg'=>$paid?'Your booking payment is confirmed.':($result['msg'] ?? 'We are checking this payment.'),
            'title'=>$paid?'Booking confirmed':'Payment status','redirect'=>$paid?'booking-success':'history']);
        redirect($paid?'booking-success':'history');
    }

    public function stripe($hash = false, $status = 'No status')
    {
        $booking = $this->booking_checkout_flow->owned_booking($hash,$this->user_details->id);
        if ($booking) {
            $this->load->model('booking_checkout_model');
            $attempt = $this->booking_checkout_model->ready() ? $this->booking_checkout_model->latest_attempt($booking->id) : null;
            if ($attempt) { $this->finish_payment_return($attempt->reference,$status === '0'); return; }
        }
        $paid = $booking && payment_status_normalize($booking->payment_status) === 'completed';
        $this->set_booking_flash(['status'=>$paid,'booking_id'=>$booking->id ?? 0,
            'msg'=>$paid?'Your booking payment is confirmed.':'This older payment needs verification. Please contact support if you paid.',
            'title'=>'Payment status','redirect'=>$paid?'booking-success':'history']);
        redirect($paid?'booking-success':'history');
    }

    public function paystack($hash = false)
    {
        $booking = $this->booking_checkout_flow->owned_booking($hash,$this->user_details->id);
        $reference = (string)$this->input->get('reference',true);
        if (!$booking || !$reference || !hash_equals((string)($booking->paystack_ref ?? ''),$reference)) {
            $this->session->set_flashdata('status_error','This payment link is unavailable.'); redirect('history'); return;
        }
        $this->load->model('booking_checkout_model');
        $attempt = $this->booking_checkout_model->ready() ? $this->booking_checkout_model->attempt_by_reference($reference) : null;
        if ($attempt) { $this->finish_payment_return($reference,false); return; }
        // Older Paystack bookings retain a server-verifiable reference and expected amount.
        $this->load->library('booking_payment_gateway');
        $legacy = (object)['provider'=>'paystack','reference'=>$reference,'booking_id'=>$booking->id,
            'user_id'=>$booking->user_id,'provider_session_id'=>null,'currency'=>'NGN',
            'amount_minor'=>(int)round((float)$booking->total_amount*(float)$booking->paystack_exchange_rate*100)];
        $verified = $this->booking_payment_gateway->verify($legacy);
        if (($verified['outcome'] ?? '') === 'paid') {
            $result = $this->users_model->finalize_booking_payment_by_hash($hash,true);
        } else {
            $result = ['status'=>false,'title'=>'Payment status','msg'=>'Your payment has not been confirmed. Please check again or contact support.','redirect'=>'history'];
        }
        $this->set_booking_flash($result); redirect($result['redirect']);
    }

    public function paystack_cancel($hash = false)
    {
        $booking = $this->booking_checkout_flow->owned_booking($hash,$this->user_details->id);
        if ($booking) {
            $this->load->model('booking_checkout_model');
            $attempt = $this->booking_checkout_model->ready() ? $this->booking_checkout_model->latest_attempt($booking->id) : null;
            if ($attempt) { $this->finish_payment_return($attempt->reference,true); return; }
        }
        $this->session->set_flashdata('status_error','Payment was not completed.'); redirect('history');
    }

    private function set_booking_flash($result)
    {
        $flash_key = !empty($result['status']) ? 'status_success' : 'status_error';
        $this->session->set_flashdata($flash_key, $result['msg']);
        $this->session->set_flashdata('title', $result['title']);
        $this->session->set_flashdata('msg_timeout', ($result['msg_timeout'] ?? 6000));

        if (!empty($result['status']) && !empty($result['booking_id'])) {
            $this->session->set_flashdata('booking_success_id', (int) $result['booking_id']);
        }
    }


    public function booking_success()
    {
        $data['booking'] = null;
        $booking_id = (int) $this->session->flashdata('booking_success_id');

        if ($booking_id > 0) {
            $booking = $this->booking_read_model->get_booking_details_by_id($booking_id);
            if (
                $booking &&
                (int) $booking->user_id === (int) $this->user_details->id &&
                payment_status_normalize($booking->payment_status) === 'completed'
            ) {
                $data['booking'] = $booking;
            }
        }

        $this->dashboard_header('Booking Successful');
        $this->load->view('users/booking_success', $data);
        $this->dashboard_footer();
    }
}
