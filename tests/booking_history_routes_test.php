<?php
// Real public-route lookup/query builder with an isolated in-memory database.
error_reporting(E_ALL & ~E_DEPRECATED);
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
class MY_Model {
    public $db;
    protected $table;
    public function __construct() {}
}
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function route_check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
require BASEPATH.'database/DB.php';
require APPPATH.'models/Traveller_read_model.php';
$db = DB(['dbdriver'=>'sqlite3','database'=>':memory:','db_debug'=>false], true);
$db->query('CREATE TABLE travellers (id INTEGER PRIMARY KEY, location TEXT, destination TEXT, area TEXT, current_state TEXT, destination_area TEXT, arrival_state TEXT, email TEXT, deleted_at TEXT)');
foreach ([[1,'United Kingdom','Nigeria',null],[2,'Nigeria','Canada','2026-10-01'],[3,'Canada','Nigeria',null]] as $row) {
    $db->insert('travellers', ['id'=>$row[0],'location'=>$row[1],'destination'=>$row[2],'area'=>'Gatwick','current_state'=>'Greater London','destination_area'=>'Oshodi','arrival_state'=>'Lagos','email'=>'private@example.invalid','deleted_at'=>$row[3]]);
}
$model = new Traveller_read_model();
$model->db = $db;
$before = $db->total_queries();
$routes = $model->get_routes_by_ids([1,2,2,0,-4,999]);
route_check($db->total_queries() === $before + 1, 'All booking countries are fetched in one query.');
route_check(array_keys($routes) === [1,2], 'Only the supplied existing traveller IDs are returned, without duplicates.');
route_check($routes[1]->location === 'United Kingdom' && $routes[2]->destination === 'Canada', 'Country direction is preserved.');
route_check($routes[1]->area === 'Gatwick' && $routes[1]->current_state === 'Greater London'
    && $routes[1]->destination_area === 'Oshodi' && $routes[1]->arrival_state === 'Lagos', 'Search route fields are available without per-booking queries.');
route_check(!isset($routes[1]->email) && !isset($routes[2]->deleted_at), 'Only route metadata is selected, without contact details.');
route_check(isset($routes[2]), 'Archived travellers retain a country label in historical bookings.');
$before = $db->total_queries();
route_check($model->get_routes_by_ids([]) === [] && $db->total_queries() === $before, 'Empty history needs no route query.');
echo "PASS: batched country lookup preserves direction, archived trips and narrow fields without extra queries or unrelated travellers.\n";
