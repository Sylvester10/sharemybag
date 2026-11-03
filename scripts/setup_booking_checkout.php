<?php
// Local-only, additive checkout setup. Production uses the reviewed SQL/migration deployment procedure.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development'
    || !in_array($_ENV['DB_HOSTNAME_LOCAL'] ?? '', ['localhost','127.0.0.1','::1'], true)) {
    throw new RuntimeException('Restricted to the local development database.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($_ENV['DB_HOSTNAME_LOCAL'],$_ENV['DB_USERNAME_LOCAL'],$_ENV['DB_PASSWORD_LOCAL'],
    $_ENV['DB_DATABASE_LOCAL'],(int)($_ENV['DB_PORT_LOCAL'] ?? 3306),'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
$version = (int)($db->query('SELECT version FROM migrations LIMIT 1')->fetch_assoc()['version'] ?? 0);
if (($argv[1] ?? '') === '--inspect') {
    $state=['migration_version'=>$version,'tables'=>[]];
    $tables=$db->query("SHOW TABLE STATUS WHERE Name IN ('bookings','travellers','mail_safety_state','mail_send_events','mail_bulk_queue','booking_checkouts','booking_payment_attempts')");
    while($row=$tables->fetch_assoc()){$state['tables'][$row['Name']]=['engine'=>$row['Engine']];}
    echo json_encode($state,JSON_PRETTY_PRINT)."\n"; $db->close(); exit;
}
if (!in_array($version,[25,26,27],true)) { throw new RuntimeException('Apply preceding migrations through 025 first.'); }
$engines=[];
$tables=$db->query("SHOW TABLE STATUS WHERE Name IN ('bookings','travellers')");
while($row=$tables->fetch_assoc()){$engines[$row['Name']]=$row['Engine'];}
if(count($engines)!==2 || count(array_filter($engines,fn($engine)=>$engine==='InnoDB'))!==2){
    throw new RuntimeException('Bookings and travellers must use InnoDB before checkout can reserve capacity.');
}
// SQL 026 may already have been applied manually without advancing the local ledger.
$files=$version===25 ? ['026_mail_safety.sql','027_booking_checkout.sql'] : ['027_booking_checkout.sql'];
foreach($files as$file){
    $sql=preg_replace('/^--.*$/m','',file_get_contents($root.'/database/'.$file));
    foreach(explode(';',$sql)as$statement){if(trim($statement)!==''){$db->query($statement);}}
}
if($version<27){$db->query('UPDATE migrations SET version = 27 WHERE version = '.(int)$version);}
$definitions=[];
foreach(['booking_checkouts','booking_payment_attempts']as$table){
    $definitions[$table]=$db->query('SHOW CREATE TABLE '.$table)->fetch_assoc()['Create Table'];
}
$evidence=['generated_at'=>gmdate('c'),'migration_version'=>27,'tracked_sql'=>'database/027_booking_checkout.sql',
    'tracked_sql_sha256'=>hash_file('sha256',$root.'/database/027_booking_checkout.sql'),
    'schema_sha256'=>hash('sha256',json_encode($definitions)),'engines'=>$engines,
    'replayed_sql'=>$files,'business_rows_changed'=>0,'provider_calls'=>0];
$path=$root.'/evidence/booking-recovery';
if(!is_dir($path)){mkdir($path,0775,true);}
file_put_contents($path.'/migration-state.json',json_encode($evidence,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
echo "PASS: local additive checkout tables ready; migration version 27. No booking/user/traveller records changed.\n";
$db->close();
