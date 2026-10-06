<?php
// Apply only the additive phone-delivery preference locally.
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development'
    || !in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', array('localhost', '127.0.0.1', '::1'), true)) {
    throw new RuntimeException('This setup script is restricted to the local development database.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($_ENV['DB_HOSTNAME_LOCAL'], $_ENV['DB_USERNAME_LOCAL'], $_ENV['DB_PASSWORD_LOCAL'],
    $_ENV['DB_DATABASE_LOCAL'], (int) ($_ENV['DB_PORT_LOCAL'] ?? 3306), '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
$fields = array();
$result = $db->query('SHOW COLUMNS FROM users');
while ($row = $result->fetch_assoc()) { $fields[] = $row['Field']; }
foreach (array('verified_phone_e164', 'phone_verified_at', 'phone_signin_enabled', 'email_verified_at') as $field) {
    if (!in_array($field, $fields, true)) { throw new RuntimeException('Required authentication schema missing: ' . $field); }
}
$result = $db->query("SHOW TABLE STATUS WHERE Name IN ('users', 'auth_login_challenges')");
$engines = array();
while ($row = $result->fetch_assoc()) { $engines[$row['Name']] = $row['Engine']; }
if (count($engines) !== 2 || count(array_filter($engines, static function ($engine) { return $engine === 'InnoDB'; })) !== 2) {
    throw new RuntimeException('Account and challenge tables must use InnoDB for atomic updates.');
}
$version = $db->query('SELECT version FROM migrations LIMIT 1')->fetch_assoc()['version'] ?? null;
if (!in_array((int) $version, array(23, 24), true)) {
    throw new RuntimeException('Apply the preceding account-history migration before this setup.');
}
$sql = file_get_contents($root . '/database/024_user_phone_code_channel.sql');
$db->multi_query($sql);
do {
    if ($result = $db->store_result()) { $result->free(); }
} while ($db->more_results() && $db->next_result());
if ((int) $version === 23) { $db->query('UPDATE migrations SET version = 24 WHERE version = 23'); }
echo "PASS: local phone delivery preference ready; migration version 24.\n";
