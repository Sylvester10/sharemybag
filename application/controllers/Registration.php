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
    private const SIGNUP_RESUME_TTL = 86400;

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
        if (!$this->require_post()) { return; }
        $email = strtolower(trim((string) $this->input->post('email', TRUE)));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existing = $this->user_read_model->get_user_details($email);
            if ($existing) { $this->start_ajax(); return; }
        }
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
                    'field' => 'c_captcha_code',
                    'title' => 'Sign Up Error',
                    'msg_timeout' => 6000,
                    'csrf_hash' => $csrf_hash
                ];
                echo json_encode($res);
                return;
            }

            $existing_user = $this->user_read_model->get_user_details($email);

            if ($existing_user) { $this->start_ajax(); return; }

            $user_id = $this->users_model->add_new_user(); // insert the data into the database and get user ID
            if (!$user_id) {
                echo json_encode(['status'=>false, 'msg'=>'We could not save your registration. Please try again.', 'csrf_hash'=>$csrf_hash]);
                return;
            }
            $resume_token = $this->users_model->issue_signup_resume_token($user_id, self::SIGNUP_RESUME_TTL);
            $delivery = $this->users_model->send_signup_verification_code($user_id, $resume_token);
            auth_throttle_clear($signup_throttle_key);
            $this->refresh_signup_captcha();
            $res = [
                'status' => true,
                'msg' => $delivery['status'] ? 'A verification code has been sent to your email.' : 'Your details are saved, but we could not send your code. Continue to try again.',
                'title' => $delivery['status'] ? 'Check Your Email' : 'Continue Registration',
                'msg_timeout' => 7000,
                'redirect' => $delivery['status'] ? $this->build_verification_redirect($resume_token) : base_url('continue-registration/' . rawurlencode((string) $resume_token)),
                'csrf_hash' => $csrf_hash
            ];
            echo json_encode($res);
        } else {
            auth_throttle_hit($signup_throttle_key, self::SIGNUP_RATE_LIMIT_MAX, self::SIGNUP_RATE_LIMIT_WINDOW);
            $res = [
                'status' => false,
                'msg' => first_validation_error('Please complete the sign-up form and try again.'),
                'field' => array_key_first($this->form_validation->error_array()),
                'title' => 'Sign Up Error',
                'msg_timeout' => 6000,
                'csrf_hash' => $csrf_hash
            ];
            echo json_encode($res);
        }
    }

    /** Email-first entry shared by signup and expired-link recovery. No email is sent here. */
    public function start_ajax()
    {
        if (!$this->require_post()) { return; }
        $csrf = $this->security->get_csrf_hash();
        $email = strtolower(trim((string) $this->input->post('email', TRUE)));
        $key = 'registration-lookup:' . get_user_ip();
        $limit = auth_throttle_check($key, 20, 900);
        if (!$limit['allowed']) {
            echo json_encode(['status'=>false, 'msg'=>auth_throttle_message($limit['retry_after'], 'registration'), 'field'=>'email', 'csrf_hash'=>$csrf]);
            return;
        }
        auth_throttle_hit($key, 20, 900);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status'=>false, 'msg'=>'Enter a valid email address.', 'field'=>'email', 'csrf_hash'=>$csrf]);
            return;
        }
        $user = $this->user_read_model->get_user_details($email);
        if (!$user) {
            echo json_encode(['status'=>true, 'new_registration'=>true, 'msg'=>'Complete your details to create your account.', 'csrf_hash'=>$csrf]);
            return;
        }
        $token = registration_can_resume($user) ? $this->users_model->issue_signup_resume_token($user->id) : null;
        echo json_encode($token
            ? ['status'=>true, 'redirect'=>base_url('continue-registration/' . rawurlencode($token)), 'csrf_hash'=>$csrf]
            : ['status'=>false, 'msg'=>'Sign in to your account, or contact support if you cannot continue.', 'field'=>'email', 'csrf_hash'=>$csrf]);
    }

    public function continue_registration($resume_token = null)
    {
        $this->output->set_header('Cache-Control: no-store');
        $this->output->set_header('Referrer-Policy: no-referrer');
        $user = $this->users_model->get_signup_resume_user($resume_token);
        $this->load->view('user_login/continue_registration', [
            'resume_token'=>$user ? $resume_token : null,
            'email_hint'=>$user ? registration_email_hint($user->email) : null,
            'expired_link'=>!$user && !empty($resume_token),
        ]);
    }

    private function require_post()
    {
        if ($this->input->method() === 'post') { return true; }
        echo json_encode(['status'=>false, 'msg'=>'Submit the form to continue.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
        return false;
    }

    public function verify_email($resume_token = null)
    {
        $this->output->set_header('Cache-Control: no-store');
        $this->output->set_header('Referrer-Policy: no-referrer');
        $pending_user = $this->get_pending_user_by_resume_token($resume_token);
        if (!$pending_user) {
            redirect('continue-registration');
            return;
        }

        $data['verification_email'] = $pending_user->email;
        $data['resume_token'] = $resume_token;
        $data['resend_cooldown_seconds'] = self::RESEND_COOLDOWN_SECONDS;
        $this->load->view('user_login/verify_email', $data);
    }

    public function verify_email_ajax()
    {
        if (!$this->require_post()) { return; }
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
                // Complete signup once, even if another request has the same pending snapshot.
                $completed = $this->users_model->update_user_verification(
                    $pending_user->id, $password, $verification_code, $resume_token, $pending_user->email
                );
                if (!$completed) {
                    echo json_encode(['status' => false, 'msg' => 'This verification could not be completed. Sign in if you have already verified, or request a new code and try again.', 'title' => 'Verification Error', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash]);
                    return;
                }
                auth_throttle_clear($verify_throttle_key);
                $current_user = $this->user_read_model->get_user_details_by_id($pending_user->id);
                if (!$current_user || (int) $current_user->account_status !== 1 || !empty($current_user->deleted_at)
                    || $current_user->email !== $pending_user->email || !hash_equals((string) $current_user->password, $password)) {
                    echo json_encode(['status'=>false, 'msg'=>'Your registration is complete. Please contact support to access your account.', 'csrf_hash'=>$csrf_hash]);
                    return;
                }
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata(['email'=>$current_user->email, 'user_id'=>(int) $current_user->id, 'user_loggedin'=>true]);
                $this->session->unset_userdata('user_impersonator_id');
                $this->common_model->update_last_login($current_user->id);
                $this->users_model->record_signup_login($current_user);

                if (!send_email_notification($this, $pending_user->email, 'Welcome to ' . business, array(
                    'firstname' => $pending_user->firstname,
                ), 'user_welcome_email')) {
                    // Welcome delivery is best effort; a verified account stays verified.
                    log_message('error', 'Welcome email delivery failed for verified user ID ' . (int) $pending_user->id);
                }

                $res = ['status' => true, 'msg' => 'Your registration is complete.', 'title' => 'Verification Complete', 'redirect'=>base_url('dashboard'), 'msg_timeout' => 5000, 'csrf_hash' => $csrf_hash];
            } else {
                auth_throttle_hit($verify_throttle_key, self::VERIFY_RATE_LIMIT_MAX, self::VERIFY_RATE_LIMIT_WINDOW);
                $this->users_model->record_verification_failure(
                    $pending_user->id,
                    self::VERIFICATION_MAX_ATTEMPTS,
                    self::VERIFICATION_LOCK_MINUTES,
                    $pending_user->verification_code,
                    $resume_token
                );
                $res = ['status' => false, 'msg' => 'Enter the correct verification code and try again.', 'field' => 'verification_code', 'title' => 'Verification Error', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            }
            echo json_encode($res);
        } else {
            $res = ['status' => false, 'msg' => first_validation_error('Please complete the verification form.'), 'field' => array_key_first($this->form_validation->error_array()), 'title' => 'Verification Error', 'msg_timeout' => 6000, 'csrf_hash' => $csrf_hash];
            echo json_encode($res);
        }
    }

    public function resend_verification_email_ajax()
    {
        if (!$this->require_post()) { return; }
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

        if ($pending_user) {
            auth_throttle_hit($resend_throttle_key, self::RESEND_RATE_LIMIT_MAX, self::RESEND_RATE_LIMIT_WINDOW);
            $res = $this->users_model->send_signup_verification_code($user_id, $resume_token);
            $res['csrf_hash'] = $csrf_hash;
            if ($res['status']) { $res['redirect'] = $this->build_verification_redirect($resume_token); }
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
        return $this->users_model->get_signup_resume_user(trim((string) $resume_token));
    }

    private function build_verification_redirect($resume_token)
    {
        return base_url('verify-email/' . rawurlencode((string) $resume_token));
    }




}
