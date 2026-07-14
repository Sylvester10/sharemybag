<?php

$root = dirname(__DIR__);
$view = file_get_contents($root . '/application/views/users/book_space.php');
$javascript = file_get_contents($root . '/assets/users/js/booking.js');
$controller = file_get_contents($root . '/application/controllers/User_bookings.php');

function assert_payment_config($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

assert_payment_config(
    strpos($view, 'id="stripe" value="stripe" checked') !== false,
    'Stripe should be selected in the booking payment UI.'
);
assert_payment_config(
    strpos($view, '<?php /* Paystack is temporarily unavailable.') !== false,
    'The Paystack payment card should remain disabled in the view.'
);
assert_payment_config(
    strpos($javascript, "|| 'paystack'") === false,
    'Paystack must not remain as a JavaScript fallback.'
);
assert_payment_config(
    substr_count($javascript, "|| 'stripe'") === 3,
    'Every booking payment fallback should use Stripe.'
);
assert_payment_config(
    strpos($controller, 'required|in_list[stripe]') !== false,
    'Server-side booking validation should accept only Stripe.'
);

fwrite(STDOUT, "PASS: booking checkout is configured for Stripe only.\n");
