<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_login extends MY_Controller
{
    private const LOGIN_RATE_LIMIT_MAX = 5;
    private const LOGIN_RATE_LIMIT_WINDOW = 900;
    private const CODE_REQUEST_RATE_LIMIT_MAX = 3;
    private const CODE_VERIFY_RATE_LIMIT_MAX = 5;
    private const CODE_RATE_LIMIT_WINDOW = 900;
    private const SIGNUP_RESUME_TTL = 3600;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_read_model');
		$this->load->model('users_model');
		$this->load->model('auth_challenge_model');
	}

	public function index()
	{
		$this->load->view('user_login/login');
	}

	public function login_ajax()
	{
		$csrf_hash = $this->security->get_csrf_hash();
		$identifier = trim((string) ($this->input->post('identifier', TRUE) ?: $this->input->post('email', TRUE)));
		if (empty($_POST['identifier']) && $identifier !== '') {
			$_POST['identifier'] = $identifier;
		}
		$login_throttle_key = 'login:' . get_user_ip() . ':' . strtolower($identifier !== '' ? $identifier : 'unknown');
		$this->form_validation->set_rules('identifier', 'Email or WhatsApp number', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if (!$this->form_validation->run()) {
			auth_throttle_hit($login_throttle_key, self::LOGIN_RATE_LIMIT_MAX, self::LOGIN_RATE_LIMIT_WINDOW);
			echo json_encode([
				'status' => false,
				'msg' => first_validation_error('Enter your email or verified WhatsApp number and password.'),
				'title' => 'Sign In Error',
				'msg_timeout' => 6000,
				'csrf_hash' => $csrf_hash
			]);
			return;
		}

		$password = $this->input->post('password', TRUE);
		$login_throttle_state = auth_throttle_check($login_throttle_key, self::LOGIN_RATE_LIMIT_MAX, self::LOGIN_RATE_LIMIT_WINDOW);
		if (!$login_throttle_state['allowed']) {
			echo json_encode([
				'status' => false,
				'msg' => auth_throttle_message($login_throttle_state['retry_after'], 'sign in'),
				'title' => 'Too Many Attempts',
				'msg_timeout' => 7000,
				'csrf_hash' => $csrf_hash
			]);
			return;
		}
		$user = $this->user_read_model->get_login_user($identifier);

		if ($user && filter_var($identifier, FILTER_VALIDATE_EMAIL) && empty($user->password)) {
			$new_verification_code = generate_verification_code();
			$this->users_model->update_user_verification_code($user->id, $new_verification_code);
			$this->users_model->resend_verification_code($user->id);
			$resume_token = $this->users_model->issue_signup_resume_token($user->id, self::SIGNUP_RESUME_TTL);
			auth_throttle_clear($login_throttle_key);
			echo json_encode([
				'status' => true,
				'msg' => 'Your account setup is not complete yet. Verify your email to continue.',
				'title' => 'Complete Setup',
				'msg_timeout' => 7000,
				'redirect' => base_url('verify-email/' . rawurlencode((string) $resume_token)),
				'csrf_hash' => $csrf_hash
			]);
			return;
		}

		if ($user && !empty($user->password) && password_verify($password, $user->password)) {
			if (!$this->accountCanSignIn($user, $csrf_hash)) {
				return;
			}

			$this->completeLogin($user);
			auth_throttle_clear($login_throttle_key);
			echo json_encode([
				'status' => true,
				'msg_timeout' => 3000,
				'csrf_hash' => $csrf_hash
			]);
		} else {
			auth_throttle_hit($login_throttle_key, self::LOGIN_RATE_LIMIT_MAX, self::LOGIN_RATE_LIMIT_WINDOW);
			echo json_encode([
				'status' => false,
				'msg' => 'Enter valid account details and try again.',
				'title' => 'Sign In Error',
				'msg_timeout' => 6000,
				'csrf_hash' => $csrf_hash
			]);
		}
	}

	public function passwordless_request_ajax()
	{
		$csrf_hash = $this->security->get_csrf_hash();
		$identifier = trim((string) $this->input->post('identifier', TRUE));
		$this->form_validation->set_rules('identifier', 'Email or WhatsApp number', 'trim|required');

		if (!$this->form_validation->run()) {
			echo json_encode(array('status' => false, 'msg' => first_validation_error('Enter your email or WhatsApp number.'), 'title' => 'Check Your Details', 'csrf_hash' => $csrf_hash));
			return;
		}

		$key = 'passwordless-request:' . get_user_ip() . ':' . sha1(strtolower($identifier));
		$state = auth_throttle_check($key, self::CODE_REQUEST_RATE_LIMIT_MAX, self::CODE_RATE_LIMIT_WINDOW);
		if (!$state['allowed']) {
			echo json_encode(array('status' => false, 'msg' => auth_throttle_message($state['retry_after'], 'code request'), 'title' => 'Too Many Requests', 'csrf_hash' => $csrf_hash));
			return;
		}

		auth_throttle_hit($key, self::CODE_REQUEST_RATE_LIMIT_MAX, self::CODE_RATE_LIMIT_WINDOW);
		$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
		$user = $this->user_read_model->get_login_user($identifier);

		if (!$user || empty($user->password) || (int) $user->account_status === 0) {
			$this->sendAnonymousChallengeResponse($isEmail ? 'email' : 'phone', $csrf_hash);
			return;
		}

		if ($isEmail) {
			$code = generate_verification_code();
			$token = $this->auth_challenge_model->createChallenge($user->id, 'login', 'email', strtolower($user->email), $code);
			if (!$token) {
				echo json_encode(array('status' => false, 'msg' => 'We could not prepare a sign-in code. Please try again.', 'title' => 'Code Not Sent', 'csrf_hash' => $csrf_hash));
				return;
			}

			if (!send_email_notification($this, $user->email, 'Your Sign-In Code', array(
				'firstname' => $user->firstname,
				'login_code' => $code,
			), 'user_login_code_email')) {
				$this->auth_challenge_model->consume($this->challengeIdFromToken($token, 'login'));
				$this->sendAnonymousChallengeResponse('email', $csrf_hash);
				return;
			}
			$channel = 'email';
			$destination = $user->email;
		} else {
			$channel = $this->auth_challenge_model->getPhoneOtpChannel();
			$this->load->library('twilio_verify_service');
			$result = $this->twilio_verify_service->sendCode($user->verified_phone_e164, $channel);
			if (empty($result['success'])) {
				$this->sendAnonymousChallengeResponse('phone', $csrf_hash);
				return;
			}

			$token = $this->auth_challenge_model->createChallenge($user->id, 'login', $channel, $user->verified_phone_e164);
			if (!$token) {
				echo json_encode(array('status' => false, 'msg' => 'We could not prepare a sign-in code. Please try again.', 'title' => 'Code Not Sent', 'csrf_hash' => $csrf_hash));
				return;
			}
			$destination = $user->verified_phone_e164;
		}

		echo json_encode(array(
			'status' => true,
			'msg' => 'Enter the one-time code we sent to continue.',
			'challenge_token' => $token,
			'delivery_channel' => $channel,
			'destination_hint' => $this->maskDestination($destination, $channel),
			'resend_after' => 30,
			'csrf_hash' => $csrf_hash,
		));
	}

	public function passwordless_verify_ajax()
	{
		$csrf_hash = $this->security->get_csrf_hash();
		$token = trim((string) $this->input->post('challenge_token', TRUE));
		$code = trim((string) $this->input->post('code', TRUE));
		$this->form_validation->set_rules('challenge_token', 'Verification session', 'trim|required');
		$this->form_validation->set_rules('code', 'One-time code', 'trim|required|numeric|exact_length[6]');

		if (!$this->form_validation->run()) {
			echo json_encode(array('status' => false, 'msg' => first_validation_error('Enter the complete one-time code.'), 'title' => 'Check Your Code', 'csrf_hash' => $csrf_hash));
			return;
		}

		$key = 'passwordless-verify:' . get_user_ip() . ':' . sha1($token);
		$state = auth_throttle_check($key, self::CODE_VERIFY_RATE_LIMIT_MAX, self::CODE_RATE_LIMIT_WINDOW);
		if (!$state['allowed']) {
			echo json_encode(array('status' => false, 'msg' => auth_throttle_message($state['retry_after'], 'code verification'), 'title' => 'Too Many Attempts', 'csrf_hash' => $csrf_hash));
			return;
		}

		$challenge = $this->auth_challenge_model->getActiveChallenge($token, 'login');
		$user = $challenge ? $this->user_read_model->get_user_details_by_id($challenge->user_id) : null;
		$approved = false;

		$destination = $challenge && $user && $challenge->delivery_channel === 'email'
			? $user->email
			: ($user->verified_phone_e164 ?? '');

		if ($challenge && $user && $this->auth_challenge_model->destinationMatches($challenge, $destination)) {
			if ($challenge->delivery_channel === 'email') {
				$approved = $this->auth_challenge_model->codeMatches($challenge, $code);
			} else {
				$this->load->library('twilio_verify_service');
				$result = $this->twilio_verify_service->checkCode($user->verified_phone_e164, $code);
				$approved = !empty($result['approved']);
			}
		}

		if (!$approved || !$user || (int) $user->account_status === 0) {
			auth_throttle_hit($key, self::CODE_VERIFY_RATE_LIMIT_MAX, self::CODE_RATE_LIMIT_WINDOW);
			if ($challenge) {
				$this->auth_challenge_model->recordFailure($challenge->id);
			}
			echo json_encode(array('status' => false, 'msg' => 'This code is invalid or has expired. Request a new code and try again.', 'title' => 'Code Not Verified', 'csrf_hash' => $csrf_hash));
			return;
		}

		$this->auth_challenge_model->consume($challenge->id);
		auth_throttle_clear($key);
		$this->completeLogin($user);
		echo json_encode(array('status' => true, 'msg' => 'Sign-in successful.', 'title' => 'Welcome Back', 'csrf_hash' => $csrf_hash));
	}

	private function challengeIdFromToken($token, $purpose)
	{
		$challenge = $this->auth_challenge_model->getActiveChallenge($token, $purpose);
		return $challenge ? (int) $challenge->id : 0;
	}

	public function logout()
	{
		$this->session->unset_userdata(['email', 'user_id', 'user_loggedin']);
		redirect(site_url('signin'));
	}

	private function completeLogin($user)
	{
		$this->session->sess_regenerate(TRUE);
		$this->session->set_userdata(array(
			'email' => $user->email,
			'user_id' => $user->id,
			'user_loggedin' => true,
		));
		$this->common_model->update_last_login($user->id);
	}

	private function accountCanSignIn($user, $csrfHash)
	{
		if ((int) $user->account_status !== 0) {
			return true;
		}

		echo json_encode(array(
			'status' => false,
			'msg' => 'Your account is currently blocked. Please contact support.',
			'title' => 'Account Blocked',
			'msg_timeout' => 7000,
			'csrf_hash' => $csrfHash,
		));
		return false;
	}

	private function sendAnonymousChallengeResponse($type, $csrfHash)
	{
		echo json_encode(array(
			'status' => true,
			'msg' => 'If those details match an eligible account, a one-time code has been sent.',
			'challenge_token' => bin2hex(random_bytes(32)),
			'delivery_channel' => $type === 'email' ? 'email' : $this->auth_challenge_model->getPhoneOtpChannel(),
			'destination_hint' => 'your registered contact',
			'resend_after' => 30,
			'csrf_hash' => $csrfHash,
		));
	}

	private function maskDestination($destination, $channel)
	{
		$destination = (string) $destination;
		if ($channel === 'email') {
			$parts = explode('@', $destination, 2);
			return substr($parts[0], 0, 2) . '***@' . ($parts[1] ?? '');
		}

		return strlen($destination) > 4 ? '***' . substr($destination, -4) : 'your phone';
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
