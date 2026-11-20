<?php
// Actual recycle insert and shipping context, in SQLite memory. Notifications are intercepted.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function send_email_notification($ci, $email, $subject, $data, $template) {
    if ($email !== 'kodevros@gmail.com') throw new RuntimeException('Unexpected test recipient.');
    $GLOBALS['recycle_test_notifications'][] = array($email, $subject, $template);
    return true;
}
ob_start();
require __DIR__ . '/shipping_workflow_test.php';
ob_end_clean();
require APPPATH . 'models/Travellers_model.php';
$sourceDb = $db;
$db = DB(array('dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => true), true);
support_check($sourceDb->conn_id->backup($db->conn_id), 'Recycle test uses a separate in-memory database.');
$columns = array('fullname','phone','alt_phone','email','current_state','drop_date1','departure_state',
    'arrival_airport','arrival_state','destination_area','drop_address2','drop_date2','travel_date','arrival_date',
    'airline','address','available_space','original_bag_space','unwanted_items','itinerary_photo','itinerary_photo_thumb','status','hash');
$existingFields = $db->list_fields('travellers');
foreach ($columns as $column) {
    if (!in_array($column, $existingFields, true)) $db->query('ALTER TABLE travellers ADD COLUMN ' . $column . ' TEXT');
}
$originalTraveller = $db->where('id', 1)->get('travellers')->row_array();
$originalShipment = $db->where('booking_id', 1)->get('shipping_records')->row_array();
$_POST = array_fill_keys($columns, '');
$_POST = array_merge($_POST, array('fullname'=>'Recycled Traveller','email'=>'kodevros@gmail.com',
    'c_code1'=>'+44','phone'=>'7424591497','c_code2'=>'','location'=>'United Kingdom','destination'=>'Nigeria',
    'travel_date'=>'2026-11-18','arrival_date'=>'2026-11-19','available_space'=>'10','airline'=>'Fixture Airline',
    'additional_info'=>"Collection point, Benin\nAsk for the traveler."));
$recycle = (new ReflectionClass('Travellers_model'))->newInstanceWithoutConstructor();
$recycle->db = $db;
$recycle->input = new class { function post($key, $clean = false) { return $_POST[$key] ?? ''; } };
$recycle->traveller_read_model = new class { public $clears=0; function clearTravellerCountCaches() { $this->clears++; } };
support_check($recycle->recycle_traveller(1, 'fixture.pdf', 'fixture-thumb.jpg'), 'The first recycle save succeeds.');
$newTravellerId = (int) $db->insert_id();
$saved = $db->where('id', $newTravellerId)->get('travellers')->row();
support_check($newTravellerId !== 1 && $saved->additional_info === $_POST['additional_info'], 'The first recycle insert must retain the submitted pickup address, including line breaks.');
support_check($db->where('id', 1)->get('travellers')->row_array() === $originalTraveller, 'The original traveler trip is unchanged.');
support_check($db->where('booking_id', 1)->get('shipping_records')->row_array() === $originalShipment, 'Existing shipment address snapshots are unchanged.');
support_check($recycle->traveller_read_model->clears === 1, 'Successful recycling retains count-cache invalidation.');
$booking = $db->where('id', 1)->get('bookings')->row_array();
$booking['id'] = 4;
$booking['traveller_id'] = $newTravellerId;
$booking['tracking_id'] = 'SMBRECYCLED';
$db->insert('bookings', $booking);
$shippingRead = (new ReflectionClass('ShippingTestReadModel'))->newInstanceWithoutConstructor();
$shippingRead->db = $db;
$controller = support_controller('Shipping', 'shipping_context_ajax', 'super_admin');
$controller->shipping_read_model = $shippingRead;
ob_start(); $controller->shipping_context_ajax(4); $result = json_decode(ob_get_clean(), true);
support_check($result['status'] && $result['context']['pickup_address'] === $_POST['additional_info'], 'Book Shipping receives the pickup address immediately after the first save, without a second traveler edit.');
$_POST['additional_info'] = str_repeat('a', 100);
support_check($recycle->recycle_traveller(1, 'fixture.pdf', 'fixture-thumb.jpg'), 'A 100-character address can be recycled.');
support_check(strlen($db->where('id', $db->insert_id())->get('travellers')->row()->additional_info) === 100, 'The maximum supported address length is preserved.');
$_POST['additional_info'] = '';
support_check($recycle->recycle_traveller(1, 'fixture.pdf', 'fixture-thumb.jpg'), 'The existing optional Additional Information behavior is preserved.');
support_check($db->where('id', $db->insert_id())->get('travellers')->row()->additional_info === '', 'Blank optional information is stored explicitly.');
support_check(count($GLOBALS['recycle_test_notifications']) === 3, 'Existing recycle notification calls were intercepted; no email was sent.');

// Capture the real recycle handler's validation rules before upload/insert.
class RecyclePickupValidationController extends Admin_travellers {
    public $redisplayed = false;
    public function recycle_traveller($id, $error = array('error' => '')) { $this->redisplayed = true; }
}
$handler = (new ReflectionClass('RecyclePickupValidationController'))->newInstanceWithoutConstructor();
$handler->db = $db;
$handler->load = new class { function library($name, $config = array()) {} };
$handler->form_validation = new class {
    public $rules = array();
    function set_rules($field, $label, $rules, $messages = array()) { $this->rules[$field] = array($label, $rules); return $this; }
    function run() { return false; }
};
$handler->recycle_traveller_ajax(1);
support_check($handler->redisplayed && isset($handler->form_validation->rules['additional_info']), 'The recycle handler validates Additional Information before uploading or inserting.');
require BASEPATH . 'libraries/Form_validation.php';
$ci = get_instance();
$ci->input = new class { function method() { return 'post'; } };
$ci->lang = new class {
    private $lines = array();
    function load($name) { $lang = array(); include BASEPATH . 'language/english/form_validation_lang.php'; $this->lines = $lang; }
    function line($name, $log = true) { return $this->lines[$name] ?? false; }
};
list($label, $rules) = $handler->form_validation->rules['additional_info'];
foreach (array(array(str_repeat('a', 101), false), array(str_repeat('é', 100), true), array('', true)) as $case) {
    $validator = new CI_Form_validation();
    $validator->set_data(array('additional_info' => $case[0]))->set_rules('additional_info', $label, $rules);
    support_check($validator->run() === $case[1], 'The actual recycle rule enforces 100 characters and preserves optional input.');
    if (!$case[1]) support_check(isset($validator->error_array()['additional_info']), 'An oversized address produces an inline field error.');
}
$view = file_get_contents(APPPATH . 'views/admin/travellers/recycle_traveller.php');
support_check(strpos($view, 'name="additional_info" maxlength="100"') !== false && strpos($view, "form_error('additional_info')") !== false, 'The form retains its inline error slot and matches the server length limit.');
echo "PASS: first-save recycled pickup persistence, Book Shipping context, original-trip/shipment preservation and address boundaries (SQLite memory; emails intercepted).\n";
