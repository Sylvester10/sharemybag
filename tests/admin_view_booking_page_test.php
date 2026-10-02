<?php

$root = dirname(__DIR__);

function view_booking_test_contents($root, $path)
{
    $fullPath = $root . '/' . $path;
    if (!is_file($fullPath)) {
        fwrite(STDERR, "FAIL: Missing {$path}.\n");
        exit(1);
    }

    return file_get_contents($fullPath);
}

function view_booking_test_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

function view_booking_test_not_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\n");
        exit(1);
    }
}

function view_booking_test_count_at_least($contents, $needle, $minimum, $message)
{
    if (substr_count($contents, $needle) < $minimum) {
        fwrite(STDERR, "FAIL: {$message}\nExpected at least {$minimum} occurrences of: {$needle}\n");
        exit(1);
    }
}

$controller = view_booking_test_contents($root, 'application/controllers/Admin_bookings.php');
$view = view_booking_test_contents($root, 'application/views/admin/bookings/view_booking.php');

view_booking_test_contains($controller, '$data[\'y\'] = $bookings_details', 'The controller must pass the selected booking to the page.');
view_booking_test_contains($controller, '$page_title = \'Booking Info: \' . $booking_reference', 'The page title must identify the booking rather than one contact.');
view_booking_test_contains($view, 'Booking Overview', 'The page must identify and summarize the selected booking.');
view_booking_test_contains($view, 'Route &amp; Traveller', 'The page must show the traveller and route information.');
view_booking_test_contains($view, 'Booking Contacts', 'The page must show the booking contact information.');
view_booking_test_contains($view, 'Parcel Details', 'The page must show every parcel attached to the booking.');
view_booking_test_contains($view, 'Payment Breakdown', 'The page must show the booking payment breakdown.');
view_booking_test_contains($view, 'json_decode($y->items)', 'The page must decode and display the booking items.');
view_booking_test_contains($view, 'window.history.back()', 'Back must return to the page the admin actually came from.');
view_booking_test_contains($view, 'admin-status-line', 'The top summary must use the established compact status treatment.');
view_booking_test_contains($view, 'delivery_status_badge($y->delivery_status)', 'The top summary must show the delivery status.');
view_booking_test_count_at_least($view, 'well profile_view', 5, 'Every booking section must use the existing traveller-profile card style.');
view_booking_test_count_at_least($view, 'col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]', 5, 'Every booking card must use the existing gray title bar.');
view_booking_test_not_contains($view, 'Booking Reference:', 'The booking reference must not be repeated below the page title.');
view_booking_test_not_contains($view, 'admin-summary-chip', 'The redundant summary chip must be removed.');
view_booking_test_not_contains($view, 'admin-panel-card', 'The page must not use the mismatched custom panel-card styling.');
view_booking_test_not_contains($view, '$shipping_details', 'The booking page must not expect shipping-record data.');
view_booking_test_not_contains($view, 'admin/bookings/modal/edit.php', 'The booking page must not load the obsolete shipping-update modal.');

fwrite(STDOUT, "PASS: admin View Booking displays the selected booking with the existing admin UI.\n");
