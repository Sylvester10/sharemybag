<?php
// Real CI query builder and payout model. SQLite memory or separately named disposable MySQL databases.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
class CI_Model { public $db; }
function log_message($level, $message) {}
function show_error($message, $code = 500, $heading = '') { throw new RuntimeException($message, $code); }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function payout_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Traveller_payout_model.php';
class PayoutFixtureModel extends Traveller_payout_model {
    public $fixtureNow = '2026-11-13 12:00:00';
    protected function now() { return $this->fixtureNow; }
}
$mysql = getenv('SMB_PAYOUT_MYSQL') === '1';
if ($mysql) {
    require $root . '/vendor/autoload.php';
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
    payout_check(($_ENV['APP_ENV'] ?? '') === 'development' && in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', ['localhost','127.0.0.1','::1'], true), 'Local development fixtures only.');
    $payoutTestControl = DB(['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],'database'=>$_ENV['DB_DATABASE_LOCAL'],'db_debug'=>false], true);
    $payoutTestDatabases = [];
    register_shutdown_function(function () use ($payoutTestControl) {
        foreach ($GLOBALS['payoutTestDatabases'] as $database) $payoutTestControl->query('DROP DATABASE `' . $database . '`');
        $payoutTestControl->close();
    });
}
function payout_fixture()
{
    global $mysql, $root, $payoutTestControl, $payoutTestDatabases;
    $database = ':memory:';
    if ($mysql) {
        $database = 'smb_payout_fixture_' . bin2hex(random_bytes(6));
        payout_check($payoutTestControl->query('CREATE DATABASE `' . $database . '`'), 'Create isolated payout fixture database.');
        $payoutTestDatabases[] = $database;
    }
    $db = DB($mysql ? ['dbdriver'=>'mysqli', 'hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'], 'password'=>$_ENV['DB_PASSWORD_LOCAL'], 'database'=>$database, 'db_debug'=>false]
        : ['dbdriver'=>'sqlite3', 'database'=>':memory:', 'db_debug'=>false], true);
    $create = 'CREATE TABLE ';
    $engine = $mysql ? ' ENGINE=InnoDB' : '';
    foreach ([
        'travellers'=>'(id INTEGER PRIMARY KEY, fullname VARCHAR(100), travel_date VARCHAR(100), arrival_date VARCHAR(100), deleted_at DATETIME)',
        'bookings'=>'(id INTEGER PRIMARY KEY, traveller_id INTEGER, tracking_id VARCHAR(50), traveller_commission DECIMAL(12,2), currency VARCHAR(20), payment_status VARCHAR(20), payment_method VARCHAR(20), date_added DATETIME, deleted_at DATETIME)',
    ] as $table=>$columns) payout_check($db->query($create . $table . $columns . $engine), 'Create fixture ' . $table);
    if ($mysql) {
        $sql = preg_replace('/^--.*$/m', '', file_get_contents($root . '/database/028_traveller_payouts.sql'));
        foreach ([1,2] as $pass) foreach (explode(';', $sql) as $statement) {
            if (trim($statement) !== '') payout_check($db->query($statement), 'Idempotent actual payout schema');
        }
    } else {
        $db->query("CREATE TABLE traveller_payout_opening_balances (id INTEGER PRIMARY KEY, opening_date TEXT, status TEXT, snapshot_booking_id INTEGER, audit_snapshot TEXT, applied_at TEXT)");
        $db->query("INSERT INTO traveller_payout_opening_balances VALUES (1, '2026-11-13', 'pending', NULL, NULL, NULL)");
        $db->query('CREATE TABLE traveller_payouts (id INTEGER PRIMARY KEY AUTOINCREMENT, operation_key TEXT UNIQUE, opening_balance_id INTEGER, traveller_id INTEGER, traveller_name TEXT, currency TEXT, amount DECIMAL(14,2), source TEXT, admin_id INTEGER, paid_at TEXT, recorded_at TEXT, note TEXT)');
        $db->query('CREATE TABLE traveller_payout_items (id INTEGER PRIMARY KEY AUTOINCREMENT, payout_id INTEGER, booking_id INTEGER, booking_reference TEXT, traveller_id INTEGER, currency TEXT, commission_snapshot DECIMAL(12,2), amount DECIMAL(12,2), UNIQUE(payout_id, booking_id))');
    }
    $model = (new ReflectionClass('PayoutFixtureModel'))->newInstanceWithoutConstructor();
    $model->db = $db;
    foreach ([1=>['2026-11-10','2026-11-11'], 2=>['2026-11-15','2026-11-16'], 3=>['2026-11-12','2026-11-13'], 4=>['2026-11-12',''], 5=>['2026-11-12','2026-11-14'], 6=>['2026-02-30','2026-03-01']] as $id=>$dates) {
        $db->insert('travellers', ['id'=>$id,'fullname'=>'Fixture ' . $id,'travel_date'=>$dates[0],'arrival_date'=>$dates[1]]);
    }
    $rows = [
        [1,1,'12.34','gbp','completed','stripe',null],
        [2,1,'7.66','POUNDS','completed','offline',null],
        [3,1,'10.10','CAD','completed','stripe',null],
        [4,2,'25.50','GBP','completed','paystack',null],
        [5,3,'30.25','GBP','completed','stripe',null],
        [6,4,'4.05','GBP','completed','offline',null],
        [7,5,'3.00','DOLLARS','completed','bank',null],
        [8,1,'90.00','GBP','canceled','stripe',null],
        [9,1,'90.00','GBP','pending','stripe',null],
        [10,1,'90.00','GBP','completed','stripe','2026-11-12'],
        [11,1,'1.01',null,'completed',null,null],
        [12,6,'6.06','GBP','completed','stripe',null],
    ];
    foreach ($rows as $row) $db->insert('bookings', ['id'=>$row[0],'traveller_id'=>$row[1],'tracking_id'=>'FIX'.$row[0], 'traveller_commission'=>$row[2],'currency'=>$row[3],'payment_status'=>$row[4],'payment_method'=>$row[5],'deleted_at'=>$row[6],'date_added'=>'2026-11-12 10:00:00']);
    return [$model,$db];
}

[$model,$db] = payout_fixture();
$before = $db->order_by('id')->get('bookings')->result_array();
$preview = $model->preview_opening_balance();
payout_check($preview['paid_booking_ids'] === [1,2,3,11], 'Only historical departed AND arrived bookings are settled.');
payout_check($preview['outstanding_booking_ids'] === [4,5,7], 'Upcoming, arrival today and in-transit remain unpaid.');
payout_check($preview['review_booking_ids'] === [6,12], 'Missing and impossible dates require review.');
payout_check($db->count_all('traveller_payouts') === 0, 'Preview cannot write.');
$result = $model->initialise_opening_balance();
payout_check($result['status'] && !$result['already_applied'], 'Opening balance committed.');
payout_check($db->count_all('traveller_payouts') === 2 && $db->count_all('traveller_payout_items') === 4, 'One historical record per traveller/currency with booking allocations.');
payout_check($before === $db->order_by('id')->get('bookings')->result_array(), 'Booking data remains unchanged.');
$summary = $model->get_outstanding_summary();
payout_check($summary['currencies']['GBP']['outstanding'] === '65.86', 'GBP excludes settled allocations and preserves unpaid/review liabilities exactly.');
payout_check($summary['currencies']['CAD']['outstanding'] === '3.00', 'Currencies remain separate.');
$history = $model->get_traveller_history(1);
payout_check(count($history) === 2 && $history[0]->paid_at === null && $history[0]->admin_id === null, 'Historical dates and actor are not fabricated.');
payout_check($history[0]->source === 'opening_balance' && count($history[0]->items) > 0, 'Immutable allocation history is available.');
$audit = json_decode($model->get_opening_balance()->audit_snapshot, true);
payout_check($audit['opening_date'] === '2026-11-13' && $audit['review_booking_ids'] === [6,12], 'Opening audit retains cutoff and exact exception/review list.');

// New bookings and newly completed payments must not become paid on replay.
$db->insert('bookings', ['id'=>13,'traveller_id'=>1,'tracking_id'=>'FIX13','traveller_commission'=>'1.11','currency'=>'GBP','payment_status'=>'completed','payment_method'=>'offline','date_added'=>'2026-11-14 10:00:00']);
$db->where('id',9)->update('bookings',['payment_status'=>'completed']);
$again = $model->initialise_opening_balance();
payout_check($again['status'] && $again['already_applied'] && $db->count_all('traveller_payout_items') === 4, 'Replay never settles later/newly completed bookings.');
payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '156.97', 'Later bookings stay outstanding even for paid travelers.');

// Financial changes/cancellation do not erase settlement history or offset other travelers.
$db->where('id',1)->update('bookings',['traveller_commission'=>'14.34']);
payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '158.97', 'Additional commission on a paid booking remains owed.');
$db->where('id',2)->update('bookings',['traveller_commission'=>'1.00']);
payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '158.97', 'Over-settlement cannot hide unrelated liabilities.');
$db->where('id',1)->update('bookings',['payment_status'=>'canceled']);
payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '156.97', 'Canceled bookings no longer accrue liability.');
payout_check($db->count_all('traveller_payout_items') === 4, 'Cancellation preserves payment audit history.');
$db->where('id',11)->update('bookings',['traveller_id'=>2]);
payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '157.98', 'A reassigned booking does not inherit another traveler payout.');

// Mid-transaction failure must roll back allocations, headers and opening state together.
[$model,$db] = payout_fixture();
// Force failure without dropping a table. MySQL fixtures additionally live in
// separately created databases, never beside application records.
$db->query('CREATE UNIQUE INDEX fixture_reject_multiple_items ON traveller_payout_items (payout_id)');
set_error_handler(function ($severity, $message) { if (strpos($message, 'UNIQUE constraint failed: traveller_payout_items.payout_id') !== false) return true; return false; });
$failed = $model->initialise_opening_balance();
restore_error_handler();
payout_check(!$failed['status'] && $db->count_all('traveller_payouts') === 0 && $model->get_opening_balance()->status === 'pending', 'Opening audit and payouts roll back on allocation failure.');

[$model,$db] = payout_fixture();
$db->where('id',1)->update('travellers',['arrival_date'=>'2026-11-11 99:99:99']);
payout_check(in_array(1, $model->preview_opening_balance()['review_booking_ids'], true), 'Malformed timestamps are held for review.');
$model->fixtureNow = '2026-11-14 12:00:00';
payout_check($model->preview_opening_balance()['requires_historical_review'], 'Delayed preview warns that historical review is required.');
payout_check(!$model->initialise_opening_balance()['status'] && $db->count_all('traveller_payouts') === 0, 'Delayed first deployment cannot guess the historical paid set.');

[$model,$db] = payout_fixture();
payout_check($model->initialise_opening_balance()['status'], 'Cutover initialization succeeds.');
$model->fixtureNow = '2026-11-14 12:00:00';
payout_check($model->initialise_opening_balance()['already_applied'], 'A later retry of an applied opening remains safe.');

[$model,$db] = payout_fixture();
$declared = $model->preview_opening_balance([2,3,4,5,6]);
payout_check(in_array(6, $declared['paid_booking_ids'], true), 'Explicit declaration resolves a missing historical arrival date.');
payout_check($declared['outstanding_booking_ids'] === [4,5,7], 'Declarations cannot override upcoming, cutoff-day or future arrivals.');
payout_check($declared['review_booking_ids'] === [12], 'Invalid departure dates cannot be overridden.');
payout_check($model->initialise_opening_balance([4])['status'], 'Historical declaration commits.');
$audit = json_decode($model->get_opening_balance()->audit_snapshot, true);
payout_check($audit['declared_paid_traveller_ids'] === [4], 'Explicit historical declarations are audited.');

[$model,$db] = payout_fixture();
$db->where('id',1)->update('travellers',['travel_date'=>'0000-01-01','arrival_date'=>'0000-01-02']);
payout_check(in_array(1, $model->preview_opening_balance()['review_booking_ids'], true), 'Year-zero dates cannot be treated as historical payments.');

echo 'PASS: payout opening balance, exact GBP/CAD liabilities, fixed-date exceptions, replay, financial changes, moved/canceled bookings, history and atomic rollback (' . ($mysql ? 'isolated MySQL databases' : 'SQLite memory') . ").\n";
