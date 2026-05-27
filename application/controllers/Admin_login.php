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
        $this->return_to_dashboard(); // redirect if already logged in
        $this->admin_login_header('Admin Login');
        $data['captcha_code'] = mt_rand(111111, 999999);
        $this->load->view('admin/login/login', $data);
        $this->admin_login_footer();
    }


    public function login_ajax()
    {
        $csrf_hash = $this->security->get_csrf_hash();

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email', array('required' => 'Please enter your email'));
        $this->form_validation->set_rules('password', 'Password', 'required', array('required' => 'Please enter your password'));
        $this->form_validation->set_rules('captcha_code', 'Captcha Code', 'trim');
        $this->form_validation->set_rules('c_captcha_code', 'Captcha Code', 'trim|required|matches[captcha_code]',
            array(
                'required' => 'Captcha is required. Reload the page if you cannot see any code.',
                'matches'  => 'Invalid captcha code'
            )
        );

        $email    = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);

        if ($this->form_validation->run()) {

            $y = $this->common_model->get_admin_details($email);

            if (!$y) {
                // Email does not exist at all
                $res = ['status' => false, 'msg' => 'Invalid login! Email or password incorrect.', 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
                return;
            }

            $stored = $y->password;

            // ── DUAL-HASH LOGIN MIGRATION (SEC-006) ──
            // Detect hash type and verify accordingly.
            // Legacy RIPEMD-128 hashes are 32-char hex strings.
            // New bcrypt hashes start with $2y$ and are 60 chars.
            $is_legacy_hash = (strlen($stored) === 32 && ctype_xdigit($stored));

            if ($is_legacy_hash) {
                // Legacy RIPEMD-128 verification
                $password_matches = ($stored === hash('ripemd128', $password));

                if ($password_matches) {
                    // AUTO-MIGRATE: Rehash to bcrypt on successful legacy login
                    $bcrypt_hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->admin_login_model->update_password_hash_by_email($email, $bcrypt_hash);
                }
            } elseif (substr($stored, 0, 4) === '$2y$') {
                // Bcrypt verification
                $password_matches = password_verify($password, $stored);
            } else {
                // Legacy plain-text account (should not exist, but handle gracefully)
                $password_matches = ($stored === $password);

                if ($password_matches) {
                    // AUTO-MIGRATE: Hash plaintext to bcrypt
                    $bcrypt_hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->admin_login_model->update_password_hash_by_email($email, $bcrypt_hash);
                }
            }

            if ($password_matches) {
                // Store admin_email in session (used by get_admin_role() and other helpers)
                $login_data = [
                    'email'          => $email,   // kept for legacy compatibility
                    'admin_email'    => $email,   // used by admin_role_restricted()
                    'admin_loggedin' => true,
                ];
                $this->session->set_userdata($login_data);

                $res = ['status' => true, 'msg' => 'Login successful! <br> Redirecting to dashboard....', 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            } else {
                $res = ['status' => false, 'msg' => 'Invalid login! Email or password incorrect.', 'csrf_hash' => $csrf_hash];
                echo json_encode($res);
            }
        } else {
            $res = ['status' => false, 'msg' => validation_errors(), 'csrf_hash' => $csrf_hash];
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
