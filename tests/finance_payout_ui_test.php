<?php
// Real finance query/JSON/view contracts with disposable SQLite rows; no production data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__ . '/dashboard_overview_test.php';
ob_end_clean();
require APPPATH . 'models/ajax/finances/Finances_ajax.php';
require APPPATH . 'models/ajax/finances/Finances_cad_ajax.php';

foreach (['original_bag_space DECIMAL', 'available_space DECIMAL', 'used_space DECIMAL'] as $field) $db->query('ALTER TABLE travellers ADD COLUMN ' . $field);
foreach (['items TEXT', 'insurance DECIMAL', 'service_charge DECIMAL', 'paystack_exchange_rate DECIMAL', 'traveller_departure_date TEXT'] as $field) $db->query('ALTER TABLE bookings ADD COLUMN ' . $field);
$db->where('id',1)->update('travellers', ['original_bag_space'=>21,'used_space'=>3,'available_space'=>12]);
$db->where('id',3)->update('travellers', ['original_bag_space'=>7.5,'used_space'=>1,'available_space'=>2]);
$db->where('id',2)->update('bookings', ['traveller_id'=>3]);
$db->update('bookings', ['items'=>'[]','insurance'=>0,'service_charge'=>0,'traveller_departure_date'=>'2026-11-13']);

foreach (['Finances_ajax'=>['all_finances_ajax','GBP'],'Finances_cad_ajax'=>['all_cad_finances_ajax','CAD']] as $class=>$case) {
    $_POST = ['length'=>10,'start'=>0,'draw'=>1];
    $table = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $table->db = $db;
    support_check(count($table->column_order) === 16 && $table->column_order[3] === 'travellers.original_bag_space', 'Finance ordering aligns with the KG Offered column.');
    $rows = $table->get_records();
    support_check(count($rows) > 0 && isset($rows[0]->traveller_kg_offered), 'Finance query selects the original KG alias.');
    $controller = support_controller('Admin_finances', $case[0], 'super_admin');
    $controller->current_model = $table;
    $controller->booking_presenter = new Booking_presenter();
    ob_start(); $controller->{$case[0]}(); $response = json_decode(ob_get_clean(), true);
    support_check(count($response['data'][0]) === 16 && $response['data'][0][3] === '21KG', $case[1] . ' JSON uses original capacity rather than used/available space.');
    support_check(strpos($response['data'][0][4], 'KG') !== false && $response['data'][0][15] === $rows[0]->tracking_id, 'Total KG and trailing Booking ID stay aligned.');
    $_POST['order'] = [['column'=>3,'dir'=>'asc']];
    $ordered = $table->get_records();
    if ($case[1] === 'GBP') support_check((float) $ordered[0]->traveller_kg_offered === 7.5, 'KG Offered sorts by original capacity.');
    unset($_POST['order']);
    $_POST['search'] = ['value'=>'7.5'];
    if ($case[1] === 'GBP') support_check(count($table->get_records()) === 1, 'KG Offered participates in finance search.');
}

$_POST = ['length'=>10,'start'=>0,'draw'=>1];
$db->where('id',1)->update('bookings', ['traveller_id'=>999]);
$controller = support_controller('Admin_finances', 'all_finances_ajax', 'super_admin');
$controller->current_model = (new ReflectionClass('Finances_ajax'))->newInstanceWithoutConstructor();
$controller->current_model->db = $db;
$controller->booking_presenter = new Booking_presenter();
ob_start(); $controller->all_finances_ajax(); $response = json_decode(ob_get_clean(), true);
support_check($response['data'][0][3] === 'N/A', 'Unknown original capacity is not replaced with purchased or available KG.');

$payoutSummary = ['opening_balance_status'=>'applied', 'review_booking_ids'=>[], 'currencies'=>[
    'GBP'=>['outstanding'=>'20.23','unpaid_booking_count'=>2],
    'CAD'=>['outstanding'=>'89.45','unpaid_booking_count'=>4],
]];
foreach (['GBP'=>['&pound;','20.23',2,'pound_finances'],'CAD'=>['$','89.45',4,'dollar_finances']] as $currency=>$case) {
    $render('admin/finances/summary_cards', ['summary'=>$gbp,'sign'=>$case[0],'currency'=>$currency,'payout_summary'=>$payoutSummary]);
    $tiles = $controller->load->views['admin/partials/stat_tiles']['tiles'];
    support_check(count($tiles) === 4 && array_unique(array_column($tiles,'col')) === [3], 'Four cards retain the shared blue stat tile layout.');
    support_check($tiles[3]['label'] === 'Outstanding Traveller Payouts' && $tiles[3]['value'] === $case[0] . $case[1] && $tiles[3]['detail'] === 'Unpaid bookings: ' . $case[2], 'Outstanding card uses only the page currency.');
    $render('admin/finances/' . $case[3], ['finance_cards'=>$gbp,'payout_summary'=>$payoutSummary]);
    $labels = array_column($controller->load->views['admin/partials/datatable_shell']['columns'],'label');
    support_check(count($labels) === 16 && array_slice($labels,3,2) === ['KG Offered','Total KG'], 'Finance headings match response column order.');
    $summaryInput = $controller->load->views['admin/finances/summary_cards'];
    support_check($summaryInput['currency'] === $currency && $summaryInput['payout_summary'] === $payoutSummary, 'Currency page passes its payout summary to the cards.');
}
$payoutSummary['opening_balance_status'] = 'pending';
$render('admin/finances/summary_cards', ['summary'=>$gbp,'sign'=>'&pound;','currency'=>'GBP','payout_summary'=>$payoutSummary]);
$tiles = $controller->load->views['admin/partials/stat_tiles']['tiles'];
support_check($tiles[3]['value'] === 'N/A' && $tiles[3]['detail'] === 'Payout opening balance needs setup.', 'Uninitialized opening cannot present a misleading outstanding amount.');

class FinancePayoutPageFixture extends Admin_finances {
    public function admin_header($title, $innerPageTitle) {}
    public function admin_footer() {}
}
foreach (['index'=>'admin/finances/pound_finances','cad_finances'=>'admin/finances/dollar_finances'] as $method=>$view) {
    support_controller('Admin_finances', $method, 'super_admin');
    $controller = new FinancePayoutPageFixture();
    $controller->finance_read_model = $finance;
    $controller->traveller_payout_model = new class($payoutSummary) {
        private $summary;
        public function __construct($summary) { $this->summary = $summary; }
        public function get_outstanding_summary() { return $this->summary; }
    };
    $controller->$method();
    support_check($controller->load->views[$view]['payout_summary'] === $payoutSummary, 'Finance controller supplies the all-time payout summary.');
}

echo "PASS: finance KG Offered query/search/sort/JSON/headings, original-capacity provenance, currency-separated outstanding cards, setup state and controller data (SQLite only).\n";
