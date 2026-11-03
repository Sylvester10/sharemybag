<?php
// Negative HTTP boundary checks only: no valid signatures, account credentials or provider calls.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$root=dirname(__DIR__);require $root.'/vendor/autoload.php';Dotenv\Dotenv::createImmutable($root)->safeLoad();
if(($_ENV['APP_ENV']??'')!=='development'){throw new RuntimeException('Local runtime proof only.');}
$checks=[
    ['history','GET','history',[302,303,307],true],
    ['resume_login','GET','user_bookings/resume_ajax',[302,303,307],true],
    ['resume_csrf','POST','user_bookings/resume_ajax',[403],false],
    ['worker_http_denied','GET','mail_worker/run',[404],false],
    ['stripe_method','GET','booking_webhooks/stripe',[405],false],
    ['paystack_signature','POST','booking_webhooks/paystack',[400],false],
    ['stripe_signature_or_missing_local_configuration','POST','booking_webhooks/stripe',[400,503],false],
];
$results=[];
foreach($checks as[$name,$method,$path,$expected,$login]){
    $curl=curl_init('http://localhost/smb/'.$path);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>10]);
    if($method==='POST'){curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>'{}',CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);}
    $body=curl_exec($curl);$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);$redirect=curl_getinfo($curl,CURLINFO_REDIRECT_URL);$error=curl_errno($curl);curl_close($curl);
    $pass=!$error && in_array($code,$expected,true) && (!$login || (bool)preg_match('~/signin$~',$redirect));
    $results[]=['check'=>$name,'method'=>$method,'path'=>$path,'status'=>$code,'passed'=>$pass];
    if(!$pass){throw new RuntimeException('HTTP boundary failed: '.$name.' (status '.$code.', transport '.$error.').');}
}
$dir=$root.'/evidence/booking-recovery';if(!is_dir($dir)){mkdir($dir,0775,true);}
file_put_contents($dir.'/http-boundaries.json',json_encode(['generated_at'=>gmdate('c'),'checks'=>$results,
    'authenticated_browser'=>'unverified','provider_calls'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
echo "PASS: local HTTP login/CSRF boundaries, CLI-only worker, webhook method and unsigned-request rejection.\n";
