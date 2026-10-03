<?php
define('BASEPATH', __DIR__);
class MY_Model { public $shipping_read_model; }
require dirname(__DIR__) . '/application/helpers/app_helper.php';
require dirname(__DIR__) . '/application/models/Shipping_model.php';

function check_arrival_default($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
}
$class = new ReflectionClass(Shipping_model::class);
$model = $class->newInstanceWithoutConstructor();
$model->shipping_read_model = new class {
    public $requested_id;
    public $address = 'First drop-off address';
    public function get_booking_shipping_context($id) {
        $this->requested_id = $id;
        return (object) array('traveller_pickup_address' => $this->address, 'traveller_pickup_country' => 'Nigeria');
    }
};
$booking = (object) array('id' => 41, 'tracking_id' => 'BOOKING-41', 'agent_address' => 'Wrong agent address',
    'receiver_address' => 'Receiver address', 'receiver_locality' => 'Lagos', 'receiver_postcode' => '100001');
$defaults = $class->getMethod('buildShippingDefaults');
$defaults->setAccessible(true);
$data = $defaults->invoke($model, $booking);
check_arrival_default($model->shipping_read_model->requested_id === 41, 'Context must come from the selected booking.');
check_arrival_default($data['pickup_address'] === 'First drop-off address', 'Pickup must use the first drop-off address, not the agent address.');
check_arrival_default($data['dropoff_address'] === 'Receiver address, Lagos, 100001', 'Receiver details must remain booking-specific.');

$prepare = $class->getMethod('prepareShippingRecordData');
$prepare->setAccessible(true);
$data = $prepare->invoke($model, $booking, array('pickup_address' => 'Wrong submitted address', 'courier' => 'DHL'));
check_arrival_default($data['pickup_address'] === 'First drop-off address', 'New shipping must save the canonical traveler pickup address.');
$saved = (object) array('pickup_address' => 'Previously saved pickup', 'staff_name' => 'Admin');
$data = $prepare->invoke($model, $booking, array('courier' => 'DHL'), $saved);
check_arrival_default($data['pickup_address'] === 'Previously saved pickup', 'Existing shipping must retain its saved pickup address.');
$model->shipping_read_model->address = '';
$data = $defaults->invoke($model, $booking);
check_arrival_default($data['pickup_address'] === '', 'A missing first drop-off address must not silently use the agent address.');
echo "PASS: arrival shipping uses the selected booking and first drop-off address, preserving existing shipments.\n";
