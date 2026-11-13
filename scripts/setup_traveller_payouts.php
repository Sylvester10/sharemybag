<?php
// Local-only Phase 1 setup. Production receives a separately reviewed SQL deployment.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development' || !in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', ['localhost','127.0.0.1','::1'], true)) {
    throw new RuntimeException('Restricted to the local development database.');
}
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
class CI_Model { public $db; }
function log_message($level, $message) { fwrite(STDERR, $level . ': ' . $message . "\n"); }
function show_error($message) { throw new RuntimeException($message); }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Traveller_payout_model.php';
$db = DB(['dbdriver'=>'mysqli', 'hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
    'username'=>$_ENV['DB_USERNAME_LOCAL'], 'password'=>$_ENV['DB_PASSWORD_LOCAL'],
    'database'=>$_ENV['DB_DATABASE_LOCAL'], 'db_debug'=>false, 'char_set'=>'utf8mb4'], true);
if (!$db->conn_id) throw new RuntimeException('Unable to connect to the local database.');
$model = (new ReflectionClass('Traveller_payout_model'))->newInstanceWithoutConstructor();
$model->db = $db;
$version = (int) $db->query('SELECT version FROM migrations LIMIT 1')->row()->version;
$tables = ['traveller_payout_opening_balances','traveller_payouts','traveller_payout_items'];
$mode = $argv[1] ?? '--inspect';
$declaredPaid = [];
foreach (array_slice($argv, 2) as $option) {
    if (!preg_match('/^--paid-travellers=([1-9][0-9]*(?:,[1-9][0-9]*)*)$/D', $option, $match)) {
        throw new RuntimeException('Optional historical declaration: --paid-travellers=ID,ID');
    }
    $declaredPaid = array_map('intval', explode(',', $match[1]));
}
if (!in_array($mode, ['--inspect','--preview','--apply'], true)) throw new RuntimeException('Use --inspect, --preview or --apply.');
if ($mode === '--inspect') {
    $state = ['database'=>$_ENV['DB_DATABASE_LOCAL'], 'migration_version'=>$version, 'payout_tables'=>[]];
    foreach ($tables as $table) $state['payout_tables'][$table] = $db->table_exists($table);
    echo json_encode($state, JSON_PRETTY_PRINT) . "\n";
    exit;
}
if ($mode === '--preview') {
    echo json_encode($model->preview_opening_balance($declaredPaid), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit;
}
if (!in_array($version, [27,28], true)) throw new RuntimeException('Apply preceding migrations through 027 before payout setup.');
foreach (['bookings','travellers'] as $table) {
    $status = $db->query('SHOW TABLE STATUS WHERE Name = ?', [$table])->row();
    if (!$status || $status->Engine !== 'InnoDB') throw new RuntimeException('Payout source tables must use InnoDB.');
}
$fingerprint = function () use ($db) {
    $hash = hash_init('sha256');
    foreach (['bookings','travellers'] as $table) {
        foreach ($db->order_by('id')->get($table)->result_array() as $row) hash_update($hash, json_encode($row));
    }
    return hash_final($hash);
};
$before = $fingerprint();
$sqlPath = $root . '/database/028_traveller_payouts.sql';
$sql = preg_replace('/^--.*$/m', '', file_get_contents($sqlPath));
foreach (explode(';', $sql) as $statement) {
    if (trim($statement) !== '' && !$db->query($statement)) throw new RuntimeException('Payout schema setup failed.');
}
$db->data_cache = [];
$definitions = [];
foreach ($tables as $table) {
    $definitions[$table] = $db->query('SHOW CREATE TABLE ' . $table)->row_array()['Create Table'];
    $status = $db->query('SHOW TABLE STATUS WHERE Name = ?', [$table])->row();
    if ($status->Engine !== 'InnoDB') throw new RuntimeException('Payout tables must use InnoDB.');
}
if ($version === 27 && !$db->query('UPDATE migrations SET version = 28 WHERE version = 27')) throw new RuntimeException('Migration ledger update failed.');
$result = $model->initialise_opening_balance($declaredPaid);
$after = $fingerprint();
if ($before !== $after) throw new RuntimeException('Operational data changed during setup. Review before continuing.');
$opening = $model->get_opening_balance();
$audit = $opening && $opening->audit_snapshot ? json_decode($opening->audit_snapshot, true) : null;
$tracked = [];
foreach (glob(APPPATH . 'migrations/*.php') as $file) $tracked[basename($file)] = hash_file('sha256', $file);
$evidence = [
    'generated_at'=>gmdate('c'), 'database'=>$_ENV['DB_DATABASE_LOCAL'], 'migration_version'=>28,
    'tracked_migrations'=>$tracked, 'sql_sha256'=>hash_file('sha256', $sqlPath),
    'schema_sha256'=>hash('sha256', json_encode($definitions)),
    'source_rows_unchanged'=>$before === $after, 'source_sha256'=>$after,
    'opening_status'=>$opening ? $opening->status : 'missing', 'already_applied'=>$result['already_applied'] ?? false,
    'paid_bookings'=>$audit ? count($audit['paid_booking_ids']) : 0,
    'outstanding_bookings'=>$audit ? count($audit['outstanding_booking_ids']) : 0,
    'review_bookings'=>$audit ? count($audit['review_booking_ids']) : 0,
    'summary'=>$model->get_outstanding_summary(), 'provider_calls'=>0,
];
$path = $root . '/evidence/verification/traveller-payouts';
if (!is_dir($path)) mkdir($path, 0775, true);
file_put_contents($path . '/migration-state.json', json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo json_encode([
    'status'=>$result['status'], 'msg'=>$result['msg'], 'migration_version'=>28,
    'already_applied'=>$result['already_applied'] ?? false,
    'paid_bookings'=>$evidence['paid_bookings'], 'outstanding_bookings'=>$evidence['outstanding_bookings'],
    'review_bookings'=>$evidence['review_bookings'], 'summary'=>$evidence['summary'],
    'source_rows_unchanged'=>$evidence['source_rows_unchanged'],
    'evidence_file'=>'evidence/verification/traveller-payouts/migration-state.json',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
$db->close();
exit($result['status'] ? 0 : 1);
