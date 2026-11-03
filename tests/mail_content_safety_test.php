<?php
require __DIR__.'/support/email_render.php';
$renderer=new EmailPreviewRenderer(APPPATH.'views/mail');
$probe='<a href="https://example.invalid/untrusted">Untrusted name</a>';
$data=email_preview_data();
foreach(['firstname','lastname','fullname','agent_name','id_type','traveller_departure_state','traveller_arrival_airport','traveller_current_state','traveller_arrival_state','traveller_drop_address1','traveller_drop_address2'] as $key){$data[$key]=$probe;}
$data['items']=json_encode([(object)['category'=>$probe,'item_name'=>$probe,'size'=>1,'price'=>10]]);
$templates=['password_recovery_email','admin_booking_notification_email','admin_new_traveller_notification_email','admin_user_message_email','user_document_verification_email','user_document_verification_failed_email','user_document_verification_success_email','traveller_approval_notification_email','user_booking_notification_email'];
foreach($templates as$template){
    $html=$renderer->view($template,$data,true);
    if(strpos($html,$probe)!==false){throw new RuntimeException($template.': untrusted data became an email link');}
    if(strpos($html,'&lt;a href=')===false){throw new RuntimeException($template.': expected escaped data');}
}
echo "PASS: names, ID labels, routes, addresses and parcel details stay escaped in transactional email templates.\n";
