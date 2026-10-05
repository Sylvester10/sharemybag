<?php

define('BASEPATH', __DIR__);
require_once dirname(__DIR__) . '/application/helpers/app_helper.php';

function assert_same_value($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, "FAIL: {$message}\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

assert_same_value(
    array('Awaiting Collection', 'In Transit', 'Completed'),
    shipping_status_creation_options(),
    'New shipping records should offer all three approved statuses.'
);
assert_same_value(
    array('In Transit', 'Completed'),
    shipping_status_next_options('Awaiting Collection'),
    'Awaiting Collection should only move forward to In Transit or Completed.'
);
assert_same_value(
    array('Completed'),
    shipping_status_next_options('In Transit'),
    'In Transit should only move forward to Completed.'
);
assert_same_value(array(), shipping_status_next_options('Completed'), 'Completed should be terminal.');

assert_same_value(true, shipping_status_transition_allowed('Awaiting Collection', 'In Transit'), 'Awaiting Collection to In Transit should be allowed.');
assert_same_value(true, shipping_status_transition_allowed('Awaiting Collection', 'Completed'), 'Awaiting Collection may be completed directly.');
assert_same_value(true, shipping_status_transition_allowed('In Transit', 'Completed'), 'In Transit to Completed should be allowed.');
assert_same_value(false, shipping_status_transition_allowed('In Transit', 'Awaiting Collection'), 'Shipping status must not move backwards.');
assert_same_value(false, shipping_status_transition_allowed('Completed', 'In Transit'), 'Completed shipping must remain terminal.');

assert_same_value('Shipment Created', shipping_status_to_booking_delivery('Awaiting Collection'), 'Awaiting Collection should map safely to the booking delivery enum.');
assert_same_value('In Transit', shipping_status_to_booking_delivery('In Transit'), 'In Transit should map to the booking delivery enum.');
assert_same_value('Delivered', shipping_status_to_booking_delivery('Completed'), 'Completed should map to Delivered in the booking delivery enum.');

fwrite(STDOUT, "PASS: Shipping statuses are forward-only and map safely to booking delivery states.\n");
