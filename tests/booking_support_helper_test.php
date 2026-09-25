<?php

define('BASEPATH', __DIR__);
define('business_phone_number', '+2348149265396');
require_once dirname(__DIR__) . '/application/helpers/app_helper.php';

$_ENV['SUPPORT_WHATSAPP_NUMBER'] = '+44 7700 900123';

$url = booking_support_whatsapp_url((object) array(
    'tracking_id' => 'SMB-ABC-123',
    'user_fullname' => 'Ada Example',
    'traveller_name' => 'Tolu Traveller',
    'traveller_current_state' => 'London',
    'traveller_destination' => 'Lagos',
    'total_amount' => 123.4,
    'currency' => 'GBP',
));

$parts = parse_url($url);
if (($parts['host'] ?? '') !== 'wa.me' || ($parts['path'] ?? '') !== '/447700900123') {
    fwrite(STDERR, "FAIL: Support URL should use the normalized configured WhatsApp number.\n");
    exit(1);
}

parse_str($parts['query'] ?? '', $query);
$message = $query['text'] ?? '';
$expectedLines = array(
    'Hi, I need help with this booking.',
    'Booking reference: SMB-ABC-123',
    'Customer: Ada Example',
    'Traveller: Tolu Traveller',
    'Route: London to Lagos',
    'Amount: £123.40',
);

foreach ($expectedLines as $line) {
    if (strpos($message, $line) === false) {
        fwrite(STDERR, "FAIL: Support message is missing: {$line}\n");
        exit(1);
    }
}

fwrite(STDOUT, "PASS: Booking support WhatsApp URL is normalized and pre-filled correctly.\n");
