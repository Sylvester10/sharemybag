<?php
// Real CI query builder, disposable SQLite, and recording mail/session adapters. No network.
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
define('business', 'ShareMyBag');
define('VERIFY_NONE', 0);
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
if (getenv('SMB_REGISTRATION_MYSQL') === '1') {
    require $root . '/vendor/autoload.php';
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
    if (($_ENV['APP_ENV'] ?? '') !== 'development') { throw new RuntimeException('MySQL proof is local-development only.'); }
}
function show_error($message) { throw new RuntimeException($message); }
function log_message($level, $message) { $GLOBALS['logs'][] = $message; }
function base_url($path = '') { return 'https://example.invalid/' . $path; }
function production_url($path = '') { return base_url($path); }
function generate_verification_code() { return (string) random_int(100000, 999999); }
function normalize_phone_number($prefix, $phone) { return $prefix . $phone; }
function get_user_ip() { return '127.0.0.1'; }
function auth_throttle_check($key) { return ['allowed'=>!isset($GLOBALS['limits'][$key]), 'retry_after'=>900]; }
function auth_throttle_hit($key) { $GLOBALS['hits'][] = $key; }
function auth_throttle_clear() {}
function auth_throttle_message($seconds, $action) { return 'Please wait before trying again.'; }
function first_validation_error($fallback) { return $fallback; }
function send_email_notification($ci, $email, $subject, $data, $template)
{
    $GLOBALS['mail'][] = compact('email', 'data', 'template');
    return $GLOBALS['delivery'];
}
class CI_Model { public $db; }
class MY_Model { public $db; public $input; public $user_read_model; public $load; public $user_activity_model; }
class MY_Controller {
    public $input; public $security; public $form_validation; public $users_model; public $user_read_model; public $session; public $load; public $common_model; public $user_activity_model; public $admin_details; public $output;
    public $role = 'customer_support';
    public function admin_role_restricted($roles) { if (!in_array($this->role, $roles, true)) { throw new RuntimeException('Role denied'); } }
}
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Users_model.php';
require APPPATH . 'controllers/Registration.php';
require APPPATH . 'controllers/User_login.php';
require APPPATH . 'controllers/Admin_users.php';
require APPPATH . 'models/User_activity_model.php';
function check($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
class RecoveryInput
{
    public $values = ['email'=>'fixture@example.invalid','identifier_type'=>'email','password'=>'fixture-password', 'confirm_password'=>'fixture-password'];
    public $method = 'post';
    public function post($key, $filter = false) { return $this->values[$key] ?? ''; }
    public function method($upper = false) { return $upper ? strtoupper($this->method) : $this->method; }
}
class RecoveryReader
{
    public $db;
    public function get_user_details($email) { return $this->db->where('email', $email)->where('deleted_at IS NULL', null, false)->get('users')->row(); }
    public function get_login_user($email) { return $this->get_user_details($email); }
    public function get_user_details_by_id($id) { return $this->db->where('id', $id)->where('deleted_at IS NULL', null, false)->get('users')->row(); }
    public function get_user_by_signup_resume_token($token) { return $this->db->where('signup_resume_token', $token)->where('deleted_at IS NULL', null, false)->get('users')->row(); }
    public function clearUserCountCaches() {}
}
class RecoveryValidation { public function set_rules() {} public function run() { return true; } }
class RecoverySecurity { public function get_csrf_hash() { return 'fixture-csrf'; } }
class RecoverySession
{
    public $values = []; public $regenerated = false;
    public function sess_regenerate($destroy) { $this->regenerated = $destroy; }
    public function set_userdata($key, $value = null) { $this->values = array_merge($this->values, is_array($key) ? $key : [$key=>$value]); }
    public function unset_userdata($key) { unset($this->values[$key]); }
    public function userdata($key) { return $this->values[$key] ?? null; }
}
class RecoveryLoader
{
    public $views = [];
    public function view($view, $data = []) { $this->views[] = compact('view','data'); }
    public function model($model) {}
}
class RecoveryCommon { public function update_last_login($id) {} }
class RecoveryOutput { public function set_header($header) {} }
function fixture($updates = [])
{
    $mysql = getenv('SMB_REGISTRATION_MYSQL') === '1';
    $settings = $mysql ? ['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],
        'database'=>$_ENV['DB_DATABASE_LOCAL'],'db_debug'=>false,'char_set'=>'utf8mb4']
        : ['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false,'dbprefix'=>''];
    $db = DB($settings, true);
    check((bool) $db->conn_id, 'Disposable database connection');
    $schema = 'CREATE TABLE users (id INTEGER PRIMARY KEY, firstname TEXT, lastname TEXT, email TEXT, number TEXT, country TEXT, username TEXT, referral_link TEXT, pass_reset_code TEXT, is_verified INTEGER, password TEXT, verification_code TEXT, email_verified_at TEXT, verification_code_expires_at TEXT, verification_attempts INTEGER, verification_locked_until TEXT, signup_resume_token TEXT, signup_resume_expires_at TEXT, account_status INTEGER, deleted_at TEXT)';
    if ($mysql) { $schema = str_replace(['CREATE TABLE','id INTEGER PRIMARY KEY'], ['CREATE TEMPORARY TABLE','id INT AUTO_INCREMENT PRIMARY KEY'], $schema); }
    check($db->query($schema), 'Disposable users schema');
    $db->insert('users', array_merge(['id'=>42,'firstname'=>'Fixture','email'=>'fixture@example.invalid','password'=>null,'account_status'=>1,'verification_attempts'=>0], $updates));
    $schema = 'CREATE TABLE user_activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, actor_type TEXT, actor_id INTEGER, actor_name TEXT, event TEXT, method TEXT, changes TEXT, date_added TEXT)';
    if ($mysql) {
        $schema = str_replace(['CREATE TABLE','id INTEGER PRIMARY KEY AUTOINCREMENT'], ['CREATE TEMPORARY TABLE','id INT AUTO_INCREMENT PRIMARY KEY'], $schema);
        $db->data_cache['table_names'] = ['users','user_activity_logs'];
    }
    check($db->query($schema), 'Disposable activity schema');
    $model = (new ReflectionClass('Users_model'))->newInstanceWithoutConstructor();
    $model->db = $db; $model->input = new RecoveryInput(); $model->load = new RecoveryLoader();
    $model->user_read_model = new RecoveryReader(); $model->user_read_model->db = $db;
    $model->user_activity_model = (new ReflectionClass('User_activity_model'))->newInstanceWithoutConstructor();
    $model->user_activity_model->db = $db;
    $controller = (new ReflectionClass('Registration'))->newInstanceWithoutConstructor();
    foreach (['input'=>$model->input,'security'=>new RecoverySecurity(),'form_validation'=>new RecoveryValidation(),'session'=>new RecoverySession(),'users_model'=>$model,'user_read_model'=>$model->user_read_model,'load'=>new RecoveryLoader(),'common_model'=>new RecoveryCommon()] as $key=>$value) { $controller->$key = $value; }
    $controller->output = new RecoveryOutput();
    $GLOBALS['mail'] = []; $GLOBALS['delivery'] = true; $GLOBALS['limits'] = []; $GLOBALS['hits'] = [];
    return [$controller, $model, $db];
}
function response($controller, $method)
{
    ob_start(); $controller->$method(); return json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
}

[$controller,$model,$db] = fixture();
$token = $model->issue_signup_resume_token(42, 86400);
check(strlen($token) === 64, 'Pending account receives a random resume token');
check($model->issue_signup_resume_token(42, 86400) === $token, 'Public retries must preserve a valid token');
check(count($GLOBALS['mail']) === 0, 'Issuing a recovery token must not send a code');
$controller->continue_registration($token);
check(count($GLOBALS['mail']) === 0 && $db->get('users')->row()->verification_code === null, 'GET continuation must be read-only');
$before = $db->get('users')->row();
$controller->input->values['resume_token'] = $token;
$sent = response($controller, 'resend_verification_email_ajax');
check($sent['status'] === true && count($GLOBALS['mail']) === 1, 'Get verification code sends once');
$row = $db->get('users')->row();
check($GLOBALS['mail'][0]['data']['verification_code'] === $row->verification_code, 'Sent code matches persisted code');
check(response($controller, 'resend_verification_email_ajax')['status'] === false && count($GLOBALS['mail']) === 1, 'Cooldown prevents immediate duplicate send');
$controller->input->values['verification_code'] = $row->verification_code;
$completed = response($controller, 'verify_email_ajax');
check($completed['status'] === true && $completed['redirect'] === base_url('dashboard'), 'Successful completion routes to dashboard');
check($controller->session->regenerated && $controller->session->values['user_id'] === 42 && $controller->session->values['user_loggedin'] === true, 'Login is established only after verification');
check($db->get('users')->row()->signup_resume_token === null, 'Completion consumes resume token');
check(response($controller, 'verify_email_ajax')['status'] === false, 'Replayed verification is rejected');

foreach ([['account_status'=>0], ['deleted_at'=>'2026-01-01'], ['password'=>'existing-hash']] as $updates) {
    [$controller,$model,$db] = fixture($updates);
    check(!$model->issue_signup_resume_token(42), 'Ineligible account cannot receive recovery token');
}
[$controller,$model,$db] = fixture();
$controller->input->values['password'] = '';
$start = response($controller, 'start_ajax');
check($start['status'] && strpos($start['redirect'], '/continue-registration/') !== false && count($GLOBALS['mail']) === 0, 'Email-only signup resumes without sending');
foreach (['login_ajax','passwordless_request_ajax'] as $method) {
    $login = (new ReflectionClass('User_login'))->newInstanceWithoutConstructor();
    foreach (get_object_vars($controller) as $key=>$value) { $login->$key = $value; }
    $result = response($login, $method);
    check($result['status'] && strpos($result['redirect'], '/continue-registration/') !== false && count($GLOBALS['mail']) === 0, "$method resumes pending registration without sending");
}
[$controller,$model,$db] = fixture();
$token = $model->issue_signup_resume_token(42);
$GLOBALS['delivery'] = false;
$controller->input->values['resume_token'] = $token;
check(response($controller,'resend_verification_email_ajax')['status'] === false, 'Delivery failure must not report code sent');
check($db->get('users')->row()->verification_code === null, 'Unsent code is revoked');
check($db->get('users')->row()->password === null, 'Delivery failure preserves unfinished account');
[$controller,$model,$db] = fixture(['verification_locked_until'=>date('Y-m-d H:i:s',time()+900), 'verification_attempts'=>5]);
$token = $model->issue_signup_resume_token(42);
$controller->input->values['resume_token'] = $token;
check(response($controller,'resend_verification_email_ajax')['status'] === false && !$GLOBALS['mail'], 'Active verification lock survives resend');
check((int) $db->get('users')->row()->verification_attempts === 5, 'Resend cannot reset locked attempt count');
[$controller,$model,$db] = fixture();
$token = $model->issue_signup_resume_token(42);
$db->where('id',42)->update('users',['signup_resume_expires_at'=>date('Y-m-d H:i:s',time()-1)]);
check(!$model->get_signup_resume_user($token), 'Expired continuation link is invalid');
$db->where('id',42)->update('users',['account_status'=>0]);
check(!$model->update_user_verification(42,'password','482915',$token), 'Blocked accounts cannot complete registration');

[$controller,$model,$db] = fixture();
$actor = ['type'=>'admin','id'=>7,'name'=>'Support'];
$result = $model->send_registration_continuation(42, $actor);
check($result['status'] && count($GLOBALS['mail']) === 1, 'Admin continuation email sends');
check($GLOBALS['mail'][0]['template'] === 'user_registration_continuation_email' && !isset($GLOBALS['mail'][0]['data']['verification_code']), 'Continuation email includes no premature code');
check($db->get('users')->row()->verification_code === null, 'Admin send does not issue a verification code');
$event = $db->get('user_activity_logs')->row();
check($event->event === 'registration_email_sent' && $event->actor_id == 7 && $event->changes === null, 'Admin send is audited without credentials');
check($model->last_registration_email(42) === $event->date_added, 'Last sent date comes from persisted audit');
$GLOBALS['limits']['registration-email:cooldown:42'] = true;
check(!$model->send_registration_continuation(42,$actor)['status'] && count($GLOBALS['mail']) === 1, 'Admin cooldown is enforced');
[$controller,$model,$db] = fixture();
$GLOBALS['delivery'] = false;
check(!$model->send_registration_continuation(42,$actor)['status'], 'Admin delivery failure is reported');
check($db->get('user_activity_logs')->row()->event === 'registration_email_failed', 'Admin delivery failure is audited');
foreach ([['account_status'=>0],['deleted_at'=>'2026-01-01'],['password'=>'completed']] as $updates) {
    [$controller,$model,$db] = fixture($updates);
    check(!$model->send_registration_continuation(42,$actor)['status'] && !$GLOBALS['mail'], 'Admin cannot send recovery to an ineligible account');
}
[$controller,$model,$db] = fixture();
$admin = (new ReflectionClass('Admin_Users'))->newInstanceWithoutConstructor();
foreach (get_object_vars($controller) as $key=>$value) { $admin->$key = $value; }
$admin->input->method = 'get';
ob_start(); $admin->send_registration_email(42); $denied = json_decode(ob_get_clean(), true);
check(!$denied['status'] && !$GLOBALS['mail'], 'Admin GET cannot send');
$admin->input->method = 'post'; $admin->role = 'traveller_support';
$deniedRole = false;
try { ob_start(); $admin->send_registration_email(42); } catch (RuntimeException $error) { $deniedRole = $error->getMessage() === 'Role denied'; } finally { ob_end_clean(); }
check($deniedRole && !$GLOBALS['mail'], 'Wrong admin role cannot send');

foreach ([true,false] as $delivered) {
    [$controller,$model,$db] = fixture();
    $controller->input->values = array_merge($controller->input->values,
        ['email'=>'new@example.invalid','firstname'=>'New','lastname'=>'User','country_code'=>'+44','phone'=>'7700900000','country'=>'UK','c_captcha_code'=>'482915']);
    $controller->session->set_userdata('signup_captcha_code','482915');
    $GLOBALS['delivery'] = $delivered;
    $signup = response($controller,'signup');
    $new = $model->user_read_model->get_user_details('new@example.invalid');
    check($new && $signup['status'] && count($GLOBALS['mail']) === 1, 'New signup persists and attempts one email');
    check(strpos($signup['redirect'], $delivered ? '/verify-email/' : '/continue-registration/') !== false, 'Initial signup routes according to actual delivery result');
    check($delivered ? !empty($new->verification_code) : $new->verification_code === null, 'Failed initial email does not leave a usable unsent code');
    $again = response($controller,'signup');
    check($again['status'] && $db->count_all('users') === 2 && count($GLOBALS['mail']) === 1, 'Repeated signup resumes the saved record without duplicate users or automatic email');
}
[$controller,$model,$db] = fixture();
$token = $model->issue_signup_resume_token(42);
$model->send_signup_verification_code(42,$token);
$code = $db->get('users')->row()->verification_code;
for ($attempt=0;$attempt<5;$attempt++) { $model->record_verification_failure(42,5,15,$code,$token); }
$row = $db->get('users')->row();
check((int) $row->verification_attempts === 5 && strtotime($row->verification_locked_until) > time(), 'Five failures atomically establish the verification lock');
$model->record_verification_failure(42,5,15,$code,$token);
check((int) $db->get('users')->row()->verification_attempts === 5, 'Active lock cannot be extended by more invalid attempts');
[$controller,$model,$db] = fixture(['password'=>password_hash('fixture-password',PASSWORD_DEFAULT)]);
$login = (new ReflectionClass('User_login'))->newInstanceWithoutConstructor();
foreach (get_object_vars($controller) as $key=>$value) { $login->$key = $value; }
// The completed-account login uses its existing activity adapter.
$login->user_activity_model = new class {
    public function actor($type,$id) { return ['type'=>$type,'id'=>$id,'name'=>'Fixture']; }
    public function record() { return true; }
};
check(response($login,'login_ajax')['status'] && $login->session->values['user_loggedin'], 'Existing password sign-in remains functional');
echo "PASS: registration recovery, token stability, read-only GET, email-only signup/sign-in, cooldown, failure, lock, replay, and verified login.\n";
echo "PASS: admin continuation template, persisted recipient, audit events, last-send date, delivery failure, GET and wrong-role denial.\n";
echo 'PASS: initial signup success/failure, repeat signup, attempt locking and completed-account login (' . (getenv('SMB_REGISTRATION_MYSQL') === '1' ? 'temporary MySQL tables' : 'in-memory SQLite') . ").\n";
