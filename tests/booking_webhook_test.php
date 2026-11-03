<?php
// Real webhook controller and Stripe signature parser; recording reconciliation, no network/SMTP/DB.
error_reporting(E_ALL & ~E_DEPRECATED);
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');define('ENVIRONMENT','development');
require $root.'/vendor/autoload.php';
class CI_Controller {public $input,$output,$booking_checkout_model,$booking_payment_gateway,$booking_payment_reconciler;}
function get_paystack_secret_key(){return 'sk_test_fixture_not_a_real_key';}
require APPPATH.'libraries/Booking_payment_gateway.php';require APPPATH.'controllers/Booking_webhooks.php';
function webhook_check($ok,$message){if(!$ok){throw new RuntimeException($message);}}
function webhook_fixture($provider,$raw,$signature)
{
    $controller=(new ReflectionClass('Booking_webhooks'))->newInstanceWithoutConstructor();
    $controller->input=new class($raw,$signature){public $raw_input_stream,$signature,$verb='post';function __construct($r,$s){$this->raw_input_stream=$r;$this->signature=$s;}function method(){return $this->verb;}function get_request_header($name){return $this->signature;}};
    $controller->output=new class{public $status=200,$body;function set_status_header($s){$this->status=$s;return $this;}function set_content_type($s){return $this;}function set_output($b){$this->body=$b;return $this;}};
    $controller->booking_payment_gateway=new Booking_payment_gateway();
    $controller->booking_checkout_model=new class($provider){public $ready=true,$attaches=0,$attempt;function __construct($p){$this->attempt=(object)['id'=>1,'provider'=>$p,'reference'=>'SMBfixture','provider_session_id'=>null,'booking_id'=>42,'user_id'=>7,'amount_minor'=>10000,'currency'=>$p==='stripe'?'GBP':'NGN'];}function ready(){return $this->ready;}function attempt_by_reference($ref){return $ref==='SMBfixture'?$this->attempt:null;}function attempt_by_id($id){return $this->attempt;}function attach_gateway($id,$session,$url){$this->attaches++;$this->attempt->provider_session_id=$session;return true;}};
    $controller->booking_payment_reconciler=new class{public $calls=0,$ok=true;function reconcile($attempt,$close){$this->calls++;return ['status'=>$this->ok,'paid'=>$this->ok];}};
    return $controller;
}
$_ENV['STRIPE_TEST_WEBHOOK_SECRET']='whsec_fixture_not_a_real_secret';
$stripe=['id'=>'evt_fixture','object'=>'event','type'=>'checkout.session.completed','data'=>['object'=>[
    'id'=>'cs_fixture','object'=>'checkout.session','status'=>'complete','payment_status'=>'paid','amount_total'=>10000,'currency'=>'gbp',
    'metadata'=>['reference'=>'SMBfixture','booking_id'=>'42','user_id'=>'7']]]];
function stripe_signature($raw){$time=time();return 't='.$time.',v1='.hash_hmac('sha256',$time.'.'.$raw,$_ENV['STRIPE_TEST_WEBHOOK_SECRET']);}
$raw=json_encode($stripe);
$controller=webhook_fixture('stripe',$raw,'invalid');$controller->stripe();
webhook_check($controller->output->status===400 && $controller->booking_payment_reconciler->calls===0,'Forged Stripe webhook cannot reconcile');
$controller=webhook_fixture('stripe',$raw,stripe_signature($raw));$controller->stripe();
webhook_check($controller->output->status===200 && $controller->booking_checkout_model->attaches===1 && $controller->booking_payment_reconciler->calls===1,'Signed bound Stripe event recovers lost initialization ID and invokes reconciliation without a customer session');
$wrong=$stripe;$wrong['data']['object']['amount_total']=1;$raw=json_encode($wrong);
$controller=webhook_fixture('stripe',$raw,stripe_signature($raw));$controller->stripe();
webhook_check($controller->output->status===400 && $controller->booking_checkout_model->attaches===0,'Wrong amount cannot bind a lost Stripe session');
$raw=json_encode($stripe);$controller=webhook_fixture('stripe',$raw,stripe_signature($raw));$controller->booking_payment_reconciler->ok=false;$controller->stripe();
webhook_check($controller->output->status===503,'Transient reconciliation failure asks Stripe to retry');
$paystack=json_encode(['event'=>'charge.success','data'=>['reference'=>'SMBfixture']]);
$controller=webhook_fixture('paystack',$paystack,'forged');$controller->paystack();
webhook_check($controller->output->status===400 && $controller->booking_payment_reconciler->calls===0,'Forged Paystack webhook cannot reconcile');
$signature=hash_hmac('sha512',$paystack,get_paystack_secret_key());
$controller=webhook_fixture('paystack',$paystack,$signature);$controller->paystack();
webhook_check($controller->output->status===200 && $controller->booking_payment_reconciler->calls===1,'Valid Paystack HMAC invokes verified reconciliation');
$controller=webhook_fixture('paystack',$paystack.' ',$signature);$controller->paystack();
webhook_check($controller->output->status===400 && $controller->booking_payment_reconciler->calls===0,'Signature binds the exact raw body');
$controller=webhook_fixture('paystack',$paystack,$signature);$controller->booking_checkout_model->ready=false;$controller->paystack();
webhook_check($controller->output->status===503,'Missing schema asks the provider to retry');
$controller=webhook_fixture('paystack',$paystack,$signature);$controller->input->verb='get';$controller->paystack();
webhook_check($controller->output->status===405,'Webhook requires POST');
$controller=webhook_fixture('paystack',str_repeat('x',262145),'forged');$controller->paystack();
webhook_check($controller->output->status===413,'Webhook body is bounded');
echo "PASS: real Stripe signature and Paystack HMAC verification, owner/amount-bound lost acknowledgements, session-independent delivery, replay-safe reconciliation handoff and provider retry responses.\n";
