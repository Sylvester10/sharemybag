<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_login extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_read_model');
	}

	public function index()
	{
		$this->load->view('user_login/login');
	}

	public function login_ajax()
	{
		$csrf_hash = $this->security->get_csrf_hash();
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if (!$this->form_validation->run()) {
			echo json_encode([
				'status' => false,
				'msg' => first_validation_error('Enter your email address and password.'),
				'title' => 'Sign In Error',
				'msg_timeout' => 6000,
				'csrf_hash' => $csrf_hash
			]);
			return;
		}

		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password', TRUE);
		$user = $this->user_read_model->get_user_details($email);

		if ($user && password_verify($password, $user->password)) {
			// Optional: Check account status here if needed
			// if ($user->account_status == 0) { ... }

			$this->session->set_userdata([
				'email'         => $user->email,
				'user_id'       => $user->id,
				'user_loggedin' => true
			]);

			$this->common_model->update_last_login($user->id);
			echo json_encode([
				'status' => true,
				'msg' => 'Sign-in successful.',
				'title' => 'Welcome Back',
				'msg_timeout' => 3000,
				'csrf_hash' => $csrf_hash
			]);
		} else {
			echo json_encode([
				'status' => false,
				'msg' => 'Enter a valid email and password.',
				'title' => 'Sign In Error',
				'msg_timeout' => 6000,
				'csrf_hash' => $csrf_hash
			]);
		}
	}

	public function logout()
	{
		$this->session->unset_userdata(['email', 'user_id', 'user_loggedin']);
		redirect(site_url('signin'));
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
	// 	$user = $this->user_read_model->get_users_phone($dbPhone);

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
	// 		$user = $this->user_read_model->get_user_details_by_id($user_id);
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
}
