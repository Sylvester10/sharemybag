<?php
defined('BASEPATH') or die('Direct access not allowed');


class Profile extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->user_restricted(); //allow only logged in users to access this class
        $this->load->model('users_model');
        $this->load->model('user_read_model');
		$this->load->model('auth_challenge_model');
        $this->load->model('traveller_read_model');
        $this->load->model('user_bookings_model');
        $this->load->model('travellers_model');
        $this->user_details = $this->user_read_model->get_user_details($this->session->email);
        $this->traveller_details = $this->traveller_read_model->get_traveller_details_by_id($this->session->id);
    }



    public function index()
    {
        $user_details = $this->user_read_model->get_user_details($this->session->email);
        $this->dashboard_header('Profile');
        $data['user_details'] = $user_details;
        $data['referral_link'] = $this->user_details->referral_link;
        $this->load->view('users/profile', $data);
        $this->dashboard_footer();
    }


    public function profile_ajax($id)
    {
		if ((int) $id !== (int) $this->session->user_id) {
			show_error('You are not allowed to update this profile.', 403);
		}

        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'profile');
        $csrf_hash = $this->security->get_csrf_hash();

        // validation rules
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('state', 'State', 'trim|required');
        $this->form_validation->set_rules('post_code', 'Post Code', 'trim|required');

        if ($this->form_validation->run()) {
            //
            if ($this->users_model->update_profile_to_db($id)) {

                $res = ['status' => true, 'msg' => 'Your profile has been updated successfully.', 'title' => 'Profile Updated.', 'msg_timeout' =>  6000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            } else {
                $res = ['status' => false, 'msg' => 'We could not update your profile right now. Please try again.', 'title' => 'Update Failed', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            }
        } else {
            $res = ['status' => false, 'msg' => first_validation_error('Please complete all required profile fields.'), 'title' => 'Check Your Profile', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res); // Show validation errors
        }
    }


    public function change_password($id)
    {
		if ((int) $id !== (int) $this->session->user_id) {
			show_error('You are not allowed to update this profile.', 403);
		}

        //check user exists
        $this->check_data_exists($id, 'id', 'users', 'profile');
        $csrf_hash = $this->security->get_csrf_hash();

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

                $res = ['status' => true, 'msg' => 'Your password has been updated successfully.', 'title' => 'Password Updated.', 'msg_timeout' =>  6000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            } else {
                $res = ['status' => false, 'msg' => 'We could not update your password right now. Please try again.', 'title' => 'Update Failed', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            }
        } else {
            $res = ['status' => false, 'msg' => first_validation_error('Please check your password details and try again.'), 'title' => 'Check Your Password', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res); // Show validation errors
        }
    }

    public function set_phone_signin_ajax()
    {
        if ($this->input->method() !== 'post') { show_error('Method not allowed.', 405); }
        $csrf_hash = $this->security->get_csrf_hash();
        $enabled = (string) $this->input->post('enabled', true);
        $channel = (string) $this->input->post('phone_otp_channel', true);
        if (($enabled !== '0' && $enabled !== '1') || !in_array($channel, array('whatsapp', 'sms'), true)) {
            echo json_encode(array('status' => false, 'msg' => 'Choose a valid phone sign-in setting.', 'csrf_hash' => $csrf_hash));
            return;
        }

        $user = $this->user_read_model->get_user_details_by_id((int) $this->session->user_id);
        if (!$user || ($enabled === '1' && (empty($user->phone_verified_at) || empty($user->verified_phone_e164)))) {
            echo json_encode(array('status' => false, 'msg' => 'Verify your phone number before enabling phone sign-in.', 'csrf_hash' => $csrf_hash));
            return;
        }

        if (!$this->users_model->set_phone_signin_enabled($user->id, $enabled === '1', $channel)) {
            echo json_encode(array('status' => false, 'msg' => 'We could not update phone sign-in. Please try again.', 'csrf_hash' => $csrf_hash));
            return;
        }

        echo json_encode(array(
            'status' => true,
            'enabled' => $enabled === '1',
            'phone_otp_channel' => $channel,
            'msg' => 'Sign-in method updated.',
            'csrf_hash' => $csrf_hash,
        ));
    }

	public function request_phone_verification_ajax()
	{
		if ($this->input->method() !== 'post') { show_error('Method not allowed.', 405); }
		$csrf_hash = $this->security->get_csrf_hash();
		$this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
		$this->form_validation->set_rules('number', 'Phone number', 'trim|required');

		if (!$this->form_validation->run()) {
			echo json_encode(array('status' => false, 'msg' => first_validation_error('Enter a valid phone number.'), 'title' => 'Check Your Number', 'csrf_hash' => $csrf_hash));
			return;
		}
		$currentUser = $this->user_read_model->get_user_details($this->session->email);
		if ($currentUser && !empty($currentUser->phone_verified_at) && !empty($currentUser->verified_phone_e164)) {
			echo json_encode(array('status' => false, 'msg' => 'Your phone number is already verified. Contact Support to change it.', 'title' => 'Phone Verified', 'csrf_hash' => $csrf_hash));
			return;
		}

		$phone = normalize_phone_number(
			$this->input->post('country_code', true),
			$this->input->post('number', true)
		);
		if (!preg_match('/^\+[1-9][0-9]{7,14}$/', $phone)) {
			echo json_encode(array('status' => false, 'msg' => 'Enter a complete international phone number.', 'title' => 'Invalid Number', 'csrf_hash' => $csrf_hash));
			return;
		}

		$userId = (int) $this->session->user_id;
		if ($this->user_read_model->verified_phone_belongs_to_another_user($phone, $userId)) {
			echo json_encode(array('status' => false, 'msg' => 'This phone number is already verified on another account.', 'title' => 'Number Unavailable', 'csrf_hash' => $csrf_hash));
			return;
		}

		$key = 'profile-phone-request:' . get_user_ip() . ':' . $userId;
		$state = auth_throttle_check($key, 3, 900);
		if (!$state['allowed']) {
			echo json_encode(array('status' => false, 'msg' => auth_throttle_message($state['retry_after'], 'phone verification'), 'title' => 'Too Many Requests', 'csrf_hash' => $csrf_hash));
			return;
		}
		auth_throttle_hit($key, 3, 900);

		$channel = $this->auth_challenge_model->getPhoneOtpChannel($currentUser);
		$this->load->library('twilio_verify_service');
		$result = $this->twilio_verify_service->sendCode($phone, $channel);
		if (empty($result['success'])) {
			echo json_encode(array('status' => false, 'msg' => 'Phone verification is temporarily unavailable. Please try again later.', 'title' => 'Code Not Sent', 'csrf_hash' => $csrf_hash));
			return;
		}

		$token = $this->auth_challenge_model->createChallenge($userId, 'profile_phone', $channel, $phone);
		if (!$token) {
			echo json_encode(array('status' => false, 'msg' => 'We could not prepare phone verification. Please try again.', 'title' => 'Code Not Sent', 'csrf_hash' => $csrf_hash));
			return;
		}
		$this->session->set_userdata('phone_verification_candidate', array(
			'token_hash' => hash('sha256', $token),
			'phone' => $phone,
		));

		echo json_encode(array(
			'status' => true,
			'msg' => 'Enter the code sent to ***' . substr($phone, -4) . '.',
			'challenge_token' => $token,
			'delivery_channel' => $channel,
			'csrf_hash' => $csrf_hash,
		));
	}

	public function verify_phone_ajax()
	{
		if ($this->input->method() !== 'post') { show_error('Method not allowed.', 405); }
		$csrf_hash = $this->security->get_csrf_hash();
		$token = trim((string) $this->input->post('challenge_token', true));
		$code = trim((string) $this->input->post('code', true));
		$this->form_validation->set_rules('challenge_token', 'Verification session', 'trim|required');
		$this->form_validation->set_rules('code', 'Verification code', 'trim|required|numeric|min_length[4]|max_length[10]');

		if (!$this->form_validation->run()) {
			echo json_encode(array('status' => false, 'msg' => first_validation_error('Enter the complete verification code.'), 'title' => 'Check Your Code', 'csrf_hash' => $csrf_hash));
			return;
		}
		$currentUser = $this->user_read_model->get_user_details($this->session->email);
		if ($currentUser && !empty($currentUser->phone_verified_at) && !empty($currentUser->verified_phone_e164)) {
			echo json_encode(array('status' => false, 'msg' => 'Your phone number is already verified. Contact Support to change it.', 'title' => 'Phone Verified', 'csrf_hash' => $csrf_hash));
			return;
		}

		$challenge = $this->auth_challenge_model->getActiveChallenge($token, 'profile_phone');
		$candidate = $this->session->userdata('phone_verification_candidate');
		$userId = (int) $this->session->user_id;
		$phone = is_array($candidate) ? (string) ($candidate['phone'] ?? '') : '';
		$bound = $challenge
			&& (int) $challenge->user_id === $userId
			&& is_array($candidate)
			&& hash_equals((string) ($candidate['token_hash'] ?? ''), hash('sha256', $token))
			&& hash_equals((string) $challenge->destination_hash, hash('sha256', strtolower($phone)));

		if (!$bound || $this->user_read_model->verified_phone_belongs_to_another_user($phone, $userId)) {
			echo json_encode(array('status' => false, 'msg' => 'This verification request is invalid or expired.', 'title' => 'Verification Failed', 'csrf_hash' => $csrf_hash));
			return;
		}

		$key = 'profile-phone-verify:' . get_user_ip() . ':' . $userId;
		$state = auth_throttle_check($key, 5, 900);
		if (!$state['allowed']) {
			echo json_encode(array('status' => false, 'msg' => auth_throttle_message($state['retry_after'], 'phone verification'), 'title' => 'Too Many Attempts', 'csrf_hash' => $csrf_hash));
			return;
		}

		$this->load->library('twilio_verify_service');
		$result = $this->twilio_verify_service->checkCode($phone, $code);
		if (empty($result['approved'])) {
			auth_throttle_hit($key, 5, 900);
			$this->auth_challenge_model->recordFailure($challenge->id);
			echo json_encode(array('status' => false, 'msg' => 'This code is invalid or has expired.', 'title' => 'Code Not Verified', 'csrf_hash' => $csrf_hash));
			return;
		}

		if (!$this->users_model->mark_phone_verified($userId, $phone, $challenge->id)) {
			echo json_encode(array('status' => false, 'msg' => 'We could not save the verified number. Please try again.', 'title' => 'Update Failed', 'csrf_hash' => $csrf_hash));
			return;
		}

		$this->auth_challenge_model->consume($challenge->id);
		auth_throttle_clear($key);
		$this->session->unset_userdata('phone_verification_candidate');
		$verifiedParts = split_phone_number($phone);
		echo json_encode(array('status' => true, 'msg' => 'Your phone number is now verified.', 'title' => 'Phone Verified',
            'country_code' => $verifiedParts['country_code'], 'local_number' => $verifiedParts['local_number'], 'csrf_hash' => $csrf_hash));
	}
}
