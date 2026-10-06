<?php
// Real model / CI query builder against disposable connection-local tables.
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function show_error($message) { throw new RuntimeException($message); }
class CI_Model {
    public $db, $load, $session, $common_model, $input, $user_read_model, $user_activity_model;
    public function __construct() {
        foreach ($GLOBALS['activity_model_dependencies'] ?? array() as $key => $value) { $this->$key = $value; }
    }
}
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development') { throw new RuntimeException('Local development only.'); }
require BASEPATH . 'database/DB.php';
$db = DB(array('dbdriver' => 'mysqli', 'hostname' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
    'username' => $_ENV['DB_USERNAME_LOCAL'], 'password' => $_ENV['DB_PASSWORD_LOCAL'],
    'database' => $_ENV['DB_DATABASE_LOCAL'], 'port' => (int) ($_ENV['DB_PORT_LOCAL'] ?? 3306),
    'db_debug' => false, 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_general_ci'), true);
function check_activity($value, $message) {
    if (!$value) { throw new RuntimeException('FAIL: ' . $message); }
}
check_activity((bool) $db->conn_id, 'Local database connection.');
$db->query('CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, firstname VARCHAR(100), lastname VARCHAR(100), email VARCHAR(255), number VARCHAR(50), verified_phone_e164 VARCHAR(20), phone_verified_at DATETIME, phone_signin_enabled TINYINT DEFAULT 0, phone_otp_channel VARCHAR(8) DEFAULT \'sms\', email_verified_at DATETIME, address VARCHAR(500), state VARCHAR(100), post_code VARCHAR(20), country VARCHAR(100), account_status INT DEFAULT 1, is_verified INT DEFAULT 2, UNIQUE KEY verified_phone (verified_phone_e164)) ENGINE=InnoDB');
$db->query('CREATE TEMPORARY TABLE auth_login_challenges (id INT PRIMARY KEY, user_id INT, purpose VARCHAR(30), delivery_channel VARCHAR(20), session_hash VARCHAR(64), destination_hash VARCHAR(64), consumed_at DATETIME NULL, expires_at DATETIME) ENGINE=InnoDB');
$db->query('CREATE TEMPORARY TABLE user_activity_logs (id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id INT, actor_type VARCHAR(10), actor_id INT, actor_name VARCHAR(255) NOT NULL, event VARCHAR(40), method VARCHAR(30), changes LONGTEXT, date_added DATETIME) ENGINE=InnoDB');
require $root . '/application/models/User_activity_model.php';
$model = new User_activity_model(); $model->db = $db;
// information_schema cannot see temporary tables; use a real schema readiness check
// with cached table names so model operations resolve the temporary tables.
$db->data_cache['table_names'] = array('users', 'auth_login_challenges', 'user_activity_logs');
$admin = array('type' => 'admin', 'id' => 7, 'name' => 'Support');
$user = array('type' => 'user', 'id' => 1, 'name' => 'Test User');
$oldPhone = '+2348022223347'; $newPhone = '+447911123456';
$db->insert('users', array('id' => 1, 'firstname' => 'Test', 'email' => 'test@example.invalid', 'number' => $oldPhone,
    'verified_phone_e164' => $oldPhone, 'phone_verified_at' => date('Y-m-d H:i:s'), 'phone_signin_enabled' => 1));
$db->insert('auth_login_challenges', array('id' => 1, 'user_id' => 1, 'purpose' => 'profile_phone', 'delivery_channel' => 'whatsapp', 'destination_hash' => hash('sha256', $oldPhone), 'expires_at' => date('Y-m-d H:i:s', time()+600)));
$db->insert('auth_login_challenges', array('id' => 2, 'user_id' => 1, 'purpose' => 'login', 'delivery_channel' => 'email', 'expires_at' => date('Y-m-d H:i:s', time()+600)));
check_activity($model->updateDetails(1, array('number' => null), $admin, 'phone_cleared'), 'Admin clear succeeds.');
$row = $db->where('id', 1)->get('users')->row();
check_activity($row->number === null && $row->verified_phone_e164 === null && $row->phone_verified_at === null && (int) $row->phone_signin_enabled === 0, 'Clearing resets all phone state.');
check_activity((int) $row->is_verified === 2, 'Identity verification stays intact.');
check_activity($db->where('id',1)->get('auth_login_challenges')->row()->consumed_at !== null, 'Old phone challenge revoked.');
check_activity($db->where('id',2)->get('auth_login_challenges')->row()->consumed_at === null, 'Email recovery challenge preserved.');
$log = $db->get('user_activity_logs')->row(); $changes = json_decode($log->changes, true);
check_activity($changes['number']['before'] === $oldPhone && $changes['number']['after'] === null && (int) $log->actor_id === 7, 'Historical number and admin actor retained.');
check_activity(!$model->updateDetails(1, array('number'=>$oldPhone, 'verified_phone_e164'=>$oldPhone, 'phone_verified_at'=>date('Y-m-d H:i:s'), 'phone_signin_enabled'=>1), $user, 'phone_verified',1), 'Reset prevents a stale verification result restoring the number.');
check_activity(!$model->updateDetails(1, array('phone_signin_enabled'=>1), $user,'phone_signin_updated'), 'Cannot enable sign-in before verification.');
$db->insert('auth_login_challenges', array('id'=>3, 'user_id'=>1,'purpose'=>'profile_phone','delivery_channel'=>'whatsapp','destination_hash'=>hash('sha256',$newPhone),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity($model->updateDetails(1, array('number'=>$newPhone,'verified_phone_e164'=>$newPhone,'phone_verified_at'=>date('Y-m-d H:i:s'),'phone_signin_enabled'=>1),$user,'phone_verified',3), 'New challenge verifies new phone.');
check_activity($db->where('id',3)->get('auth_login_challenges')->row()->consumed_at !== null, 'Verified challenge consumed atomically.');
check_activity(!$model->updateDetails(1, array('number'=>$newPhone),$user,'phone_verified',3), 'Consumed challenge cannot be replayed.');
check_activity($model->updateDetails(1, array('number'=>$oldPhone),$admin), 'Direct admin edit saves unverified number.');
check_activity($db->where('id',1)->get('users')->row()->verified_phone_e164 === null, 'Direct edit also resets verification.');
check_activity($model->updateDetails(1, array('address'=>'New address','state'=>'Lagos'),$user), 'User profile edits recorded.');
$count = $db->count_all('user_activity_logs');
check_activity($model->updateDetails(1, array('address'=>'New address','password'=>'never-log'),$user), 'No-op succeeds.');
check_activity($db->count_all('user_activity_logs') === $count, 'No-op and forbidden credential fields do not generate history.');
// Force audit insert failure: profile update must roll back.
class Failing_user_activity_model extends User_activity_model {
    public function record($userId, $event, array $actor, array $changes = array(), $method = null) { return false; }
}
$failingModel = new Failing_user_activity_model(); $failingModel->db = $db;
check_activity(!$failingModel->updateDetails(1,array('address'=>'Unsaved address'),$admin), 'Audit insert failure aborts update.');
check_activity($db->where('id',1)->get('users')->row()->address === 'New address', 'Failed audit rolls back account change.');
check_activity($model->record(1,'signed_in',$user,array(),'email_code') && $model->record(1,'signed_out',$user), 'Sign-in and sign-out recorded.');
$model->record(2,'signed_in',array('type'=>'user','id'=>2,'name'=>'Other user'));
$history = $model->history(1,'signin',1);
check_activity(count($history['rows']) === 2 && $history['rows'][0]->event === 'signed_out', 'History isolates account and orders latest first.');
for ($i=0; $i<21; $i++) { $model->record(1,'signed_in',$user,array(),'password'); }
check_activity($model->history(1,'signin',1)['more'] && count($model->history(1,'signin',2)['rows']) === 3, 'Pagination bounds results.');
require $root . '/application/models/Auth_challenge_model.php';
$auth = new Auth_challenge_model(); $auth->db = $db;
$db->insert('auth_login_challenges', array('id'=>4,'user_id'=>1,'purpose'=>'login','delivery_channel'=>'sms','destination_hash'=>hash('sha256',$newPhone),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity(!$auth->consumeLoginChallenge(4,1), 'Phone sign-in rejects an account reset during external code verification.');
$db->insert('auth_login_challenges', array('id'=>5,'user_id'=>1,'purpose'=>'login','delivery_channel'=>'email','destination_hash'=>hash('sha256','test@example.invalid'),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity((bool) $auth->consumeLoginChallenge(5,1), 'Email code sign-in remains available after phone reset.');
check_activity(!$auth->consumeLoginChallenge(5,1), 'Login code cannot be replayed.');
$db->insert('users', array('id'=>50,'verified_phone_e164'=>$newPhone));
$db->insert('auth_login_challenges', array('id'=>9,'user_id'=>1,'purpose'=>'profile_phone','delivery_channel'=>'whatsapp','destination_hash'=>hash('sha256',$newPhone),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity(!$model->updateDetails(1,array('number'=>$newPhone,'verified_phone_e164'=>$newPhone,'phone_verified_at'=>date('Y-m-d H:i:s'),'phone_signin_enabled'=>1),$user,'phone_verified',9), 'Unique verified phone collision fails safely.');
check_activity($db->where('id',1)->get('users')->row()->verified_phone_e164 === null && $db->where('id',9)->get('auth_login_challenges')->row()->consumed_at === null, 'Duplicate-phone failure rolls back account and code state.');
// The real admin update model must retain the administrator after impersonation.
$db->query('CREATE TEMPORARY TABLE admins (id INT PRIMARY KEY, name VARCHAR(255), email VARCHAR(255)) ENGINE=InnoDB');
$db->insert('admins',array('id'=>7,'name'=>'Support Admin','email'=>'support@example.invalid'));
$session = (object) array('email'=>'test@example.invalid','admin_email'=>'support@example.invalid');
$common = new class($db) {
    public $db;
    public function __construct($db) { $this->db=$db; }
    public function get_admin_details($email) { return $this->db->where('email',$email)->get('admins')->row(); }
};
$input = new class {
    public function post($key, $filter=true) {
        return array('clear_phone'=>'1','firstname'=>'Test','lastname'=>'User','email'=>'test@example.invalid',
            'country'=>'Nigeria','address'=>'New address','state'=>'Lagos','post_code'=>'100001')[$key] ?? null;
    }
};
$GLOBALS['activity_model_dependencies'] = array('db'=>$db,'session'=>$session,'common_model'=>$common,'input'=>$input,
    'load'=>new class { public function model($name) {} }, 'user_activity_model'=>$model,
    'user_read_model'=>new class { public function clearUserCountCaches() {} });
require $root . '/application/models/Admin_user_model.php';
$adminModel = new Admin_user_model();
check_activity((int) $adminModel->admin_details->id===7 && $adminModel->update_user(1), 'Admin reset works after impersonating a user.');
$session->email=null;
$adminModel = new Admin_user_model();
check_activity((int) $adminModel->admin_details->id===7 && $adminModel->update_user(1), 'Admin reset works after signing out of impersonation.');
$entry=$db->order_by('id','DESC')->get('user_activity_logs')->row();
check_activity((int) $entry->actor_id===7 && $entry->actor_name==='Support Admin', 'Original admin owns reset history.');
// Render the actual history fragment: stored user data must be escaped.
function html_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$rows = array((object) array('event'=>'details_updated','date_added'=>'2026-10-05 10:00:00','actor_type'=>'admin','actor_name'=>'<script>alert(1)</script>','method'=>null,'changes'=>json_encode(array('address'=>array('before'=>'Old address','after'=>'<img src=x onerror=alert(1)>')))));
$available=true; $page=1; $more=false;
ob_start(); include $root . '/application/views/admin/users/activity_history.php'; $html=ob_get_clean();
check_activity(strpos($html,'<script>')===false && strpos($html,'<img src=x')===false && strpos($html,'&lt;img')!==false, 'History escapes actor names and changed values.');
echo "PASS: phone reset, old-code rejection, new verification, history isolation, pagination, audit rollback, sign-in/out; only temporary tables used.\n";
