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
		$this->load->view('user_login/login');
	}


	// public function login_ajax()
	// {
	// 	$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
	// 	$this->form_validation->set_rules('password', 'Password', 'required');

	// 	$email = $this->input->post('email', TRUE);
	// 	$password = $this->input->post('password', TRUE);
	// 	$email_exists = $this->user_login_model->check_user_email_exists($email);

	// 	if ($this->form_validation->run()) {

	// 		$y = $this->common_model->get_user_details($email);

	// 		if ($y->account_status == 0){
	// 			$res = ['status' => false, 'msg' => 'Unable to login. Contact Admin'];
	// 			echo json_encode($res);
	// 			die;
	// 		}

	// 		if ($email_exists && password_verify($password, $y->password)) {

	// 			//email and password correct and user is active, create session with email and create login session
	// 			$login_data = array(
	// 				'email' => $y->email,
	// 				'user_id' => $y->id, // Store user_id as well
	// 				'user_loggedin' => true
	// 			);
	// 			$this->session->set_userdata($login_data);
	// 			$this->common_model->update_last_login($y->id);
	// 			$res = ['status' => true];
	// 			echo json_encode($res);
	// 			die;
	// 		} else {
	// 			//admin supplied wrong password
	// 			$res = ['status' => false, 'msg' => 'Invalid login. Username or password incorrect'];
	// 			echo json_encode($res);
	// 			die;
	// 		}
	// 	} else { //form validation is not successful
	// 		$res = ['status' => false, 'msg' => validation_errors()];
	// 		echo json_encode($res);
	// 	}
	// }

	public function login_ajax()
	{
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run()) {
			$email = $this->input->post('email', TRUE);
			$password = $this->input->post('password', TRUE);

			// Fetch user details first
			$user = $this->common_model->get_user_details($email);

			// Check if user exists AND password matches
			if ($user && password_verify($password, $user->password)) {

				// check if the account is active
				if ($user->account_status == 0) {
					echo json_encode(['status' => false, 'msg' => 'Your account is inactive. Please contact Admin.']);
					return; // Use return instead of die for cleaner execution
				}

				// Success: Set session and update login time
				$login_data = [
					'email'         => $user->email,
					'user_id'       => $user->id,
					'user_loggedin' => true
				];
				$this->session->set_userdata($login_data);
				$this->common_model->update_last_login($user->id);

				echo json_encode(['status' => true]);
				return;
			} else {
				// Generic error for either wrong email or wrong password
				echo json_encode(['status' => false, 'msg' => 'Invalid email or password.']);
				return;
			}
		} else {
			// Form validation failed
			echo json_encode(['status' => false, 'msg' => validation_errors()]);
		}
	}


	// public function loginPhone()
	// {
	// 	$this->load->view('user_login/login_phone');
	// }


	// public function send_otp()
	// {
	// 	// 1. Get phone number
	// 	$phone = $this->input->post('phone');
	// 	$country_code = $this->input->post('country_code'); // e.g., +234

	// 	// Basic validation
	// 	if (empty($phone) || empty($country_code)) {
	// 		echo json_encode(['status' => false, 'msg' => 'Country code and phone number are required.']);
	// 		return;
	// 	}

	// 	// 2. Clean phone number and create the full phone number to check in DB
	// 	// (e.g., remove leading zero if country code is +234)
	// 	if ($country_code == '+234' && substr($phone, 0, 1) == '0') {
	// 		$phone = substr($phone, 1); // $phone is now '7069785153'
	// 	}

	// 	// This is the format you said is in your database (e.g., +2347069785153)
	// 	$dbPhone = $country_code . $phone;

	// 	// 3. Check if user exists with this full phone number
	// 	// *** THIS IS THE FIX ***
	// 	// Search the database using the full international number ($dbPhone)
	// 	$user = $this->common_model->get_users_phone($dbPhone);

	// 	if (!$user) {
	// 		// This message is correct.
	// 		echo json_encode(['status' => false, 'msg' => 'This phone number is not registered with an account.']);
	// 		return;
	// 	}

	// 	// 4. User exists. Prepare number for Infobip (remove the '+')
	// 	$infobipPhone = str_replace('+', '', $dbPhone); // e.g., 2347069785153

	// 	// 5. Send OTP to the Infobip-formatted number
	// 	$this->load->helper('infobip');
	// 	$send = send_infobip_otp($infobipPhone); // This is correct, Infobip needs the number without '+'

	// 	if ($send['status']) {
	// 		// 6. Store pinId and the USER'S ID in session temporarily
	// 		$this->session->set_userdata('otp_pin_id', $send['pinId']);
	// 		$this->session->set_userdata('otp_user_id', $user->id); // Store user ID for verification

	// 		echo json_encode(['status' => true, 'msg' => 'OTP sent successfully to ' . $dbPhone]);
	// 	} else {
	// 		log_message('error', 'Infobip Send OTP Failed: ' . json_encode($send));
	// 		echo json_encode(['status' => false, 'msg' => 'Failed to send OTP. Please try again.', 'error' => $send['response']]);
	// 	}
	// }



	// public function verify_otp()
	// {
	// 	// 1. Get PIN from form and pinId from session
	// 	$pin = $this->input->post('otp'); // From JS: data: { otp }
	// 	$pinId = $this->session->userdata('otp_pin_id');
	// 	$user_id = $this->session->userdata('otp_user_id'); // Get the user ID we stored

	// 	if (empty($pin) || empty($pinId) || empty($user_id)) {
	// 		echo json_encode(['status' => false, 'msg' => 'Invalid session or missing OTP. Please request a new code.']);
	// 		return;
	// 	}

	// 	$this->load->helper('infobip');
	// 	$verify = verify_infobip_otp($pinId, $pin);

	// 	if ($verify['status']) {
	// 		// 2. OTP verified successfully! Now, log the user in.
	// 		$user = $this->common_model->get_user_details_by_id($user_id);
	// 		if (!$user) {
	// 			echo json_encode(['status' => false, 'msg' => 'User account not found.']);
	// 			return;
	// 		}

	// 		// 3. Create the real login session
	// 		$login_data = array(
	// 			'email' => $user->email, // Store email, just like email login
	// 			'user_id' => $user->id,
	// 			'user_loggedin' => true
	// 		);
	// 		$this->session->set_userdata($login_data);
	// 		$this->common_model->update_last_login($user->id);

	// 		// 4. Clean up temp session data
	// 		$this->session->unset_userdata(['otp_pin_id', 'otp_user_id']);

	// 		echo json_encode(['status' => true, 'msg' => 'Login successful']);
	// 	} else {
	// 		// 5. Verification failed
	// 		log_message('error', 'Infobip Verify OTP Failed: ' . json_encode($verify));
	// 		echo json_encode(['status' => false, 'msg' => 'Invalid or expired OTP.']);
	// 	}
	// }


	public function logout()
	{
		$data = array('email', 'user_id', 'user_loggedin');
		$this->session->unset_userdata($data);
		redirect(site_url('signin'));
	}
}
