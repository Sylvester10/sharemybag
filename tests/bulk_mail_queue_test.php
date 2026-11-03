<?php
// Real CodeIgniter query builder, disposable SQLite or connection-local MySQL temporary tables; no SMTP.
error_reporting(E_ALL & ~E_DEPRECATED);
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');
function log_message($level,$message){}
function show_error($message){throw new RuntimeException($message);}
function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
function bulk_check($ok,$message){if(!$ok){throw new RuntimeException($message);}}
class CI_Model{public $db;}
class CI_Controller{public $input;public function __construct(){$this->input=$GLOBALS['worker_input'];}}
class MY_Controller {public $input;public $session;public $agent;}
function show_404(){throw new RuntimeException('fixture 404');}
function redirect($url){$GLOBALS['bulk_redirects'][]=$url;}
require BASEPATH.'database/DB.php';require APPPATH.'models/Bulk_mail_model.php';require APPPATH.'controllers/Mail_worker.php';require APPPATH.'controllers/Message.php';
function send_email_notification($ci,$email,$subject,$data,$template,&$outcome=null,$bulk=false){
    bulk_check($bulk===true,'Worker must reserve bulk sending capacity');
    $GLOBALS['bulk_calls'][]=compact('email','subject','data','template');
    $outcome=$GLOBALS['bulk_outcome'];
    if($outcome==='throw'){throw new RuntimeException('fixture transport interruption');}
    return $outcome==='sent';
}
class BulkFixtureModel extends Bulk_mail_model {
    public $lock;
    protected function worker_lock_path(){return $this->lock;}
}
if(getenv('SMB_BULK_MYSQL')==='1'){
    require $root.'/vendor/autoload.php';Dotenv\Dotenv::createImmutable($root)->safeLoad();
    bulk_check(($_ENV['APP_ENV']??'')==='development','MySQL proof is local development only');
    foreach(['DB_USERNAME_LOCAL','DB_PASSWORD_LOCAL','DB_DATABASE_LOCAL'] as $key){
        bulk_check(isset($_ENV[$key]),'Missing local MySQL fixture configuration');
    }
}
function bulk_fixture(){
    $mysql=getenv('SMB_BULK_MYSQL')==='1';
    $db=DB($mysql?['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],
        'database'=>$_ENV['DB_DATABASE_LOCAL'],'db_debug'=>false,'pconnect'=>false,'dbprefix'=>'']
        :['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false,'dbprefix'=>''],true);
    bulk_check((bool)$db->conn_id,'Disposable fixture database must connect');
    if($mysql){
        // Shadow any persistent tables only within this private connection. Never migrate live tables.
        $sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/database/026_mail_safety.sql'));
        $sql=preg_replace('/\bCREATE\s+TABLE\b/i','CREATE TEMPORARY TABLE',$sql);
        foreach([1,2] as $pass){foreach(explode(';',$sql) as $statement){
            if(trim($statement)===''){continue;}
            bulk_check(stripos(ltrim($statement),'CREATE TEMPORARY TABLE')===0,'Fixture accepts only temporary CREATE statements');
            bulk_check($db->query($statement),'Actual mail schema must apply idempotently to temporary tables');
        }}
        bulk_check($db->query('CREATE TEMPORARY TABLE users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(254), password VARCHAR(255), account_status INT, deleted_at DATETIME NULL, email_verified_at DATETIME NULL, verification_code VARCHAR(20) NULL) ENGINE=InnoDB'),'Private fixture users table');
        // SHOW TABLES excludes temporary tables, so prime CI metadata only after every CREATE succeeds.
        $db->data_cache['table_names']=['users','mail_bulk_queue','mail_safety_state','mail_send_events'];
    }else{
        $db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, password TEXT, account_status INTEGER, deleted_at TEXT, email_verified_at TEXT, verification_code TEXT)');
        $db->query("CREATE TABLE mail_bulk_queue (id INTEGER PRIMARY KEY AUTOINCREMENT,recipient TEXT,subject TEXT,body TEXT,template TEXT,actor_id INTEGER,dedupe_key TEXT UNIQUE,status TEXT DEFAULT 'pending',claimed_at TEXT,sent_at TEXT,created_at TEXT,last_error TEXT)");
    }
    $model=new BulkFixtureModel();$model->db=$db;$model->lock=tempnam(sys_get_temp_dir(),'smb-bulk-mail-test-');
    $GLOBALS['bulk_calls']=[];$GLOBALS['bulk_outcome']='sent';return [$model,$db];
}
class BulkWriteFailureDatabase {
    private $db;
    public $db_debug=false,$inserts=0;
    function __construct($db){$this->db=$db;}
    function __call($method,$arguments){
        if($method==='insert' && ++$this->inserts===2){throw new RuntimeException('Fixture queue write failure');}
        return $this->db->$method(...$arguments);
    }
}
function bulk_user($email,$overrides=[]){return (object)array_merge(['email'=>$email,'password'=>'fixture-hash','account_status'=>1,'deleted_at'=>null,'email_verified_at'=>'2026-01-01','verification_code'=>null],$overrides);}
function bulk_save_users($db,$users){foreach($users as $user){$db->insert('users',(array)$user);}}

[$partialModel,$partialDb]=bulk_fixture();
$partialUsers=[bulk_user('partial1@example.invalid'),bulk_user('partial2@example.invalid'),bulk_user('partial3@example.invalid')];
$partialModel->db=new BulkWriteFailureDatabase($partialDb);
$partial=$partialModel->enqueue_users($partialUsers,'Fixture notice','Fixture message',7);
bulk_check(!$partial['status'] && $partial['queued']===1 && $partial['errors']===2 && $partial['skipped']===0,
    'A partial queue failure reports every remaining unqueued recipient');
bulk_check($partialDb->count_all_results('mail_bulk_queue')===1,'Successful earlier queue rows survive a later write failure');
unlink($partialModel->lock);

// Real worker constructor denies HTTP before loading a DB or a queue model.
$GLOBALS['worker_input']=new class {public function is_cli_request(){return false;}};
try {new Mail_worker();bulk_check(false,'HTTP worker entry must be denied');}
catch(RuntimeException $error){bulk_check($error->getMessage()==='fixture 404','HTTP request denied before delivery');}
$controller=(new ReflectionClass('Message'))->newInstanceWithoutConstructor();
$controller->session=new class {
    public $token='fixture-session-token'; public $flashes=[];
    public function userdata($key){return $this->token;}
    public function set_flashdata($key,$value){$this->flashes[$key]=$value;}
};
$controller->agent=new class {public function referrer(){return '/admin';}};
$controller->input=new class {
    public $verb='post';public $token='';
    public function method(){return $this->verb;}
    public function post($key,$clean=false){return $this->token;}
};
foreach(['send_email','send_bulk_mail'] as $action){
    $controller->$action();bulk_check(isset($controller->session->flashes['status_msg_error']),'Missing intent must deny before validation/models');
}
$intent=new ReflectionMethod('Message','valid_mail_intent');$intent->setAccessible(true);
$controller->input->token='forged';bulk_check(!$intent->invoke($controller),'Forged intent denied');
$controller->input->token='fixture-session-token';bulk_check($intent->invoke($controller),'Session-owned form intent accepted');
$controller->session->token='';bulk_check(!$intent->invoke($controller),'No session token cannot authenticate a form');

[$model,$db]=bulk_fixture();
$users=[bulk_user('one@example.invalid'),bulk_user('ONE@example.invalid'),bulk_user('legacy@example.invalid',['email_verified_at'=>null]),
    bulk_user('pending@example.invalid',['password'=>null]),bulk_user('blocked@example.invalid',['account_status'=>0]),bulk_user('deleted@example.invalid',['deleted_at'=>'2026-01-01']),
    bulk_user('incomplete@example.invalid',['email_verified_at'=>null,'verification_code'=>'482915']),bulk_user('invalid-address')];
bulk_save_users($db,$users);
date_default_timezone_set('Pacific/Kiritimati');
$queued=$model->enqueue_users($users,'Fixture notice','Fixture message',7);
bulk_check($queued['queued']===2 && $queued['skipped']===6 && !$queued['errors'],'Only completed active verified/legacy users queue, with normalized dedupe');
bulk_check(!$GLOBALS['bulk_calls'],'Enqueue never contacts SMTP');
bulk_check(substr($db->get('mail_bulk_queue')->row()->created_at,0,10)===gmdate('Y-m-d'),'Queue timestamps use UTC regardless of application timezone');
$again=$model->enqueue_users($users,'Fixture notice','Fixture message',7);
bulk_check($again['queued']===0 && $again['skipped']===8,'Repeating a same-day campaign does not duplicate queue entries');
$valid_lock=$model->lock;$model->lock=$valid_lock.'/missing.lock';
bulk_check($model->process()['errors']===1 && !$GLOBALS['bulk_calls'],'Unusable lock storage fails closed');
$model->lock=$valid_lock;
$held=fopen($model->lock,'c');flock($held,LOCK_EX|LOCK_NB);
bulk_check($model->process()['busy'] && !$GLOBALS['bulk_calls'],'A simultaneous worker cannot take the active worker lock');
flock($held,LOCK_UN);fclose($held);
$processed=$model->process();
bulk_check($processed['sent']===2 && count($GLOBALS['bulk_calls'])===2,'Worker sends queued messages through shared safety controls');
bulk_check($db->where('status','sent')->count_all_results('mail_bulk_queue')===2,'Successful outcomes persist');
if(getenv('SMB_BULK_MYSQL')==='1'){
    bulk_check((bool)array_filter($db->queries,function($query){return strpos($query,"WHERE status = 'pending' ORDER BY id ASC LIMIT 1 FOR UPDATE")!==false;}),'MySQL worker executes actual transactional FOR UPDATE claim query');
}
bulk_check($model->process()['sent']===0 && count($GLOBALS['bulk_calls'])===2,'Completed queue entries cannot be replayed');
unlink($model->lock);

foreach(['rate_limited','unavailable','failed','throw'] as $outcome){
    [$model,$db]=bulk_fixture();$users=[bulk_user('one@example.invalid')];bulk_save_users($db,$users);
    $model->enqueue_users($users,'Fixture notice','Fixture message',7);$GLOBALS['bulk_outcome']=$outcome;
    $processed=$model->process();$row=$db->get('mail_bulk_queue')->row();
    if(in_array($outcome,['rate_limited','unavailable'],true)){
        bulk_check($processed['deferred']===1 && $row->status==='pending' && $row->claimed_at===null,'Pre-SMTP quota/config denial preserves the queue');
    }elseif($outcome==='failed'){
        bulk_check($processed['failed']===1 && $row->status==='failed','SMTP failure is terminal until manual review');
        $model->process();bulk_check(count($GLOBALS['bulk_calls'])===1,'SMTP failures do not auto-retry');
    }else{
        bulk_check($processed['review']===1 && $row->status==='review','Uncertain exception requires review instead of automatic replay');
    }
    unlink($model->lock);
}
[$model,$db]=bulk_fixture();$users=[bulk_user('one@example.invalid')];bulk_save_users($db,$users);
$model->enqueue_users($users,'Fixture notice','Fixture message',7);
$db->where('id',1)->update('mail_bulk_queue',['status'=>'sending','claimed_at'=>gmdate('Y-m-d H:i:s',time()-901)]);
$processed=$model->process();
bulk_check($processed['review']===1 && !$GLOBALS['bulk_calls'] && $db->get('mail_bulk_queue')->row()->status==='review','A crashed stale claim is never resent');
unlink($model->lock);
[$model,$db]=bulk_fixture();$users=[bulk_user('one@example.invalid')];bulk_save_users($db,$users);
$model->enqueue_users($users,'Fixture notice','Fixture message',7);$db->where('email','one@example.invalid')->update('users',['account_status'=>0]);
bulk_check($model->process()['skipped']===1 && !$GLOBALS['bulk_calls'],'Eligibility is checked again before SMTP');
unlink($model->lock);
[$model,$db]=bulk_fixture();$users=[];
for($i=0;$i<8;$i++){$users[]=bulk_user('fixture'.$i.'@example.invalid');}
bulk_save_users($db,$users);$model->enqueue_users($users,'Fixture notice','Fixture message',7);
bulk_check($model->process(99)['sent']===5 && count($GLOBALS['bulk_calls'])===5,'Worker batch cannot exceed five even if caller requests more');
unlink($model->lock);
[$model,$db]=bulk_fixture();$users=[];
for($i=0;$i<1001;$i++){$users[]=bulk_user('batch'.$i.'@example.invalid');}
$queued=$model->enqueue_users($users,'Bounded batch','Fixture message',7);
bulk_check($queued['queued']===1000 && $queued['skipped']===1,'Enqueue batch cannot exceed 1000 recipients');
if(getenv('SMB_BULK_MYSQL')==='1'){
    // TEMPORARY is explicit: this missing-schema check can never drop a persistent queue.
    bulk_check($db->query('DROP TEMPORARY TABLE mail_bulk_queue'),'Only the private temporary queue is dropped');
    $db->data_cache['table_names']=['users','mail_safety_state','mail_send_events'];
}else{$db->query('DROP TABLE mail_bulk_queue');unset($db->data_cache['table_names']);}
bulk_check(!$model->enqueue_users($users,'Notice','Message',7)['status'],'Missing migration fails enqueue closed');
bulk_check($model->process()['errors']===1,'Missing migration cannot run worker');
unlink($model->lock);
echo "PASS: admin intent and HTTP denial, UTC queue, batch bounds, bulk eligibility, daily dedupe, bounded worker, exclusive claim, quota/config deferral, terminal SMTP failure, stale-claim review and send-time eligibility (".(getenv('SMB_BULK_MYSQL')==='1'?'temporary MySQL + FOR UPDATE':'SQLite').").\n";
