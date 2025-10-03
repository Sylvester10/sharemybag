<?php
defined('BASEPATH') or exit('No direct script access allowed');


class User_login extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_login_model');
		$this->load->model('common_model');
	}



	/* ========= User Login ============ */
	public function index()
	{
		//return user to dashboard if still loggedin
		//$this->return_to_dashboard();
		$this->load->view('user_login/login');
	}


	public function login_ajax()
	{
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');

		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password', TRUE);
		$email_exists = $this->user_login_model->check_user_email_exists($email);

		if ($this->form_validation->run()) {

			$y = $this->common_model->get_user_details($email);
			if ($email_exists && password_verify($password, $y->password)) {

				//email and password correct and user is active, create session with email and create login session
				$login_data = array('email' => $email, 'user_loggedin' => true);
				$this->session->set_userdata($login_data);
				$this->common_model->update_last_login($y->id);
				$res = ['status' => true];
				echo json_encode($res);
				die;
			} else {
				//admin supplied wrong password
				$res = ['status' => false, 'msg' => 'Invalid login. Username or password incorrect'];
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
		redirect(site_url('signin'));
	}
}
