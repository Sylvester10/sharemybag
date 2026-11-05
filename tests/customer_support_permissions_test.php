<?php
// Real controller guards, presenters and CI query builder; disposable in-memory rows.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
define('VIEWPATH', APPPATH . 'views/');
define('FCPATH', $root . '/');
class SupportDenied extends RuntimeException {}
function show_error($message, $code = 500, $heading = '') { throw new SupportDenied($message, $code); }
function show_404() { throw new SupportDenied('Not found', 404); }
function redirect($url) { throw new SupportDenied('Redirect: ' . $url, 403); }
function base_url($path = '') { return 'http://smb.example.invalid/' . $path; }
function site_url($path = '') { return base_url($path); }
function html_escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function log_message($level, $message) {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function &get_instance() { return $GLOBALS['support_ci']; }
function support_check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function form_close() { return '</form>'; }
function form_open_multipart($url, $attributes = '') { return '<form ' . $attributes . '>'; }
function set_value($key, $default = '') { return $default; }
function form_error($key) { return ''; }
function validation_errors() { return ''; }
class CI_Controller {
    public function __construct() {
        foreach ($GLOBALS['support_dependencies'] as $key => $value) { $this->$key = $value; }
        $GLOBALS['support_ci'] = $this;
    }
}
class CI_Model { public function __construct() {} }
require BASEPATH . 'database/DB.php';
require BASEPATH . 'core/Loader.php';
class SupportViewLoader extends CI_Loader {
    public function __construct() {}
    public function render($view, $data) {
        return $this->_ci_load(array('_ci_path' => APPPATH . 'views/' . $view . '.php', '_ci_vars' => $data, '_ci_return' => true));
    }
}
require APPPATH . 'helpers/app_helper.php';
require APPPATH . 'helpers/registration_helper.php';
require APPPATH . 'libraries/Customer_support_policy.php';
require APPPATH . 'libraries/Booking_presenter.php';
require APPPATH . 'core/MY_Controller.php';
foreach (array('Admin_users', 'Admin_travellers', 'Admin_bookings', 'Admin_exchange', 'Admin_finances', 'Admin_pricing', 'Shipping', 'Secure_file') as $name) {
    require APPPATH . 'controllers/' . $name . '.php';
}
require APPPATH . 'models/ajax/admins/Admin_users_model_ajax.php';
require APPPATH . 'models/ajax/travellers/Upcoming_travellers_ajax.php';
require APPPATH . 'models/ajax/bookings/Bookings_ajax.php';
require APPPATH . 'models/ajax/bookings/Completed_bookings_ajax.php';

$db = DB(array('dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => false), true);
$db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, selfie TEXT, id_card TEXT, utility TEXT, deleted_at TEXT)');
$db->query('CREATE TABLE travellers (id INTEGER PRIMARY KEY, email TEXT, itinerary_photo TEXT, deleted_at TEXT)');
$db->query('CREATE TABLE admins (id INTEGER PRIMARY KEY, role TEXT)');
$db->insert('users', array('id' => 1, 'email' => 'owner@example.invalid', 'selfie' => 'own.jpg', 'id_card' => 'own-id.pdf', 'utility' => 'own-utility.pdf'));
$db->insert('users', array('id' => 2, 'email' => 'other@example.invalid', 'selfie' => 'other.jpg'));
$db->insert('travellers', array('id' => 1, 'email' => 'owner@example.invalid', 'itinerary_photo' => 'own-trip.pdf'));
$db->insert('admins', array('id' => 9, 'role' => 'customer_support'));

function support_controller($class, $method, $role = 'customer_support', $trip = null)
{
    global $db;
    $trip = $trip ?: (object) array('id' => 1, 'status' => 'Approved', 'travel_date' => date('Y-m-d', time() + 86400), 'deleted_at' => null);
    $GLOBALS['support_dependencies'] = array(
        'db' => $db,
        'router' => new class($class, $method) {
            private $class, $method;
            function __construct($class, $method) { $this->class = strtolower($class); $this->method = $method; }
            function fetch_class() { return $this->class; }
            function fetch_method() { return $this->method; }
        },
        'uri' => new class { function rsegment($index) { return 1; } },
        'session' => new class {
            public $admin_loggedin = true, $user_loggedin = false, $admin_email = 'staff@example.invalid', $email = 'owner@example.invalid';
            public $values = array();
            function userdata($key) { return $this->values[$key] ?? null; }
            function set_flashdata($key, $value) { $this->values[$key] = $value; }
            function flashdata($key) { return null; }
        },
        'common_model' => new class($role) {
            private $role;
            function __construct($role) { $this->role = $role; }
            function get_admin_details($email) { return (object) array('id' => 9, 'role' => $this->role, 'can_manage_shipping' => 1); }
        },
        'load' => new class {
            public $views = array();
            function database() {} function dbutil() {} function helper($name) {} function model($name, $alias = null) {}
            function library($name, $config = null, $alias = null) {
                if ($name === 'customer_support_policy') { get_instance()->customer_support_policy = new Customer_support_policy(); }
                if ($name === 'booking_presenter') { get_instance()->booking_presenter = new Booking_presenter(); }
            }
            function view($name, $data = array()) { $this->views[$name] = $data; }
        },
        'traveller_read_model' => new class($trip) {
            private $trip;
            function __construct($trip) { $this->trip = $trip; }
            function get_traveller_details_by_id($id) { return $this->trip; }
        },
        'security' => new class { function get_csrf_hash() { return 'test-token'; } },
        'input' => new class {
            function post($key, $clean = false) { return $_POST[$key] ?? null; }
            function get($key, $clean = false) { return $_GET[$key] ?? null; }
        },
        'output' => new class { function set_header($header) { return $this; } },
    );
    return new $class();
}

$allowed = array(
    'Admin_users' => array('index', 'user_ajax', 'user_profile', 'update_user_ajax', 'activity_history', 'send_registration_email', 'valid_user_phone'),
    'Admin_travellers' => array('index', 'upcoming_travellers_ajax', 'traveller_profile', 'get_user_details', 'add_traveller_bag_space', 'remove_traveller_bag_space', 'add_offline_booking', 'lock_traveller_bag'),
    'Admin_bookings' => array('index', 'all_bookings_ajax', 'completed_bookings', 'completed_bookings_ajax', 'canceled_bookings', 'canceled_bookings_ajax', 'view_booking', 'add_parcel_ajax'),
);
$checked = 0;
foreach ($allowed as $class => $methods) {
    foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (strcasecmp($method->getDeclaringClass()->getName(), $class) !== 0 || $method->name === '__construct') { continue; }
        $permitted = in_array($method->name, $methods, true);
        try { support_controller($class, $method->name); support_check($permitted, $class . ' unexpectedly allows ' . $method->name); }
        catch (SupportDenied $e) { support_check(!$permitted && $e->getCode() === 403, $class . ' unexpectedly denies ' . $method->name); }
        $checked++;
    }
    support_controller($class, 'index', 'super_admin');
}
foreach (array('Admin_exchange', 'Admin_finances', 'Admin_pricing', 'Shipping') as $class) {
    try { support_controller($class, 'index'); throw new RuntimeException($class . ' allowed Customer Support.'); }
    catch (SupportDenied $e) { support_check($e->getCode() === 403, $class . ' must deny.'); }
    support_controller($class, 'index', 'super_admin');
}
support_controller('Admin_travellers', 'index', 'traveller_support');
foreach (array(
    (object) array('status' => 'Approved', 'travel_date' => date('Y-m-d', time() - 86400)),
    (object) array('status' => 'Pending', 'travel_date' => date('Y-m-d', time() + 86400)),
    (object) array('status' => 'Approved', 'travel_date' => date('Y-m-d', time() + 86400), 'deleted_at' => '2026-01-01'),
) as $trip) {
    foreach (array('traveller_profile', 'add_traveller_bag_space', 'remove_traveller_bag_space', 'add_offline_booking', 'lock_traveller_bag') as $method) {
        try { support_controller('Admin_travellers', $method, 'customer_support', $trip); throw new RuntimeException('Non-upcoming traveller allowed.'); }
        catch (SupportDenied $e) { support_check($e->getCode() === 403, 'Traveller object scope must be enforced.'); }
    }
}

// Exercise the actual All Users response and action generation; document fields never leave the server.
$controller = support_controller('Admin_users', 'user_ajax');
$user = (object) array('id' => 1, 'firstname' => 'Ada', 'lastname' => 'Example', 'number' => '+44000000000',
    'email' => 'ada@example.invalid', 'country' => 'United Kingdom', 'address' => 'Example Street', 'state' => 'London',
    'post_code' => 'TEST', 'platform' => 'instagram', 'socials' => '', 'account_status' => 1, 'is_verified' => 1,
    'selfie' => 'secret-selfie.jpg', 'id_card' => 'secret-id.pdf', 'utility' => 'secret-utility.pdf', 'password' => 'stored-hash',
    'last_login' => null, 'date_registered' => '2026-11-01', 'deleted_at' => null);
$controller->current_model = new class($user) extends Admin_users_model_ajax {
    private $user;
    function __construct($user) { $this->user = $user; }
    function get_records() { return array($this->user); }
    function count_all_records() { return 1; }
    function count_filtered_records() { return 1; }
};
$_POST = array('draw' => 1);
ob_start(); $controller->user_ajax(); $json = ob_get_clean();
$rows = json_decode($json, true)['data'];
support_check(count($rows[0]) === 9, 'Customer Support gets no document columns.');
foreach (array('secret-selfie', 'secret-id', 'secret-utility', 'verify_user', 'unverify_user', 'delete_user', 'block_user', 'user_login_admin') as $blocked) {
    support_check(strpos($json, $blocked) === false, 'User response leaks ' . $blocked);
}
$trip = (object) array('id' => 1, 'bag_locked' => 0);
$tripModel = (new ReflectionClass('Upcoming_travellers_ajax'))->newInstanceWithoutConstructor();
$tripActions = $tripModel->actions($trip);
foreach (array('View Traveller', 'Manage Parcels', 'Add Bag Space', 'Remove Bag Space', 'Offline Booking', 'Lock Bag') as $label) {
    support_check(strpos($tripActions, $label) !== false, 'Allowed traveller action missing: ' . $label);
}
foreach (array('las la-plus" style="color: green', 'las la-minus" style="color: red', 'las la-file-invoice" style="color: grey', 'las la-briefcase" style="color: green') as $icon) {
    support_check(strpos($tripActions, $icon) !== false, 'Customer Support retains the existing action icon and color: ' . $icon);
}
foreach (array('update_traveller', 'recycle_traveller', 'approve_traveller', 'delete', 'Unlock Bag') as $blocked) {
    support_check(strpos($tripActions, $blocked) === false, 'Traveller actions leak ' . $blocked);
}
$booking = (object) array('id' => 1, 'payment_status' => 'completed', 'new' => 0, 'shipping_record_id' => null);
foreach (array('Bookings_ajax', 'Completed_bookings_ajax') as $class) {
    $model = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $html = $model->actions($booking, false);
    foreach (array('Mark as Seen', 'open-move-parcel', 'open-cancel-parcel') as $blocked) {
        support_check(strpos($html, $blocked) === false, 'Booking actions leak ' . $blocked);
    }
    support_check(strpos($html, 'View Invoice') !== false, 'Invoices remain accessible.');
}

// Real view metadata and the real profile markup must not restore hidden columns or selfies.
$render = function ($view, $data = array()) {
    // Exercise the framework's actual Loader scope, rather than controller scope.
    return (new SupportViewLoader())->render($view, $data);
};
$controller = support_controller('Admin_users', 'index');
$render('admin/users/users');
$columns = $controller->load->views['admin/partials/datatable_shell']['columns'];
support_check(count($columns) === 9, 'All Users renders exactly the supported columns.');
support_check(!preg_match('/Photo|Id Card|Utility Bill/', json_encode($columns)), 'Private columns cannot be restored through column visibility.');
$profile = $render('admin/users/user_profile', array(
    'y' => $user, 'bookings' => array(), 'total_bookings' => 0, 'is_super_admin' => false, 'last_registration_email' => '',
));
support_check(strpos($profile, '>AE</span>') !== false && strpos($profile, 'assets/selfie/') === false
    && strpos($profile, 'secret-') === false, 'Profile renders initials without a photo URL.');
$controller = support_controller('Admin_travellers', 'index');
$render('admin/travellers/upcoming_travellers');
$columns = $controller->load->views['admin/partials/datatable_shell']['columns'];
support_check(count($columns) === 18 && strpos(json_encode($columns), 'Itinerary') === false && strpos(json_encode($columns), 'Referred By') === false,
    'Upcoming Travellers renders no itinerary column.');

foreach (array('all_bookings' => 'index', 'completed_bookings' => 'completed_bookings', 'canceled_bookings' => 'canceled_bookings') as $view => $method) {
    $controller = support_controller('Admin_bookings', $method);
    $render('admin/bookings/' . $view, array('is_super_admin' => false));
    $columns = $controller->load->views['admin/partials/datatable_shell']['columns'];
    support_check($columns[0]['label'] === '', $view . ' renders without bulk selection for Customer Support.');
}

$controller = support_controller('Admin_travellers', 'traveller_profile');
$profileTrip = (object) array_fill_keys(array('fullname', 'phone', 'email', 'location', 'current_state', 'address', 'drop_address1', 'drop_date2', 'drop_address2', 'destination', 'departure_state', 'airline', 'arrival_date', 'unwanted_items', 'additional_info'), '');
foreach (array('drop_date1', 'travel_date') as $key) { $profileTrip->$key = '2026-11-06'; }
foreach (array('id', 'available_space', 'original_bag_space', 'used_space') as $key) { $profileTrip->$key = 1; }
$tripProfile = $render('admin/travellers/traveller_profile', array('y' => $profileTrip, 'bookings' => array(), 'is_super_admin' => false));
support_check(strpos($tripProfile, 'Edit\n        Traveller') === false && strpos($tripProfile, 'admin_travellers/update_traveller/') === false, 'Traveller profile renders without the edit action for Customer Support.');

// Activity data is redacted before it reaches the template, without losing ordinary edit history.
$controller = support_controller('Admin_users', 'activity_history');
$controller->user_activity_model = new class {
    function history($id, $type, $page) {
        return array('rows' => array((object) array('changes' => json_encode(array(
            'number' => array('before' => 'old-number', 'after' => 'new-number'),
            'selfie' => array('before' => 'secret-old-selfie', 'after' => 'secret-new-selfie'),
            'id_card' => array('after' => 'secret-id'), 'utility' => array('after' => 'secret-utility'),
        )))));
    }
};
$controller->activity_history(1);
$history = json_encode($controller->load->views['admin/users/activity_history']);
support_check(strpos($history, 'secret-') === false && strpos($history, 'old-number') !== false
    && strpos($history, 'new-number') !== false, 'History hides documents and keeps before/after edits.');

// Viewing a booking must not consume the NEW flag for Customer Support.
$db->query('CREATE TABLE bookings (id INTEGER PRIMARY KEY, payment_status TEXT, tracking_id TEXT, new INTEGER)');
$db->insert('bookings', array('id' => 1, 'payment_status' => 'completed', 'tracking_id' => 'SMBTEST1', 'new' => 0));
class SupportBookingView extends Admin_bookings {
    public function admin_header($title, $inner_page_title) {}
    public function admin_footer() {}
}
foreach (array('customer_support' => 0, 'super_admin' => 1) as $role => $expected) {
    // Keep the real route class; the subclass changes layout rendering only.
    $controller = support_controller('Admin_bookings', 'view_booking', $role);
    $controller = new SupportBookingView();
    $controller->booking_read_model = new class($db) {
        private $db;
        function __construct($db) { $this->db = $db; }
        function get_booking_details_by_id($id) { return $this->db->where('id', $id)->get('bookings')->row(); }
    };
    $controller->bookings_model = new class { public $seen = 0; function update_new_status($id) { $this->seen++; } };
    $controller->booking_action_log_model = new class { function getByBookingId($id) { return array(); } };
    $controller->view_booking(1);
    support_check($controller->bookings_model->seen === $expected, 'Viewing must respect seen permissions.');
}

// The secure-file guard applies to all document types, including old impersonation sessions.
foreach (array('selfie', 'id_cards', 'utility', 'itinerary') as $type) {
    $controller = support_controller('Secure_file', 'serve');
    try { $controller->serve($type, 'any-file.pdf'); throw new RuntimeException('Restricted file allowed.'); }
    catch (SupportDenied $e) { support_check($e->getCode() === 403, 'Customer Support file denial.'); }
}
$controller = support_controller('Secure_file', 'serve', 'super_admin');
$controller->session->admin_loggedin = false;
$controller->session->user_loggedin = true;
foreach (array('selfie' => 'own.jpg', 'id_cards' => 'own-id.pdf', 'utility' => 'own-utility.pdf', 'itinerary' => 'own-trip.pdf') as $type => $file) {
    try { $controller->serve($type, $file); throw new RuntimeException('Missing fixture file served.'); }
    catch (SupportDenied $e) { support_check($e->getCode() === 404, 'Owner should pass authorization before missing-file check.'); }
}
try { $controller->serve('selfie', 'other.jpg'); throw new RuntimeException('Cross-user file allowed.'); }
catch (SupportDenied $e) { support_check($e->getCode() === 403, 'Other users cannot open documents.'); }
$controller->session->values['user_impersonator_id'] = 9;
try { $controller->serve('selfie', 'own.jpg'); throw new RuntimeException('Old impersonation can access files.'); }
catch (SupportDenied $e) { support_check($e->getCode() === 403, 'Old impersonation cannot access files.'); }
try { $controller->user_restricted(); throw new RuntimeException('Old impersonation still works.'); }
catch (SupportDenied $e) { support_check($e->getCode() === 403, 'Old impersonation is blocked on user pages.'); }
echo 'PASS: ' . $checked . " real controller route guards, upcoming-record scope, safe action/JSON output, file ownership, impersonation denial and Super Admin access.\n";
