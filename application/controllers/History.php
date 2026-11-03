<?php
defined('BASEPATH') or die('Direct access not allowed');


class History extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('user_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('booking_read_model');
        $this->load->model('user_bookings_model');
        $this->load->model('travellers_model');
        $this->user_details = $this->user_read_model->get_user_details($this->session->email);
        $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
    }


    public function index()
    {
        $activeOnly = $this->input->get('filter', true) === 'active';
        $this->dashboard_header($activeOnly ? 'Active Bookings' : 'History');
        $user_id = $this->user_details->id;
        $data['active_only'] = $activeOnly;
        $data['booking'] = $this->booking_read_model->get_bookings_by_user_id($user_id, $activeOnly);
        $travellerIds = array_map(static function ($booking) { return (int) $booking->traveller_id; }, $data['booking']);
        $data['booking_routes'] = $this->traveller_read_model->get_route_countries_by_ids($travellerIds);
        $this->load->library('booking_checkout_flow');
        $this->load->model('booking_checkout_model');
        $data['resume_token'] = $this->booking_checkout_flow->intent();
        $data['booking_checkout'] = [];
        if ($this->booking_checkout_model->ready()) {
            foreach ($data['booking'] as $booking) {
                $checkout = $this->booking_checkout_model->checkout($booking->id,$user_id);
                if (!$checkout || payment_status_normalize($booking->payment_status) === 'completed') { continue; }
                $state = $this->booking_checkout_model->resume_state($booking,$user_id);
                $data['booking_checkout'][$booking->id] = $state;
            }
        }
        $data['user_country'] = $this->user_details->country;
        $this->load->view('users/history', $data);
        $this->dashboard_footer();
    }
}
