<?php
require_once '../vendor/autoload.php';
require_once '../secrets.php';

\Stripe\Stripe::setApiKey($stripeSecretKey);
header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost/smb/buy-bag-space/71';  // Your domain for redirect after payment

// Get the total amount from the AJAX request
$totalAmount = $_POST['totalAmount']; // This is the amount sent from the frontend (in pounds)

// Convert the total amount to cents (Stripe uses cents, not pounds)
$totalAmountCents = round($totalAmount * 100); // Assuming it's in pounds, multiply by 100 to get pence

// Create a Stripe Checkout session
$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'gbp',  // Currency in GBP (Pounds)
            'product_data' => [
                'name' => 'Booking Payment',
            ],
            'unit_amount' => $totalAmountCents,  // Amount in cents
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/smb/buy-bag-space/71',
    'cancel_url' => 'http://localhost/smb/buy-bag-space/71',
    'automatic_tax' => ['enabled' => true],
]);

// Return the sessionId to the front-end
echo json_encode(['status' => true, 'sessionId' => $checkout_session->id]);
