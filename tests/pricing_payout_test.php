<?php

define('BASEPATH', dirname(__DIR__));
require dirname(__DIR__) . '/application/helpers/app_helper.php';

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

    assert_amount(
        $expected_payout * 2,
        booking_category_commission_delta($pricing, $category, 2),
        'Admin parcel changes for ' . $category . ' must use only its configured payout.'
    );
}

$category_payout_fields = array(
    'Normal' => 'normal_payout_rate',
    'Duty Free' => 'normal_payout_rate',
    'Fish/Medicine' => 'special_payout_rate',
    'Fish/Meat' => 'special_payout_rate',
    'Medication' => 'special_payout_rate',
    'Documents/Electronics' => 'premium_small_payout_rate',
    'Documents/Small Electronics' => 'premium_small_payout_rate',
    'Gold' => 'premium_small_payout_rate',
    'Laptop' => 'premium_laptop_payout_rate',
);

foreach (booking_route_definition_map() as $route_key => $route_pricing) {
    foreach ($category_payout_fields as $category => $payout_field) {
        $expected_delta = $route_pricing[$payout_field] * 2;
        assert_amount(
            $expected_delta,
            booking_category_commission_delta($route_pricing, $category, 2),
            $route_key . ' admin parcel changes for ' . $category . ' must use only the matching route payout.'
        );

        $single_item = json_encode(array(array('category' => $category, 'size' => 2)));
        assert_amount(
            $expected_delta,
            booking_calculate_traveller_commission($route_pricing, 2, $single_item),
            $route_key . ' bookings for ' . $category . ' must use only the matching route payout.'
        );
    }
}

$booking = (object) array(
    'selected_space' => 1,
    'traveller_commission' => 7.00,
);

assert_amount(
    7.00,
    booking_stored_traveller_commission($booking),
    'Every admin display must use the stored traveller commission.'
);

$pricing_view = file_get_contents(dirname(__DIR__) . '/application/views/admin/pricing/index.php');
$required_pricing_labels = array(
    'Service Charge (per booking)',
    'Normal Items (per kg)',
    'Fish/Meat &amp; Medication (per kg)',
    'Duty-Free Shopping (per kg)',
    'Documents &amp; Small Electronics (per item)',
    'Laptop (per item)',
);

foreach ($required_pricing_labels as $label) {
    if (strpos($pricing_view, $label) === false) {
        fwrite(STDERR, 'FAIL: Missing pricing page label: ' . $label . PHP_EOL);
        exit(1);
    }
}

fwrite(STDOUT, "PASS: traveller commission uses configured category payouts.\n");
