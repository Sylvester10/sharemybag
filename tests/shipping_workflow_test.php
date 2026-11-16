<?php
// Actual model/controller integration, using an isolated in-memory database only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__ . '/customer_support_permissions_test.php';
ob_end_clean();
require APPPATH . 'core/MY_Model.php';
require APPPATH . 'models/Shipping_read_model.php';
require APPPATH . 'models/Shipping_model.php';
require APPPATH . 'models/ajax/shipping/Shipping_records_ajax.php';
function form_open($url) { return '<form>'; }
class ShippingTestReadModel extends Shipping_read_model {
    public function clearShippingCountCaches() {}
}
$db = DB(array('dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => true), true);
$bookingFields = array('tracking_id', 'payment_status', 'delivery_status', 'date_added', 'user_fullname', 'user_email', 'traveller_name', 'traveller_contact', 'traveller_email', 'traveller_current_state', 'traveller_arrival_state', 'traveller_destination', 'agent_name', 'agent_phone', 'agent_email', 'agent_address', 'agent_locality', 'agent_postcode', 'receiver_name', 'receiver_phone', 'receiver_email', 'receiver_address', 'receiver_locality', 'receiver_postcode', 'deleted_at');
$db->query('CREATE TABLE bookings (id INTEGER PRIMARY KEY, user_id INTEGER, traveller_id INTEGER, ' . implode(' TEXT, ', $bookingFields) . ' TEXT)');
$db->query('CREATE TABLE travellers (id INTEGER PRIMARY KEY, additional_info TEXT, drop_address1 TEXT, location TEXT, destination TEXT)');
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, number TEXT, deleted_at TEXT)');
$db->query('CREATE TABLE admins (id INTEGER PRIMARY KEY, name TEXT, email TEXT, role TEXT, can_manage_shipping INTEGER)');
$db->query('CREATE TABLE shipping_records (id INTEGER PRIMARY KEY AUTOINCREMENT, booking_id INTEGER UNIQUE, tracking_id TEXT, carrier_tracking_id TEXT, pickup_address TEXT, dropoff_address TEXT, pickup_country TEXT, courier TEXT, staff_admin_id INTEGER, staff_name TEXT, status TEXT, date_added TEXT DEFAULT CURRENT_TIMESTAMP, date_updated TEXT)');
$db->query('CREATE TABLE shipping (id INTEGER PRIMARY KEY, tracking_id TEXT, heading TEXT, body TEXT, delivery_status TEXT, icon_text TEXT)');
$db->insert('admins', array('id' => 9, 'name' => 'Test Staff', 'email' => 'staff@example.invalid', 'role' => 'super_admin', 'can_manage_shipping' => 1));
$db->insert('users', array('id' => 1, 'number' => '+2340000000000'));
$db->insert('travellers', array('id' => 1, 'additional_info' => 'Collection address, Benin', 'drop_address1' => 'Old drop-off, London', 'location' => 'United Kingdom', 'destination' => 'Nigeria'));
for ($i = 1; $i <= 3; $i++) {
    $row = array_fill_keys($bookingFields, '');
    $row['deleted_at'] = null;
    $db->insert('bookings', array_merge($row, array('id' => $i, 'traveller_id' => 1, 'user_id' => 1, 'tracking_id' => 'SMBTEST' . $i, 'payment_status' => 'completed', 'delivery_status' => 'Pending', 'date_added' => '2026-11-05 10:00:00', 'user_fullname' => 'Test User', 'traveller_name' => 'Test Traveller', 'receiver_address' => 'Receiver address', 'receiver_locality' => 'Benin', 'receiver_postcode' => '1001', 'agent_address' => 'Old agent address, London')));
}
$read = (new ReflectionClass('ShippingTestReadModel'))->newInstanceWithoutConstructor();
$read->db = $db;
$bookingRead = new class($db) {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function get_booking_details_by_id($id) { return $this->db->where('id', $id)->get('bookings')->row(); }
    public function clearBookingCountCaches() {}
};
$model = (new ReflectionClass('Shipping_model'))->newInstanceWithoutConstructor();
$model->db = $db;
$model->shipping_read_model = $read;
$model->booking_read_model = $bookingRead;
$context = $read->get_booking_shipping_context(1);
support_check($context->traveller_pickup_address === 'Collection address, Benin' && $context->traveller_pickup_country === 'Nigeria', 'New context uses Additional Information and destination country.');
$search = $read->search_bookings_for_shipping('SMBTEST1');
support_check($search[0]->traveller_pickup_address === 'Collection address, Benin', 'Search and direct booking use the same collection address.');
$payload = array('carrier_tracking_id' => 'CARRIER1', 'pickup_address' => 'Old drop-off, London', 'dropoff_address' => 'Receiver address, Benin, 1001', 'pickup_country' => 'Nigeria', 'courier' => 'Uber', 'staff_admin_id' => 9, 'status' => 'Awaiting Collection');
$result = $model->create_shipping_record(1, $payload);
support_check($result['status'] && $result['record']->pickup_address === 'Collection address, Benin', 'Actual insert saves the collection address, ignoring a stale origin address.');
support_check(!$model->create_shipping_record(1, $payload)['status'], 'Duplicate shipping remains blocked.');
$db->where('id', 1)->update('travellers', array('additional_info' => 'Updated collection address'));
$controller = support_controller('Shipping', 'shipping_context_ajax', 'super_admin');
$controller->shipping_read_model = $read;
ob_start(); $controller->shipping_context_ajax(1); $response = json_decode(ob_get_clean(), true);
support_check($response['context']['pickup_address'] === 'Collection address, Benin', 'Existing shipping retains its saved address after traveler changes.');
$db->where('id', 1)->update('travellers', array('additional_info' => '  '));
$missing = $model->create_shipping_record(3, $payload);
support_check(!$missing['status'] && !empty($missing['field_errors']['pickup_address']) && !$read->get_shipping_record_by_booking_id(3), 'Missing collection address produces a field error without writing a record.');
$db->where('id', 1)->update('travellers', array('additional_info' => 'Second collection address'));
$payload['carrier_tracking_id'] = 'CARRIER2';
$payload['status'] = 'Completed';
support_check($model->create_shipping_record(2, $payload)['status'], 'Second record is created.');

// Exercise actual table query ordering, JSON cell order, and framework-rendered headings.
$table = (new ReflectionClass('Shipping_records_ajax'))->newInstanceWithoutConstructor();
$table->db = $db;
$_POST = array('length' => 10, 'start' => 0, 'order' => array(array('column' => 1, 'dir' => 'asc')));
$rows = $table->get_records();
support_check($rows[0]->status === 'Awaiting Collection', 'Status column sorts on shipping status.');
$controller = support_controller('Shipping', 'records_ajax', 'super_admin');
$controller->security = new class {
    public function get_csrf_token_name() { return 'q2r_secure'; }
    public function get_csrf_hash() { return 'test-token'; }
};
$controller->current_model = $table;
ob_start(); $controller->records_ajax(); $response = json_decode(ob_get_clean(), true);
$cells = $response['data'][0];
support_check(count($cells) === 11 && strpos($cells[1], 'Awaiting Collection') !== false && $cells[2] === 'Uber' && strpos($cells[3], 'CARRIER1') !== false && strpos($cells[10], 'Test Staff') !== false, 'JSON follows Actions, Status, Courier, Carrier Tracking ID, then details, with Staff last.');
$render('admin/shipping/all_shipping', array('tiles' => array(), 'staff_options' => array(), 'courier_options' => array()));
$labels = array_column($controller->load->views['admin/partials/datatable_shell']['columns'], 'label');
support_check(array_slice($labels, 0, 4) === array('Actions', 'Status', 'Courier', 'Carrier Tracking ID') && end($labels) === 'Staff', 'Shipping headings match JSON order.');

// The Arrival action color changes only when a shipping record exists.
$booking = $bookingRead->get_booking_details_by_id(1);
foreach (array('items' => '[]', 'payment_method' => 'offline', 'currency' => 'gbp', 'total_amount' => 0, 'traveller_commission' => 0) as $key => $value) { $booking->$key = $value; }
foreach (array(null => 'btn-danger', 1 => 'btn-primary') as $shippingId => $buttonClass) {
    $booking->shipping_record_id = $shippingId;
    $html = $render('admin/travellers/traveller_profile', array('y' => $profileTrip, 'booking_details' => array($booking), 'is_super_admin' => false, 'is_arrival_profile' => true, 'staff_options' => array(), 'courier_options' => array(), 'current_admin_id' => 9, 'lock_staff_selection' => false));
    support_check(strpos($html, 'class="btn ' . $buttonClass . ' btn-sm modal-toggle-btn clickable"') !== false, 'Arrival action uses ' . $buttonClass . ' for the shipping state.');
}
// An arrived traveler can correct a missing address without editing dates/capacity or existing shipments.
$shippingTestDb = $db;
$db = DB(array('dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => true), true);
support_check($shippingTestDb->conn_id->backup($db->conn_id), 'Copy fixtures into a separate in-memory address test database.');
foreach (array('travel_date TEXT', 'arrival_date TEXT', 'deleted_at TEXT', 'original_bag_space INTEGER') as $column) {
    $db->query('ALTER TABLE travellers ADD COLUMN ' . $column);
}
$db->where('id', 1)->update('travellers', array('travel_date' => date('Y-m-d', strtotime('-2 days')), 'arrival_date' => date('Y-m-d', strtotime('-1 day')), 'original_bag_space' => 21));
$model->db = $db;
$read->db = $db;
$model->booking_read_model = new class($db) {
    private $db;
    function __construct($db) { $this->db = $db; }
    function get_booking_details_by_id($id) { return $this->db->where('id', $id)->get('bookings')->row(); }
    function clearBookingCountCaches() {}
};
$db->insert('admins', array('id' => 12, 'name' => 'Customer Support Staff', 'email' => 'support@example.invalid', 'role' => 'customer_support', 'can_manage_shipping' => 0));
support_check(in_array(12, array_map(function ($staff) { return (int) $staff->id; }, $read->get_staff_options(9, true)), true), 'Super Admin staff selection includes Customer Support even when an existing account has a zero shipping flag.');
$selfStaff = $read->get_staff_options(12, false);
support_check(count($selfStaff) === 1 && (int) $selfStaff[0]->id === 12, 'Non-super staff selection remains restricted to the signed-in staff account.');
$customerBooking = $db->where('id', 1)->get('bookings')->row_array();
$customerBooking['id'] = 4; $customerBooking['tracking_id'] = 'SMBTEST4';
$db->insert('bookings', $customerBooking);
$customerPayload = $payload;
$customerPayload['carrier_tracking_id'] = 'CUSTOMER-SUPPORT-CARRIER';
$customerPayload['staff_admin_id'] = 12;
$customerPayload['status'] = 'Awaiting Collection';
$customerShipping = $model->create_shipping_record(4, $customerPayload);
support_check($customerShipping['status'] && (int) $customerShipping['record']->staff_admin_id === 12, 'Customer Support can create a persisted shipment with their own staff identity despite a legacy zero permission flag.');
require_once APPPATH . 'models/Admin_model.php';
$adminModel = (new ReflectionClass('Admin_model'))->newInstanceWithoutConstructor();
$adminModel->db = $db;
$adminModel->input = new class { function post($key, $clean = false) { return $_POST[$key] ?? ''; } };
$db->query('ALTER TABLE admins ADD COLUMN phone TEXT');
$_POST = array('name' => 'Customer Support Staff', 'email' => 'support@example.invalid', 'country_code' => '+44', 'phone' => '7424591497', 'role' => 'customer_support', 'can_manage_shipping' => '0');
support_check($adminModel->update_admin(12) && (int) $db->where('id', 12)->get('admins')->row()->can_manage_shipping === 1, 'Saving Customer Support persists its automatic shipping access even when a disabled checkbox submits zero.');
$_POST['role'] = 'traveller_support';
support_check($adminModel->update_admin(12) && (int) $db->where('id', 12)->get('admins')->row()->can_manage_shipping === 0, 'Switching to Traveller Support restores the explicit account-level shipping choice.');
$before = $db->where('id', 1)->get('travellers')->row_array();
$token = hash('sha256', $before['additional_info']);
foreach (array('', '   ', str_repeat('a', 101), array('not a string')) as $invalid) {
    $result = $model->update_traveller_collection_address(1, $invalid, $token, 9);
    support_check(!$result['status'] && isset($result['field_errors']['collection_address']), 'Address validation returns a field error.');
}
$db->insert('admins', array('id' => 10, 'role' => 'customer_support', 'can_manage_shipping' => 1));
support_check(!$model->update_traveller_collection_address(1, 'No access', $token, 10)['status'], 'Customer Support cannot change the address even with a shipping flag.');
support_check(!$model->update_traveller_collection_address(1, 'No access', $token, 999)['status'], 'Unknown actors cannot change addresses.');
support_check(!$model->update_traveller_collection_address(1, 'Stale address', str_repeat('a', 64), 9)['status'], 'Stale changes cannot overwrite another edit.');
$result = $model->update_traveller_collection_address(1, '  New collection address, Benin  ', $token, 9);
support_check($result['status'], 'Past travel dates do not block collection-address corrections.');
$after = $db->where('id', 1)->get('travellers')->row_array();
$expected = $before; $expected['additional_info'] = 'New collection address, Benin';
support_check($after === $expected, 'Only Additional Information changes: original dates, route and capacity stay intact.');
support_check($read->get_shipping_record_by_booking_id(1)->pickup_address === 'Collection address, Benin', 'Previously booked shipments keep their address snapshots.');
$payload['carrier_tracking_id'] = 'CARRIER3';
support_check($model->create_shipping_record(3, $payload)['status'], 'A new shipment can be booked after correcting the address.');
support_check($read->get_shipping_record_by_booking_id(3)->pickup_address === 'New collection address, Benin', 'New shipping uses the corrected address.');
$token = hash('sha256', $after['additional_info']);
$db->insert('admins', array('id' => 11, 'role' => 'traveller_support', 'can_manage_shipping' => 0));
support_check(!$model->update_traveller_collection_address(1, $after['additional_info'], $token, 11)['status'], 'Traveler Support without shipping access cannot edit collection addresses.');
$db->where('id', 11)->update('admins', array('can_manage_shipping' => 1));
support_check($model->update_traveller_collection_address(1, $after['additional_info'], $token, 11)['status'], 'Traveler Support with shipping access can correct an arrival collection address.');
$db->where('traveller_id', 1)->update('bookings', array('payment_status' => 'cancelled'));
support_check(!$model->update_traveller_collection_address(1, 'No active bookings', $token, 9)['status'], 'The address editor requires an active completed booking.');
$db->where('traveller_id', 1)->update('bookings', array('payment_status' => 'completed'));
$db->where('id', 1)->update('travellers', array('travel_date' => date('Y-m-d', strtotime('+1 day'))));
support_check(!$model->update_traveller_collection_address(1, 'Future', $token, 9)['status'], 'Arrival-only editor rejects upcoming travelers.');
$db->where('id', 1)->update('travellers', array('travel_date' => $before['travel_date'], 'deleted_at' => '2026-11-14'));
support_check(!$model->update_traveller_collection_address(1, 'Archived', $token, 9)['status'], 'Archived travelers cannot be changed.');
$db->where('id', 1)->update('travellers', array('deleted_at' => null));

// Actual authenticated controller endpoint, including method guard and JSON/CSRF contract.
$controller = support_controller('Shipping', 'update_collection_address_ajax', 'super_admin');
$controller->shipping_model = $model;
$controller->input = new class {
    public $requestMethod = 'POST';
    function method($upper = false) { return $upper ? $this->requestMethod : strtolower($this->requestMethod); }
    function post($key, $clean = false) { return $_POST[$key] ?? null; }
};
$controller->input->requestMethod = 'GET';
try { $controller->update_collection_address_ajax(1); throw new RuntimeException('GET write allowed.'); }
catch (SupportDenied $error) { support_check($error->getCode() === 405, 'Address writes require POST.'); }
$controller->input->requestMethod = 'POST';
$_POST = array('collection_address' => '', 'address_token' => $token);
ob_start(); $controller->update_collection_address_ajax(1); $response = json_decode(ob_get_clean(), true);
support_check(!$response['status'] && isset($response['field_errors']['collection_address']) && $response['csrf_hash'] === 'test-token', 'Endpoint returns inline error and refreshed CSRF.');
$_POST['collection_address'] = 'Saved through endpoint';
ob_start(); $controller->update_collection_address_ajax(1); $response = json_decode(ob_get_clean(), true);
support_check($response['status'] && $db->where('id', 1)->get('travellers')->row()->additional_info === 'Saved through endpoint', 'Endpoint persists the corrected address.');
$db = $shippingTestDb;
$model->db = $db;
$read->db = $db;
$model->booking_read_model = $bookingRead;
echo "PASS: shipping defaults/snapshots, arrived-address persistence, permissions, validation, stale writes, POST guard, JSON, table ordering and Arrival action colors (in-memory database only).\n";
