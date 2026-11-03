<?php
// Two real connections race for the same capacity in a disposable local MySQL database.
// Never opens a gateway, sends mail, or changes the application's business tables.
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($root)->safeLoad();
if (($_ENV['APP_ENV'] ?? '') !== 'development') { throw new RuntimeException('Local development fixture only.'); }
define('BASEPATH', $root . '/system/'); define('APPPATH', $root . '/application/');
class CI_Model { public $db; }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function traveller_is_bookable_by_cutoff($date) { return substr((string)$date, 0, 10) > gmdate('Y-m-d'); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Booking_checkout_model.php';
function race_assert($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function race_db($name) {
    return DB(['dbdriver'=>'mysqli', 'hostname'=>'/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        'username'=>$_ENV['DB_USERNAME_LOCAL'], 'password'=>$_ENV['DB_PASSWORD_LOCAL'],
        'database'=>$name, 'db_debug'=>false], true);
}

if (($argv[1] ?? '') === '--worker') {
    $name = $argv[2] ?? '';
    race_assert((bool)preg_match('/^smb_checkout_proof_[a-f0-9]{16}$/', $name), 'Invalid disposable database name.');
    $id = (int)($argv[3] ?? 0); race_assert(in_array($id,[1,2],true), 'Invalid fixture booking.');
    $db = race_db($name);
    $model = (new ReflectionClass('Booking_checkout_model'))->newInstanceWithoutConstructor(); $model->db = $db;
    echo "READY\n"; fflush(STDOUT);
    race_assert(trim((string)fgets(STDIN)) === 'START', 'Missing concurrency barrier.');
    $result = $model->create_attempt((object)['id'=>$id], $id, 'paystack', 10000, 'NGN', 1.0, []);
    echo json_encode(['status'=>!empty($result['status']), 'msg'=>$result['msg'] ?? '']) . "\n";
    $db->close(); exit(0);
}

$name = 'smb_checkout_proof_' . bin2hex(random_bytes(8));
$admin = race_db(''); $created = false; $db = null; $workers = [];
try {
    race_assert($admin->query('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4'), 'Create disposable concurrency database.');
    $created = true; $db = race_db($name);
    foreach ([
        'users'=>'(id INT PRIMARY KEY,account_status INT,deleted_at DATETIME)',
        'travellers'=>'(id INT PRIMARY KEY,original_bag_space DECIMAL(12,3),used_space DECIMAL(12,3),available_space DECIMAL(12,3),bag_locked INT,status VARCHAR(24),travel_date DATETIME,deleted_at DATETIME)',
        'bookings'=>'(id INT PRIMARY KEY,user_id INT,traveller_id INT,selected_space DECIMAL(12,3),payment_status VARCHAR(24),cancelled_at DATETIME,deleted_at DATETIME)',
    ] as $table=>$columns) { race_assert($db->query('CREATE TABLE ' . $table . $columns . ' ENGINE=InnoDB'), 'Create fixture table.'); }
    $sql = preg_replace('/^--.*$/m', '', file_get_contents($root . '/database/027_booking_checkout.sql'));
    foreach (explode(';',$sql) as $statement) {
        if (trim($statement) !== '') { race_assert($db->query($statement), 'Apply actual checkout SQL to isolated database.'); }
    }
    $db->insert('travellers',['id'=>1,'original_bag_space'=>10,'used_space'=>0,'available_space'=>10,'bag_locked'=>0,
        'status'=>'Approved','travel_date'=>gmdate('Y-m-d H:i:s',time()+86400*10)]);
    $model = (new ReflectionClass('Booking_checkout_model'))->newInstanceWithoutConstructor(); $model->db=$db;
    foreach ([1,2] as $id) {
        $db->insert('users',['id'=>$id,'account_status'=>1]);
        $row=['id'=>$id,'user_id'=>$id,'traveller_id'=>1,'selected_space'=>6,'payment_status'=>'pending'];
        $db->insert('bookings',$row); race_assert($model->capture((object)$row,['items'=>'[]']), 'Capture fixture checkout.');
        $pipes=[];
        $process=proc_open([PHP_BINARY,__FILE__,'--worker',$name,(string)$id],
            [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
        race_assert(is_resource($process),'Start competing checkout process.');
        $workers[]=['process'=>$process,'pipes'=>$pipes];
        stream_set_timeout($pipes[1],15);
        race_assert(trim((string)fgets($pipes[1]))==='READY','Worker reaches concurrency barrier.');
    }
    foreach ($workers as $worker) { fwrite($worker['pipes'][0],"START\n"); fflush($worker['pipes'][0]); }
    $results=[];
    foreach ($workers as &$worker) {
        $line=fgets($worker['pipes'][1]); $result=json_decode((string)$line,true);
        race_assert(is_array($result),'Worker returns a checkout outcome.'); $results[]=$result;
        fclose($worker['pipes'][0]); fclose($worker['pipes'][1]);
        $errors=stream_get_contents($worker['pipes'][2]); fclose($worker['pipes'][2]); $worker['pipes']=[];
        $exit=proc_close($worker['process']); $worker['process']=null;
        race_assert($exit===0 && trim($errors)==='', 'Worker finishes without database/runtime errors.');
    }
    unset($worker);
    race_assert(count(array_filter($results,fn($result)=>$result['status']))===1,'Exactly one competing checkout acquires the last capacity.');
    race_assert($db->count_all('booking_payment_attempts')===1,'Rejected checkout creates no extra attempt.');
    race_assert((float)$db->get('travellers')->row()->available_space===4.0,'Ten units minus the winning six-unit hold leaves four units.');
    echo "PASS: simultaneous MySQL checkout processes serialize capacity allocation; one winner, one rejected allocation, no overbooking.\n";
} finally {
    foreach ($workers as $worker) {
        foreach ($worker['pipes'] as $pipe) { if(is_resource($pipe)){fclose($pipe);} }
        if(is_resource($worker['process'])){proc_terminate($worker['process']);proc_close($worker['process']);}
    }
    if($db){$db->close();}
    if($created){race_assert($admin->query('DROP DATABASE `' . $name . '`'),'Remove only this disposable test database.');}
    $admin->close();
}
