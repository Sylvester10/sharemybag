<?php
// Verifies the pasteable metadata correction in a separately created/discarded database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development' || !in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', ['localhost','127.0.0.1','::1'], true)) throw new RuntimeException('Local fixture verification only.');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function migration_record_assert($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function migration_record_connect($database) {
    return new mysqli($_ENV['DB_HOSTNAME_LOCAL'], $_ENV['DB_USERNAME_LOCAL'], $_ENV['DB_PASSWORD_LOCAL'], $database,
        (int) ($_ENV['DB_PORT_LOCAL'] ?? 3306), '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
}
function migration_record_execute(mysqli $db, $sql) {
    $last = [];
    $db->multi_query($sql);
    do {
        $result = $db->store_result();
        if ($result instanceof mysqli_result) { $last = $result->fetch_all(MYSQLI_ASSOC); $result->free(); }
        if (!$db->more_results()) break;
        $db->next_result();
    } while (true);
    return $last;
}
$database = 'smb_migration_record_' . bin2hex(random_bytes(6));
$admin = migration_record_connect($_ENV['DB_DATABASE_LOCAL']);
$admin->query('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4');
try {
    $db = migration_record_connect($database);
    $db->query('CREATE TABLE migrations (version BIGINT NOT NULL) ENGINE=InnoDB');
    $db->query('INSERT INTO migrations VALUES (23)');
    $db->query("CREATE TABLE users (id INT PRIMARY KEY, email VARCHAR(100), phone_otp_channel VARCHAR(8) NOT NULL DEFAULT 'sms', signin_code_methods TINYINT UNSIGNED NOT NULL DEFAULT 1) ENGINE=InnoDB");
    $db->query("INSERT INTO users VALUES (1,'fixture@example.invalid','whatsapp',3),(2,'another@example.invalid','sms',5)");
    $db->query('CREATE TABLE bookings (id INT PRIMARY KEY, traveller_commission DECIMAL(12,2)) ENGINE=InnoDB');
    $db->query('INSERT INTO bookings VALUES (1,12.34)');
    $db->query('CREATE TABLE travellers (id INT PRIMARY KEY, original_bag_space DECIMAL(8,2)) ENGINE=InnoDB');
    $db->query('INSERT INTO travellers VALUES (1,21.00)');
    foreach (['026_mail_safety.sql','027_booking_checkout.sql'] as $file) migration_record_execute($db,file_get_contents($root.'/database/'.$file));
    $fingerprint = function () use ($db,$database) {
        $hash = hash_init('sha256');
        foreach (['users','bookings','travellers'] as $table) foreach ($db->query('SELECT * FROM `'.$database.'`.`'.$table.'` ORDER BY id')->fetch_all(MYSQLI_ASSOC) as $row) hash_update($hash,json_encode($row));
        return hash_final($hash);
    };
    $before = $fingerprint();
    $sql = str_replace('u543551821_sharemybag',$database,file_get_contents($root.'/database/027_reconcile_manual_migration_record.sql'));
    $db->select_db('information_schema'); // Reproduce the user's current phpMyAdmin selection.
    $run = function ($version,$ready) use ($db,$sql) {
        $result = migration_record_execute($db,$sql)[0];
        migration_record_assert((int)$result['recorded_migration_version']===$version,'Unexpected migration version.');
        migration_record_assert(($result['result']==='Ready for payout SQL')===$ready,'Unexpected readiness result.');
        return $result;
    };
    $report = $run(27,true);
    foreach (['required_columns_match','required_indexes_match','required_engines_match','single_version_record'] as $key) migration_record_assert($report[$key]==1,'Expected schema guard pass: '.$key);
    migration_record_assert($fingerprint()===$before,'Only migration metadata changes; user choices and finances remain identical.');
    $run(27,true); // Repeat must be a no-op.

    $db->query('UPDATE `'.$database.'`.migrations SET version=23');
    $db->query('ALTER TABLE `'.$database.'`.booking_payment_attempts MODIFY exchange_rate DECIMAL(18,4) NOT NULL DEFAULT 1');
    migration_record_assert($run(23,false)['required_columns_match']==0,'Wrong financial precision fails closed.');
    $db->query('ALTER TABLE `'.$database.'`.booking_payment_attempts MODIFY exchange_rate DECIMAL(18,8) NOT NULL DEFAULT 1');
    $db->query('ALTER TABLE `'.$database.'`.mail_bulk_queue DROP INDEX mail_bulk_dedupe');
    $db->query('ALTER TABLE `'.$database.'`.mail_bulk_queue ADD KEY mail_bulk_dedupe (dedupe_key)');
    migration_record_assert($run(23,false)['required_indexes_match']==0,'Wrong index uniqueness fails closed.');
    $db->query('ALTER TABLE `'.$database.'`.mail_bulk_queue DROP INDEX mail_bulk_dedupe, ADD UNIQUE KEY mail_bulk_dedupe (dedupe_key)');
    $db->query('ALTER TABLE `'.$database.'`.mail_safety_state ENGINE=MyISAM');
    migration_record_assert($run(23,false)['required_engines_match']==0,'Non-transactional required tables fail closed.');
    $db->query('ALTER TABLE `'.$database.'`.mail_safety_state ENGINE=InnoDB');
    $db->query('ALTER TABLE `'.$database.'`.booking_checkouts DROP COLUMN resume_until');
    migration_record_assert($run(23,false)['required_columns_match']==0,'Missing required columns fail closed.');
    $db->query('ALTER TABLE `'.$database.'`.booking_checkouts ADD COLUMN resume_until DATETIME DEFAULT NULL');
    $run(27,true);
    $db->query('UPDATE `'.$database.'`.migrations SET version=22');
    $run(22,false);
    $db->query('UPDATE `'.$database.'`.migrations SET version=28');
    $run(28,true);
    $db->query('UPDATE `'.$database.'`.migrations SET version=23');
    $db->query('INSERT INTO `'.$database.'`.migrations VALUES (23)');
    $report=$run(23,false);
    migration_record_assert($report['single_version_record']==0,'Ambiguous migration ledger is not changed.');
    migration_record_assert($fingerprint()===$before,'Negative tests do not modify business data or user preferences.');
    echo "PASS: guarded manual 23-to-27 correction, phpMyAdmin information_schema context, complete column/type/index/engine checks, replay, version boundaries and unchanged business rows (isolated database).\n";
} finally {
    if (isset($db)) $db->close();
    $admin->query('DROP DATABASE `' . $database . '`');
    $admin->close();
}
