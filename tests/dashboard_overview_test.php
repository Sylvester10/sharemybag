<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
ob_start();
require __DIR__ . '/shipping_workflow_test.php';
ob_end_clean();
require APPPATH . 'models/Dashboard_read_model.php';
require APPPATH . 'models/User_read_model.php';
require APPPATH . 'models/Finance_read_model.php';
if (!defined('VERIFY_PENDING')) define('VERIFY_PENDING', 1);
foreach (array('firstname TEXT', 'lastname TEXT', 'date_registered TEXT', 'is_verified INTEGER') as $field) $db->query('ALTER TABLE users ADD COLUMN ' . $field);
foreach (array('fullname TEXT', 'status TEXT', 'travel_date TEXT', 'date_added TEXT', 'deleted_at TEXT') as $field) $db->query('ALTER TABLE travellers ADD COLUMN ' . $field);
foreach (array('total_amount DECIMAL', 'selected_price DECIMAL', 'traveller_commission DECIMAL', 'vat DECIMAL', 'currency TEXT', 'payment_method TEXT', 'selected_space DECIMAL') as $field) $db->query('ALTER TABLE bookings ADD COLUMN ' . $field);
$now = date('Y-m-d H:i:s');
$db->where('id', 1)->update('users', array('firstname' => 'Alice', 'lastname' => 'Example', 'date_registered' => $now, 'is_verified' => VERIFY_PENDING));
$db->insert('users', array('id' => 2, 'firstname' => 'Old', 'lastname' => 'Example', 'date_registered' => date('Y-m-d H:i:s', strtotime('-6 days'))));
$db->insert('users', array('id' => 3, 'firstname' => 'Removed', 'lastname' => 'Example', 'date_registered' => $now, 'deleted_at' => $now));
$db->insert('users', array('id' => 4, 'firstname' => 'Year Old', 'lastname' => 'Example', 'date_registered' => date('Y-m-d H:i:s', strtotime('-260 days'))));
$db->where('id', 1)->update('travellers', array('fullname' => 'Upcoming Traveller', 'status' => 'Approved', 'travel_date' => date('Y-m-d', strtotime('+3 days')), 'date_added' => $now));
$db->insert('travellers', array('id' => 2, 'fullname' => 'Private Pending Traveller', 'status' => 'Pending', 'travel_date' => date('Y-m-d', strtotime('+3 days')), 'date_added' => $now));
$db->insert('travellers', array('id' => 3, 'fullname' => 'Past Traveller', 'status' => 'Approved', 'travel_date' => date('Y-m-d', strtotime('-3 days')), 'date_added' => $now));
$db->where('id', 1)->update('bookings', array('date_added' => $now, 'total_amount' => 100, 'selected_price' => 80, 'traveller_commission' => 30, 'vat' => 7, 'currency' => 'GBP', 'payment_method' => 'offline'));
$db->where('id', 2)->update('bookings', array('date_added' => date('Y-m-d H:i:s', strtotime('-2 days')), 'total_amount' => 60, 'selected_price' => 50, 'traveller_commission' => 20, 'currency' => 'GBP', 'payment_method' => 'stripe'));
$db->where('id', 3)->update('bookings', array('date_added' => $now, 'total_amount' => 500, 'selected_price' => 400, 'traveller_commission' => 100, 'currency' => 'GBP', 'payment_status' => 'canceled', 'payment_method' => 'paystack'));
$db->insert('bookings', array('id' => 4, 'date_added' => $now, 'payment_status' => 'completed', 'tracking_id' => 'CADTEST', 'total_amount' => 200, 'selected_price' => 150, 'traveller_commission' => 70, 'vat' => 12, 'currency' => 'CAD', 'payment_method' => 'paystack', 'user_id' => 1, 'traveller_id' => 1, 'user_fullname' => 'CAD User'));
$db->insert('bookings', array('id' => 5, 'date_added' => $now, 'payment_status' => 'completed', 'tracking_id' => 'DELETED', 'total_amount' => 999, 'selected_price' => 888, 'traveller_commission' => 777, 'currency' => 'GBP', 'payment_method' => 'stripe', 'deleted_at' => $now));
$users = (new ReflectionClass('User_read_model'))->newInstanceWithoutConstructor();
$users->db = $db;
$users->table = 'users';
support_check($users->count_recent_signups() === 1, 'Signup badge excludes old and deleted accounts.');
$dashboard = (new ReflectionClass('Dashboard_read_model'))->newInstanceWithoutConstructor();
$dashboard->db = $db;
$dashboard->user_read_model = $users;
$dashboard->load = $controller->load;
foreach (array('super_admin', 'customer_support', 'traveller_support') as $role) {
    $controller = support_controller('Admin_travellers', 'index', $role);
    $controller->user_read_model = $users;
    $result = $dashboard->get_overview($role, 7);
    support_check(count($result['daily']) === 7, 'Seven daily buckets including zero activity.');
    support_check(count($result['chart_daily']) === 30, 'Both chart ranges are preloaded for local switching.');
    support_check(strpos(json_encode($result['recent']), 'Year Old') === false, 'Recent activity excludes events older than seven days.');
    foreach ($result['recent'] as $activity) support_check(strtotime($activity['occurred_at']) >= strtotime('-7 days'), 'Every recent row is within seven days.');
    foreach ($result['attention'] as $item) support_check($item['count'] > 0, 'Zero-count attention items do not hide the empty state.');
    support_check(array_key_first($result['daily']) === date('Y-m-d', strtotime('-6 days')) && array_key_last($result['daily']) === date('Y-m-d'), 'Seven days ends today.');
    $html = $render('admin/dashboard/activity', array('overview' => $result));
    support_check(strpos($html, 'Activity Overview') !== false && strpos($html, 'Recent Activity') !== false && strpos($html, 'Needs Attention') !== false, 'Actual activity view renders.');
    support_check(strpos($html, 'View All') === false && strpos($html, 'Total Revenue') === false, 'No unwanted view-all button or finance cards on dashboard.');
    if ($role === 'customer_support') {
        support_check(strpos(json_encode($result), 'Private Pending Traveller') === false && strpos(json_encode($result), 'Past Traveller') === false, 'Customer Support activity cannot reveal hidden travelers.');
        support_check(count($result['attention']) === 1 && $result['attention'][0]['count'] === 1, 'Support receives new-signup attention only.');
    } elseif ($role === 'traveller_support') {
        support_check(array_keys($result['sources']) === array('travellers') && !in_array('Total Users', array_column($result['tiles'], 'label'), true) && !in_array('Completed Bookings', array_column($result['tiles'], 'label'), true), 'Traveller Support sees no user or booking activity.');
    } else {
        support_check(count($result['sources']) === 3 && count($result['recent']) >= 6, 'Super Admin receives all three activity sources.');
    }
    $thirty = $dashboard->get_overview($role, 30);
    support_check(count($thirty['daily']) === 30, 'Thirty-day selection is supported.');
    support_check(array_key_first($thirty['daily']) === date('Y-m-d', strtotime('-29 days')) && array_key_last($thirty['daily']) === date('Y-m-d'), 'Thirty days ends today.');
}
$finance = (new ReflectionClass('Finance_read_model'))->newInstanceWithoutConstructor();
$finance->db = $db;
$finance->table = 'bookings';
$gbp = $finance->get_finance_cards('GBP');
support_check((float) $gbp->payments === 160.0 && (float) $gbp->commission === 103.0 && (float) $gbp->payouts === 50.0, 'GBP gross profit retains total payments minus traveller payouts and stored VAT, including legacy NULL VAT and excluding canceled/deleted/CAD rows.');
support_check((float) $gbp->payments_today === 100.0 && (float) $gbp->commission_today === 63.0 && (float) $gbp->payouts_today === 30.0, 'Created-today amounts use the same gross profit formula without including older bookings.');
$cad = $finance->get_finance_cards('CAD');
support_check((float) $cad->payments === 200.0 && (float) $cad->commission === 118.0 && (float) $cad->payouts === 70.0, 'CAD gross profit deducts its own payout and stored VAT without mixing currencies.');
$cards = $render('admin/finances/summary_cards', array('summary' => $gbp, 'sign' => '&pound;'));
$tiles = $controller->load->views['admin/partials/stat_tiles']['tiles'];
support_check(array_column($tiles, 'label') === array('Total Revenue', 'Gross Profit', 'Traveller Payouts') && $tiles[0]['detail'] === 'Bookings created today: £100.00', 'Finance retains the shared stat-card component and explicitly labels its creation-date cohort.');
$db->where('id', 2)->update('bookings', array('payment_status' => 'pending'));
$beforeConfirmation = $finance->get_finance_cards('GBP');
$db->where('id', 2)->update('bookings', array('payment_status' => 'completed'));
$afterConfirmation = $finance->get_finance_cards('GBP');
support_check((float) $afterConfirmation->commission - (float) $beforeConfirmation->commission === 40.0
    && (float) $afterConfirmation->payments_today === (float) $beforeConfirmation->payments_today,
    'Confirming an older booking increases all-time profit while the explicitly labelled created-today cohort remains unchanged.');
foreach (array('pound_finances', 'dollar_finances') as $view) {
    $render('admin/finances/' . $view, array('finance_cards' => $gbp));
    $filters = $controller->load->views['admin/partials/filter_row']['filters'];
    support_check(count($filters) === 3 && strpos($filters[0]['id'], 'month_filter') === 0 && strpos($filters[1]['id'], 'year_filter') === 0 && strpos($filters[2]['id'], 'route_filter') === 0, 'Month, Year and Route controls remain intact.');
}
echo "PASS: role-scoped activity, chart periods, recent-signup expiry, real view rendering, isolated currency totals and retained finance filters (temporary database only).\n";
