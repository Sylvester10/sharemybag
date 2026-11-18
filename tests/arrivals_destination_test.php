<?php
// Production Arrivals markup/query against isolated in-memory records only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
class CI_Model {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function log_message($level, $message) {}
function html_escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function arrival_destination_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
class ArrivalDestinationViewFixture {
    public $load, $security;
    function __construct() {
        $this->load = $this;
        $this->security = new class { function get_csrf_hash() { return 'fixture-csrf'; } };
    }
    function view($view, $data = array()) {
        extract($data);
        include APPPATH . 'views/' . $view . '.php';
    }
}
ob_start();
(new ArrivalDestinationViewFixture())->view('admin/travellers/arrivals', array('can_record_payout' => false));
$html = ob_get_clean();
if (($argv[1] ?? '') === '--render') { echo $html; exit; }
arrival_destination_check(strpos($html, '>Nigeria Arrivals</button>') !== false && strpos($html, '>UK Arrivals</button>') !== false, 'Both destination controls render.');
arrival_destination_check(strpos($html, '>All Arrivals</button>') !== false && strpos($html, 'arrivals_lifecycle_filter') !== false, 'Other destinations and lifecycle filtering remain accessible.');
arrival_destination_check(substr_count($html, 'id="arrivals_destination_filter"') === 1, 'One canonical destination input avoids conflicting filters.');
require BASEPATH . 'database/DB.php';
require APPPATH . 'helpers/app_helper.php';
require APPPATH . 'models/ajax/travellers/Arrivals_ajax.php';
$db = DB(array('dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => false), true);
$db->query('CREATE TABLE travellers (id INTEGER PRIMARY KEY, fullname TEXT, phone TEXT, alt_phone TEXT, email TEXT, location TEXT, current_state TEXT, destination TEXT, arrival_state TEXT, arrival_airport TEXT, travel_date TEXT, arrival_date TEXT, deleted_at TEXT)');
$db->query('CREATE TABLE bookings (id INTEGER PRIMARY KEY, traveller_id INTEGER, tracking_id TEXT, user_fullname TEXT, payment_status TEXT, deleted_at TEXT)');
$db->query('CREATE TABLE shipping_records (id INTEGER PRIMARY KEY, booking_id INTEGER, status TEXT)');
$past = date('Y-m-d', strtotime('-2 days'));
$future = date('Y-m-d', strtotime('+1 day'));
foreach (array(1 => 'Nigeria', 2 => 'United Kingdom', 3 => 'Nigeria', 4 => 'Nigeria', 5 => 'Canada', 6 => 'United Kingdom', 7 => 'Nigeria') as $id => $destination) {
    $db->insert('travellers', array('id' => $id, 'fullname' => 'Traveller ' . $id, 'destination' => $destination,
        'travel_date' => $id === 3 ? $future : $past, 'arrival_date' => $past, 'deleted_at' => $id === 6 ? $past : null));
}
foreach (array(1 => 1, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6, 8 => 7) as $id => $travellerId) {
    $db->insert('bookings', array('id' => $id, 'traveller_id' => $travellerId, 'tracking_id' => 'BOOK' . $id,
        'payment_status' => $id === 5 ? 'canceled' : 'completed', 'deleted_at' => $id === 8 ? $past : null));
}
$db->insert('shipping_records', array('id' => 1, 'booking_id' => 1, 'status' => 'Completed'));
$db->insert('shipping_records', array('id' => 2, 'booking_id' => 2, 'status' => 'Awaiting Collection'));
$model = new Arrivals_ajax();
$model->db = $db;
$model->input = new class { function post($name, $clean = false) { return $_POST[$name] ?? null; } };
$ids = function ($rows) { $ids = array_map(function ($row) { return (int) $row->id; }, $rows); sort($ids); return $ids; };
$_POST = array('destination' => 'Nigeria', 'length' => 10, 'start' => 0);
$nigeria = $model->get_records();
arrival_destination_check($ids($nigeria) === array(1) && $model->count_all_records() === 1 && $model->count_filtered_records() === 1, 'Nigeria excludes UK, other countries, upcoming, deleted and unbooked travelers.');
arrival_destination_check((int) $nigeria[0]->booking_count === 2 && (int) $nigeria[0]->shipping_count === 2 && $nigeria[0]->arrival_lifecycle === 'Fully Arranged', 'The destination filter preserves grouped shipping and lifecycle totals.');
$_POST['search'] = array('value' => 'BOOK1');
$nigeria = $model->get_records();
arrival_destination_check($ids($nigeria) === array(1) && (int) $nigeria[0]->booking_count === 2 && (int) $nigeria[0]->shipping_count === 2, 'Searching one booking must retain the traveler\'s other bookings in shipping totals.');
unset($_POST['search']);
$_POST['destination'] = 'United Kingdom';
$uk = $model->get_records();
arrival_destination_check($ids($uk) === array(2) && $model->count_all_records() === 1 && $uk[0]->arrival_lifecycle === 'Needs Shipping', 'UK Arrivals includes only the destination United Kingdom.');
$_POST['lifecycle'] = 'Fully Arranged';
arrival_destination_check($model->get_records() === array() && $model->count_filtered_records() === 0, 'Lifecycle filtering is combined with the selected destination.');
$_POST['destination'] = 'Nigeria';
arrival_destination_check($ids($model->get_records()) === array(1), 'Switching destination retains the selected lifecycle filter.');
unset($_POST['lifecycle']);
$_POST['destination'] = '';
arrival_destination_check($ids($model->get_records()) === array(1,2,5) && $model->count_all_records() === 3, 'All Arrivals retains access to Canada and all other existing destinations.');
echo "PASS: Arrivals destination markup, actual Nigeria/UK/All queries, counts, eligibility, grouped search and lifecycle filters (SQLite memory).\n";
