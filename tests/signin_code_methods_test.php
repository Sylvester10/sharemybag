<?php
require __DIR__ . '/phone_code_channel_test.php';
$combinations = array(array('email'), array('whatsapp'), array('sms'),
    array('email', 'whatsapp'), array('email', 'sms'), array('whatsapp', 'sms'));
foreach ($combinations as $methods) {
    check_activity($users->set_signin_code_methods(63, $methods), 'Valid enabled-method combination saves.');
    $row = $db->where('id', 63)->get('users')->row();
    check_activity(signin_code_methods_mask(signin_code_enabled_methods($row)) === signin_code_methods_mask($methods), 'All chosen methods persist.');
    foreach (array('email', 'whatsapp', 'sms') as $method) {
        check_activity(signin_code_method_enabled($row, $method) === in_array($method, $methods, true), 'Login allows exactly the enabled methods.');
    }
    if (array_diff($methods, array('email'))) {
        check_activity(signin_code_method_enabled($row, $auth->getPhoneOtpChannel($row)), 'Phone login automatically chooses an enabled phone channel.');
    }
}
foreach (array(array(), array('email','whatsapp','sms'), array('email','email'), array('email','voice'), 'sms') as $invalid) {
    check_activity(!$users->set_signin_code_methods(63, $invalid), 'Invalid combination is rejected.');
}
$db->insert('users', array('id'=>64,'firstname'=>'Unverified','email'=>'unverified@example.invalid','signin_code_methods'=>1));
check_activity(!$users->set_signin_code_methods(64,array('email','sms')), 'Unverified phone cannot be enabled.');
check_activity($db->where('id',64)->get('users')->row()->signin_code_methods==='1', 'Rejected update preserves Email.');

$users->set_signin_code_methods(63,array('email','sms'));
$phone = '+447911111163';
foreach (array(70=>'email',71=>'sms',72=>'whatsapp') as $id=>$method) {
    $db->insert('auth_login_challenges',array('id'=>$id,'user_id'=>63,'purpose'=>'login','delivery_channel'=>$method,
        'destination_hash'=>hash('sha256',$method==='email'?'security@example.invalid':$phone),
        'expires_at'=>date('Y-m-d H:i:s',time()+600)));
}
check_activity($users->set_signin_code_methods(63,array('sms','whatsapp')), 'Email can be removed while two phone methods remain.');
check_activity($db->where('id',70)->get('auth_login_challenges')->row()->consumed_at!==null, 'Removing Email revokes its outstanding code.');
check_activity($db->where('id',71)->get('auth_login_challenges')->row()->consumed_at===null, 'An enabled SMS challenge is preserved.');
check_activity(!$auth->consumeLoginChallenge(70,63), 'Removed Email method cannot complete sign-in.');
check_activity((bool)$auth->consumeLoginChallenge(71,63), 'Enabled SMS completes sign-in.');
check_activity((bool)$auth->consumeLoginChallenge(72,63), 'Enabled WhatsApp completes sign-in.');
$entry = $db->where('user_id',63)->order_by('id','DESC')->get('user_activity_logs')->row();
$changes = json_decode($entry->changes,true);
check_activity(isset($changes['signin_code_methods']) && $entry->event==='signin_methods_updated', 'Method changes have before/after history.');
check_activity(!$failingModel->updateDetails(63,array('signin_code_methods'=>1),array('type'=>'user','id'=>63,'name'=>'Test'),'signin_methods_updated'), 'Failed audit rolls back settings.');
check_activity((int)$db->where('id',63)->get('users')->row()->signin_code_methods===6, 'Failed save preserves both phone methods.');
check_activity($model->updateDetails(63,array('number'=>null),$admin,'phone_cleared'), 'Admin phone reset succeeds.');
$row = $db->where('id',63)->get('users')->row();
check_activity(signin_code_enabled_methods($row)===array('email') && !$row->phone_signin_enabled, 'Phone reset restores Email and removes phone methods.');
echo "PASS: six supported combinations, one/two limits, phone verification guard, login eligibility, revocation, audit rollback and phone-reset recovery.\n";
