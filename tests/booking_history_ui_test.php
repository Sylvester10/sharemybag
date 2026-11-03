<?php
// Actual view, synthetic owner-scoped rows, no DB/provider/SMTP.
require __DIR__.'/booking_recovery_view_test.php';
function history_route_label($xpath) { return preg_replace('/\s+/u', ' ', trim($xpath->query('//*[@data-history-route]')->item(0)->textContent)); }
$state=['resume_available'=>true,'checking_payment'=>false,'message'=>'','resume_until'=>time()+900];
$pending=recovery_history($state,['tracking_id'=>'SMB7654321','traveller_id'=>81,'traveller_departure_state'=>'London Heathrow Airport','traveller_arrival_state'=>'Lagos International Airport','selected_space'=>3,'agent_name'=>'<img src=x onerror=alert(1)>',
    'traveller_drop_address1'=>'Must not be exposed','items'=>'[{"item_name":"<script>alert(1)</script>","category":"Normal","size":2,"unit_price":6.5,"unit":"KG"},{"item_name":"Laptop","category":"Laptop","size":1,"unit_price":30,"unit":"PC"}]'],[81=>(object)['location'=>'United Kingdom','destination'=>'Nigeria']]);
recovery_view_check($pending->query('//*[@data-history-open]')->length===1,'Each row has one accessible details trigger.');
recovery_view_check($pending->query('//*[@id="history-booking-details"]')->length===1,'All rows share one details modal.');
recovery_view_check($pending->query('//table[@id="default_order"]/thead/tr/th')->length===6,'Desktop keeps the original six columns.');
recovery_view_check($pending->query('//*[@data-history-reference]')->item(0)->getAttribute('data-history-reference')==='SMB7654321','Details retain the existing booking tracking reference.');
recovery_view_check(history_route_label($pending)==='UK → Nigeria','Country names replace airport names in the mobile title.');
recovery_view_check($pending->query('//*[@data-history-route]//i[contains(@class,"cf-gb")][@aria-hidden="true"]')->length===1
    && $pending->query('//*[@data-history-route]//i[contains(@class,"cf-ng")][@aria-hidden="true"]')->length===1,'Each country has its matching decorative flag.');
recovery_view_check($pending->query('//*[@id="history-details-title"][@data-history-target="route"]')->length===1,'The modal heading receives the country route and flags.');
$sections=[];
foreach ($pending->query('//*[contains(@class,"modal-body")]/section/h3') as $heading) { $sections[]=trim($heading->textContent); }
recovery_view_check($sections===['Traveller Details','Agent Details','Item Details','Payment Details'],'The modal sections follow the requested reading order.');
recovery_view_check($pending->query('//*[contains(@class,"modal-body")]/section[last()]//dd[@data-history-target="status" or @data-history-target="date" or @data-history-target="reference"]')->length===3,'Status, date and booking ID share the final section.');
recovery_view_check(trim($pending->query('//*[@data-history-space]')->item(0)->textContent)==='2 kg · 1 pc','Purchased weight is visible without mislabelling pieces as kg.');
recovery_view_check(strpos($pending->query('//td[contains(@class,"history-payment-cell")]')->item(0)->getAttribute('data-search'),'Nigeria')!==false,'Country routes are searchable even while traveller contacts are withheld.');
recovery_view_check($pending->query('//*[@data-history-status]/span[contains(@class,"rounded-pill")]')->length===1,'Payment status uses a pill.');
recovery_view_check(trim($pending->query('//*[@data-history-status]')->item(0)->textContent)==='Not completed','The incomplete checkout status uses the shorter label.');
recovery_view_check($pending->query('//button[@id="history-posting-toggle"][@aria-controls="history-posting-content"][@aria-expanded="false"]')->length===1,'The initially collapsed accordion has an accessible toggle.');
recovery_view_check(trim($pending->query('//button[@id="history-posting-toggle"]/span')->item(0)->textContent)==='There is no refund or transfer of service to another traveler','The policy replaces the Posting instructions heading.');
recovery_view_check(strpos($pending->query('//*[@id="history-posting-content"]')->item(0)->textContent,'If you are posting your items, label your parcel like this: name of traveller + SMB[sender’s name].')!==false,'Original parcel-label wording is retained.');
recovery_view_check($pending->query('//*[@id="history-posting-content"]//ul[@role="list"]/li[contains(@class,"list-item")]')->length===3,'Original note list styling is restored for all three instructions.');
recovery_view_check($pending->query('//*[contains(@class,"modal-fullscreen-md-down")]')->length===0,'Mobile details no longer force a fullscreen dialog.');
recovery_view_check($pending->query('//*[contains(@class,"history-card-footer")]/p[contains(@class,"history-card-date")]/following-sibling::div[@data-history-status]')->length===1,'The footer contains the date followed by the single status.');
recovery_view_check($pending->query('//*[contains(@class,"history-card-content")]/*[last()][@data-booking-recovery]')->length===1,'Resume is the final control below date and status in the card.');
recovery_view_check($pending->query('//*[contains(@class,"history-card-space")]/i[contains(@class,"ti-luggage")]')->length===1,'Purchased bag space has a luggage icon.');
recovery_view_check(strpos($pending->query('//*[@data-history-traveller]')->item(0)->textContent,'Must not be exposed')===false,'Unpaid traveller information remains withheld before modal copying.');
recovery_view_check($pending->query('//*[@data-history-agent]//img')->length===0 && $pending->query('//*[@data-history-items]//script')->length===0,'Customer text stays escaped in both source cells and modal inputs.');
recovery_view_check(strpos($pending->query('//*[@data-history-items]')->item(0)->textContent,'1PC')!==false,'Piece and weight units remain intact.');
recovery_view_check($pending->query('//*[@data-history-invoice]//a')->length===0,'An unpaid booking cannot acquire an invoice action.');
$paid=recovery_history([],['payment_status'=>'completed','tracking_id'=>'SMB7654308','traveller_drop_address1'=>'Existing first drop',
    'traveller_drop_address2'=>'Existing second drop','traveller_drop_date1'=>'2026-11-06']);
recovery_view_check(strpos($paid->query('//*[@data-history-traveller]')->item(0)->textContent,'Existing second drop')!==false,'Details preserve every permitted drop-off, not a shortened summary.');
recovery_view_check($paid->query('//*[@data-history-invoice]//a')->length===1,'Completed booking retains its existing invoice link.');
recovery_view_check($paid->query('//*[@data-booking-resume]')->length===0,'Completed booking has no recovery action.');
recovery_view_check($paid->query('//*[contains(@class,"history-posting-notes")]')->length===1,'Posting instructions appear once.');
recovery_view_check($paid->query('//*[contains(@class,"history-posting-notes")]//i[contains(@class,"ti-info-circle")]')->length===1,'The policy uses a circled information icon.');
$legacy=recovery_history([],['items'=>'[]','selected_space'=>8,'traveller_current_state'=>'Nigeria','traveller_destination'=>'United Kingdom']);
recovery_view_check(trim($legacy->query('//*[@data-history-space]')->item(0)->textContent)==='8 kg','Legacy bookings without item JSON retain the stored purchased space.');
recovery_view_check(history_route_label($legacy)==='Nigeria → UK','Legacy country fields remain usable.');
$unknown=recovery_history([],['traveller_departure_state'=>'London Heathrow Airport','traveller_arrival_state'=>'Lagos International Airport']);
recovery_view_check(history_route_label($unknown)==='Route unavailable','Missing country metadata does not turn airports into countries.');
recovery_view_check($unknown->query('//*[@data-history-route]//i')->length===0,'Unavailable routes do not invent flags.');
foreach ([['Nigeria','Canada','Nigeria → Canada'],['Canada','Nigeria','Canada → Nigeria']] as $route) {
    $row=recovery_history([],['traveller_current_state'=>$route[0],'traveller_destination'=>$route[1]]);
    recovery_view_check(history_route_label($row)===$route[2],'Both Canada route directions are retained.');
    recovery_view_check($row->query('//*[@data-history-route]//i[contains(@class,"cf-ca")]')->length===1,'Canada routes retain the Canadian flag in either direction.');
}
echo "PASS: shared mobile/desktop booking rows, accessible single modal, financial actions, safe permitted details and retained parcel instructions.\n";
