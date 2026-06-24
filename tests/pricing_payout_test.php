<?php

define('BASEPATH', dirname(__DIR__));
require dirname(__DIR__) . '/application/helpers/app_helper.php';
require dirname(__DIR__) . '/application/libraries/Booking_presenter.php';

function assert_amount($expected, $actual, $message)
{
    if (abs($expected - $actual) > 0.001) {
        fwrite(STDERR, sprintf("FAIL: %s Expected %.2f, got %.2f.\n", $message, $expected, $actual));
        exit(1);
    }
}

$pricing = array(
    'normal_payout_rate' => 5.00,
    'special_payout_rate' => 7.00,
    'premium_small_payout_rate' => 11.00,
    'premium_laptop_payout_rate' => 17.00,
);

$items = json_encode(array(
    array('category' => 'Normal', 'size' => 2),
    array('category' => 'Medication', 'size' => 1),
    array('category' => 'Documents/Small Electronics', 'size' => 3),
    array('category' => 'Laptop', 'size' => 1),
    array('category' => 'Duty Free', 'size' => 1),
));

assert_amount(
    72.00,
    booking_calculate_traveller_commission($pricing, 8, $items),
    'Each item must receive only its configured category payout.'
);

assert_amount(
    20.00,
    booking_calculate_traveller_commission($pricing, 4, null),
    'Bookings without item details must use the configured normal payout.'
);

$category_payouts = array(
    'Normal' => 5.00,
    'Duty Free' => 5.00,
    'Fish/Medicine' => 7.00,
    'Fish/Meat' => 7.00,
    'Medication' => 7.00,
    'Documents/Electronics' => 11.00,
    'Documents/Small Electronics' => 11.00,
    'Gold' => 11.00,
    'Laptop' => 17.00,
);

foreach ($category_payouts as $category => $expected_payout) {
    $single_item = json_encode(array(array('category' => $category, 'size' => 1)));
    assert_amount(
        $expected_payout,
        booking_calculate_traveller_commission($pricing, 1, $single_item),
        $category . ' must receive exactly one category payout.'
    );
}

$presenter = new Booking_presenter();
$booking = (object) array(
    'selected_space' => 1,
    'traveller_commission' => 7.00,
);
$traveller = (object) array('destination' => 'Nigeria');
$metrics = array('extra_commission' => 10.00);

assert_amount(
    7.00,
    $presenter->calculate_booking_commission($booking, $traveller, $metrics),
    'Admin booking displays must use the stored traveller commission.'
);

fwrite(STDOUT, "PASS: traveller commission uses configured category payouts.\n");
