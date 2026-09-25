<?php

$root = dirname(__DIR__);
$view = file_get_contents($root . '/application/views/users/book_space.php');
$javascript = file_get_contents($root . '/assets/users/js/booking.js');
$controller = file_get_contents($root . '/application/controllers/User_bookings.php');

function assert_payment_option_config($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

assert_payment_option_config(
    strpos($view, 'id="paystack" value="paystack" checked') !== false,
    'Paystack should be available and selected by default.'
);
assert_payment_option_config(
    strpos($view, 'id="stripe" value="stripe"') !== false,
    'Stripe should be available in the booking payment UI.'
);
assert_payment_option_config(
    strpos($view, 'Paystack is temporarily unavailable') === false,
    'Paystack should not be disabled in the booking view.'
);
assert_payment_option_config(
    substr_count($javascript, "|| 'paystack'") === 3,
    'Every booking payment fallback should use Paystack.'
);
assert_payment_option_config(
    strpos($controller, 'required|in_list[stripe,paystack]') !== false,
    'Server-side booking validation should accept Stripe and Paystack.'
);

fwrite(STDOUT, "PASS: booking checkout offers Paystack and Stripe.\n");
