<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Registration extends MY_Controller
{
    private const CAPTCHA_SESSION_KEY = 'signup_captcha_code';
    private const VERIFICATION_MAX_ATTEMPTS = 5;
    private const VERIFICATION_LOCK_MINUTES = 15;
    private const SIGNUP_RATE_LIMIT_MAX = 5;
    private const SIGNUP_RATE_LIMIT_WINDOW = 900;
    private const VERIFY_RATE_LIMIT_MAX = 5;
    private const VERIFY_RATE_LIMIT_WINDOW = 900;
    private const RESEND_RATE_LIMIT_MAX = 3;
    private const RESEND_RATE_LIMIT_WINDOW = 900;
    private const RESEND_COOLDOWN_SECONDS = 30;
    private const SIGNUP_RESUME_TTL = 3600;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('user_read_model');
    }



    /* ========= User Signup ============ */
    public function index()
    {
        $data['captcha_code'] = $this->refresh_signup_captcha();
        $this->load->view('user_login/signup', $data);
    }


    public function signup()
    {
        $csrf_hash = $this->security->get_csrf_hash();
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email',  'Email',  'trim|required|valid_email');
        $this->form_validation->set_rules('country_code', 'Country code', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone Number', 'trim|is_natural|required');
        $this->form_validation->set_rules('country', 'Country', 'trim|required');
        $this->form_validation->set_rules('c_captcha_code', 'Captcha Code', 'trim|required',
            array('required' => 'Captcha is required. Reload the page if you cannot see any code.',
            )
        );

        $email = $this->input->post('email', TRUE);
        $signup_throttle_key = 'signup:' . get_user_ip() . ':' . strtolower(trim((string) $email));
        $signup_throttle_state = auth_throttle_check($signup_throttle_key, self::SIGNUP_RATE_LIMIT_MAX, self::SIGNUP_RATE_LIMIT_WINDOW);
        if (!$signup_throttle_state['allowed']) {
            echo json_encode([
                'status' => false,
                'msg' => auth_throttle_message($signup_throttle_state['retry_after'], 'sign up'),
                'title' => 'Too Many Attempts',
                'msg_timeout' => 7000,
                'csrf_hash' => $csrf_hash
            ]);
            return;
        }

        if ($this->form_validation->run()) {
            $posted_captcha = trim((string) $this->input->post('c_captcha_code', TRUE));
            $expected_captcha = (string) $this->session->userdata(self::CAPTCHA_SESSION_KEY);

            if ($expected_captcha === '' || !hash_equals($expected_captcha, $posted_captcha)) {
                auth_throttle_hit($signup_throttle_key, self::SIGNUP_RATE_LIMIT_MAX, self::SIGNUP_RATE_LIMIT_WINDOW);
                $this->refresh_signup_captcha();
                $res = [
                    'status' => false,
                    'msg' => 'Enter the correct captcha code to continue.',
                    'title' => 'Sign Up Error',
                    'msg_timeout' => 6000,
                    'csrf_hash' => $csrf_hash
                ];
                echo json_encode($res);
                return;
            }

            $existing_user = $this->user_read_model->get_user_details($email);

            if ($existing_user) {
                if (empty($existing_user->password)) {
                    auth_throttle_clear($signup_throttle_key);
                    $new_verification_code = generate_verification_code();
                    $this->users_model->update_user_verification_code($existing_user->id, $new_verification_code);
                    $this->users_model->resend_verification_code($existing_user->id);
                    $resume_token = $this->users_model->issue_signup_resume_token($existing_user->id, self::SIGNUP_RESUME_TTL);

                    $res = [
                        'status' => true,
                        'msg' => "Your account setup is already in progress. We've sent a fresh verification code to $email.",
                        'title' => 'Complete Setup',
                        'msg_timeout' => 7000,
                        'redirect' => $this->build_verification_redirect($resume_token),
                        'csrf_hash' => $csrf_hash
                    ];
                    echo json_encode($res);
                    return;
                }

                $res = [
                    'status' => false,
                    'msg' => 'This email address is already registered. Sign in instead to continue.',
                    'title' => 'Sign Up Error',
                    'msg_timeout' => 7000,
                    'csrf_hash' => $csrf_hash
                ];
                echo json_encode($res);
                return;
            }

            $user_id = $this->users_model->add_new_user(); // insert the data into the database and get user ID
            $resume_token = $this->users_model->issue_signup_resume_token($user_id, self::SIGNUP_RESUME_TTL);
            auth_throttle_clear($signup_throttle_key);
            $this->refresh_signup_captcha();
            $res = [
                'status' => true,
                'msg' => "A verification code has been sent to $email.",
                'title' => 'Check Your Email',
                'msg_timeout' => 7000,
                'redirect' => $this->build_verification_redirect($resume_token),
                'csrf_hash' => $csrf_hash
            ];
            echo json_encode($res);
        } else {
            auth_throttle_hit($signup_throttle_key, self::SIGNUP_RATE_LIMIT_MAX, self::SIGNUP_RATE_LIMIT_WINDOW);
            $res = [
                'status' => false,
                'msg' => first_validation_error('Please complete the sign-up form and try again.'),
                'title' => 'Sign Up Error',
                'msg_timeout' => 6000,
                'csrf_hash' => $csrf_hash
            ];
            echo json_encode($res);
        }
    }


    public function verify_email($resume_token = null)
    {
        $pending_user = $this->get_pending_user_by_resume_token($resume_token);
        if (!$pending_user) {
            redirect('signin');
        }

        $data['verification_email'] = $pending_user->email;
        $data['resume_token'] = $resume_token;
        $data['resend_cooldown_seconds'] = self::RESEND_COOLDOWN_SECONDS;
        $this->load->view('user_login/verify_email', $data);
    }

    public function verify_email_ajax()
    {
        $csrf_hash = $this->security->get_csrf_hash();
        $this->form_validation->set_rules('verification_code', 'Verification Code', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]',
            array('matches' => 'Passwords do not match'));
        $this->form_validation->set_rules('resume_token', 'Resume Token', 'trim|required');

        if ($this->form_validation->run()) {
            $resume_token = trim((string) $this->input->post('resume_token', TRUE));
            $pending_user = $this->get_pending_user_by_resume_token($resume_token);
            $pending_user_id = $pending_user ? (int) $pending_user->id : 0;
            $verify_throttle_key = 'verify-email:' . get_user_ip() . ':' . ($pending_user_id ?: sha1($resume_token));
            $verify_throttle_state = auth_throttle_check($verify_throttle_key, self::VERIFY_RATE_LIMIT_MAX, self::VERIFY_RATE_LIMIT_WINDOW);

            if (!$verify_throttle_state['allowed']) {
                echo json_encode([
                    'status' => false,
                    'msg' => auth_throttle_message($verify_throttle_state['retry_after'], 'verification'),
                    'title' => 'Too Many Attempts',
                    'msg_timeout' => 7000,
                    'csrf_hash' => $csrf_hash
                ]);
                return;
            }

            if (!$pending_user || !empty($pending_user->password)) {
                echo json_encode(['status' => false, 'msg' => 'This verification link is no longer valid. Start again from sign up or sign in.', 'title' => 'Verification Error', 'msg_timeout' => 7000, 'csrf_hash' => $csrf_hash]);
                return;
            }

            if (!empty($pending_user->verification_locked_until) && strtotime((string) $pending_user->verification_locked_until) > time()) {
                echo json_encode([
                    'status' => false,
                    'msg' => 'Too many incorrect verification attempts. Request a new code or try again later.',
                    'title' => 'Verification Locked',
                    'msg_timeout' => 7000,
                    'csrf_hash' => $csrf_hash
                ]);
                return;
            }

            if (!empty($pending_user->verification_code_expires_at) && strtotime((string) $pending_user->verification_code_expires_at) <= time()) {
                echo json_encode([
                    'status' => false,
                    'msg' => 'This verification code has expired. Request a new code to continue.',
                    'title' => 'Verification Expired',
                    'msg_timeout' => 7000,
                    'csrf_hash' => $csrf_hash
                ]);
                return;
            }

            $verification_code = $this->input->post('verification_code', TRUE);
            $password = password_hash($this->input->post('password', TRUE), PASSWORD_DEFAULT);

            if (hash_equals((string) $pending_user->verification_code, (string) $verification_code)) {
                // Update user password, mark as verified, and set a new verification code
                $this->users_model->update_user_verification($pending_user->id, $password);
                auth_throttle_clear($verify_throttle_key);
                $this->users_model->clear_verification_security_state($pending_user->id);

                $res = ['status' => true, 'msg' => 'Your email has been verified successfully.', 'title' => 'Verification Complete', 'msg_timeout' => 5000, 'csrf_hash' => $csrf_hash];
            } else {
                auth_throttle_hit($verify_throttle_key, self::VERIFY_RATE_LIMIT_MAX, self::VERIFY_RATE_LIMIT_WINDOW);
                $this->users_model->record_verification_failure(
                    $pending_user->id,
                    self::VERIFICATION_MAX_ATTEMPTS,
                    self::VERIFICATION_LOCK_MINUTES
                );
                $res = ['status' => false, 'msg' => 'Enter the correct verification code and try again.', 'title' => 'Verification Error', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            }
            echo json_encode($res);
        } else {
            $res = ['status' => false, 'msg' => first_validation_error('Please complete the verification form.'), 'title' => 'Verification Error', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res);
        }
    }

    public function resend_verification_email_ajax()
    {
        $csrf_hash = $this->security->get_csrf_hash();
        $resume_token = trim((string) $this->input->post('resume_token', TRUE));
        $pending_user = $this->get_pending_user_by_resume_token($resume_token);
        $user_id = $pending_user ? (int) $pending_user->id : 0;
        $resend_throttle_key = 'resend-verification:' . get_user_ip() . ':' . $user_id;
        $resend_throttle_state = auth_throttle_check($resend_throttle_key, self::RESEND_RATE_LIMIT_MAX, self::RESEND_RATE_LIMIT_WINDOW);

        if (!$resend_throttle_state['allowed']) {
            echo json_encode([
                'status' => false,
                'msg' => auth_throttle_message($resend_throttle_state['retry_after'], 'verification code resend'),
                'title' => 'Too Many Attempts',
                'msg_timeout' => 7000,
                'csrf_hash' => $csrf_hash
            ]);
            return;
        }

        if ($pending_user && empty($pending_user->password)) {
            auth_throttle_hit($resend_throttle_key, self::RESEND_RATE_LIMIT_MAX, self::RESEND_RATE_LIMIT_WINDOW);
            $new_verification_code = generate_verification_code();
            $this->users_model->update_user_verification_code($user_id, $new_verification_code);
            $this->users_model->resend_verification_code($user_id);
            $res = [
                'status' => true,
                'msg' => 'A new verification code has been sent to your email.',
                'title' => 'Code Sent',
                'msg_timeout' => 6000,
                'csrf_hash' => $csrf_hash,
                'cooldown_seconds' => self::RESEND_COOLDOWN_SECONDS
            ];
            echo json_encode($res);
        } else {
            $res = ['status' => false, 'msg' => 'We could not send a new code right now. Please try again shortly.', 'title' => 'Code Not Sent', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res);
        }
    }

    private function refresh_signup_captcha()
    {
        $captcha_code = generate_verification_code();
        $this->session->set_userdata(self::CAPTCHA_SESSION_KEY, $captcha_code);
        return $captcha_code;
    }

    private function get_pending_user_by_resume_token($resume_token)
    {
        $resume_token = trim((string) $resume_token);
        if ($resume_token === '') {
            return null;
        }

        $user = $this->user_read_model->get_user_by_signup_resume_token($resume_token);
        if (!$user || !empty($user->password)) {
            return null;
        }

        if (empty($user->signup_resume_expires_at) || strtotime((string) $user->signup_resume_expires_at) <= time()) {
            return null;
        }

        return $user;
    }

    private function build_verification_redirect($resume_token)
    {
        return base_url('verify-email/' . rawurlencode((string) $resume_token));
    }




}
