<?php
// Creates and removes its own randomly named MySQL database. No application rows are used.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development' || !in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', ['localhost','127.0.0.1','::1'], true)) throw new RuntimeException('Local development only.');
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
class CI_Model { public $db; }
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function concurrent_payout_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Traveller_payout_model.php';
class ConcurrentPayoutFixture extends Traveller_payout_model {
    protected function now() { return '2026-11-13 12:00:00'; }
}
function payout_test_connection($database) {
    return DB(['dbdriver'=>'mysqli','hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'],'password'=>$_ENV['DB_PASSWORD_LOCAL'],
        'database'=>$database,'db_debug'=>false], true);
}
if (in_array($argv[1] ?? '', ['--worker','--manual-worker'], true)) {
    $database = $argv[2] ?? '';
    if (!preg_match('/^smb_payout_verify_[a-f0-9]{12}$/D', $database)) throw new RuntimeException('Invalid isolated test database.');
    $model = (new ReflectionClass('ConcurrentPayoutFixture'))->newInstanceWithoutConstructor();
    $model->db = payout_test_connection($database);
    $manual = $argv[1] === '--manual-worker';
    $context = $manual ? $model->get_payout_context(4)['context'] : null;
    echo "ready\n"; flush();
    echo json_encode($manual ? $model->record_manual_payout(4, (int) $argv[3], [
        'confirm_paid'=>'1', 'request_key'=>str_repeat($argv[3] === '9' ? 'a' : 'b',32),
        'snapshot_token'=>$context['snapshot_token'],
    ]) : $model->initialise_opening_balance()) . "\n";
    exit;
}
$database = 'smb_payout_verify_' . bin2hex(random_bytes(6));
$admin = payout_test_connection($_ENV['DB_DATABASE_LOCAL']);
concurrent_payout_check($admin->query('CREATE DATABASE `' . $database . '`'), 'Create isolated test database.');
$processes = [];
try {
    $db = payout_test_connection($database);
    $db->query('CREATE TABLE travellers (id INT PRIMARY KEY, fullname VARCHAR(100), travel_date VARCHAR(100), arrival_date VARCHAR(100), deleted_at DATETIME) ENGINE=InnoDB');
    $db->query('CREATE TABLE bookings (id INT PRIMARY KEY, traveller_id INT, tracking_id VARCHAR(50), traveller_commission DECIMAL(12,2), currency VARCHAR(10), payment_status VARCHAR(20), payment_method VARCHAR(20), date_added DATETIME, deleted_at DATETIME) ENGINE=InnoDB');
    $sql = preg_replace('/^--.*$/m', '', file_get_contents($root . '/database/028_traveller_payouts.sql'));
    foreach ([1,2] as $pass) foreach (explode(';', $sql) as $statement) if (trim($statement) !== '') concurrent_payout_check($db->query($statement), 'Replay migration.');
    foreach ([1=>['2026-11-10','2026-11-11','10.00'],2=>['2026-11-15','2026-11-16','20.00'],3=>['2026-11-12','2026-11-13','30.00']] as $id=>$row) {
        $db->insert('travellers',['id'=>$id,'fullname'=>'Fixture '.$id,'travel_date'=>$row[0],'arrival_date'=>$row[1]]);
        $db->insert('bookings',['id'=>$id,'traveller_id'=>$id,'tracking_id'=>'FIX'.$id,'traveller_commission'=>$row[2],'currency'=>'GBP','payment_status'=>'completed','payment_method'=>'offline','date_added'=>'2026-11-12 12:00:00']);
    }
    $db->trans_begin();
    $db->query('SELECT * FROM traveller_payout_opening_balances WHERE id = 1 FOR UPDATE');
    foreach ([1,2] as $index) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, '--worker', $database], [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes, $root);
        concurrent_payout_check(is_resource($process), 'Start concurrent worker.');
        fclose($pipes[0]);
        $processes[] = [$process,$pipes];
        concurrent_payout_check(trim(fgets($pipes[1])) === 'ready', 'Worker is ready.');
    }
    usleep(200000);
    $db->trans_commit();
    $results = [];
    foreach ($processes as [$process,$pipes]) {
        $output = stream_get_contents($pipes[1]); $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        concurrent_payout_check(proc_close($process) === 0 && $errors === '', 'Worker completed cleanly.');
        $result = json_decode(trim($output), true);
        concurrent_payout_check(!empty($result['status']), 'Both overlapping requests are safe.');
        $results[] = $result['already_applied'];
    }
    $processes = [];
    sort($results);
    concurrent_payout_check($results === [false,true], 'Exactly one worker initialized; the other saw the applied state.');
    concurrent_payout_check($db->count_all('traveller_payouts') === 1 && $db->count_all('traveller_payout_items') === 1, 'No duplicate payout or allocation.');
    $model = (new ReflectionClass('ConcurrentPayoutFixture'))->newInstanceWithoutConstructor(); $model->db = $db;
    concurrent_payout_check($model->get_outstanding_summary()['currencies']['GBP']['outstanding'] === '50.00', 'Upcoming and today arrivals remain unpaid after concurrent setup.');
    $db->query('CREATE TABLE admins (id INT PRIMARY KEY, role VARCHAR(30)) ENGINE=InnoDB');
    $db->query('CREATE TABLE shipping_records (id INT PRIMARY KEY, booking_id INT UNIQUE, status VARCHAR(30)) ENGINE=InnoDB');
    foreach ([9,10] as $id) $db->insert('admins',['id'=>$id,'role'=>'super_admin']);
    $db->insert('travellers',['id'=>4,'fullname'=>'Arrived Fixture','travel_date'=>'2026-11-12','arrival_date'=>'2026-11-13']);
    foreach ([4=>['GBP','11.11'],5=>['CAD','2.22']] as $id=>$row) {
        $db->insert('bookings',['id'=>$id,'traveller_id'=>4,'tracking_id'=>'FIX'.$id,'traveller_commission'=>$row[1],'currency'=>$row[0],'payment_status'=>'completed','payment_method'=>'offline','date_added'=>'2026-11-13 12:00:00']);
        $db->insert('shipping_records',['id'=>$id,'booking_id'=>$id,'status'=>'Awaiting Collection']);
    }
    $db->data_cache = [];
    $db->trans_begin();
    $db->query('SELECT * FROM bookings WHERE traveller_id = 4 FOR UPDATE');
    foreach ([9,10] as $adminId) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, '--manual-worker', $database, (string) $adminId], [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes, $root);
        concurrent_payout_check(is_resource($process), 'Start concurrent payout worker.');
        fclose($pipes[0]); $processes[] = [$process,$pipes];
        concurrent_payout_check(trim(fgets($pipes[1])) === 'ready', 'Payout worker is ready.');
    }
    usleep(200000); $db->trans_commit();
    $results = [];
    foreach ($processes as [$process,$pipes]) {
        $result = json_decode(trim(stream_get_contents($pipes[1])),true);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        concurrent_payout_check(proc_close($process) === 0 && $errors === '' && !empty($result['status']), 'Concurrent payout worker succeeded safely.');
        $results[] = $result['already_recorded'];
    }
    $processes = []; sort($results);
    concurrent_payout_check($results === [false,true], 'Exactly one manual confirmation settles the booking set.');
    concurrent_payout_check(count($model->get_traveller_history(4)) === 2, 'Concurrent confirms create one GBP and one CAD batch only.');
    concurrent_payout_check($model->get_payout_context(4)['context']['paid'], 'The arrived traveller is settled after overlapping confirmations.');
    echo "PASS: overlapping MySQL opening and manual payout requests commit once; currency batches, migration replay and unpaid exceptions verified in an isolated database.\n";
} finally {
    foreach ($processes as [$process,$pipes]) {
        if (is_resource($process)) proc_terminate($process);
        foreach ($pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
        if (is_resource($process)) proc_close($process);
    }
    if (isset($db)) { $db->trans_rollback(); $db->close(); }
    $admin->query('DROP DATABASE `' . $database . '`');
    $admin->close();
}
