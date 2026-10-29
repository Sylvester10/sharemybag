<?php
// Real CodeIgniter query builder and disposable SQLite; SMTP is deliberately stubbed.
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
define('business', 'ShareMyBag');
function log_message($level, $message) { $GLOBALS['welcome_logs'][] = $message; }
function show_error($message) { throw new RuntimeException($message); }
function get_user_ip() { return '127.0.0.1'; }
function auth_throttle_check() { return array('allowed'=>true); }
function auth_throttle_clear() {}
function auth_throttle_hit() {}
function send_email_notification($ci, $email, $subject, $data, $template)
{
    $verified = $ci->users_model->db->where('id',42)->get('users')->row();
    if (empty($verified->password) || $verified->verification_code !== null || empty($verified->email_verified_at)) {
        throw new RuntimeException('Welcome must be dispatched only after signup has persisted');
    }
    $GLOBALS['welcome_calls'][] = compact('email','subject','data','template');
    return $GLOBALS['welcome_delivery'];
}
class MY_Model { public $db; public $user_read_model; }
class MY_Controller { public $input; public $security; public $form_validation; public $users_model; public $user_read_model; public $session; public $common_model; }
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Users_model.php';
require APPPATH . 'controllers/Registration.php';

function welcome_assert($condition, $message)
{
    if (!$condition) { throw new RuntimeException($message); }
}
class WelcomeInput
{
    public $values = array('resume_token'=>'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','verification_code'=>'482915','password'=>'fixture-password');
    public function post($key, $filter = false) { return $this->values[$key] ?? ''; }
    public function method() { return 'post'; }
}
class WelcomeValidation
{
    public function set_rules() {}
    public function run() { return true; }
}
class WelcomeSecurity { public function get_csrf_hash() { return 'fixture-csrf'; } }
class WelcomeReader
{
    public $snapshot; public $db;
    public function get_user_details_by_id($id) { return $this->db->where('id', $id)->get('users')->row(); }
    public function get_user_by_signup_resume_token($token) { return $token === str_repeat('a', 64) ? clone $this->snapshot : null; }
}
class WelcomeUsers extends Users_model
{
    public $failedWrites = false;
    public function update_user_verification($id, $password, $code = null, $token = null, $email = null)
    {
        return $this->failedWrites ? false : parent::update_user_verification($id, $password, $code, $token, $email);
    }
    public function record_verification_failure($user_id, $max_attempts = 5, $lock_minutes = 15, $code = null, $token = null) {}
}
function base_url($path = '') { return 'https://example.invalid/' . $path; }
class WelcomeSession { public function sess_regenerate($destroy) {} public function set_userdata($data) {} public function unset_userdata($key) {} }
class WelcomeCommon { public function update_last_login($id) {} }
function welcome_setup($password = null, $deleted = null)
{
    $db = DB(array('dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false,'dbprefix'=>''), true);
    $db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, firstname TEXT, email TEXT, password TEXT, verification_code TEXT, email_verified_at TEXT, verification_code_expires_at TEXT, verification_attempts INTEGER, verification_locked_until TEXT, signup_resume_token TEXT, signup_resume_expires_at TEXT, deleted_at TEXT, account_status INTEGER DEFAULT 1)');
    $db->insert('users', array('id'=>42,'firstname'=>'Amara','email'=>'fixture@example.com','password'=>$password,'verification_code'=>'482915','signup_resume_token'=>str_repeat('a', 64),'signup_resume_expires_at'=>date('Y-m-d H:i:s',time()+3600),'verification_code_expires_at'=>date('Y-m-d H:i:s',time()+900),'deleted_at'=>$deleted));
    $model = (new ReflectionClass('WelcomeUsers'))->newInstanceWithoutConstructor();
    $model->db = $db;
    $controller = (new ReflectionClass('Registration'))->newInstanceWithoutConstructor();
    $controller->input = new WelcomeInput();
    $controller->security = new WelcomeSecurity();
    $controller->form_validation = new WelcomeValidation();
    $controller->users_model = $model;
    $controller->user_read_model = new WelcomeReader();
    $controller->user_read_model->snapshot = $db->get('users')->row();
    $controller->user_read_model->db = $db;
    $model->user_read_model = $controller->user_read_model;
    $controller->session = new WelcomeSession();
    $controller->common_model = new WelcomeCommon();
    $GLOBALS['welcome_calls'] = array();
    $GLOBALS['welcome_delivery'] = true;
    return array($controller,$model,$db);
}
function welcome_verify($controller)
{
    ob_start();
    $controller->verify_email_ajax();
    return json_decode(ob_get_clean(), true);
}

list($controller,$model,$db) = welcome_setup();
$result = welcome_verify($controller);
welcome_assert($result['status'] === true && count($GLOBALS['welcome_calls']) === 1, 'Completed signup must send one welcome email');
welcome_assert($GLOBALS['welcome_calls'][0]['template'] === 'user_welcome_email', 'The signup completion must use the welcome template');
welcome_assert($GLOBALS['welcome_calls'][0]['email'] === 'fixture@example.com' && $GLOBALS['welcome_calls'][0]['subject'] === 'Welcome to ShareMyBag', 'Welcome must address the newly verified account');
$row = $db->get('users')->row();
welcome_assert(password_verify('fixture-password',$row->password) && $row->verification_code === null && $row->signup_resume_token === null, 'Signup completion must store password and consume code/token');
// A second request already holding the same pre-update snapshot models a raced/stale read.
$result = welcome_verify($controller);
welcome_assert($result['status'] === false && count($GLOBALS['welcome_calls']) === 1, 'Stale/repeated completion must not send a second welcome');

list($controller,$model,$db) = welcome_setup();
$controller->input->values['verification_code'] = '000000';
welcome_assert(welcome_verify($controller)['status'] === false && count($GLOBALS['welcome_calls']) === 0, 'Wrong code must not send a welcome');

list($controller,$model,$db) = welcome_setup();
$controller->user_read_model->snapshot->verification_code_expires_at = date('Y-m-d H:i:s',time()-10);
welcome_assert(welcome_verify($controller)['status'] === false && count($GLOBALS['welcome_calls']) === 0, 'Expired code must not send a welcome');

list($controller,$model,$db) = welcome_setup();
$model->failedWrites = true;
welcome_assert(welcome_verify($controller)['status'] === false && count($GLOBALS['welcome_calls']) === 0, 'Failed persistence must not report successful verification or send welcome');

list($controller,$model,$db) = welcome_setup();
$GLOBALS['welcome_delivery'] = false;
welcome_assert(welcome_verify($controller)['status'] === true, 'Welcome delivery failure must not undo verified signup');
welcome_verify($controller);
welcome_assert(count($GLOBALS['welcome_calls']) === 1, 'Failed SMTP must not cause a duplicate attempt on replay');

foreach (array('','existing-password') as $password) {
    list($controller,$model,$db) = welcome_setup($password);
    $expected = $password === '';
    welcome_assert($model->update_user_verification(42,'new-password','482915',str_repeat('a', 64)) === $expected, 'Only pending NULL/empty-password accounts may complete signup');
}
list($controller,$model,$db) = welcome_setup(null,'2026-01-01 00:00:00');
welcome_assert($model->update_user_verification(42,'new-password','482915',str_repeat('a', 64)) === false, 'Deleted accounts must not complete signup');
list($controller,$model,$db) = welcome_setup();
$db->where('id',42)->update('users',array('signup_resume_token'=>'replacement-token'));
welcome_assert($model->update_user_verification(42,'new-password','482915',str_repeat('a', 64)) === false, 'A replaced resume token must reject stale completion');
foreach (array('verification_code_expires_at'=>date('Y-m-d H:i:s',time()-10), 'signup_resume_expires_at'=>date('Y-m-d H:i:s',time()-10), 'verification_locked_until'=>date('Y-m-d H:i:s',time()+900)) as $field=>$value) {
    list($controller,$model,$db) = welcome_setup();
    $db->where('id',42)->update('users',array($field=>$value));
    welcome_assert(welcome_verify($controller)['status'] === false && count($GLOBALS['welcome_calls']) === 0, "$field changed after lookup must reject stale completion");
}
echo "PASS: welcome triggers once after verified signup; replay, invalid/expired codes, failed writes, and SMTP failure are handled.\n";
