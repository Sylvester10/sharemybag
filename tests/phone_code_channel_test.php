<?php
// Use real models and query builder against connection-local temporary tables only.
require __DIR__ . '/user_activity_history_test.php';
$db->insert('users', array('id'=>60, 'firstname'=>'Channel Test', 'email'=>'channel@example.invalid',
    'number'=>'+447911111160', 'verified_phone_e164'=>'+447911111160',
    'phone_verified_at'=>date('Y-m-d H:i:s'), 'phone_signin_enabled'=>1));
$actor = array('type'=>'user', 'id'=>60, 'name'=>'Channel Test');
$db->insert('auth_login_challenges', array('id'=>60,'user_id'=>60,'purpose'=>'login','delivery_channel'=>'sms',
    'destination_hash'=>hash('sha256','+447911111160'),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
$db->insert('auth_login_challenges', array('id'=>61,'user_id'=>60,'purpose'=>'login','delivery_channel'=>'email',
    'destination_hash'=>hash('sha256','channel@example.invalid'),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity($model->updateDetails(60,array('phone_otp_channel'=>'whatsapp'),$actor,'phone_signin_updated'), 'Personal channel saves.');
check_activity($auth->getPhoneOtpChannel($db->where('id',60)->get('users')->row()) === 'whatsapp', 'Login reads personal channel.');
check_activity($auth->getPhoneOtpChannel((object)array()) === 'sms', 'New users default to SMS.');
check_activity($db->where('id',60)->get('auth_login_challenges')->row()->consumed_at !== null, 'Channel switch revokes pending phone code.');
check_activity($db->where('id',61)->get('auth_login_challenges')->row()->consumed_at === null, 'Channel switch preserves email code.');
$entry = $db->where('user_id',60)->order_by('id','DESC')->get('user_activity_logs')->row();
$changes = json_decode($entry->changes,true);
check_activity($changes['phone_otp_channel']['before']==='sms' && $changes['phone_otp_channel']['after']==='whatsapp', 'Channel history contains before/after.');
check_activity(!$model->updateDetails(60,array('phone_otp_channel'=>'email'),$actor,'phone_signin_updated'), 'Reject unsupported phone channel.');
check_activity(!$failingModel->updateDetails(60,array('phone_otp_channel'=>'sms'),$actor,'phone_signin_updated'), 'Audit failure prevents preference change.');
check_activity($db->where('id',60)->get('users')->row()->phone_otp_channel==='whatsapp', 'Audit failure rolls preference back.');
$db->insert('users',array('id'=>62,'firstname'=>'New Phone','email'=>'new@example.invalid'));
$db->insert('auth_login_challenges', array('id'=>62,'user_id'=>62,'purpose'=>'profile_phone','delivery_channel'=>'whatsapp',
    'destination_hash'=>hash('sha256','+447911111162'),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity($model->updateDetails(62,array('number'=>'+447911111162','verified_phone_e164'=>'+447911111162',
    'phone_verified_at'=>date('Y-m-d H:i:s'),'phone_signin_enabled'=>1,'phone_otp_channel'=>'sms'),
    array('type'=>'user','id'=>62,'name'=>'New Phone'),'phone_verified',62), 'New verification saves.');
check_activity($db->where('id',62)->get('users')->row()->phone_otp_channel==='whatsapp', 'Verification persists challenge channel, not client value.');

// Run actual booking read model with normal owner and soft-delete filters.
function ci_where_not_deleted($db,$table=null) { $db->where(($table ? $table.'.' : '').'deleted_at IS NULL',null,false); }
require $root . '/application/core/MY_Model.php';
require $root . '/application/models/Booking_read_model.php';
$db->query('CREATE TEMPORARY TABLE bookings (id INT PRIMARY KEY, user_id INT, payment_status VARCHAR(30), delivery_status VARCHAR(30), status VARCHAR(30), cancelled_at DATETIME, deleted_at DATETIME, date_added DATETIME) ENGINE=InnoDB');
$GLOBALS['activity_model_dependencies']['load'] = new class { public function database() {} };
$bookings = new Booking_read_model();
$cases = array(
    array(1,60,'completed',null,'Approved',null,null),
    array(2,60,' PAID ','Awaiting Collection','Approved',null,null),
    array(3,60,'completed','Delivered','Approved',null,null),
    array(4,60,'completed','Completed','Approved',null,null),
    array(5,60,'pending','In Transit','Approved',null,null),
    array(6,60,'canceled','In Transit','Approved',null,null),
    array(7,60,'completed','In Transit','Declined',null,null),
    array(8,60,'completed','In Transit','Approved',null,date('Y-m-d H:i:s')),
    array(9,60,'completed','In Transit','Approved',date('Y-m-d H:i:s'),null),
    array(10,61,'completed','In Transit','Approved',null,null),
    array(11,60,'completed','cancelled','Approved',null,null),
    array(12,60,'completed','In Transit','Booking Declined',null,null),
);
foreach ($cases as $case) {
    $db->insert('bookings',array_combine(array('id','user_id','payment_status','delivery_status','status','cancelled_at','deleted_at'),$case));
}
check_activity($bookings->count_active_bookings_by_user_id(60)===2, 'Active count includes only paid, undelivered, uncancelled, owned, visible bookings.');
$activeRows = $bookings->get_bookings_by_user_id(60,true);
check_activity(count($activeRows)===2, 'Dashboard destination matches count.');
check_activity(count($bookings->get_bookings_by_user_id(60))===10, 'Unfiltered history retains previous behaviour.');
echo "PASS: personal channel persistence, validation, audit rollback, challenge binding/revocation and active booking count/list.\n";

// Real public model methods: verification must not enable phone-code sign-in.
$GLOBALS['activity_model_dependencies']['load'] = new class {
    public function database() {} public function model($name) {}
};
$GLOBALS['activity_model_dependencies']['session'] = new class {
    public function userdata($key) { return null; }
};
$model->session = $GLOBALS['activity_model_dependencies']['session'];
$GLOBALS['activity_model_dependencies']['user_activity_model'] = $model;
require $root . '/application/models/Users_model.php';
$users = new Users_model();
$db->insert('users',array('id'=>63,'firstname'=>'Security','lastname'=>'Test','email'=>'security@example.invalid'));
$db->insert('auth_login_challenges',array('id'=>63,'user_id'=>63,'purpose'=>'profile_phone','delivery_channel'=>'sms',
    'destination_hash'=>hash('sha256','+447911111163'),'expires_at'=>date('Y-m-d H:i:s',time()+600)));
check_activity($users->mark_phone_verified(63,'+447911111163',63), 'Phone ownership verifies independently.');
$verified = $db->where('id',63)->get('users')->row();
check_activity(!empty($verified->phone_verified_at) && (int)$verified->phone_signin_enabled===0, 'Verification leaves Email selected.');
check_activity($users->set_phone_signin_enabled(63,true,'whatsapp'), 'Security selects WhatsApp after verification.');
check_activity($users->set_phone_signin_enabled(63,false,'sms'), 'Security selects Email.');
$verified = $db->where('id',63)->get('users')->row();
check_activity((int)$verified->phone_signin_enabled===0 && !empty($verified->phone_verified_at), 'Email selection keeps phone ownership verified.');
check_activity(!$users->set_phone_signin_enabled(60,true,'email'), 'Email cannot become a Twilio channel.');
echo "PASS: Account verification and Security sign-in selection are independent.\n";
