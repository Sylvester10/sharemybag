<?php
// Real application flow, writer, pricing and reservation model; recording gateways/SMTP, disposable SQLite.
error_reporting(E_ALL & ~E_DEPRECATED);
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');
class CI_Model {public $db,$input,$load;}
function &get_instance(){return $GLOBALS['flow_ci'];}
function log_message($level,$message){}
function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
function show_error($message){throw new RuntimeException($message);}
function base_url($path=''){return 'https://smb.example.invalid/'.$path;}
function send_email_notification(){ $GLOBALS['flow_mail']++;return true; }
function html_escape($value){return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
require BASEPATH.'database/DB.php';require APPPATH.'helpers/app_helper.php';require APPPATH.'helpers/booking_checkout_helper.php';
require APPPATH.'core/MY_Model.php';require APPPATH.'models/Users_model.php';require APPPATH.'models/Travellers_model.php';
require APPPATH.'models/Booking_checkout_model.php';require APPPATH.'libraries/Booking_checkout_flow.php';
require APPPATH.'libraries/Booking_payment_reconciler.php';
require APPPATH.'libraries/Booking_checkout_worker.php';
function flow_check($condition,$message){if(!$condition){throw new RuntimeException($message);}}
class FlowRecordingGateway {
    public $starts=0,$initialize_ok=true,$initialize_outcome='unknown',$outcome='unpaid',$outcomes=[],$closes=[];
    public function initialize($attempt,$booking){$this->starts++;return ['status'=>$this->initialize_ok,'outcome'=>$this->initialize_outcome,'session_id'=>null,'url'=>'https://checkout.paystack.com/'.$attempt->reference];}
    public function verify($attempt,$close=false){$this->closes[$attempt->id]=$close;return ['outcome'=>$this->outcomes[$attempt->id]??$this->outcome,'provider'=>$attempt->provider,'reference'=>$attempt->reference,
        'provider_session_id'=>$attempt->provider_session_id,'booking_id'=>$attempt->booking_id,'user_id'=>$attempt->user_id,
        'amount_minor'=>(int)$attempt->amount_minor,'currency'=>$attempt->currency];}
}
function flow_fixture()
{
    global $root;
    $db=DB(['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false],true);
    $db->query('CREATE TABLE users(id INTEGER PRIMARY KEY,firstname TEXT,lastname TEXT,email TEXT,account_status INTEGER,deleted_at TEXT,hash TEXT)');
    $db->query('CREATE TABLE travellers(id INTEGER PRIMARY KEY,fullname TEXT,email TEXT,phone TEXT,hash TEXT,location TEXT,destination TEXT,status TEXT,travel_date TEXT,arrival_date TEXT,original_bag_space REAL,used_space REAL,available_space REAL,bag_locked INTEGER,deleted_at TEXT)');
    // Match the real writer's field names, keeping the test independent of the hosted schema.
    $source=file_get_contents(APPPATH.'models/Users_model.php');$start=strpos($source,'// ── EXPLICIT FIELD ALLOWLIST');$end=strpos($source,'if ($existing)',$start);
    preg_match_all("/'([a-z_0-9]+)'\\s*=>/",substr($source,$start,$end-$start),$matches);
    $numeric=['total_amount','sub_total','service_charge','vat','insurance','selected_space','selected_price','traveller_commission'];
    $columns=['id INTEGER PRIMARY KEY AUTOINCREMENT','deleted_at TEXT','cancelled_at TEXT','new INTEGER DEFAULT 1','date_added TEXT DEFAULT CURRENT_TIMESTAMP','paystack_ref TEXT','paystack_exchange_rate REAL'];
    foreach(array_unique($matches[1])as$key){$columns[]='"'.$key.'" '.(in_array($key,$numeric,true)?'REAL':'TEXT');}
    flow_check($db->query('CREATE TABLE bookings('.implode(',',$columns).')'),'Create isolated booking writer fixture');
    $db->query('CREATE TABLE booking_checkouts(booking_id INTEGER PRIMARY KEY,user_id INTEGER,request_key TEXT UNIQUE,form_data TEXT,resume_until TEXT,created_at TEXT,updated_at TEXT)');
    $db->query('CREATE TABLE booking_payment_attempts(id INTEGER PRIMARY KEY AUTOINCREMENT,booking_id INTEGER,user_id INTEGER,traveller_id INTEGER,provider TEXT,reference TEXT UNIQUE,provider_session_id TEXT,authorization_url TEXT,status TEXT,amount_minor INTEGER,currency TEXT,exchange_rate REAL,quote_data TEXT,hold_space REAL,hold_until TEXT,created_at TEXT,updated_at TEXT,confirmed_at TEXT,review_reason TEXT)');
    $user=(object)['id'=>1,'firstname'=>'Ada','lastname'=>'Example','email'=>'ada@example.invalid','account_status'=>1];$db->insert('users',(array)$user);
    $traveller=(object)['id'=>7,'fullname'=>'Traveller','email'=>'traveller@example.invalid','phone'=>'+447911123456','hash'=>'trip7','location'=>'Nigeria','destination'=>'United Kingdom',
        'status'=>'Approved','travel_date'=>gmdate('Y-m-d',time()+86400*10),'arrival_date'=>gmdate('Y-m-d',time()+86400*12),
        'original_bag_space'=>10,'used_space'=>0,'available_space'=>10,'bag_locked'=>0];$db->insert('travellers',(array)$traveller);
    $ci=(object)['db'=>$db];$GLOBALS['flow_ci']=$ci;$GLOBALS['flow_mail']=0;
    $ci->session=new class {
        public $values=[];
        function userdata($key){return $this->values[$key]??null;}
        function set_userdata($key,$value){$this->values[$key]=$value;}
        function set_flashdata($key,$value){$this->values[$key]=$value;}
    };
    $ci->load=new class {function helper($name){}function model($name){}function library($name){}function database(){}};
    $ci->traveller_read_model=new class($db){private $db;function __construct($db){$this->db=$db;}function get_traveller_details_by_id($id){return $this->db->where('id',$id)->get('travellers')->row();}function is_bookable_by_cutoff($trip){return traveller_is_bookable_by_cutoff($trip->travel_date);}function clearTravellerCountCaches(){}};
    $reader=new class($db){private $db;function __construct($db){$this->db=$db;}function get_booking_details_by_id($id){return $this->db->where('id',$id)->get('bookings')->row();}function dataByHash($hash){return $this->db->where('hash',$hash)->get('bookings')->row();}function clearBookingCountCaches(){}};
    $ci->booking_read_model=$reader;
    $ci->finance_read_model=new class {function get_most_recent_pound_exchange_rate(){return(object)['rate'=>2000];}function get_most_recent_cad_exchange_rate(){return(object)['rate'=>1000];}function clearFinanceSummaryCaches(){}};
    foreach(['Users_model'=>'users_model','Travellers_model'=>'travellers_model','Booking_checkout_model'=>'booking_checkout_model']as$class=>$property){
        $model=(new ReflectionClass($class))->newInstanceWithoutConstructor();$model->db=$db;$model->load=$ci->load;
        $model->input=new class {function post($key,$clean=false){return $_POST[$key]??null;}};
        if($class==='Users_model'){$model->table='users';$model->booking_read_model=$reader;$model->finance_read_model=$ci->finance_read_model;}
        if($class!=='Booking_checkout_model'){$model->traveller_read_model=$ci->traveller_read_model;}
        $ci->$property=$model;
    }
    $ci->booking_payment_gateway=new FlowRecordingGateway();$ci->booking_payment_reconciler=new Booking_payment_reconciler();
    $flow=new Booking_checkout_flow();
    $post=['traveller_id'=>'7','payment_method'=>'paystack','insurance'=>'3.99','parcel_status'=>'someone_else','receiver_status'=>'someone_else',
        'agent_name'=>'Alice','agent_country_code'=>'+44','agent_phone'=>'7911123456','agent_email'=>'alice@example.invalid','agent_address'=>'Sender address','agent_locality'=>'London','agent_postcode'=>'SW1A',
        'receiver_name'=>'Bob','receiver_country_code'=>'+234','receiver_phone'=>'8022213347','receiver_email'=>'bob@example.invalid','receiver_address'=>'Receiver address','receiver_locality'=>'Lagos','receiver_postcode'=>'100001',
        'items'=>json_encode([['category'=>'Normal','item_name'=>'Clothes','size'=>6,'price'=>0.01]]),'_booking_step'=>'5',
        'booking_request_id'=>$flow->new_request(7),'booking_checkout_token'=>$flow->intent(),'currency'=>'GBP','rate'=>'2000'];
    $quote=booking_checkout_quote($traveller,booking_checkout_form($post),2000);
    $post['price_calculations']=json_encode(['totalAmount'=>$quote['total_amount'],'selectedSpace'=>$quote['selected_space']]);
    return [$ci,$flow,$post,$user];
}
[$ci,$flow,$post,$user]=flow_fixture();
$bad=$post;$bad['booking_request_id']=[];flow_check(!$flow->submit($bad,$user)['status'],'Malformed nonce denied without an exception');
$bad=$post;$bad['booking_checkout_token']='forged';flow_check(!$flow->submit($bad,$user)['status'],'Forged session intent denied');
$bad=$post;$bad['price_calculations']='{"totalAmount":0,"selectedSpace":6}';$changed=$flow->submit($bad,$user);
flow_check(!empty($changed['quote_changed']) && $ci->db->count_all('bookings')===0 && $ci->booking_payment_gateway->starts===0,'Changed price requires visible reconfirmation before allocating or charging');
$first=$flow->submit($post,$user);flow_check($first['status'] && $ci->booking_payment_gateway->starts===1,'First submit opens one recorded gateway attempt');
$booking=$ci->db->get('bookings')->row();$original_id=$booking->id;$tracking=$booking->tracking_id;
flow_check($ci->db->count_all('bookings')===1 && $ci->db->count_all('booking_payment_attempts')===1,'One booking and attempt');
flow_check((float)$booking->total_amount>0.01 && (float)$booking->selected_space===6.0,'Forged per-item prices are ignored by real writer');
$again=$flow->submit($post,$user);flow_check($again['status'] && $again['redirect']===$first['redirect'] && $ci->booking_payment_gateway->starts===1,'Repeated initial POST reuses gateway instead of another booking/charge');
flow_check(!$flow->resume(['booking_checkout_token'=>$flow->intent(),'booking_hash'=>$booking->hash],(object)['id'=>2])['status'],'Cross-account recovery denied');
$ci->booking_payment_gateway->outcome='pending';$pending=$flow->resume(['booking_checkout_token'=>$flow->intent(),'booking_hash'=>$booking->hash],$user);
flow_check(!$pending['status'] && !empty($pending['checking_payment']) && $ci->booking_payment_gateway->starts===1,'Uncertain payment blocks another checkout');
$ci->booking_payment_gateway->outcome='unpaid';$resume=$flow->resume(['booking_checkout_token'=>$flow->intent(),'booking_hash'=>$booking->hash],$user);
flow_check($resume['status'] && strpos($resume['redirect'],'buy-bag-space/trip7?resume=')!==false,'Confirmed interruption returns the existing booking page');
$restored=$flow->restore($booking,$user->id);flow_check($restored['status'] && $restored['form']['agent_address']==='Sender address' && $restored['form']['receiver_status']==='someone_else' && $restored['form']['_booking_step']==='5','All editable state remains available for the original wizard');
$post['resume_booking_hash']=$booking->hash;$post['booking_request_id']=$flow->new_request(7);$post['agent_address']='Updated sender address';
$retry=$flow->submit($post,$user);flow_check($retry['status'],'Resumed form can be edited and paid');
$booking=$ci->db->get('bookings')->row();flow_check($ci->db->count_all('bookings')===1 && $ci->db->count_all('booking_payment_attempts')===2 && $booking->id===$original_id && $booking->tracking_id===$tracking && $booking->agent_address==='Updated sender address','Retry preserves booking/tracking while recording a separate attempt and edits');
$ci->booking_payment_gateway->outcome='paid';$attempt=$ci->booking_checkout_model->latest_attempt($booking->id);
$paid=$ci->booking_payment_reconciler->reconcile($attempt);flow_check(!empty($paid['paid']) && $GLOBALS['flow_mail']===2,'Verified winner confirms and sends notifications once');
$ci->booking_payment_reconciler->reconcile($attempt);flow_check($GLOBALS['flow_mail']===2,'Repeated callback/webhook has no duplicate notifications');
[$ci,$flow,$post,$user]=flow_fixture();flow_check($flow->submit($post,$user)['status'],'Create late-payment fixture');
$old=$ci->booking_checkout_model->latest_attempt(1);$ci->booking_checkout_model->end_attempt($old->id,'canceled');
flow_check($flow->submit($post,$user)['status'],'Create newer checkout after verified cancellation');
$booking=$ci->db->get('bookings')->row();$ci->booking_payment_gateway->outcomes[$old->id]='paid';
$review=$ci->booking_payment_reconciler->reconcile($old);
flow_check(!empty($review['review']) && $GLOBALS['flow_mail']===1,'Late charge creates one admin review notification');
flow_check(!$flow->submit($post,$user)['status'] && $ci->booking_payment_gateway->starts===2,'Review prevents reusing or opening another gateway URL');
$blocked=$flow->resume(['booking_checkout_token'=>$flow->intent(),'booking_hash'=>$booking->hash,'mode'=>'resume'],$user);
flow_check(!empty($blocked['review_required']) && empty($blocked['find_url']) && !$blocked['status'],'Review prevents both resume and fresh-purchase shortcuts on the server');
$ci->booking_payment_reconciler->reconcile($old);flow_check($GLOBALS['flow_mail']===1,'Repeated review delivery does not resend admin alerts');
flow_check(!$ci->users_model->mark_paystack_initialized(1,'legacy-replacement',1)
    && !$ci->users_model->cancel_booking_payment_by_hash($booking->hash),'Legacy mutators cannot bypass managed checkout state');
[$ci,$flow,$post,$user]=flow_fixture();flow_check($flow->submit($post,$user)['status'],'Create expired recovery fixture');
$booking=$ci->db->get('bookings')->row();$attempt=$ci->booking_checkout_model->latest_attempt($booking->id);
$ci->booking_checkout_model->end_attempt($attempt->id,'canceled');
$ci->db->where('booking_id',$booking->id)->update('booking_checkouts',['resume_until'=>'2001-01-01 00:00:00']);
$expired=$flow->resume(['booking_checkout_token'=>$flow->intent(),'booking_hash'=>$booking->hash,'mode'=>'resume'],$user);
flow_check(!$expired['status'] && !empty($expired['recovery_expired']) && !$expired['resume_available']
    && empty($expired['find_url']) && strpos($expired['msg'],'canceled')!==false,'Expired recovery cannot return a new search action or resume the booking');
[$ci,$flow,$post,$user]=flow_fixture();$ci->booking_payment_gateway->initialize_ok=false;
flow_check(!$flow->submit($post,$user)['status'],'Lost gateway acknowledgement reports failure');
flow_check(!$flow->submit($post,$user)['status'] && $ci->booking_payment_gateway->starts===1 && $ci->db->count_all('bookings')===1,'Lost acknowledgement never blindly starts a second payment');
[$ci,$flow,$post,$user]=flow_fixture();$ci->booking_payment_gateway->initialize_ok=false;$ci->booking_payment_gateway->initialize_outcome='not_created';
flow_check(!$flow->submit($post,$user)['status'],'Definitive initialization rejection is reported');
flow_check($ci->booking_checkout_model->latest_attempt(1)->status==='failed' && (float)$ci->db->get('travellers')->row()->available_space===10.0,'Definitive no-request failure releases capacity immediately');
$ci->booking_payment_gateway->initialize_ok=true;flow_check($flow->submit($post,$user)['status'] && $ci->db->count_all('bookings')===1,'Known no-request failure can resume the same booking safely');

class FlowWorkerFixture extends Booking_checkout_worker {public $test_lock;protected function lock_path(){return $this->test_lock;}}
[$ci,$flow,$post,$user]=flow_fixture();flow_check($flow->submit($post,$user)['status'],'Create worker fixture payment');
$attempt=$ci->booking_checkout_model->latest_attempt(1);
$ci->db->where('id',$attempt->id)->update('booking_payment_attempts',['updated_at'=>gmdate('Y-m-d H:i:s',time()-60)]);
$worker=new FlowWorkerFixture();$worker->test_lock=tempnam(sys_get_temp_dir(),'smb-checkout-worker-');
$ci->booking_payment_gateway->outcome='paid';$worked=$worker->run(1);
flow_check($worked['confirmed']===1 && $ci->booking_payment_gateway->closes[$attempt->id]===false,'Worker confirms paid attempts before lease expiry when browser callback is lost');
unlink($worker->test_lock);

[$ci,$flow,$post,$user]=flow_fixture();flow_check($flow->submit($post,$user)['status'],'Create fair worker fixture');
$base=(array)$ci->db->get('bookings')->row();
for($i=2;$i<=3;$i++){
    $row=$base;unset($row['id']);$row['hash']='worker-booking-'.$i;$row['tracking_id']='worker-'.$i;$row['selected_space']=1;
    $ci->db->insert('bookings',$row);$booking=$ci->db->where('id',$ci->db->insert_id())->get('bookings')->row();
    $ci->booking_checkout_model->capture($booking,['items'=>'[]']);
    flow_check($ci->booking_checkout_model->create_attempt($booking,1,'paystack',10000,'NGN',1,[])['status'],'Create additional independently claimed attempt');
}
$ci->db->update('booking_payment_attempts',['updated_at'=>gmdate('Y-m-d H:i:s',time()-120)]);
$ci->booking_payment_gateway->outcome='pending';$ci->booking_payment_gateway->outcomes[3]='paid';
$worker=new FlowWorkerFixture();$worker->test_lock=tempnam(sys_get_temp_dir(),'smb-checkout-worker-');
$worker->run(1);$worker->run(1);$worked=$worker->run(1);
flow_check($worked['confirmed']===1 && $ci->booking_checkout_model->attempt_by_id(3)->status==='paid','Old unknown attempts cannot starve later payments in bounded cron batches');
unlink($worker->test_lock);
echo "PASS: real checkout writer/flow, full restoration, owner/intent/nonce checks, quote reconfirmation, initial and resumed idempotency, immutable attempts, verified notifications and uncertain gateway protection.\n";
