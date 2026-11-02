<?php
// Real CI queries on disposable SQLite or connection-local MySQL tables; no SMTP.
error_reporting(E_ALL & ~E_DEPRECATED);
$root=dirname(__DIR__); define('BASEPATH',$root.'/system/'); define('APPPATH',$root.'/application/');
class MY_Model { public $db,$booking_read_model,$finance_read_model,$travellers_model; }
function log_message($level,$message) {}
function show_error($message) { throw new RuntimeException($message); }
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
function payment_status_normalize($status) {
    $status=strtolower(trim((string)$status));
    if(in_array($status,['completed','complete','paid','success'],true))return 'completed';
    return in_array($status,['canceled','cancelled','failed','declined'],true)?'canceled':'pending';
}
function x_date($date){return $date;}
function currency_symbol_text($currency){return 'GBP';}
function currency_code_normalize($currency){return $currency;}
function send_email_notification(){ $GLOBALS['attempts']++; return true; }
require BASEPATH.'database/DB.php'; require APPPATH.'models/Users_model.php'; require APPPATH.'models/Bookings_model.php';
function mail_once_check($ok,$message){if(!$ok)throw new RuntimeException($message);}
if(getenv('SMB_MAIL_MYSQL')==='1'){
    require $root.'/vendor/autoload.php';Dotenv\Dotenv::createImmutable($root)->safeLoad();
    mail_once_check(($_ENV['APP_ENV']??'')==='development','MySQL proof is local development only');
}
/** Fail a transaction boundary while preserving real queries and rollback behavior. */
class BookingMailFaultDatabase {
    private $database;
    public $failBegin=false,$failCommit=false;
    function __construct($database){$this->database=$database;}
    function __call($method,$arguments){
        if($this->failBegin && in_array($method,['trans_begin','trans_start'],true))return false;
        if($this->failCommit && in_array($method,['trans_commit','trans_complete'],true))return false;
        $result=$this->database->$method(...$arguments);
        return $result===$this->database?$this:$result;
    }
}
function mail_once_fixture($status){
    $mysql=getenv('SMB_MAIL_MYSQL')==='1';
    $db=DB($mysql?['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],
        'database'=>$_ENV['DB_DATABASE_LOCAL'],'db_debug'=>false]:['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false],true);
    $table=$mysql?'CREATE TEMPORARY TABLE':'CREATE TABLE';
    mail_once_check($db->query($table.' bookings(id INTEGER PRIMARY KEY,hash VARCHAR(64),payment_status VARCHAR(24),new INTEGER,deleted_at DATETIME,traveller_id INTEGER,paystack_ref VARCHAR(64),paystack_exchange_rate DECIMAL(12,4))'),'Create isolated bookings fixture');
    $db->insert('bookings',['id'=>7,'hash'=>'fixture','payment_status'=>$status,'new'=>1,'traveller_id'=>8]);
    $reader=new class($db){public $db;function __construct($db){$this->db=$db;}function clearBookingCountCaches(){}function dataByHash($hash){return $this->row($this->db->where('hash',$hash)->get('bookings')->row());}function get_booking_details_by_id($id){return $this->row($this->db->where('id',$id)->get('bookings')->row());}function row($row){if(!$row)return null;foreach(['tracking_id','agent_name','date_added','items','insurance','user_fullname','traveller_name','traveller_contact','traveller_departure_state','traveller_drop_address1','traveller_drop_date1','traveller_drop_address2','traveller_drop_date2','traveller_departure_date','traveller_arrival_date','traveller_current_state','traveller_arrival_airport','traveller_arrival_state']as$field){$row->$field='fixture';}$row->total_amount=10;$row->currency='GBP';$row->agent_email='fixture@example.invalid';return $row;}};
    $capacity=new class {public $calls=0,$fail=false;function update_traveller_space($id){$this->calls++;return !$this->fail;}};
    $finance=new class {function clearFinanceSummaryCaches(){}};
    $models=[];foreach(['Users_model','Bookings_model']as$class){$model=(new ReflectionClass($class))->newInstanceWithoutConstructor();$model->db=$db;$model->booking_read_model=$reader;$model->finance_read_model=$finance;$model->travellers_model=$capacity;$models[]=$model;}
    $GLOBALS['attempts']=0;return [$models[0],$models[1],$db,$capacity];
}
foreach(['canceled','pending',null]as$status){
    [$user,$admin,$db,$capacity]=mail_once_fixture($status);
    mail_once_check($user->finalize_booking_payment_by_hash('fixture',true)['status'],'First completion succeeds');
    mail_once_check($GLOBALS['attempts']===2 && $capacity->calls===1,'First completion attempts one pair of notifications');
    mail_once_check($user->finalize_booking_payment_by_hash('fixture',true)['status'],'Repeat completion remains successful');
    mail_once_check($GLOBALS['attempts']===2 && $capacity->calls===1,'Repeated completion must not resend');
    mail_once_check($admin->confirm_booking(7),'Admin replay accepts persisted completion');
    mail_once_check($GLOBALS['attempts']===2,'Admin replay must not resend either');
    mail_once_check($user->finalize_booking_payment_by_hash('fixture',false)['status'],'Late cancellation reports existing success');
    mail_once_check($db->get('bookings')->row()->payment_status==='completed','Late cancellation cannot downgrade payment');
}
foreach(['completed','paid','complete','success',' PAID ']as$status){
    [$user,$admin,$db,$capacity]=mail_once_fixture($status);
    $user->finalize_booking_payment_by_hash('fixture',true);
    mail_once_check($admin->confirm_booking(7),'Admin accepts completed aliases');
    $user->cancel_booking_payment_by_hash('fixture');
    mail_once_check(!$user->mark_paystack_initialized(7,'fixture-reference',1),'Reinitialization cannot overwrite completed payment');
    mail_once_check($GLOBALS['attempts']===0 && $capacity->calls===0,'Legacy completed aliases must not trigger mail or capacity updates');
    mail_once_check($db->get('bookings')->row()->payment_status===$status,'Completed aliases remain intact');
}
// Repeated cancellation reports the same result even when MySQL reports zero changed rows.
[$user,$admin,$db,$capacity]=mail_once_fixture('canceled');
foreach([1,2]as$attempt){
    $result=$user->finalize_booking_payment_by_hash('fixture',false);
    mail_once_check(!$result['status'] && $result['title']==='Booking Canceled.','Canceled replay keeps its accurate outcome');
}
mail_once_check($GLOBALS['attempts']===0,'Cancellation sends no confirmation email');
foreach(['missing','deleted']as$case){
    [$user,$admin,$db,$capacity]=mail_once_fixture('pending');
    if($case==='missing')$db->where('id',7)->delete('bookings');
    else $db->where('id',7)->update('bookings',['deleted_at'=>'2026-01-01 00:00:00']);
    mail_once_check(!$admin->confirm_booking(7),'Admin rejects missing/deleted rows');
    mail_once_check(!$user->finalize_booking_payment_by_hash('fixture',true)['status'],'Callback rejects missing/deleted rows');
    mail_once_check($GLOBALS['attempts']===0 && $capacity->calls===0,'Unavailable booking has no side effects');
}
foreach(['user','admin']as$path){
    foreach(['capacity','begin','commit']as$failure){
        [$user,$admin,$db,$capacity]=mail_once_fixture('pending');
        $faults=new BookingMailFaultDatabase($db);$user->db=$faults;$admin->db=$faults;
        $capacity->fail=$failure==='capacity';$faults->failBegin=$failure==='begin';$faults->failCommit=$failure==='commit';
        $result=$path==='user'?$user->finalize_booking_payment_by_hash('fixture',true)['status']:$admin->confirm_booking(7);
        mail_once_check(!$result,"$path $failure failure rejects confirmation");
        mail_once_check($db->get('bookings')->row()->payment_status==='pending' && $GLOBALS['attempts']===0,"$path $failure failure rolls back and sends no mail");
    }
}
echo 'PASS: payment sends once across callback/admin replay and aliases; cancellation, missing rows, and transaction failures are safe ('.(getenv('SMB_MAIL_MYSQL')==='1'?'temporary MySQL':'SQLite').").\n";
