<?php
defined('BASEPATH') or die('Direct access not allowed');


class Market_place extends MY_Controller
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
        $this->dashboard_header('Market Place');
        $user_id = $this->user_details->id;
        $this->load->view('users/market_place');
        $this->dashboard_footer();
    }
}
