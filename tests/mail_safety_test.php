<?php
error_reporting(E_ALL & ~E_DEPRECATED);
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');
class CI_Model {public $db;}
function log_message($level,$message){}
function show_error($message){throw new RuntimeException($message);}
function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
require BASEPATH.'database/DB.php';require APPPATH.'models/Mail_safety_model.php';require APPPATH.'helpers/email_helper.php';
function safety_check($condition,$message){if(!$condition)throw new RuntimeException($message);}
if(getenv('SMB_MAIL_MYSQL')==='1'){
    require $root.'/vendor/autoload.php';Dotenv\Dotenv::createImmutable($root)->safeLoad();
    safety_check(($_ENV['APP_ENV']??'')==='development','MySQL proof is local development only');
}
function safety_fixture($settings=[]){
    $mysql=getenv('SMB_MAIL_MYSQL')==='1';
    $db=DB($mysql?['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock','username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],'database'=>$_ENV['DB_DATABASE_LOCAL'],'db_debug'=>false]:['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false],true);
    if($mysql){
        $sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/database/026_mail_safety.sql'));
        $sql=str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$sql);
        foreach([1,2]as$pass){foreach(explode(';',$sql)as$statement){if(trim($statement)!=='')safety_check($db->query($statement),'Idempotent mail schema');}}
        $db->data_cache['table_names']=['mail_safety_state','mail_send_events','mail_bulk_queue'];
    }else{
        $db->query("CREATE TABLE mail_safety_state(sender_key TEXT PRIMARY KEY,day_bucket TEXT DEFAULT '',day_used INTEGER DEFAULT 0,minute_bucket INTEGER DEFAULT 0,minute_used INTEGER DEFAULT 0,bulk_day_used INTEGER DEFAULT 0,bulk_last_at INTEGER DEFAULT 0)");
        $db->query('CREATE TABLE mail_send_events(id INTEGER PRIMARY KEY AUTOINCREMENT,sender_key TEXT,recipient_key TEXT,template TEXT,status TEXT,created_at TEXT,finished_at TEXT)');
    }
    $model=(new ReflectionClass('Mail_safety_model'))->newInstanceWithoutConstructor();$model->db=$db;
    $model->settings=array_merge(['daily_limit'=>4,'minute_limit'=>3,'recipient_daily_limit'=>2,'bulk_daily_limit'=>2,'bulk_interval_seconds'=>60],$settings);
    return [$model,$db];
}
[$model,$db]=safety_fixture();
$one=$model->reserve('fixture@example.invalid','verification');safety_check($one['status'],'First reservation');
safety_check($model->finish($one['id'],false),'Failed SMTP attempt is recorded');
safety_check($model->reserve('fixture@example.invalid','reset')['status'],'Second request shares recipient budget');
safety_check($model->reserve('fixture@example.invalid','other')['outcome']==='rate_limited','Recipient budget spans templates');
safety_check($model->reserve('other@example.invalid','verification')['status'],'Separate recipient');
safety_check($model->reserve('third@example.invalid','verification')['outcome']==='rate_limited','Mailbox minute cap');
$db->update('mail_safety_state',['minute_bucket'=>0]);
safety_check($model->reserve('third@example.invalid','verification')['status'],'Minute rollover');
$db->update('mail_safety_state',['minute_bucket'=>0]);
safety_check($model->reserve('fourth@example.invalid','verification')['outcome']==='rate_limited','Daily cap survives minute rollover');
$events=$db->get('mail_send_events')->result();
safety_check(count($events)===4 && $events[0]->status==='failed','Blocked attempts do not create a send event; failed SMTP consumes budget');
safety_check(strpos(json_encode($events),'fixture@example.invalid')===false,'Audit hashes recipients and excludes message bodies');
[$model,$db]=safety_fixture(['daily_limit'=>10,'minute_limit'=>10]);
safety_check($model->reserve('bulk1@example.invalid','bulk',true)['status'],'First bulk reservation');
safety_check($model->reserve('bulk2@example.invalid','bulk',true)['outcome']==='rate_limited','Bulk pacing');
safety_check($model->reserve('signup@example.invalid','verification')['status'],'Bulk pacing does not block transactional mail');
$db->update('mail_safety_state',['bulk_last_at'=>0]);$model->reserve('bulk2@example.invalid','bulk',true);
$db->update('mail_safety_state',['bulk_last_at'=>0]);
safety_check($model->reserve('bulk3@example.invalid','bulk',true)['outcome']==='rate_limited','Bulk daily allowance');
safety_check($model->reserve('signup2@example.invalid','verification')['status'],'Reserved transactional allowance remains usable');
class SafetyMail {
    public $sent=0,$result=false,$afterSend;
    function getToAddresses(){return [['transport@example.invalid','Fixture']];}
    function getCcAddresses(){return [];}
    function getBccAddresses(){return [];}
    function send(){$this->sent++;if($this->afterSend){($this->afterSend)();}return $this->result;}
}
[$model,$db]=safety_fixture(['recipient_daily_limit'=>1]);
$ci=(object)['mail_safety_model'=>$model,'load'=>new class{function model($name){}}];$mail=new SafetyMail();
safety_check(!send_prepared_email($ci,$mail,'fixture',$outcome) && $outcome==='failed' && $mail->sent===1,'Failed send has accurate outcome');
safety_check(!send_prepared_email($ci,$mail,'fixture',$outcome) && $outcome==='rate_limited' && $mail->sent===1,'Denied reservation never calls SMTP');
[$auditModel,$auditDb]=safety_fixture();
$ci->mail_safety_model=$auditModel;
$auditMail=new SafetyMail();$auditMail->result=true;
$auditMail->afterSend=function()use($auditDb){$auditDb->query('DROP TABLE mail_send_events');};
safety_check(@send_prepared_email($ci,$auditMail,'fixture',$outcome) && $outcome==='sent' && $auditMail->sent===1,
    'Audit storage failure after SMTP must preserve the successful transport result');
$db->query('DROP TABLE mail_safety_state');unset($db->data_cache['table_names']);
safety_check(!$model->reserve('fixture@example.invalid','fixture')['status'],'Missing migration fails closed');
echo "PASS: mailbox/recipient budgets, bulk pacing, transactional reserve, SMTP failures, hashed audit, no-send denial and idempotent schema (".(getenv('SMB_MAIL_MYSQL')==='1'?'temporary MySQL':'SQLite').").\n";
