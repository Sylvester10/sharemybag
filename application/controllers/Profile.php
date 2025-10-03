<?php
defined('BASEPATH') or die('Direct access not allowed');


class Profile extends MY_Controller
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
        $user_details = $this->common_model->get_user_details($this->session->email);
        $this->dashboard_header('Profile');
        $data['user_details'] = $user_details;
        $this->load->view('users/profile', $data);
        $this->dashboard_footer();
    }


    public function profile_ajax($id)
    {
        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'profile');

        // validation rules
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('post_code', 'Post Code', 'trim|required');
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('number', 'Number', 'trim|required');

        if ($this->form_validation->run()) {

            // 
            if ($this->users_model->update_profile_to_db($id)) {

                $res = ['status' => true, 'msg' => 'Your profile has been updated successfully.', 'title' => 'Profile Updated.', 'msg_timeout' =>  6000];
                echo json_encode($res);
            } else {
                $res = ['status' => false, 'msg' => 'Profile could not be updated, try again later.'];
                echo json_encode($res);
            }
        } else {
            $res = ['status' => false, 'msg' => validation_errors()];
            echo json_encode($res); // Show validation errors
        }
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

            if ($this->users_model->change_password($id)) {

                $res = ['status' => true, 'msg' => 'Your password has been updated successfully.', 'title' => 'Password Updated.', 'msg_timeout' =>  6000];
                echo json_encode($res);
            } else {
                $res = ['status' => false, 'msg' => 'Profile could not be updated, try again later.'];
                echo json_encode($res);
            }
        } else {
            $res = ['status' => false, 'msg' => validation_errors()];
            echo json_encode($res); // Show validation errors
        }
    }
}
