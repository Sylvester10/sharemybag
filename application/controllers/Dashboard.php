<?php
defined('BASEPATH') or die('Direct access not allowed');


class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('user_read_model');
        $this->load->model('booking_read_model');
        $this->load->model('traveller_read_model');
        $this->load->model('shipping_model');
        $this->user_details = $this->user_read_model->get_user_details($this->session->email);
    }



    public function index()
    {
        $this->dashboard_header('Dashboard');
        $id = $this->user_details->id;
        $data['firstname'] = $this->user_details->firstname;
        $data['account_status'] = $this->user_details->account_status;
        $data['is_verified'] = $this->user_details->is_verified;
        $data['user_details'] = $this->users_model->is_profile_complete($id);
        $data['approved_travellers'] = $this->traveller_read_model->count_active_approved_travellers();
        $data['total_bookings'] = $this->booking_read_model->count_bookings_by_user_id($id);
        $this->load->view('users/dashboard', $data);
        $this->dashboard_footer();
    }


    public function kyc()
    {
        $this->dashboard_header('KYC Verification');
        $data['firstname'] = $this->user_details->firstname;
        $data['account_status'] = $this->user_details->account_status;
        $data['has_uploaded'] = $this->user_details->has_uploaded;
        $this->load->view('users/kyc', $data);
        $this->dashboard_footer();
    }


    // Track parcel
    public function track_parcel()
    {
        $tracking_id = $this->input->post('parcel');
        echo json_encode($this->shipping_model->get_tracking_payload($tracking_id));
    }


    public function profile_update()
    {
        $this->form_validation->set_rules('number', 'Phone Number', 'trim|required');
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('post_code', 'Post Code', 'trim|required');

        if ($this->form_validation->run()) {
            $id = $this->user_details->id;
            $this->users_model->update_profile_to_db($id);
            $this->session->set_flashdata('status_msg', "Profile Updated");
        } else {
            $this->session->set_flashdata('status_msg_error', validation_errors());
        }
        redirect('profile');
    }


    public function change_password($id)
    {
        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'profile');
        // validation rules
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
        $this->form_validation->set_rules(
            'confirm_password',
            'Confirm Password',
            'trim|required|matches[password]',
            array('matches' => 'Passwords do not match')
        );

        if ($this->form_validation->run()) {
            $this->users_model->change_password($id); //insert the data into db
            $this->session->set_flashdata('status_msg', "Password Updated");
            redirect('profile');
        } else {
            $this->session->set_flashdata('status_msg_error', validation_errors());
            redirect('profile');
        }
    }


}
