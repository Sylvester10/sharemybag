<?php
// Actual models/query builder, disposable databases, no SMTP or provider calls.
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/'); define('APPPATH', $root . '/application/');
class CI_Model { public $db; }
class MY_Model extends CI_Model {
    public $booking_read_model, $travellers_model, $traveller_read_model, $finance_read_model, $shipping_read_model;
    public function softDelete($id) { return $this->db->where('id', $id)->update('bookings', ['deleted_at' => gmdate('Y-m-d H:i:s')]); }
}
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function traveller_is_bookable_by_cutoff($date, $now = null) { return substr((string) $date, 0, 10) > gmdate('Y-m-d'); }
function payment_status_normalize($status) { return in_array(strtolower(trim((string) $status)), ['completed','complete','success','paid'], true) ? 'completed' : 'pending'; }
function send_email_notification() { throw new RuntimeException('Unexpected email in checkout test'); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Booking_checkout_model.php';
require APPPATH . 'models/Travellers_model.php';
require APPPATH . 'models/Bookings_model.php';
require APPPATH . 'models/User_bookings_model.php';
function checkout_check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$mysql = getenv('SMB_CHECKOUT_MYSQL') === '1';
if ($mysql) {
    require $root . '/vendor/autoload.php'; Dotenv\Dotenv::createImmutable($root)->safeLoad();
    checkout_check(($_ENV['APP_ENV'] ?? '') === 'development', 'Local development fixture only');
}
function checkout_fixture()
{
    global $mysql, $root;
    $db = DB($mysql ? ['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],
        'database'=>$_ENV['DB_DATABASE_LOCAL'],'db_debug'=>false] : ['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false], true);
    $table = $mysql ? 'CREATE TEMPORARY TABLE ' : 'CREATE TABLE ';
    $engine = $mysql ? ' ENGINE=InnoDB' : '';
    foreach ([
        'users' => '(id INTEGER PRIMARY KEY, account_status INTEGER, deleted_at DATETIME)',
        'travellers' => '(id INTEGER PRIMARY KEY, original_bag_space DECIMAL(12,3), used_space DECIMAL(12,3), available_space DECIMAL(12,3), bag_locked INTEGER, status VARCHAR(24), travel_date DATETIME, deleted_at DATETIME)',
        'bookings' => '(id INTEGER PRIMARY KEY, user_id INTEGER, traveller_id INTEGER, selected_space DECIMAL(12,3), payment_status VARCHAR(24), cancelled_at DATETIME, deleted_at DATETIME, new INTEGER, hash VARCHAR(80), tracking_id VARCHAR(30), id_photo VARCHAR(255), selfie VARCHAR(255))',
        'shipping' => '(tracking_id VARCHAR(30))',
    ] as $name => $columns) { checkout_check($db->query($table . $name . $columns . $engine), 'Create isolated ' . $name); }
    if ($mysql) {
        $sql = preg_replace('/^--.*$/m', '', file_get_contents($root . '/database/027_booking_checkout.sql'));
        $sql = str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql);
        foreach ([1,2] as $pass) { foreach (explode(';', $sql) as $statement) {
            if (trim($statement) !== '') { checkout_check($db->query($statement), 'Replay actual checkout migration'); }
        } }
        $db->data_cache['table_names'] = ['users','travellers','bookings','shipping','booking_checkouts','booking_payment_attempts'];
    } else {
        checkout_check($db->query('CREATE TABLE booking_checkouts(booking_id INTEGER PRIMARY KEY,user_id INTEGER,request_key TEXT UNIQUE,form_data TEXT,resume_until TEXT,created_at TEXT,updated_at TEXT)'), 'Create checkout fixture');
        checkout_check($db->query('CREATE TABLE booking_payment_attempts(id INTEGER PRIMARY KEY AUTOINCREMENT,booking_id INTEGER,user_id INTEGER,traveller_id INTEGER,provider TEXT,reference TEXT UNIQUE,provider_session_id TEXT,authorization_url TEXT,status TEXT,amount_minor INTEGER,currency TEXT,exchange_rate DECIMAL,quote_data TEXT,hold_space DECIMAL,hold_until TEXT,created_at TEXT,updated_at TEXT,confirmed_at TEXT,review_reason TEXT)'), 'Create attempts fixture');
    }
    $db->insert('travellers', ['id'=>1,'original_bag_space'=>10,'used_space'=>0,'available_space'=>10,'bag_locked'=>0,'status'=>'Approved','travel_date'=>gmdate('Y-m-d',time()+86400*10)]);
    foreach ([1,2] as $id) {
        $db->insert('users', ['id'=>$id,'account_status'=>1]);
        $db->insert('bookings', ['id'=>$id,'user_id'=>$id,'traveller_id'=>1,'selected_space'=>6,'payment_status'=>'pending','new'=>1,'hash'=>'booking'.$id,'tracking_id'=>'fixture'.$id]);
    }
    $model = (new ReflectionClass('Booking_checkout_model'))->newInstanceWithoutConstructor(); $model->db = $db;
    $travellers = (new ReflectionClass('Travellers_model'))->newInstanceWithoutConstructor(); $travellers->db = $db;
    $cache = new class { function clearTravellerCountCaches(){} function clearFinanceSummaryCaches(){} function clearBookingCountCaches(){} function clearShippingCountCaches(){} };
    $travellers->traveller_read_model = $cache;
    $reader = new class($db) { private $db; function __construct($db){$this->db=$db;} function get_booking_details_by_id($id){return $this->db->where('id',$id)->get('bookings')->row();} function clearBookingCountCaches(){} };
    $admin = (new ReflectionClass('Bookings_model'))->newInstanceWithoutConstructor(); $admin->db=$db; $admin->travellers_model=$travellers; $admin->booking_read_model=$reader;
    $admin->finance_read_model=$cache; $admin->shipping_read_model=$cache;
    foreach ([1,2] as $id) { checkout_check($model->capture($reader->get_booking_details_by_id($id), ['items'=>'[]','agent_name'=>'Fixture','_booking_step'=>'3','_request_key'=>hash('sha256','fixture'.$id),'payment_status'=>'completed','SMTP_PASS'=>'never-store']), 'Capture form'); }
    return [$model,$db,$travellers,$admin,$reader];
}
function checkout_attempt($model, $reader, $id=1, $provider='paystack') { return $model->create_attempt($reader->get_booking_details_by_id($id), $id, $provider, 10000, 'NGN', 1.0, ['total_amount'=>100,'currency'=>'NGN']); }
function checkout_paid($attempt) { return ['outcome'=>'paid','provider'=>$attempt->provider,'reference'=>$attempt->reference,'provider_session_id'=>$attempt->provider_session_id,'booking_id'=>$attempt->booking_id,'user_id'=>$attempt->user_id,'amount_minor'=>(int)$attempt->amount_minor,'currency'=>$attempt->currency]; }

[$model,$db,$travellers,$admin,$reader]=checkout_fixture();
$stored=$model->checkout(1,1); $form=json_decode($stored->form_data,true);
checkout_check($form['_booking_step']==='3' && !isset($form['payment_status'],$form['SMTP_PASS'],$form['_request_key']), 'Capture only editable fields, preserve step, exclude secrets/server keys');
checkout_check($model->checkout_by_request(hash('sha256','fixture1'),1)->booking_id==1 && !$model->checkout_by_request(hash('sha256','fixture1'),2), 'Request dedupe is owner scoped');
checkout_check(!$model->assert_resumable($reader->get_booking_details_by_id(1),2)['status'], 'Cross-user resume denied');
$first=checkout_attempt($model,$reader); checkout_check($first['status'], 'First payment acquires hold');
checkout_check((float)$db->get('travellers')->row()->available_space===4.0, 'Live hold reduces public available space');
$again=checkout_attempt($model,$reader); checkout_check(!$again['status'] && $again['reuse'] && $again['attempt']->reference===$first['attempt']->reference, 'Repeated submit reuses existing attempt without another hold/reference');
checkout_check($db->count_all('booking_payment_attempts')===1, 'One active attempt for repeated booking submit');
checkout_check(!checkout_attempt($model,$reader,2)['status'], 'Competing booking cannot reserve the same last space');
checkout_check(!$admin->confirm_booking(2), 'Manual confirmation cannot steal another active hold');
checkout_check($reader->get_booking_details_by_id(2)->payment_status==='pending', 'Rejected manual confirmation rolls back its payment update');
checkout_check(!$admin->confirm_booking(1), 'Managed online attempt cannot be manually marked paid');
checkout_check(!$travellers->remove_traveller_bag_space(1,5), 'Capacity reduction cannot remove allocated space');
$bad=checkout_paid($first['attempt']);$bad['amount_minor']++;
checkout_check(!$model->apply_verified($first['attempt']->id,$bad)['status'], 'Wrong payment amount rejected');
$bad=checkout_paid($first['attempt']);$bad['user_id']=2;
checkout_check(!$model->apply_verified($first['attempt']->id,$bad)['status'], 'Wrong payment owner rejected');
$paid=$model->apply_verified($first['attempt']->id,checkout_paid($first['attempt']));
checkout_check($paid['paid'] && $paid['newly_paid'], 'Verified payment confirms booking and consumes its own hold');
checkout_check((float)$db->get('travellers')->row()->available_space===4.0, 'Confirm replaces hold rather than counting it twice');
$paid=$model->apply_verified($first['attempt']->id,checkout_paid($first['attempt']));
checkout_check($paid['paid'] && !$paid['newly_paid'], 'Repeated provider event has no duplicate fulfillment');
$db->where('id',1)->update('bookings',['payment_status'=>'canceled','cancelled_at'=>gmdate('Y-m-d H:i:s')]);
$afterCancel=$model->apply_verified($first['attempt']->id,checkout_paid($first['attempt']));
checkout_check(!$afterCancel['paid'] && empty($afterCancel['review']) && $model->attempt_by_id($first['attempt']->id)->status==='paid',
    'Duplicate paid event preserves immutable successful attempt after later booking cancellation/refund');

[$model,$db,$travellers,$admin,$reader]=checkout_fixture();
$first=checkout_attempt($model,$reader);$model->end_attempt($first['attempt']->id,'canceled');
$deadline=$model->checkout(1,1)->resume_until;
checkout_check($deadline>gmdate('Y-m-d H:i:s') && (float)$db->get('travellers')->row()->available_space===10.0, 'Confirmed cancellation opens recovery window and releases capacity');
$model->end_attempt($first['attempt']->id,'canceled');
checkout_check($model->checkout(1,1)->resume_until===$deadline, 'Repeated cancel does not extend recovery deadline');
$db->where('booking_id',1)->update('booking_checkouts',['resume_until'=>'2001-01-01 00:00:00']);
$expiredState=$model->resume_state($reader->get_booking_details_by_id(1),1);
checkout_check(!empty($expiredState['recovery_expired']) && !$expiredState['resume_available'] && !$expiredState['checking_payment']
    && $expiredState['message']==='', 'Elapsed unpaid recovery is status only, without a replacement action or helper message');
$db->where('booking_id',1)->update('booking_checkouts',['resume_until'=>$deadline]);
$next=checkout_attempt($model,$reader);checkout_check($next['status'] && $next['attempt']->reference!==$first['attempt']->reference, 'Retry gets separate immutable payment attempt');
$late=$model->apply_verified($first['attempt']->id,checkout_paid($first['attempt']));
checkout_check(!empty($late['review']) && !$late['paid'] && $reader->get_booking_details_by_id(1)->payment_status==='pending', 'Superseded late payment is reviewed without confirming booking');
checkout_check($model->attempt_by_id($next['attempt']->id)->status==='initializing', 'Late old event never overwrites newer attempt');
checkout_check(!$model->assert_resumable($reader->get_booking_details_by_id(1),1)['status'], 'Review blocks further payment after a late charge');
$reviewState=$model->resume_state($reader->get_booking_details_by_id(1),1);
checkout_check($reviewState['review_required'] && !$reviewState['resume_available'] && !$reviewState['checking_payment'], 'History review state suppresses resume and fresh-search shortcuts');
$reviewAgain=$model->apply_verified($first['attempt']->id,checkout_paid($first['attempt']));
checkout_check(!$reviewAgain['newly_reviewed'] && $late['newly_reviewed'], 'Review alerts are claimed once');
$nextPaid=$model->apply_verified($next['attempt']->id,checkout_paid($next['attempt']));
checkout_check($nextPaid['review'] && $nextPaid['newly_reviewed'] && !$nextPaid['paid'], 'Existing payment review freezes newer attempt fulfillment too');

[$model,$db,$travellers,$admin,$reader]=checkout_fixture();
$first=checkout_attempt($model,$reader,'1','stripe');
checkout_check($model->attach_gateway($first['attempt']->id,'cs_fixture','https://checkout.stripe.com/fixture'),'Attach Stripe session');
$attempt=$model->attempt_by_id($first['attempt']->id);$bad=checkout_paid($attempt);$bad['provider_session_id']='cs_other';
checkout_check(!$model->apply_verified($attempt->id,$bad)['status'], 'Unrelated Stripe session cannot confirm booking');
$model->mark_unknown($attempt->id,'Fixture gateway timeout');
checkout_check(!checkout_attempt($model,$reader)['status'] && $model->attempt_by_id($attempt->id)->status==='pending', 'Unknown gateway outcome blocks another charge');
$db->where('booking_id',1)->update('booking_checkouts',['resume_until'=>'2001-01-01 00:00:00']);
$pendingState=$model->resume_state($reader->get_booking_details_by_id(1),1);
checkout_check($pendingState['checking_payment'] && !$pendingState['resume_available'] && !empty($pendingState['recovery_expired']), 'Unknown payment retains its financial status while recovery actions expire');
$db->where('id',$attempt->id)->update('booking_payment_attempts',['hold_until'=>gmdate('Y-m-d H:i:s',time()-1)]);
checkout_check(count($model->expired_attempts())===1,'Expiry worker sees elapsed attempt without assuming unpaid');
$late=$model->apply_verified($attempt->id,checkout_paid($attempt));
checkout_check(!empty($late['review']) && !$late['paid'],'Payment after lease expiry requires reconciliation/refund review');

[$model,$db,$travellers,$admin,$reader]=checkout_fixture();
$first=checkout_attempt($model,$reader,1,'stripe');
checkout_check($model->attach_gateway($first['attempt']->id,'cs_lost_ack',null),'Signed lost initialization acknowledgement can bind Stripe session without fake URL');
$bound=$model->attempt_by_id($first['attempt']->id);
checkout_check($bound->provider_session_id==='cs_lost_ack' && $bound->authorization_url===null,'Absent gateway URL remains absent');
checkout_check(!$model->attach_gateway($bound->id,'cs_wrong',null),'Bound Stripe session cannot be replaced');
checkout_check($model->resume_state($reader->get_booking_details_by_id(1),1)['resume_available'],'Known open unpaid checkout can be resumed during valid window');
$db->where('id',$bound->id)->update('booking_payment_attempts',['updated_at'=>'2001-01-01 00:00:00']);
checkout_check(count($model->reconcilable_attempts())===1,'Worker checks live payment attempts before lease expiry');
$db->where('id',$bound->id)->update('booking_payment_attempts',['hold_until'=>'2001-01-01 00:00:00']);
$db->where('id',2)->update('bookings',['selected_space'=>1]);
$second=checkout_attempt($model,$reader,2);
$db->where('id',$second['attempt']->id)->update('booking_payment_attempts',['updated_at'=>'2002-01-01 00:00:00','hold_until'=>'2002-01-01 00:00:00']);
checkout_check((int)$model->reconcilable_attempts(1)[0]->id===(int)$bound->id,'Oldest unchecked payment first');
$model->mark_unknown($bound->id,'Fixture pending');
checkout_check((int)$model->reconcilable_attempts(1)[0]->id===(int)$second['attempt']->id
    && (int)$model->expired_attempts(1)[0]->id===(int)$second['attempt']->id,'Unknown old payment rotates behind next due attempt instead of starving it');
checkout_check((float)$db->get('travellers')->row()->available_space===10.0,'Unknown reconciliation refreshes cached capacity after elapsed leases');

foreach (['locked','cutoff','blocked','expired'] as $case) {
    [$model,$db,$travellers,$admin,$reader]=checkout_fixture();
    if($case==='locked')$db->where('id',1)->update('travellers',['bag_locked'=>1]);
    if($case==='cutoff')$db->where('id',1)->update('travellers',['travel_date'=>'2001-01-01']);
    if($case==='blocked')$db->where('id',1)->update('users',['account_status'=>0]);
    if($case==='expired'){$first=checkout_attempt($model,$reader);$model->end_attempt($first['attempt']->id,'failed');$db->where('booking_id',1)->update('booking_checkouts',['resume_until'=>'2001-01-01 00:00:00']);}
    checkout_check(!checkout_attempt($model,$reader)['status'],'Invalid '.$case.' allocation denied');
}

[$model,$db,$travellers,$admin,$reader]=checkout_fixture();
$first=checkout_attempt($model,$reader);
$db->where('id',2)->update('bookings',['payment_status'=>' PAID ','selected_space'=>4]);
checkout_check($travellers->update_traveller_space(1),'Completed aliases and active holds both counted');
checkout_check((float)$db->get('travellers')->row()->available_space===0.0,'Alias-confirmed space cannot be sold twice');
checkout_check(!$admin->add_parcel(2,'Fixture','Normal',1)['status'],'Admin cannot add parcel into reserved capacity');
$db->where('id',1)->update('travellers',['bag_locked'=>1]);
$snapshot=$travellers->update_traveller_space(1,true);checkout_check($snapshot && (float)$snapshot->available_space===0.0,'Recalculation honors locked bag');
checkout_check($travellers->unlock_traveller_bag(1) && (float)$db->get('travellers')->row()->available_space===0.0,'Unlock does not release someone else reservation');
$userModel=(new ReflectionClass('User_bookings_model'))->newInstanceWithoutConstructor();
$userModel->db=$db;$userModel->booking_read_model=$reader;$userModel->travellers_model=$travellers;
$userModel->finance_read_model=$admin->finance_read_model;$userModel->shipping_read_model=$admin->shipping_read_model;
$userModel->delete_booking(1);
checkout_check((float)$db->get('travellers')->row()->available_space===6.0,'Deleting unpaid booking releases only its hold, never invents an extra selected-space credit');
echo 'PASS: full-form snapshots, owner/idempotency scope, capacity leases, manual-route protection, strict provider binding, once-only confirmation, retry expiry, unknown and late-payment review ('.($mysql?'temporary MySQL':'SQLite').").\n";
