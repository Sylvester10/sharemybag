<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start(); require __DIR__ . '/traveller_payout_foundation_test.php'; ob_end_clean();
function manual_payout_fixture() {
    global $mysql;
    [$model,$db] = payout_fixture();
    $create = 'CREATE TABLE ';
    $engine = $mysql ? ' ENGINE=InnoDB' : '';
    $db->query($create . 'admins (id INTEGER PRIMARY KEY, role VARCHAR(30))' . $engine);
    $db->query($create . 'shipping_records (id INTEGER PRIMARY KEY, booking_id INTEGER UNIQUE, status VARCHAR(30))' . $engine);
    $db->insert('admins',['id'=>9,'role'=>'super_admin']);
    $db->insert('admins',['id'=>10,'role'=>'traveller_support']);
    payout_check($model->initialise_opening_balance()['status'], 'Initialize fixture opening.');
    $db->insert('bookings',['id'=>13,'traveller_id'=>3,'tracking_id'=>'FIX13','traveller_commission'=>'4.75','currency'=>'CAD','payment_status'=>'completed','payment_method'=>'offline','date_added'=>'2026-11-13 12:00:00']);
    return [$model,$db];
}
[$model,$db] = manual_payout_fixture();
$request = ['request_key'=>str_repeat('a',32),'confirm_paid'=>'1'];
$context = $model->get_payout_context(3)['context'];
payout_check(!$context['eligible'] && !$context['shipping_arranged'], 'Incomplete shipping blocks payout.');
$request['snapshot_token'] = $context['snapshot_token'];
payout_check(!$model->record_manual_payout(3,9,$request)['status'], 'Direct write cannot bypass shipping.');
$db->insert('shipping_records',['id'=>1,'booking_id'=>5,'status'=>'Awaiting Collection']);
$db->insert('shipping_records',['id'=>2,'booking_id'=>13,'status'=>'Awaiting Collection']);
$context = $model->get_payout_context(3)['context'];
payout_check($context['eligible'] && $context['arrived'] && $context['currencies']['GBP']['amount'] === '30.25' && $context['currencies']['CAD']['amount'] === '4.75', 'Arrived and all shipping arranged, amounts remain separate.');
$request['snapshot_token'] = $context['snapshot_token'];
$missing = $request; $missing['confirm_paid']='';
payout_check(isset($model->record_manual_payout(3,9,$missing)['field_errors']['confirm_paid']), 'Missing confirmation has an inline field error.');
payout_check(!$model->record_manual_payout(3,10,$request)['status'] && !$model->record_manual_payout(3,999,$request)['status'], 'Role and actor are verified from the database.');
$db->where('id',5)->update('bookings',['traveller_commission'=>'30.35']);
payout_check(!$model->record_manual_payout(3,9,$request)['status'] && count($model->get_traveller_history(3)) === 0, 'A changed amount invalidates confirmation without writing.');
$request['snapshot_token'] = $model->get_payout_context(3)['context']['snapshot_token'];
$request['amount'] = '0.01'; // Must be ignored by the server.
$saved = $model->record_manual_payout(3,9,$request);
payout_check($saved['status'] && !$saved['already_recorded'], 'Confirmed payout commits.');
$history = $model->get_traveller_history(3);
payout_check(count($history) === 2 && $history[0]->admin_id == 9 && $history[0]->source === 'manual', 'Currency batches retain real actor and immutable history.');
payout_check($history[0]->paid_at === null && $history[0]->recorded_at === '2026-11-13 12:00:00', 'Record the actual confirmation time without inventing a bank transfer timestamp.');
$amounts = [];
foreach ($history as $payout) $amounts[$payout->currency] = number_format((float) $payout->amount,2,'.','');
payout_check($amounts === ['CAD'=>'4.75','GBP'=>'30.35'], 'Client amount is ignored; exact stored commission is allocated.');
payout_check($model->get_payout_context(3)['context']['paid'], 'Traveller is now paid.');
payout_check($model->get_arrival_payout_statuses([2,3])[3] === 'paid' && $model->get_arrival_payout_statuses([2,3])[2] === 'unpaid', 'Arrivals bulk status reflects unpaid and settled travellers.');
payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '35.61', 'Finance outstanding decreases by the committed GBP payout.');
payout_check($model->record_manual_payout(3,9,$request)['already_recorded'] && count($model->get_traveller_history(3)) === 2, 'Retry does not duplicate payouts.');

$db->insert('bookings',['id'=>14,'traveller_id'=>3,'tracking_id'=>'FIX14','traveller_commission'=>'3.21','currency'=>'GBP','payment_status'=>'completed','payment_method'=>'offline','date_added'=>'2026-11-13 12:00:00']);
payout_check($model->record_manual_payout(3,9,$request)['already_recorded'], 'Replayed old confirmation never pays a new booking.');
payout_check($model->get_payout_context(3)['context']['currencies']['GBP']['amount'] === '3.21', 'New booking remains outstanding.');
$db->insert('shipping_records',['id'=>3,'booking_id'=>14,'status'=>'In Transit']);
$request['request_key'] = str_repeat('b',32);
$request['snapshot_token'] = $model->get_payout_context(3)['context']['snapshot_token'];
payout_check($model->record_manual_payout(3,9,$request)['status'], 'A new explicit payout settles only the new booking.');
payout_check(count($model->get_traveller_history(3)) === 3, 'Additional settlement appends history.');

[$model,$db] = manual_payout_fixture();
$db->insert('shipping_records',['id'=>1,'booking_id'=>4,'status'=>'Completed']);
$upcoming = $model->get_payout_context(2)['context'];
payout_check(!$upcoming['eligible'] && !$upcoming['arrived'], 'Upcoming traveller cannot be marked paid even with shipping.');
$db->where('id',3)->update('travellers',['arrival_date'=>'2026-11-14']);
payout_check(!$model->get_payout_context(3)['context']['arrived'], 'Future arrival date prevents payout.');
$db->where('id',3)->update('travellers',['arrival_date'=>'']);
payout_check(!$model->get_payout_context(3)['context']['arrived'], 'Missing arrival date prevents payout.');
if (!$mysql) {
    [$model,$db] = manual_payout_fixture();
    $db->insert('shipping_records',['id'=>1,'booking_id'=>5,'status'=>'Completed']);
    $db->insert('shipping_records',['id'=>2,'booking_id'=>13,'status'=>'Completed']);
    $request = ['request_key'=>str_repeat('c',32),'confirm_paid'=>'1','snapshot_token'=>$model->get_payout_context(3)['context']['snapshot_token']];
    $db->query("CREATE TRIGGER reject_payout_allocation BEFORE INSERT ON traveller_payout_items WHEN NEW.currency = 'CAD' BEGIN SELECT RAISE(ABORT, 'fixture allocation failure'); END");
    set_error_handler(function($severity,$message) { return strpos($message,'fixture allocation failure') !== false; });
    $result = $model->record_manual_payout(3,9,$request);
    restore_error_handler();
    payout_check(!$result['status'] && count($model->get_traveller_history(3)) === 0, 'Allocation failure rolls back every currency batch and history row.');
}

// Real Shipping controller request guards and response shape with the actual payout model.
define('FCPATH', $root . '/');
function base_url($path='') { return 'http://smb.example.invalid/' . $path; }
function site_url($path='') { return base_url($path); }
function html_escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirect($path) { throw new RuntimeException('Denied: ' . $path,403); }
function &get_instance() { return $GLOBALS['payout_ci']; }
class CI_Controller { public function __construct() { foreach($GLOBALS['payout_dependencies'] as $key=>$value) $this->$key=$value; $GLOBALS['payout_ci']=$this; } }
require APPPATH . 'helpers/app_helper.php';
require APPPATH . 'core/MY_Controller.php';
require APPPATH . 'controllers/Shipping.php';
function payout_controller($model, $role='super_admin', $method='POST') {
    $GLOBALS['payout_dependencies'] = [
        'db'=>$model->db, 'traveller_payout_model'=>$model,
        'load'=>new class {function database(){} function dbutil(){} function library(...$args){} function helper(...$args){} function model(...$args){}},
        'session'=>(object)['admin_loggedin'=>true,'admin_email'=>'fixture@example.invalid'],
        'common_model'=>new class($role) {private $role; function __construct($role){$this->role=$role;} function get_admin_details($email){return (object)['id'=>9,'role'=>$this->role,'can_manage_shipping'=>1];}},
        'input'=>new class($method){private $method;function __construct($method){$this->method=$method;} function method($upper=false){return $upper?$this->method:strtolower($this->method);} function post($key,$clean=false){return $_POST[$key]??null;}},
        'security'=>new class {function get_csrf_hash(){return 'fixture-csrf';}},
    ];
    return new Shipping();
}
[$model,$db] = manual_payout_fixture();
try {payout_controller($model,'traveller_support')->payout_context_ajax(3); throw new RuntimeException('Support payout context allowed.');}
catch(RuntimeException $error){payout_check($error->getCode()===403,'Support cannot see payout details.');}
try {payout_controller($model,'super_admin','GET')->mark_traveller_paid_ajax(3); throw new RuntimeException('GET payout write allowed.');}
catch(RuntimeException $error){payout_check($error->getCode()===405,'Payout writes require POST.');}
$controller = payout_controller($model);
ob_start(); $controller->payout_context_ajax(3); $response = json_decode(ob_get_clean(),true);
payout_check($response['status'] && $response['csrf_hash']==='fixture-csrf' && strlen($response['context']['request_key'])===32, 'Real endpoint provides CSRF and request identity.');
$_POST=['confirm_paid'=>'','snapshot_token'=>$response['context']['snapshot_token'],'request_key'=>$response['context']['request_key']];
ob_start(); $controller->mark_traveller_paid_ajax(3); $response=json_decode(ob_get_clean(),true);
payout_check(!$response['status'] && isset($response['field_errors']['confirm_paid']), 'Real endpoint returns inline confirmation error.');
require APPPATH . 'models/ajax/travellers/Arrivals_ajax.php';
$arrivalModel = (new ReflectionClass('Arrivals_ajax'))->newInstanceWithoutConstructor();
$trip = (object)['id'=>3,'fullname'=>'Fixture Traveller','booking_count'=>2,'shipping_count'=>2];
$actions = $arrivalModel->action_menu($trip,true,'paid');
payout_check(strpos($actions,'open-traveller-payout') !== false && strpos($actions,'Paid') !== false, 'Arrivals exposes the payout action and paid status to Super Admin.');
$restrictedActions = $arrivalModel->action_menu($trip,false,'paid');
payout_check(strpos($restrictedActions,'open-traveller-payout') === false && strpos($restrictedActions,'Paid') === false, 'Non-Super Admin actions disclose no payout controls or status.');
echo 'PASS: actual payout model/controller: eligibility, immutable GBP/CAD allocations, role/POST guards, stale confirmation, retries, new bookings and rollback (' . ($mysql?'isolated MySQL databases':'SQLite memory') . ").\n";
