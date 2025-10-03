<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Recover_password extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_login_model');
        $this->load->model('common_model');
    }



    public function index()
    {
        //return user to dashboard if still logged in
        //$this->return_to_dashboard();
        $this->load->view('user_login/recover_password');
    }


    public function password_recovery_ajax()
    {
        $this->form_validation->set_rules('email', ' Email', 'trim|required|valid_email');
        $email = $this->input->post('email', TRUE);
        $email_exists = $this->user_login_model->check_user_email_exists($email);


        if ($this->form_validation->run()) {

            if ($email_exists) {

                $y = $this->common_model->get_user_details($email);
                $user_id = $y->id;
                $data['firstname'] = $y->firstname;
                $pass_reset_code = $y->pass_reset_code;
                $password_reset_url = base_url('recover_password/change_password/' . $user_id . '/' . $pass_reset_code);
                $reset_link = email_call2action_blue($password_reset_url, 'Reset Password');
                $data['reset_link'] = $reset_link;
                $data['pass_reset_code'] = $pass_reset_code;

                // Send email to User
                send_email_notification($this, $email, 'Reset Password', $data, 'password_recovery_email');

                $res = ['status' => true, 'msg' => 'We have sent a reset code to<br> <b>' . $email . '</b> <br> with further instructions to reset your password.'];
                echo json_encode($res);
                die;
            } else {
                $res = ['status' => false, 'msg' => 'This email address <br> <b> ' . $email . ' </b> <br> does not exist in our database. <br>  Please enter the email address that is associated with your account.'];
                echo json_encode($res);
                die;
            }
        } else { //form validation is not successful
            $res = ['status' => false, 'msg' => validation_errors()];
            echo json_encode($res);
        }

        return true;
    }


    public function change_password($id, $pass_reset_code)
    {
        $data['y'] = $this->common_model->get_user_details_by_id($id);
        $data['valid_code'] = $this->user_login_model->validate_pass_reset_code($id, $pass_reset_code);
        $this->load->view('user_login/change_password', $data);
    }


    public function change_password_ajax()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
        $this->form_validation->set_rules('pass_reset_code', 'Reset Code');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
        $this->form_validation->set_rules(
            'confirm_password',
            'Confirm Password',
            'trim|required|matches[password]',
            array('matches' => 'Passwords do not match')
        );

        $email = $this->input->post('email', TRUE);
        $pass_reset_code = $this->input->post('pass_reset_code', TRUE);
        $email_exists = $this->user_login_model->check_user_email_exists($email);

        if ($this->form_validation->run()) {

            $y = $this->common_model->get_user_details($email);
            $id = $y->id;
            if ($email_exists && $y->pass_reset_code === $pass_reset_code) {

                $this->user_login_model->change_pass($id);
                $res = ['status' => true, 'msg' => 'Password reset successful.'];
                echo json_encode($res);
                die;
            } else {
                //user supplied wrong password
                $res = ['status' => false, 'msg' => 'Incorrect reset code.'];
                echo json_encode($res);
                die;
            }
        } else { //form validation is not successful
            $res = ['status' => false, 'msg' => validation_errors()];
            echo json_encode($res);
        }
    }


    public function logout()
    {
        $data = array('email', 'user_loggedin');
        $this->session->unset_userdata($data);
        redirect(site_url('user-login'));
    }
}
