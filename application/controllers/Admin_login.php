<?php
defined('BASEPATH') or exit('No direct script access allowed');



class Admin_login extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin_login_model');
    }



    /* ========= Admin Login ============ */
    public function index()
    {
        $this->admin_login_header('Admin Login');
        $data['captcha_code'] = mt_rand(111111, 999999);
        $this->load->view('admin/login/login', $data);
        $this->admin_login_footer();
    }


    public function login_ajax()
    {
        $this->form_validation->set_rules(
            'email',
            'Email',
            'trim|required|valid_email',
            array('required' => 'Please enter your email')
        );
        $this->form_validation->set_rules(
            'password',
            'Password',
            'required',
            array('required' => 'Please enter your password')
        );
        $this->form_validation->set_rules('captcha_code', 'Captcha Code', 'trim');
        $this->form_validation->set_rules(
            'c_captcha_code',
            'Captcha Code',
            'trim|required|matches[captcha_code]',
            array(
                'required' => 'Captcha is required. Reload the page if you cannot see any code.',
                'matches' => 'Invalid captcha code'
            )
        );

        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);
        $email_exists = $this->admin_login_model->check_admin_email_exists($email);


        if ($this->form_validation->run()) {

            $y = $this->common_model->get_admin_details($email);
            if ($email_exists && $y->password == $password) {

                //email and password correct and user is active, create session with email and create login session
                $login_data = array('email' => $email, 'admin_loggedin' => true);
                $this->session->set_userdata($login_data);
                $res = ['status' => true, 'msg' => 'Login successful! <br> Redirecting to dashboard....'];
                echo json_encode($res);
                die;
            } else {
                //admin supplied wrong password
                $res = ['status' => false, 'msg' => 'Invalid login! Email or password incorrect.'];
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
        $data = array('email', 'admin_loggedin');
        //$this->session->unset_userdata($data);
        $this->session->sess_destroy($data);
        redirect(site_url('admin_login'));
    }
}
