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
		$email = strtolower(trim((string) $this->input->post('email', TRUE)));
		$isEmailLogin = $this->input->post('identifier_type', TRUE) === 'email' || $email !== '';
		$identifier = $isEmailLogin ? $email : $this->submittedLoginPhone();
		$login_throttle_key = 'login:' . get_user_ip() . ':' . strtolower($identifier !== '' ? $identifier : 'unknown');
		if ($isEmailLogin) {
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		} else {
			$this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
			$this->form_validation->set_rules('phone', 'WhatsApp number', 'trim|required');
		}
		$this->form_validation->set_rules('password', 'Password', 'required');

		if (!$this->form_validation->run() || $identifier === '') {
			auth_throttle_hit($login_throttle_key, self::LOGIN_RATE_LIMIT_MAX, self::LOGIN_RATE_LIMIT_WINDOW);
			echo json_encode([
				'status' => false,
				'msg' => $identifier === '' && !$isEmailLogin ? 'Enter a valid phone number with its country code.' : first_validation_error('Enter your login details.'),
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
		if ($isEmailLogin && $user && empty($user->password)) {
			$new_verification_code = generate_verification_code();
			$this->users_model->update_user_verification_code($user->id, $new_verification_code);
			$this->users_model->resend_verification_code($user->id);
			$resume_token = $this->users_model->issue_signup_resume_token($user->id, self::SIGNUP_RESUME_TTL);
			auth_throttle_clear($login_throttle_key);
			echo json_encode([
				'status' => true,
				'msg' => 'Your account setup is not complete yet. Verify your email to continue.',
				'title' => 'Complete Setup',
				'redirect' => base_url('verify-email/' . rawurlencode((string) $resume_token)),
				'csrf_hash' => $csrf_hash
			]);
			return;
		}

		if ($user && !empty($user->password) && password_verify($password, $user->password)) {
			if (!$this->accountCanSignIn($user, $csrf_hash)) {
				return;
			}

			$this->completeLogin($user, 'password');
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
		$isEmail = $this->input->post('identifier_type', TRUE) === 'email';
		$email = strtolower(trim((string) $this->input->post('email', TRUE)));
		$identifier = $isEmail ? $email : $this->submittedLoginPhone();
		if ($isEmail) {
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		} else {
			$this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
			$this->form_validation->set_rules('phone', 'WhatsApp number', 'trim|required');
		}

		if (!$this->form_validation->run() || $identifier === '') {
			echo json_encode(array('status' => false, 'msg' => $identifier === '' && !$isEmail ? 'Enter a valid phone number with its country code.' : first_validation_error('Enter a valid email address.'), 'title' => 'Check Your Details', 'csrf_hash' => $csrf_hash));
			return;
		}

		$key = 'passwordless-request:' . get_user_ip() . ':' . sha1(strtolower($identifier));
		$state = auth_throttle_check($key, self::CODE_REQUEST_RATE_LIMIT_MAX, self::CODE_RATE_LIMIT_WINDOW);
		if (!$state['allowed']) {
			echo json_encode(array('status' => false, 'msg' => auth_throttle_message($state['retry_after'], 'code request'), 'title' => 'Too Many Requests', 'csrf_hash' => $csrf_hash));
			return;
		}

		auth_throttle_hit($key, self::CODE_REQUEST_RATE_LIMIT_MAX, self::CODE_RATE_LIMIT_WINDOW);
		$user = $this->user_read_model->get_login_user($identifier);

		if (!$user || empty($user->password) || (int) $user->account_status === 0) {
			$this->sendUnavailableChallengeResponse($csrf_hash);
			return;
		}

		if ($isEmail) {
			$code = generate_verification_code();
			$token = $this->auth_challenge_model->createChallenge($user->id, 'login', 'email', strtolower($user->email), $code);
			if (!$token) {
				$this->sendUnavailableChallengeResponse($csrf_hash);
				return;
			}
			if (!send_email_notification($this, $user->email, 'Your Sign-In Code', array(
				'firstname' => $user->firstname,
				'login_code' => $code,
			), 'user_login_code_email')) {
				$challenge = $this->auth_challenge_model->getActiveChallenge($token, 'login');
				if ($challenge) {
					$this->auth_challenge_model->consume($challenge->id);
				}
				$this->sendUnavailableChallengeResponse($csrf_hash);
				return;
			}
			$channel = 'email';
			$destination = $user->email;
		} else {
			$channel = $this->auth_challenge_model->getPhoneOtpChannel($user);
			$this->load->library('twilio_verify_service');
			$result = $this->twilio_verify_service->sendCode($user->verified_phone_e164, $channel);
			if (empty($result['success'])) {
				$this->sendUnavailableChallengeResponse($csrf_hash);
				return;
			}
			$token = $this->auth_challenge_model->createChallenge($user->id, 'login', $channel, $user->verified_phone_e164);
			if (!$token) {
				$this->sendUnavailableChallengeResponse($csrf_hash);
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

		if ($challenge && $user
			&& ($challenge->delivery_channel === 'email' || !empty($user->phone_signin_enabled))
			&& $this->auth_challenge_model->destinationMatches($challenge, $destination)) {
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

		$user = $this->auth_challenge_model->consumeLoginChallenge($challenge->id, $user->id);
        if (!$user) {
            echo json_encode(array('status' => false, 'msg' => 'This code is invalid or has expired. Request a new code and try again.', 'title' => 'Code Not Verified', 'csrf_hash' => $csrf_hash));
            return;
        }
		auth_throttle_clear($key);
		$this->completeLogin($user, $challenge->delivery_channel . '_code');
		echo json_encode(array('status' => true, 'msg' => 'Sign-in successful.', 'title' => 'Welcome Back', 'csrf_hash' => $csrf_hash));
	}

	private function submittedLoginPhone()
	{
		$countryCode = phone_country_code_normalize($this->input->post('country_code', TRUE));
		$phone = trim((string) $this->input->post('phone', TRUE));
		$supportedCodes = array_map(function ($country) {
			return phone_country_code_normalize($country['code']);
		}, phone_country_options());
		if (!in_array($countryCode, $supportedCodes, true) || $phone === '') {
			return '';
		}

		$identifier = normalize_phone_number($countryCode, $phone);
		return preg_match('/^\+[1-9][0-9]{7,14}$/', $identifier) ? $identifier : '';
	}

	public function logout()
	{
        if ($this->session->user_loggedin && $this->session->user_id) {
            $this->load->model('user_activity_model');
            $adminId = (int) $this->session->userdata('user_impersonator_id');
            $actor = $this->user_activity_model->actor($adminId ? 'admin' : 'user', $adminId ?: $this->session->user_id);
            if (!$this->user_activity_model->record($this->session->user_id, 'signed_out', $actor)) {
                log_message('error', 'Could not record account sign-out activity.');
            }
        }
        $this->session->unset_userdata(['email', 'user_id', 'user_loggedin', 'user_impersonator_id', 'phone_verification_candidate']);
        $this->session->sess_regenerate(TRUE);
		redirect(site_url('signin'));
	}

	private function completeLogin($user, $method)
	{
		$this->session->sess_regenerate(TRUE);
		$this->session->set_userdata(array(
			'email' => $user->email,
			'user_id' => $user->id,
			'user_loggedin' => true,
		));
        $this->session->unset_userdata('user_impersonator_id');
        $this->common_model->update_last_login($user->id);
        $this->load->model('user_activity_model');
        if (!$this->user_activity_model->record($user->id, 'signed_in',
            $this->user_activity_model->actor('user', $user->id), array(), $method)) {
            log_message('error', 'Could not record account sign-in activity.');
        }
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

	private function sendUnavailableChallengeResponse($csrfHash)
	{
		echo json_encode(array(
			'status' => false,
			'msg' => 'We could not send a code. Check your details or use your password.',
			'title' => 'Code Not Sent',
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
