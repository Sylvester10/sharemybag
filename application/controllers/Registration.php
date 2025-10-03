<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Registration extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('users_model');
    }



    /* ========= User Signup ============ */
    public function index()
    {
        $this->load->view('user_login/signup');
    }
    

    public function signup()
    {
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email',  'Email',  'trim|required|valid_email|is_unique[users.email]',
            array('is_unique' => 'This email address is already registered to another user')
        );
        $this->form_validation->set_rules('country', 'Country', 'trim|required');

        $email = $this->input->post('email', TRUE);

        if ($this->form_validation->run()) {
            $user_id = $this->users_model->add_new_user(); // insert the data into the database and get user ID
            $this->session->set_userdata('user_id', $user_id); // Store user ID in session
            $this->session->set_userdata('is_verification_pending', true); // Set verification session flag
            $res = ['status' => true, 'msg' => "A verification code was sent to your email <br> <b>$email</b> "];
            echo json_encode($res);
        } else {
            $res = ['status' => false, 'msg' => validation_errors()];
            echo json_encode($res);
        }
    }


    public function verify_email()
    {
        if (!$this->session->userdata('is_verification_pending')) {
            redirect('signin'); // Redirect to signin if no verification is pending
        }

        $this->load->view('user_login/verify_email');
    }

    public function verify_email_ajax()
    {
        $this->form_validation->set_rules('verification_code', 'Verification Code', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]',
            array('matches' => 'Passwords do not match'));

        if ($this->form_validation->run()) {
            $verification_code = $this->input->post('verification_code', TRUE);
            $password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);

            // Fetch the user by verification code and check if it matches
            $user = $this->users_model->get_user_by_verification_code($verification_code);

            if ($user) {
                // Update user password, mark as verified, and set a new verification code
                $this->users_model->update_user_verification($user->id, $password);

                // Generate a new verification code and update the database
                $new_verification_code = generate_verification_code();
                $this->users_model->update_user_verification_code($user->id, $new_verification_code);

                // Remove session flag to prevent re-access to the verification page
                $this->session->unset_userdata('is_verification_pending');

                $res = ['status' => true, 'msg' => 'Your email has been successfully verified.'];
            } else {
                $res = ['status' => false, 'msg' => 'Invalid verification code.'];
            }
            echo json_encode($res);
        } else {
            $res = ['status' => false, 'msg' => validation_errors()];
            echo json_encode($res);
        }
    }

    public function resend_verification_email_ajax()
    {
        // Assume user_id is stored in session after signup
        $user_id = $this->session->userdata('user_id');

        if ($user_id) {
            $this->users_model->resend_verification_code($user_id);
            $res = ['status' => true, 'msg' => 'A new verification code has been sent to your email.'];
            echo json_encode($res);
        } else {
            $res = ['status' => false, 'msg' => 'Unable to send code. Please wait 30 seconds and try again'];
            echo json_encode($res);
        }
    }




}
