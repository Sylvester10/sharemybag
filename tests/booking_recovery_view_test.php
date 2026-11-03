<?php
// Render the existing History component with synthetic checkout states; no DB or network.
define('BASEPATH', dirname(__DIR__).'/system/');
define('business_phone_number', '+440000000000');
function base_url($path='') { return 'https://example.invalid/'.$path; }
function html_escape($value) { return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8'); }
require dirname(__DIR__).'/application/helpers/app_helper.php';
function recovery_view_check($condition,$message) { if (!$condition) { throw new RuntimeException($message); } }
function recovery_history(array $state, array $overrides = [], array $routes = []) {
    $booking=[(object)['id'=>7,'hash'=>'fixture-booking','currency'=>'GBP','traveller_name'=>'Fixture Traveller',
        'agent_name'=>'Fixture Agent','agent_phone'=>'+447911111111','agent_email'=>'fixture@example.invalid',
        'agent_address'=>'Fixture address','agent_locality'=>'London','agent_postcode'=>'SW1A 1AA',
        'payment_status'=>'pending','delivery_status'=>'Pending','date_added'=>'2026-11-02 12:00:00',
        'items'=>'[{"category":"Normal","item_name":"Clothes","size":2,"unit_price":6.5}]','total_amount'=>16.99]];
    $booking[0]=(object)array_merge((array)$booking[0],$overrides);
    $booking_checkout=[7=>$state];$resume_token='fixture-intent';$booking_routes=$routes;
    ob_start();include dirname(__DIR__).'/application/views/users/history.php';$html=ob_get_clean();
    $doc=new DOMDocument();$prior=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html);libxml_clear_errors();libxml_use_internal_errors($prior);
    return new DOMXPath($doc);
}
$resume=recovery_history(['resume_available'=>true,'checking_payment'=>false,'message'=>'','resume_until'=>time()+900]);
$button=$resume->query('//button[@data-booking-resume]')->item(0);
recovery_view_check($button && $button->getAttribute('data-booking-mode')==='resume','Eligible unpaid booking offers explicit Resume mode.');
recovery_view_check($button->hasAttribute('disabled') && $button->hasAttribute('data-resume-until'),'Resume begins disabled until the safe POST handler binds, with a hidden deadline.');
recovery_view_check(strpos($button->textContent,'Resume booking')!==false,'Existing button has approved copy.');
$pending=recovery_history(['resume_available'=>false,'checking_payment'=>true,'message'=>'Payment is processing.','resume_until'=>time()+10]);
$button=$pending->query('//button[@data-booking-resume]')->item(0);
recovery_view_check($button && $button->getAttribute('data-booking-mode')==='check' && $button->hasAttribute('data-resume-until'),'Status checking follows the same hidden action deadline.');
$review=recovery_history(['resume_available'=>false,'checking_payment'=>false,'review_required'=>true,'message'=>'We are reviewing this payment.']);
recovery_view_check($review->query('//button[@data-booking-resume]')->length===0,'A captured payment needing review must not offer another payment.');
recovery_view_check($review->query('//a[@data-booking-find-space]')->length===0,'Review state has no Find space fallback.');
recovery_view_check($review->query('//span[contains(@class,"badge")]')->item(0)->textContent==='Payment needs review','Review state must not mislabel captured funds as unpaid.');
$expired=recovery_history(['resume_available'=>false,'checking_payment'=>false,'recovery_expired'=>true,'message'=>'','resume_until'=>time()-10]);
recovery_view_check($expired->query('//button[@data-booking-resume]')->length===0,'Expired unpaid bookings do not retain Resume.');
recovery_view_check($expired->query('//a[@data-booking-find-space]')->length===0,'Expired checkout has no replacement action.');
recovery_view_check($expired->query('//span[contains(@class,"badge")]')->item(0)->textContent==='Canceled','Expired unpaid checkout displays Canceled after reload.');
$expiredBadge=$expired->query('//span[contains(@class,"badge")]')->item(0);
recovery_view_check(strpos($expiredBadge->getAttribute('class'),'bg-danger-subtle')!==false
    && strpos($expiredBadge->getAttribute('class'),'text_danger')!==false,'Expired Canceled badge uses the same subtle background and red text as historical cancellations.');
recovery_view_check($expired->query('//*[@data-booking-recovery]')->length===0,'Only the status remains after expiry.');
$uncertain=recovery_history(['resume_available'=>false,'checking_payment'=>true,'recovery_expired'=>true,'message'=>'Payment is processing.','resume_until'=>time()-10]);
recovery_view_check($uncertain->query('//button[@data-booking-resume]')->length===0,'Expired uncertain payment has no action either.');
recovery_view_check($uncertain->query('//span[contains(@class,"badge")]')->item(0)->textContent==='Checking payment','Expiry does not claim an unknown payment was canceled.');
echo "PASS: actual History view differentiates resume, uncertain, captured-review and expired states without prompting duplicate purchases.\n";
