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
echo "PASS: collection address search/context/insert, missing-address inline error, existing address preservation, table ordering and Arrival action colors (in-memory database only).\n";
