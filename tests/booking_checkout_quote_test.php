<?php
define('BASEPATH',dirname(__DIR__).'/system/');
require dirname(__DIR__).'/application/helpers/app_helper.php';
require dirname(__DIR__).'/application/helpers/booking_checkout_helper.php';
function quote_check($value,$message){if(!$value){throw new RuntimeException($message);}}
$traveller=(object)['id'=>7,'location'=>'Nigeria','destination'=>'United Kingdom'];
$form=['payment_method'=>'paystack','insurance'=>'3.99','items'=>json_encode([
    ['category'=>'Normal','item_name'=>'Clothes','size'=>2,'unit_price'=>0.01,'price'=>0.02],
    ['category'=>'Medication','item_name'=>'Medicine','size'=>1,'price'=>0],
    ['category'=>'Fish/Meat','item_name'=>'Fish','size'=>1,'price'=>0],
    ['category'=>'Laptop','item_name'=>'Laptop','size'=>1,'price'=>0],
])];
$quote=booking_checkout_quote($traveller,$form,2000);
quote_check($quote['selected_space']===5.0 && $quote['selected_price']===59.0,'All parcel units preserve current capacity/pricing semantics');
quote_check($quote['service_charge']===3.49 && $quote['special_charge']===10 && $quote['insurance']===3.99,'Trusted fees and one special fee');
quote_check($quote['vat']===0.0 && $quote['total_amount']===76.48 && $quote['charge_minor']===15296000,'Exact displayed total and NGN minor-unit charge; suspended VAT');
quote_check($quote['items'][3]['unit']==='PC' && $quote['items'][0]['unit']==='KG','Units are derived, never posted');
$form['payment_method']='stripe';$stripe=booking_checkout_quote($traveller,$form,0);
quote_check($stripe['charge_currency']==='GBP' && $stripe['charge_minor']===7648,'Stripe exact quote without hidden automatic tax or FX');
$token=booking_checkout_quote_token(7,$form,$stripe,'session-intent');
$changed=$form;$changed['insurance']='13.99';
quote_check(!hash_equals($token,booking_checkout_quote_token(7,$changed,booking_checkout_quote($traveller,$changed,1),'session-intent')),'Consent token binds parcel and fee changes');
quote_check(!hash_equals($token,booking_checkout_quote_token(7,$form,$stripe,'other-session')),'Consent token is session bound');
foreach([
    ['items'=>'[]'],['items'=>'not json'],['insurance'=>'0.01'],['payment_method'=>'offline'],
    ['items'=>json_encode([['category'=>'Unknown','item_name'=>'Parcel','size'=>1]])],
    ['items'=>json_encode([['category'=>'Duty Free','item_name'=>'Parcel','size'=>1]])],
    ['items'=>json_encode([['category'=>'Normal','item_name'=>'Parcel','size'=>-1]])],
    ['items'=>json_encode([['category'=>'Normal','item_name'=>'Parcel','size'=>1000000]])],
] as $bad){try{booking_checkout_quote($traveller,array_merge($form,$bad),1);throw new RuntimeException('Invalid quote accepted');}catch(InvalidArgumentException $expected){}}
$safe=booking_checkout_form($form+['payment_status'=>'completed','user_id'=>999,'SMTP_PASS'=>'secret','resume_booking_hash'=>'forged']);
quote_check(!isset($safe['payment_status'],$safe['user_id'],$safe['SMTP_PASS'],$safe['resume_booking_hash']),'Restoration never captures credentials or server state');
echo "PASS: trusted parcel/fee/FX quotes, exact gateway amounts, quote consent, route category validation and editable-only snapshots.\n";
