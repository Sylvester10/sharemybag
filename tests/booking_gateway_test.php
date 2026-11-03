<?php
define('BASEPATH',dirname(__DIR__).'/system/');
define('ENVIRONMENT','development');
require dirname(__DIR__).'/application/helpers/sk_helper.php';
require dirname(__DIR__).'/application/libraries/Booking_payment_gateway.php';
function gateway_check($ok,$message){if(!$ok){throw new RuntimeException($message);}}
function log_message($level,$message){}
function base_url($path=''){return 'https://smb.example.invalid/'.$path;}
$gateway=new Booking_payment_gateway();
$attempt=(object)['provider'=>'paystack','reference'=>'SMBfixture','provider_session_id'=>null,'booking_id'=>42,'user_id'=>7,'amount_minor'=>120000,'currency'=>'NGN'];
$data=['status'=>'success','reference'=>'SMBfixture','amount'=>120000,'currency'=>'NGN','metadata'=>['booking_id'=>42,'user_id'=>7]];
$verified=$gateway->paystack_result($data);
gateway_check($verified['outcome']==='paid' && $gateway->matches($attempt,$verified),'Successful payment binds reference/amount/currency/account/booking');
foreach(['reference'=>'other','amount_minor'=>120001,'currency'=>'GBP','booking_id'=>43,'user_id'=>8,'provider'=>'stripe'] as$key=>$value){
    gateway_check(!$gateway->matches($attempt,array_merge($verified,[$key=>$value])),'Unrelated provider data cannot confirm this booking');
}
gateway_check(!$gateway->matches($attempt,array_merge($verified,['amount_minor'=>120000.5])),'Fractional minor units cannot pass exact amount binding');
foreach(['pending','ongoing','processing','reversed','unknown']as$status){gateway_check($gateway->paystack_result(array_merge($data,['status'=>$status]))['outcome']==='pending','Unsettled outcomes never imply unpaid');}
foreach(['abandoned','failed']as$status){gateway_check($gateway->paystack_result(array_merge($data,['status'=>$status]))['outcome']==='unpaid','Terminal failed/abandoned can be reconciled but old references stay tracked');}
$attempt->provider='stripe';$attempt->provider_session_id='cs_fixture';$attempt->currency='GBP';
$stripe=['id'=>'cs_fixture','status'=>'complete','payment_status'=>'paid','amount_total'=>120000,'currency'=>'gbp','metadata'=>['reference'=>'SMBfixture','booking_id'=>42,'user_id'=>7]];
gateway_check($gateway->matches($attempt,$gateway->stripe_result($stripe)),'Stripe binding includes the saved session');
gateway_check(!$gateway->matches($attempt,$gateway->stripe_result(array_merge($stripe,['id'=>'cs_other']))),'Forged session cannot confirm');
gateway_check($gateway->stripe_result(array_merge($stripe,['payment_status'=>'unpaid']))['outcome']==='pending','Completed URL/session without paid state is not financial proof');
gateway_check($gateway->stripe_result(array_merge($stripe,['status'=>'expired','payment_status'=>'unpaid']))['outcome']==='unpaid','Expired Stripe session can release its allocation');
class UnavailableFixtureGateway extends Booking_payment_gateway {protected function stripe(){throw new RuntimeException('Fixture offline');}protected function paystack($m,$p,array$b=[]){throw new RuntimeException('Fixture offline');}}
$offline=new UnavailableFixtureGateway();gateway_check($offline->verify($attempt,true)['outcome']==='unknown','Transport failure cannot authorize a second charge');
unset($_ENV['STRIPE_TEST_KEY']);
gateway_check($gateway->initialize($attempt,(object)['user_email'=>'fixture@example.invalid','tracking_id'=>'fixture'])['outcome']==='not_created','Missing local configuration is a known no-request outcome');
$_ENV['PAYSTACK_TEST_KEY']='sk_test_fixture_not_a_real_key';$attempt->provider='paystack';
class RejectedFixtureGateway extends Booking_payment_gateway {protected function paystack($m,$p,array$b=[]){throw new Booking_gateway_rejected('Fixture definitive rejection');}}
gateway_check((new RejectedFixtureGateway())->initialize($attempt,(object)['user_email'=>'fixture@example.invalid','tracking_id'=>'fixture'])['outcome']==='not_created','Definitive provider initialization rejection permits safe recovery');
$_ENV['STRIPE_TEST_KEY']='sk_test_fixture_not_a_real_key';
class RecordingStripeGateway extends Booking_payment_gateway {
    public $parameters;
    protected function stripe(){
        $sessions=new class($this){private $gateway;function __construct($gateway){$this->gateway=$gateway;}
            function create($parameters,$options){$this->gateway->parameters=$parameters;return(object)['id'=>'cs_fixture','url'=>'https://checkout.stripe.com/fixture'];}};
        return(object)['checkout'=>(object)['sessions'=>$sessions]];
    }
}
$attempt->provider='stripe';$attempt->created_at=gmdate('Y-m-d H:i:s',time()-60);$recording=new RecordingStripeGateway();$before=time();
gateway_check($recording->initialize($attempt,(object)['user_email'=>'fixture@example.invalid','tracking_id'=>'fixture'])['status'],'Recorded Stripe initialization succeeds');
gateway_check($recording->parameters['expires_at'] >= $before+1800,'Stripe automatic expiry meets its minimum even after local allocation took time');
echo "PASS: gateway state normalization, exact reference/session/amount/currency/owner binding and unknown transport outcomes; no network calls.\n";
