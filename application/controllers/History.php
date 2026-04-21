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
        $this->dashboard_header('History');
        $user_id = $this->user_details->id;
        $data['booking'] = $this->booking_read_model->get_bookings_by_user_id($user_id);
        $data['user_country'] = $this->user_details->country;
        $this->load->view('users/history', $data);
        $this->dashboard_footer();
    }
}
