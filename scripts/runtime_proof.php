<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable($root)->safeLoad();

$isDevelopment = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$suffix = $isDevelopment ? '_LOCAL' : '';
$host = (string) ($_ENV['DB_HOSTNAME' . $suffix] ?? 'localhost');
$username = (string) ($_ENV['DB_USERNAME' . $suffix] ?? '');
$password = (string) ($_ENV['DB_PASSWORD' . $suffix] ?? '');
$database = (string) ($_ENV['DB_DATABASE' . $suffix] ?? '');
$port = (int) ($_ENV['DB_PORT' . $suffix] ?? 3306);
$socket = (string) ($_ENV['DB_SOCKET' . $suffix] ?? '');

if ($socket === '' && $isDevelopment) {
    $xamppSocket = dirname($root, 2) . '/var/mysql/mysql.sock';
    if (file_exists($xamppSocket)) {
        $socket = $xamppSocket;
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $username, $password, $database, $port, $socket ?: null);
$db->set_charset('utf8');

$tracked = array_values(array_map(
    static fn(string $path): int => (int) basename($path),
    glob($root . '/application/migrations/*.php') ?: array()
));
sort($tracked);

$migrationResult = $db->query('SELECT version FROM migrations LIMIT 1');
$appliedVersion = (int) ($migrationResult->fetch_assoc()['version'] ?? 0);

$schemaRows = array();
$columns = $db->query(
    "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE,
            COALESCE(COLUMN_DEFAULT, '<NULL>') AS COLUMN_DEFAULT, COLUMN_KEY, EXTRA
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "'
     ORDER BY TABLE_NAME, ORDINAL_POSITION"
);
while ($row = $columns->fetch_assoc()) {
    $schemaRows[] = $row;
}

$indexes = $db->query(
    "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME
     FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "'
     ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX"
);
while ($row = $indexes->fetch_assoc()) {
    $schemaRows[] = $row;
}

$required = array(
    "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "' AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'can_manage_shipping'",
    "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "' AND TABLE_NAME = 'shipping_records' AND COLUMN_NAME = 'carrier_tracking_id'",
    "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'verified_phone_e164'",
    "SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "' AND TABLE_NAME IN ('auth_login_challenges', 'auth_settings')",
    "SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "' AND TABLE_NAME = 'booking_action_logs'",
    "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $db->real_escape_string($database) . "' AND TABLE_NAME = 'bookings' AND COLUMN_NAME IN ('cancelled_at', 'cancelled_by_admin_id', 'cancellation_reason', 'refund_status', 'refund_reference', 'refund_amount')",
);

$checks = array();
foreach ($required as $index => $sql) {
    $result = $db->query($sql)->fetch_assoc();
    $checks[] = (int) ($result['total'] ?? 0);
}

$latestTracked = empty($tracked) ? 0 : max($tracked);
$invariantQueries = array(
    'invalid_audit_actions' => "SELECT COUNT(*) AS total FROM booking_action_logs WHERE action NOT IN ('cancel', 'move')",
    'invalid_move_audits' => "SELECT COUNT(*) AS total FROM booking_action_logs WHERE action = 'move' AND (from_traveller_id IS NULL OR to_traveller_id IS NULL OR from_traveller_id = to_traveller_id)",
    'invalid_cancel_audits' => "SELECT COUNT(*) AS total FROM booking_action_logs WHERE action = 'cancel' AND (from_traveller_id IS NULL OR refund_status NOT IN ('pending', 'refunded', 'not_required'))",
    'orphan_audit_bookings' => 'SELECT COUNT(*) AS total FROM booking_action_logs logs LEFT JOIN bookings ON bookings.id = logs.booking_id WHERE bookings.id IS NULL',
    'invalid_controlled_cancellations' => "SELECT COUNT(*) AS total FROM bookings WHERE payment_status = 'canceled' AND cancelled_at IS NOT NULL AND (cancelled_by_admin_id IS NULL OR TRIM(COALESCE(cancellation_reason, '')) = '' OR refund_status NOT IN ('pending', 'refunded', 'not_required'))",
    'invalid_shipping_statuses' => "SELECT COUNT(*) AS total FROM shipping_records WHERE status NOT IN ('Awaiting Collection', 'In Transit', 'Completed')",
    'duplicate_shipping_bookings' => 'SELECT COUNT(*) AS total FROM (SELECT booking_id FROM shipping_records GROUP BY booking_id HAVING COUNT(*) > 1) duplicates',
    'shipping_on_inactive_bookings' => "SELECT COUNT(*) AS total FROM shipping_records INNER JOIN bookings ON bookings.id = shipping_records.booking_id WHERE bookings.payment_status <> 'completed' OR bookings.deleted_at IS NOT NULL",
);

$invariants = array();
foreach ($invariantQueries as $name => $sql) {
    $row = $db->query($sql)->fetch_assoc();
    $invariants[$name] = (int) ($row['total'] ?? 0);
}

$lifecycleRows = $db->query("
    SELECT arrival_lifecycle, COUNT(*) AS total
    FROM (
        SELECT travellers.id,
            CASE
                WHEN COUNT(DISTINCT CASE WHEN shipping_records.id IS NOT NULL THEN bookings.id END) = 0 THEN 'Needs Shipping'
                WHEN COUNT(DISTINCT CASE WHEN shipping_records.id IS NOT NULL THEN bookings.id END) < COUNT(DISTINCT bookings.id) THEN 'Partially Arranged'
                WHEN (
                    COUNT(DISTINCT CASE WHEN shipping_records.status = 'In Transit' THEN bookings.id END)
                    + COUNT(DISTINCT CASE WHEN shipping_records.status = 'Completed' THEN bookings.id END)
                ) = COUNT(DISTINCT bookings.id) THEN 'Cleared'
                ELSE 'Fully Arranged'
            END AS arrival_lifecycle
        FROM travellers
        INNER JOIN bookings ON bookings.traveller_id = travellers.id
        LEFT JOIN shipping_records ON shipping_records.booking_id = bookings.id
        WHERE travellers.deleted_at IS NULL
          AND bookings.deleted_at IS NULL
          AND travellers.travel_date < CURDATE()
          AND bookings.payment_status = 'completed'
        GROUP BY travellers.id
    ) lifecycle
    GROUP BY arrival_lifecycle
");
$lifecycleCounts = array(
    'Needs Shipping' => 0,
    'Partially Arranged' => 0,
    'Fully Arranged' => 0,
    'Cleared' => 0,
);
while ($row = $lifecycleRows->fetch_assoc()) {
    if (array_key_exists($row['arrival_lifecycle'], $lifecycleCounts)) {
        $lifecycleCounts[$row['arrival_lifecycle']] = (int) $row['total'];
    }
}

$passed = $appliedVersion === $latestTracked
    && $checks[0] === 1
    && $checks[1] === 1
    && $checks[2] === 1
    && $checks[3] === 2
    && $checks[4] === 1
    && $checks[5] === 6
    && array_sum($invariants) === 0;

$report = array(
    'generated_at' => gmdate('c'),
    'environment' => $isDevelopment ? 'development' : 'production',
    'database' => $database,
    'tracked_migrations' => $tracked,
    'applied_migration_version' => $appliedVersion,
    'schema_fingerprint_sha256' => hash('sha256', json_encode($schemaRows, JSON_UNESCAPED_SLASHES)),
    'required_schema_checks' => array(
        'shipping_permission_column' => $checks[0] === 1,
        'carrier_tracking_column' => $checks[1] === 1,
        'verified_phone_column' => $checks[2] === 1,
        'authentication_tables' => $checks[3] === 2,
        'booking_action_audit_table' => $checks[4] === 1,
        'booking_cancellation_columns' => $checks[5] === 6,
    ),
    'parcel_workflow_invariants' => $invariants,
    'arrival_lifecycle_counts' => $lifecycleCounts,
    'open_recovery_findings' => array(),
    'passed' => $passed,
);

$evidenceDir = $root . '/evidence/verification';
if (!is_dir($evidenceDir) && !mkdir($evidenceDir, 0775, true) && !is_dir($evidenceDir)) {
    throw new RuntimeException('Unable to create the verification evidence directory.');
}

file_put_contents(
    $evidenceDir . '/migration-state.json',
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

echo $passed ? "Runtime database proof passed.\n" : "Runtime database proof failed.\n";
exit($passed ? 0 : 1);
