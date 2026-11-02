<?php
// Exercise actual controllers/helpers with deterministic throttle/mail adapters; no SMTP or real accounts.
error_reporting(E_ALL & ~E_DEPRECATED);
$root=dirname(__DIR__);
define('BASEPATH',$root.'/system/'); define('APPPATH',$root.'/application/');
function mail_check($ok,$message){if(!$ok){throw new RuntimeException($message);}}
$GLOBALS['mail_ip']='192.0.2.1'; $GLOBALS['mail_ci']=(object)[];
function &get_instance(){return $GLOBALS['mail_ci'];}
require APPPATH.'helpers/my_helper.php';
require APPPATH.'helpers/signin_code_helper.php';
require APPPATH.'helpers/registration_helper.php';
function auth_throttle_check($key,$max,$window){
    $entry=$GLOBALS['mail_limits'][$key]??['count'=>0,'start'=>$GLOBALS['mail_clock']];
    if($entry['start']+$window<=$GLOBALS['mail_clock']){$entry=['count'=>0,'start'=>$GLOBALS['mail_clock']];}
    return ['allowed'=>$entry['count']<$max,'retry_after'=>max(1,$entry['start']+$window-$GLOBALS['mail_clock']),'entry'=>$entry];
}
function auth_throttle_hit($key,$max,$window){$entry=auth_throttle_check($key,$max,$window)['entry'];$entry['count']++;$GLOBALS['mail_limits'][$key]=$entry;}
function auth_throttle_message($seconds,$action){return 'Please wait before another '.$action.'.';}
function first_validation_error($fallback){return $fallback;}
function email_call2action_blue($url,$label){return $label;}
function base_url($path=''){return 'https://example.invalid/'.$path;}
function generate_verification_code(){return '482915';}
function send_email_notification($ci,$email,$subject,$data,$template){
    $GLOBALS['mail_calls'][]=[$email,$template];return $GLOBALS['mail_delivery'];
}
class MY_Controller {public $input,$security,$form_validation,$user_read_model,$user_login_model,$auth_challenge_model,$session,$load;public function website_header(...$args){}public function website_footer(){}}
require APPPATH.'controllers/Recover_password.php';
require APPPATH.'controllers/User_login.php';
require APPPATH.'controllers/Home.php';
class MailSafetyInput {
    public $values=['email'=>'fixture@example.invalid','identifier_type'=>'email'];public $verb='post';
    function post($key,$filter=false){return $this->values[$key]??'';}
    function method(){return $this->verb;}
    function ip_address(){return $GLOBALS['mail_ip'];}
}
class MailSafetySecurity{function get_csrf_hash(){return 'fixture';}}
class MailSafetyValidation{public $valid=true;function set_rules(...$args){}function run(){return $this->valid;}function error_array(){return ['fullname'=>'Name required'];}}
class MailSafetyReader{
    public $overrides=[];
    function get_user_details($email){return (object)array_merge(['id'=>$email==='fixture@example.invalid'?42:100+abs(crc32($email)),'email'=>$email,'firstname'=>'Fixture','password'=>'fixture-hash','account_status'=>1,'pass_reset_code'=>'123456','signin_code_methods'=>1],$this->overrides);}
    function get_login_user($email){return $this->get_user_details($email);}
}
class MailSafetySession{public $values=[];function userdata($key){return $this->values[$key]??null;}function set_userdata($key,$value){$this->values[$key]=$value;}}
class MailSafetyLoader{public $views=[];function view($name,$data=[]){$this->views[]=[$name,$data];}}
function mail_fixture($class){
    $GLOBALS['mail_limits']=[];$GLOBALS['mail_calls']=[];$GLOBALS['mail_clock']=1000;$GLOBALS['mail_delivery']=true;$GLOBALS['mail_ip']='192.0.2.1';
    $controller=(new ReflectionClass($class))->newInstanceWithoutConstructor();
    $controller->input=new MailSafetyInput();$controller->security=new MailSafetySecurity();$controller->form_validation=new MailSafetyValidation();
    $controller->user_read_model=new MailSafetyReader();$controller->session=new MailSafetySession();$controller->load=new MailSafetyLoader();
    $controller->user_login_model=new class{function check_user_email_exists($email){return true;}};
    $controller->auth_challenge_model=new class{public $consumed=false;function createChallenge(...$args){return 'fixture-token';}function getActiveChallenge(...$args){return (object)['id'=>7];}function consume($id){$this->consumed=true;}};
    $GLOBALS['mail_ci']=(object)['input'=>$controller->input];return $controller;
}
function mail_response($controller,$method){ob_start();$controller->$method();return json_decode(ob_get_clean(),true,512,JSON_THROW_ON_ERROR);}

$_SERVER['REMOTE_ADDR']='192.0.2.10';$_SERVER['HTTP_CLIENT_IP']='203.0.113.1';$_SERVER['HTTP_X_FORWARDED_FOR']='203.0.113.2';
mail_check(get_user_ip()==='192.0.2.10','Untrusted forwarded headers cannot replace REMOTE_ADDR');
$GLOBALS['mail_ci']->input=new MailSafetyInput();$GLOBALS['mail_ip']='2001:db8::7';
mail_check(get_user_ip()==='2001:db8::7','Configured CI proxy handling is retained');
$GLOBALS['mail_ci']=(object)[];$_SERVER['REMOTE_ADDR']='not-an-address';
mail_check(get_user_ip()==='0.0.0.0','Invalid connection addresses cannot create arbitrary throttle buckets');

foreach(['Recover_password'=>'password_recovery_ajax','User_login'=>'passwordless_request_ajax'] as $class=>$method){
    $controller=mail_fixture($class);
    mail_check(mail_response($controller,$method)['status']===true && count($GLOBALS['mail_calls'])===1,"$class sends the first request");
    $GLOBALS['mail_ip']='192.0.2.2';
    mail_check(mail_response($controller,$method)['status']===false && count($GLOBALS['mail_calls'])===1,"$class cooldown survives an IP change");
    $GLOBALS['mail_clock']+=31;
    mail_check(mail_response($controller,$method)['status']===true && count($GLOBALS['mail_calls'])===2,"$class can send after cooldown");
    $controller=mail_fixture($class);$GLOBALS['mail_delivery']=false;
    mail_check(mail_response($controller,$method)['status']===false && count($GLOBALS['mail_calls'])===1,"$class reports SMTP failure");
    $GLOBALS['mail_ip']='192.0.2.3';
    mail_response($controller,$method);
    mail_check(count($GLOBALS['mail_calls'])===1,"$class failed SMTP still consumes cooldown");
    if($class==='User_login'){mail_check($controller->auth_challenge_model->consumed,'Failed login delivery revokes the unsent challenge');}
    $controller=mail_fixture($class);$controller->input->verb='get';
    mail_check(mail_response($controller,$method)['status']===false && !$GLOBALS['mail_calls'],"$class rejects GET sending");
    $controller=mail_fixture($class);
    for($i=0;$i<16;$i++){$controller->input->values['email']='fixture'.$i.'@example.invalid';$result=mail_response($controller,$method);}
    mail_check(!$result['status'] && strpos($result['msg'],'wait')!==false,'Aggregate IP limit cannot be evaded by changing addresses');
    $controller=mail_fixture($class);$max=$class==='Recover_password'?5:3;
    for($i=0;$i<$max;$i++){
        $GLOBALS['mail_ip']='192.0.2.'.($i+1);$GLOBALS['mail_clock']+=31;
        mail_check(mail_response($controller,$method)['status'],"$class permits a request inside its account budget");
    }
    $GLOBALS['mail_ip']='192.0.2.50';$GLOBALS['mail_clock']+=31;
    mail_check(!mail_response($controller,$method)['status'] && count($GLOBALS['mail_calls'])===$max,"$class account budget cannot be bypassed with different source IPs");
}

foreach([['password'=>''],['account_status'=>0],['deleted_at'=>'2026-01-01']] as $overrides){
    $recovery=mail_fixture('Recover_password');$recovery->user_read_model->overrides=$overrides;
    $result=mail_response($recovery,'password_recovery_ajax');
    mail_check(!$result['status'] && !$GLOBALS['mail_calls'],'Pending, blocked and deleted accounts cannot request reset email');
    if(isset($overrides['password'])){mail_check(strpos($result['msg'],'continue registration')!==false,'Pending account receives registration recovery guidance');}
}

$home=mail_fixture('Home');$home->travellers();
$captcha=$home->session->userdata('traveller_captcha_code');
mail_check($captcha==='482915' && $home->load->views[0][1]['captcha_code']===$captcha,'Traveller page stores the displayed CAPTCHA in server session');
$home->input->values=['captcha_code'=>'111111','c_captcha_code'=>'111111'];
$result=mail_response($home,'add_traveller_ajax');
mail_check(!$result['status'] && isset($result['errors']['c_captcha_code']),'Matching forged POST captcha fields do not bypass server proof');
$home->input->values['c_captcha_code']=$captcha;$home->input->values['location']='Nigeria';$home->input->values['destination']='United Kingdom';$home->form_validation->valid=false;
$result=mail_response($home,'add_traveller_ajax');
mail_check(!$result['status'] && !isset($result['errors']['c_captcha_code']),'Correct session CAPTCHA reaches normal form validation');
$home=mail_fixture('Home');$home->input->verb='get';
mail_check(!mail_response($home,'add_traveller_ajax')['status'],'Traveller submissions require POST');
$home=mail_fixture('Home');
for($i=0;$i<6;$i++){$result=mail_response($home,'add_traveller_ajax');}
mail_check(!$result['status'] && strpos($result['msg'],'wait')!==false,'Traveller endpoint enforces aggregate IP throttling');
echo "PASS: trusted IP handling, reset/sign-in account cooldowns and aggregate limits, SMTP failure reporting, POST guards and server-held traveller CAPTCHA.\n";
