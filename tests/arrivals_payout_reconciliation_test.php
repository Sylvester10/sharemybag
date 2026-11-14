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
$database = 'smb_arrival_reconcile_' . bin2hex(random_bytes(6));
$admin = production_payout_connection(getenv('SMB_PAYOUT_SQL_SOCKET') ? 'mysql' : $_ENV['DB_DATABASE_LOCAL']);
$admin->query('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $db = production_payout_connection($database);
    $db->set_charset('utf8mb4');
    $db->query("SET collation_connection='utf8mb4_unicode_ci'");
    $db->query('CREATE TABLE migrations (version BIGINT NOT NULL) ENGINE=InnoDB');
    $db->query('INSERT INTO migrations VALUES (28)');
    $db->query('CREATE TABLE travellers (id INT PRIMARY KEY, fullname VARCHAR(100), travel_date VARCHAR(100), arrival_date VARCHAR(100), deleted_at DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
    $db->query('CREATE TABLE bookings (id INT PRIMARY KEY, traveller_id INT, tracking_id VARCHAR(50), traveller_commission DECIMAL(12,2), currency VARCHAR(20), payment_status VARCHAR(20), payment_method VARCHAR(20), date_added DATETIME, deleted_at DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
    production_payout_execute($db, file_get_contents($root . '/database/028_traveller_payouts.sql'));
    $db->query("UPDATE traveller_payout_opening_balances SET status='applied',snapshot_booking_id=3,audit_snapshot='{\"fixture\":true}',applied_at='2026-11-13 12:00:00'");
    $db->query("INSERT INTO travellers VALUES (1,'Paid Other','2026-11-12','2026-11-13',NULL),(1458,'  Onyekachi Vitalis  ','2026-11-13','2026-11-13',NULL),(1457,'CHRIS IHEUWA','2026-11-12','2026-11-13',NULL),(4,'Upcoming','2026-11-15','2026-11-16',NULL),(5,'Historical blank arrival','2026-11-01','',NULL),
        (1459,'Vitalis Onyekachi','2026-11-10','2026-11-11',NULL),(1460,'Vitalis Onyekachi','2026-11-15','2026-11-16',NULL),(1461,'Vitalis Onyekachi','2026-11-17','2026-11-17',NULL),(1456,'Chris Iheuwa','2026-11-08','2026-11-09',NULL)");
    $db->query("INSERT INTO bookings VALUES
        (1,1,'FIX1',10.00,'GBP','completed','stripe','2026-11-13 12:00:00',NULL),
        (2,1,'FIX2',2.00,'POUNDS','completed','offline','2026-11-13 12:00:00',NULL),
        (3,1,'FIX3',3.00,'CAD','completed',NULL,'2026-11-13 12:00:00',NULL),
        (4,1458,'FIX4',20.00,'GBP','completed','stripe','2026-11-13 12:00:00',NULL),
        (5,1457,'FIX5',30.00,'GBP','completed','stripe','2026-11-13 12:00:00',NULL),
        (6,4,'FIX6',12.00,'GBP','completed','stripe','2026-11-13 12:00:00',NULL),
        (7,5,'FIX7',7.00,'DOLLARS','completed','stripe','2026-11-13 12:00:00',NULL),
        (8,1,'FIX8',100.00,'GBP','pending','stripe','2026-11-13 12:00:00',NULL),
        (9,1,'FIX9',100.00,'GBP','canceled','stripe','2026-11-13 12:00:00',NULL),
        (10,1,'FIX10',100.00,'GBP','completed','stripe','2026-11-13 12:00:00','2026-11-13 13:00:00'),
        (13,1459,'FIX13',4.00,'GBP','completed','stripe','2026-11-11 12:00:00',NULL),
        (14,1460,'FIX14',50.00,'GBP','completed','stripe','2026-11-13 12:00:00',NULL),
        (15,1461,'FIX15',60.00,'GBP','completed','stripe','2026-11-13 12:00:00',NULL),
        (16,1456,'FIX16',6.00,'GBP','completed','stripe','2026-11-09 12:00:00',NULL)");
    $db->query("INSERT INTO traveller_payouts (operation_key,traveller_id,traveller_name,currency,amount,source,recorded_at,note) VALUES ('existing',1,'Paid Other','GBP',4,'manual','2026-11-13 12:00:00','Existing partial settlement')");
    $db->query("INSERT INTO traveller_payout_items (payout_id,booking_id,booking_reference,traveller_id,currency,commission_snapshot,amount) VALUES (1,1,'FIX1',1,'GBP',10,4)");
    $sql=str_replace('USE `u543551821_sharemybag`;', 'USE `' . $database . '`;', file_get_contents($root . '/database/029_arrivals_payout_reconciliation.sql'));
    $opening=production_payout_opening($db);
    $before=production_payout_fingerprint($db);
    $originalHeader=$db->query('SELECT * FROM traveller_payouts WHERE id=1')->fetch_assoc();
    $originalItem=$db->query('SELECT * FROM traveller_payout_items WHERE id=1')->fetch_assoc();
    production_payout_clock($db,'2026-11-14 12:00:00');
    $failed=function($expected) use ($db,$sql) {
        try { production_payout_execute($db,$sql); throw new RuntimeException('Expected failure '.$expected); }
        catch (mysqli_sql_exception $error) { production_payout_check(strpos($error->getMessage(),$expected)!==false,$error->getMessage()); }
        production_payout_check((int)$db->query('SELECT COUNT(*) n FROM traveller_payouts')->fetch_assoc()['n']===1,'Failure leaves existing payout headers only.');
        production_payout_check((int)$db->query('SELECT COUNT(*) n FROM traveller_payout_items')->fetch_assoc()['n']===1,'Failure leaves existing allocations only.');
        production_payout_check((int)$db->query('SELECT COUNT(*) n FROM traveller_payout_reconciliations')->fetch_assoc()['n']===0,'Failure rolls back reconciliation marker.');
    };
    $db->query("UPDATE travellers SET fullname='Wrong identity' WHERE id=1457");
    $failed('CHRIS_TRIP_1457_REQUIRES_REVIEW');
    $db->query("UPDATE travellers SET fullname='CHRIS IHEUWA' WHERE id=1457");
    $db->query("UPDATE travellers SET arrival_date='2026-11-12' WHERE id=1457");
    $failed('CHRIS_TRIP_1457_REQUIRES_REVIEW');
    $db->query("UPDATE travellers SET arrival_date='2026-11-13' WHERE id=1457");
    $db->query('UPDATE travellers SET id=2 WHERE id=1458');
    $failed('VITALIS_TRIP_1458_REQUIRES_REVIEW');
    $db->query('UPDATE travellers SET id=1458 WHERE id=2');
    $db->query("UPDATE travellers SET travel_date='2026-11-12' WHERE id=1458");
    $failed('VITALIS_TRIP_1458_REQUIRES_REVIEW');
    $db->query("UPDATE travellers SET travel_date='2026-11-13' WHERE id=1458");
    $db->query("UPDATE travellers SET arrival_date='2026-11-20' WHERE id=1");
    $failed('ARRIVAL_DATE_REQUIRES_REVIEW');
    $db->query("UPDATE travellers SET arrival_date='2026-11-13' WHERE id=1");
    $db->query("UPDATE travellers SET arrival_date='2026-02-30' WHERE id=1");
    $failed('ARRIVAL_DATE_REQUIRES_REVIEW');
    $db->query("UPDATE travellers SET arrival_date='2026-11-13' WHERE id=1");
    production_payout_clock($db,'2026-11-13 12:00:00');
    $failed('HISTORICAL_SNAPSHOT_REQUIRED');
    production_payout_clock($db,'2026-11-15 12:00:00');
    $failed('HISTORICAL_SNAPSHOT_REQUIRED');
    production_payout_clock($db,'2026-11-14 12:00:00');
    $db->query("UPDATE bookings SET payment_method='' WHERE id=3");
    $failed('COMMISSION_OR_PAYMENT_METHOD_REQUIRES_REVIEW');
    $db->query('UPDATE bookings SET payment_method=NULL WHERE id=3');
    $db->query('UPDATE bookings SET traveller_commission=0 WHERE id=5');
    $failed('EXCEPTION_ALREADY_PAID_REQUIRES_ACCOUNTING_REVIEW');
    $db->query('UPDATE bookings SET traveller_commission=30 WHERE id=5');
    $db->query('CREATE UNIQUE INDEX fixture_fail_allocations ON traveller_payout_items (booking_id)');
    $failed('Duplicate entry');
    $db->query('DROP INDEX fixture_fail_allocations ON traveller_payout_items');
    production_payout_execute($db,$sql);
    $marker=$db->query('SELECT * FROM traveller_payout_reconciliations')->fetch_assoc();
    $audit=json_decode($marker['audit_snapshot'],true);
    production_payout_check($marker['status']==='applied' && $marker['snapshot_booking_id']==='16','Frozen highwater marker commits.');
    production_payout_check($audit['paid_booking_ids']===[1,2,3,7,13,16] && $audit['excluded_booking_ids']===[4,5],'Only the two selected trips stay unpaid; older same-name trips are settled.');
    production_payout_check($audit['excluded_traveller_ids']===[1458,1457] && $audit['blank_arrival_booking_ids']===[7],'Exact trip IDs and explicit historical blank-arrival basis audited.');
    $totals=[];
    foreach ($db->query("SELECT currency,SUM(amount) amount FROM traveller_payouts WHERE source='owner_reconciliation' GROUP BY currency")->fetch_all(MYSQLI_ASSOC) as $row) $totals[$row['currency']]=$row['amount'];
    production_payout_check($totals===['CAD'=>'10.00','GBP'=>'18.00'],'Only remaining exact GBP/CAD amounts appended; previous partial allocation subtracted.');
    production_payout_check($db->query('SELECT * FROM traveller_payouts WHERE id=1')->fetch_assoc()===$originalHeader,'Previous header immutable.');
    production_payout_check($db->query('SELECT * FROM traveller_payout_items WHERE id=1')->fetch_assoc()===$originalItem,'Previous allocation immutable.');
    production_payout_check(production_payout_opening($db)===$opening,'Opening audit unchanged.');
    production_payout_check(production_payout_fingerprint($db)===$before,'Operational source rows unchanged.');
    production_payout_check((int)$db->query('SELECT version FROM migrations')->fetch_assoc()['version']===28,'Not an application migration; version remains28.');
    production_payout_check((int)$db->query("SELECT COUNT(*) n FROM traveller_payouts WHERE admin_id IS NOT NULL OR paid_at IS NOT NULL")->fetch_assoc()['n']===0,'No invented staff identity or bank date.');
    production_payout_check((int)$db->query('SELECT COUNT(*) n FROM traveller_payout_items WHERE booking_id IN(4,5,6,8,9,10,14,15)')->fetch_assoc()['n']===0,'Exceptions/upcoming same-name trips/noncompleted/deleted never paid.');
    $allocations=$db->query('SELECT * FROM traveller_payout_items ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    $db->query("INSERT INTO bookings VALUES (12,1,'LATER',5.00,'GBP','completed','stripe','2026-11-15 12:00:00',NULL)");
    $db->query("UPDATE bookings SET payment_status='completed' WHERE id=8");
    production_payout_clock($db,'2026-11-16 12:00:00');
    production_payout_execute($db,$sql);
    production_payout_check($db->query('SELECT * FROM traveller_payout_reconciliations')->fetch_assoc()===$marker,'Replay preserves original audit.');
    production_payout_check($db->query('SELECT * FROM traveller_payout_items ORDER BY id')->fetch_all(MYSQLI_ASSOC)===$allocations,'Replay never settles new or newly completed booking.');
    // Force an overlapping manual settlement after the routine has established its
    // repeatable-read snapshot but before it takes the booking locks. The production
    // allocation SUM must be a current locking read, not the earlier snapshot.
    $db->query("DELETE i FROM traveller_payout_items i INNER JOIN traveller_payouts p ON p.id=i.payout_id WHERE p.source='owner_reconciliation'");
    $db->query("DELETE FROM traveller_payouts WHERE source='owner_reconciliation'");
    $db->query('DELETE FROM traveller_payout_reconciliations');
    $db->query('DELETE FROM bookings WHERE id=12');
    $db->query("UPDATE bookings SET payment_status='pending' WHERE id=8");
    production_payout_clock($db,'2026-11-14 12:00:00');
    $lockSuffix=bin2hex(random_bytes(6));
    $pause='smb029pause'.$lockSuffix;
    $ready='smb029ready'.$lockSuffix;
    $control=production_payout_connection($database);
    $control->query("SELECT GET_LOCK('$pause',10)")->free();
    $raceSql=str_replace('    OPEN booking_rows;',"    DO GET_LOCK('$ready',10);\n    DO GET_LOCK('$pause',10);\n    DO RELEASE_LOCK('$pause');\n    DO RELEASE_LOCK('$ready');\n    OPEN booking_rows;",$sql);
    $routineSql=substr($raceSql,0,strpos($raceSql,'CALL smb_029_reconcile_arrival_payouts();'));
    production_payout_execute($db,$routineSql);
    $db->query('CALL smb_029_reconcile_arrival_payouts()',MYSQLI_ASYNC);
    $deadline=microtime(true)+5;
    do {
        $readyOwner=$control->query("SELECT IS_USED_LOCK('$ready') owner")->fetch_assoc()['owner'];
        if ($readyOwner!==null) break;
        usleep(10000);
    } while(microtime(true)<$deadline);
    production_payout_check($readyOwner!==null,'Routine reached race checkpoint after establishing its old snapshot.');
    $control->query("INSERT INTO traveller_payouts (operation_key,traveller_id,traveller_name,currency,amount,source,recorded_at,note) VALUES ('overlapping-manual',1,'Paid Other','GBP',2,'manual','2026-11-14 12:00:00','Concurrent confirmed manual settlement')");
    $manualId=$control->insert_id;
    $control->query("INSERT INTO traveller_payout_items (payout_id,booking_id,booking_reference,traveller_id,currency,commission_snapshot,amount) VALUES ($manualId,1,'FIX1',1,'GBP',10,2)");
    $control->query("SELECT RELEASE_LOCK('$pause')")->free();
    $result=$db->reap_async_query();
    if ($result instanceof mysqli_result) $result->free();
    while ($db->more_results()) { $db->next_result(); $result=$db->store_result(); if ($result instanceof mysqli_result) $result->free(); }
    production_payout_check($db->query('SELECT SUM(amount) amount FROM traveller_payout_items WHERE booking_id=1')->fetch_assoc()['amount']==='10.00','Current-read allocation honors the overlapping manual settlement without double paying.');
    $db->query('DROP PROCEDURE smb_029_reconcile_arrival_payouts');
    $control->close();
    echo "PASS: arrival payout reconciliation identity guards, remaining allocations, exceptions, source immutability, mixed collations, replay, rollback and concurrent manual settlement.\n";
} finally {
    $admin->query('DROP DATABASE `' . $database . '`');
    $admin->close();
}
