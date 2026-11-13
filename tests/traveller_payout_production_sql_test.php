<?php
// Runs the pasteable SQL in a randomly named disposable database only. No application rows are touched.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development' || !in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', ['localhost','127.0.0.1','::1'], true)) {
    throw new RuntimeException('Local development scratch-database verification only.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function production_payout_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function production_payout_connection($database)
{
    $isolatedSocket = getenv('SMB_PAYOUT_SQL_SOCKET');
    if ($isolatedSocket) {
        production_payout_check((bool) preg_match('#^/private/tmp/smb-payout-sql\.[A-Za-z0-9]+/mysql\.sock$#D', $isolatedSocket), 'Isolated verification socket only.');
        $connection = new mysqli('localhost', 'root', '', $database, 0, $isolatedSocket);
        $directory = rtrim($connection->query('SELECT @@datadir AS directory')->fetch_assoc()['directory'], '/');
        production_payout_check(realpath($directory) === realpath(dirname($isolatedSocket) . '/data'), 'Verification server must own the isolated data directory.');
        return $connection;
    }
    return new mysqli($_ENV['DB_HOSTNAME_LOCAL'], $_ENV['DB_USERNAME_LOCAL'], $_ENV['DB_PASSWORD_LOCAL'], $database,
        (int) ($_ENV['DB_PORT_LOCAL'] ?? 3306), '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
}
function production_payout_execute(mysqli $db, $sql)
{
    // phpMyAdmin also interprets DELIMITER locally; it is not sent as server SQL.
    $delimiter = ';'; $statement = '';
    foreach (preg_split('/\r?\n/', $sql) as $line) {
        if (preg_match('/^DELIMITER\s+(\S+)\s*$/i', trim($line), $match)) {
            production_payout_check(trim($statement) === '', 'Delimiter changed with a pending statement.');
            $delimiter = $match[1];
            continue;
        }
        if (trim($statement) === '' && preg_match('/^\s*--/', $line)) continue;
        $statement .= $line . "\n";
        $trimmed = rtrim($statement);
        if (substr($trimmed, -strlen($delimiter)) !== $delimiter) continue;
        $query = substr($trimmed, 0, -strlen($delimiter));
        $statement = '';
        if (trim($query) === '') continue;
        $result = $db->query($query);
        if ($result instanceof mysqli_result) $result->free();
        // CALL returns another status result in addition to its SELECT result.
        while ($db->more_results()) {
            $db->next_result();
            $result = $db->store_result();
            if ($result instanceof mysqli_result) $result->free();
        }
    }
    production_payout_check(trim($statement) === '', 'Unterminated deployment statement.');
}
function production_payout_clock(mysqli $db, $date)
{
    // Test session clock only. The production SQL contains no bypass flag or injected clock.
    $db->query("SET time_zone = '+00:00'");
    $db->query("SET timestamp = UNIX_TIMESTAMP('" . $db->real_escape_string($date) . "')");
}
function production_payout_fingerprint(mysqli $db)
{
    $hash = hash_init('sha256');
    foreach (['bookings','travellers'] as $table) {
        foreach ($db->query('SELECT * FROM ' . $table . ' ORDER BY id')->fetch_all(MYSQLI_ASSOC) as $row) hash_update($hash, json_encode($row));
    }
    return hash_final($hash);
}
function production_payout_opening(mysqli $db)
{
    return $db->query('SELECT * FROM traveller_payout_opening_balances WHERE id = 1')->fetch_assoc();
}
function production_payout_reset_ledger(mysqli $db)
{
    // These are fixtures in the newly created scratch database, never application records.
    foreach (['traveller_payout_items','traveller_payouts'] as $table) $db->query('DELETE FROM ' . $table);
    $db->query("UPDATE traveller_payout_opening_balances SET status='pending', snapshot_booking_id=NULL, audit_snapshot=NULL, applied_at=NULL WHERE id=1");
    $db->query('UPDATE migrations SET version=27');
}

$database = 'smb_payout_sql_' . bin2hex(random_bytes(6));
production_payout_check((bool) preg_match('/^smb_payout_sql_[a-f0-9]{12}$/D', $database), 'Safe scratch database name.');
$admin = production_payout_connection(getenv('SMB_PAYOUT_SQL_SOCKET') ? 'mysql' : $_ENV['DB_DATABASE_LOCAL']);
$admin->query('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $db = production_payout_connection($database);
    $db->set_charset('utf8mb4');
    $db->query("SET collation_connection = 'utf8mb4_unicode_ci'");
    $db->query('CREATE TABLE migrations (version BIGINT NOT NULL) ENGINE=InnoDB');
    $db->query('INSERT INTO migrations VALUES (27)');
    $db->query('CREATE TABLE travellers (id INT PRIMARY KEY, fullname VARCHAR(100), travel_date VARCHAR(100), arrival_date VARCHAR(100), deleted_at DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
    $db->query('CREATE TABLE bookings (id INT PRIMARY KEY, traveller_id INT, tracking_id VARCHAR(50), traveller_commission DECIMAL(12,2), currency VARCHAR(20), payment_status VARCHAR(20), payment_method VARCHAR(20), date_added DATETIME, deleted_at DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
    $traveller = $db->prepare('INSERT INTO travellers (id,fullname,travel_date,arrival_date,deleted_at) VALUES (?,?,?,?,?)');
    $tripRows = [
        [1,'Historical One','2026-11-10','2026-11-11',null],
        [2,'Upcoming Person','2026-11-15','2026-11-16',null],
        [3,"  OnyEKachi\t Vitalis  ",'2026-11-12','',null],
        [4,'Missing Departure','','',null],
        [5,'Invalid Departure','2026-02-30','2026-03-01',null],
        [6,'Historical Blank Arrival','2026-11-01','',null],
        [7,'Invalid Arrival','2026-10-09','2026-10-10 99:99:99',null],
        [8,'Future Arrival','2026-11-12','2026-11-14',null],
        [9,'Archived Traveller','2026-11-10','2026-11-11','2026-11-12'],
        [11,'Canadian History','2026-11-10','2026-11-11',null],
        [12,'Unknown Currency','2026-11-10','2026-11-11',null],
        [13,'Arrived Today','2026-11-12','2026-11-13',null],
        [14,'Year Zero','0000-01-01','0000-01-02',null],
    ];
    foreach ($tripRows as [$id,$name,$departure,$arrival,$deleted]) {
        $traveller->bind_param('issss',$id,$name,$departure,$arrival,$deleted); $traveller->execute();
    }
    $booking = $db->prepare('INSERT INTO bookings (id,traveller_id,tracking_id,traveller_commission,currency,payment_status,payment_method,date_added,deleted_at) VALUES (?,?,?,?,?,?,?,?,?)');
    $bookingRows = [
        [1,1,'12.34','gbp','completed','stripe',null], [2,1,'7.66','POUNDS','completed','offline',null],
        [3,11,'10.10','CAD','completed','stripe',null], [4,2,'25.50','GBP','completed','paystack',null],
        [5,3,'30.25','GBP','completed','stripe',null], [6,4,'4.05','GBP','completed','offline',null],
        [7,8,'3.00','DOLLARS','completed','bank',null], [8,1,'90.00','GBP','canceled','stripe',null],
        [9,1,'90.00','GBP','pending','stripe',null], [10,1,'90.00','GBP','completed','stripe','2026-11-12'],
        [11,1,'1.01',null,'completed',null,null], [12,5,'6.06','GBP','completed','stripe',null],
        [13,6,'9.99','GBP','completed','offline',null], [14,7,'2.00','GBP','completed','stripe',null],
        [15,9,'1.00','GBP','completed','stripe',null], [16,999,'1.00','GBP','completed','stripe',null],
        [17,12,'2.00','EUR','completed','stripe',null], [18,1,'1.11','GBP','completed','offline',null],
        [19,1,'0.00','GBP','completed','offline',null], [20,13,'2.00','GBP','completed','stripe',null],
        [21,14,'1.00','GBP','completed','stripe',null],
    ];
    foreach ($bookingRows as [$id,$trip,$amount,$currency,$status,$method,$deleted]) {
        $reference = 'FIX' . $id;
        $added = $id === 18 ? '2026-11-14 10:00:00' : '2026-11-12 10:00:00';
        $booking->bind_param('iisssssss',$id,$trip,$reference,$amount,$currency,$status,$method,$added,$deleted); $booking->execute();
    }
    $sql = file_get_contents($root . '/database/028_traveller_payouts_production.sql');
    production_payout_clock($db,'2026-11-13 12:00:00');
    $before = production_payout_fingerprint($db);
    production_payout_execute($db,$sql);
    $opening = production_payout_opening($db);
    $audit = json_decode($opening['audit_snapshot'],true);
    production_payout_check($opening['status'] === 'applied', 'Opening state commits.');
    production_payout_check($audit['paid_booking_ids'] === [1,2,3,11,13,19], 'Correct historical set including explicit blank-arrival declaration.');
    production_payout_check($audit['outstanding_booking_ids'] === [4,5,7,18,20], 'Upcoming, Vitalis, cutoff-day/future arrivals and later bookings stay unpaid.');
    production_payout_check($audit['review_booking_ids'] === [6,12,14,15,16,17,21], 'Missing/invalid/year-zero dates, missing/archived travellers and unknown currency require review.');
    $totals = [];
    foreach ($db->query('SELECT currency,SUM(amount) amount FROM traveller_payouts GROUP BY currency')->fetch_all(MYSQLI_ASSOC) as $row) $totals[$row['currency']] = $row['amount'];
    production_payout_check($totals === ['CAD'=>'10.10','GBP'=>'31.00'], 'Stored GBP/CAD commissions are exact and separate.');
    production_payout_check((int)$db->query('SELECT COUNT(*) count FROM traveller_payout_items')->fetch_assoc()['count'] === 6, 'Every paid booking has one allocation.');
    production_payout_check((int)$db->query('SELECT version FROM migrations')->fetch_assoc()['version'] === 28, 'Migration metadata advances.');
    production_payout_check(production_payout_fingerprint($db) === $before, 'Source rows are byte-for-byte unchanged.');
    $collations = [];
    foreach ($db->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".$db->real_escape_string($database)."' AND TABLE_NAME IN ('bookings','travellers','traveller_payouts')")->fetch_all(MYSQLI_ASSOC) as $row) $collations[$row['TABLE_NAME']]=$row['TABLE_COLLATION'];
    production_payout_check(count(array_filter($collations,function($value){return $value==='utf8mb4_general_ci';}))===3, 'Deployment preserves existing source and ledger collations despite the Unicode database/routine default.');
    production_payout_check((int)$db->query('SELECT COUNT(*) count FROM traveller_payouts WHERE admin_id IS NOT NULL OR paid_at IS NOT NULL')->fetch_assoc()['count'] === 0, 'No fabricated historical actor/date.');

    // Replays on a later day cannot silently settle newly completed payments.
    $db->query("UPDATE bookings SET payment_status='completed' WHERE id=9");
    $before = production_payout_fingerprint($db);
    production_payout_clock($db,'2026-11-15 12:00:00');
    production_payout_execute($db,$sql);
    production_payout_check(production_payout_opening($db)['audit_snapshot'] === $opening['audit_snapshot'], 'Audit snapshot is immutable on replay.');
    production_payout_check((int)$db->query('SELECT COUNT(*) count FROM traveller_payout_items')->fetch_assoc()['count'] === 6, 'No duplicate or newly classified allocation on replay.');
    production_payout_check(production_payout_fingerprint($db) === $before, 'Replay preserves sources.');

    // A failed allocation rolls back headers, allocations, audit and migration metadata.
    production_payout_reset_ledger($db);
    production_payout_clock($db,'2026-11-13 12:00:00');
    $db->query("CREATE TRIGGER fixture_block_payout_item BEFORE INSERT ON traveller_payout_items FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='forced fixture allocation failure'");
    $failed = false;
    try { production_payout_execute($db,$sql); } catch (mysqli_sql_exception $error) { $failed = strpos($error->getMessage(),'forced fixture allocation failure') !== false; }
    production_payout_check($failed, 'Allocation failure is reported.');
    production_payout_check((int)$db->query('SELECT COUNT(*) count FROM traveller_payouts')->fetch_assoc()['count'] === 0, 'Failed allocation rolls back headers.');
    production_payout_check(production_payout_opening($db)['status'] === 'pending', 'Failed allocation preserves pending opening state.');
    production_payout_check((int)$db->query('SELECT version FROM migrations')->fetch_assoc()['version'] === 27, 'Failed allocation does not advance migration metadata.');
    production_payout_check(production_payout_fingerprint($db) === $before, 'Rollback preserves sources.');
    $db->query('DROP TRIGGER fixture_block_payout_item');

    // Initial delayed deployment must fail without classifying any booking.
    production_payout_clock($db,'2026-11-14 12:00:00');
    $failed = false;
    try { production_payout_execute($db,$sql); } catch (mysqli_sql_exception $error) { $failed = strpos($error->getMessage(),'SMB_PAYOUT_HISTORICAL_SNAPSHOT_REQUIRED') !== false; }
    production_payout_check($failed && production_payout_opening($db)['status'] === 'pending', 'Delayed first initialization fails closed.');
    production_payout_check((int)$db->query('SELECT COUNT(*) count FROM traveller_payouts')->fetch_assoc()['count'] === 0, 'Date safeguard creates no payouts.');
    production_payout_check(production_payout_fingerprint($db) === $before, 'Delayed rejection preserves sources.');

    echo "PASS: actual pasteable production SQL, mixed collations, schema replay, exact GBP/CAD allocations, Vitalis/upcoming exclusions, date validation, blank-arrival declaration, source preservation, atomic rollback, immutable replay and delayed first-run safeguard (isolated MySQL database only).\n";
} finally {
    if (isset($db)) { $db->rollback(); $db->close(); }
    $admin->query('DROP DATABASE `' . $database . '`');
    $admin->close();
}
