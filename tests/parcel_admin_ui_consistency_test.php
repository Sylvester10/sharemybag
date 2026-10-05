<?php

$root = dirname(__DIR__);

function parcel_admin_ui_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function parcel_admin_ui_position($contents, $needle, $message)
{
    $position = strpos($contents, $needle);
    parcel_admin_ui_assert($position !== false, $message);
    return $position;
}

$moveModal = file_get_contents($root . '/application/views/admin/bookings/modal/move_parcel.php');
$cancelModal = file_get_contents($root . '/application/views/admin/bookings/modal/cancel_parcel.php');
$script = file_get_contents($root . '/assets/admin/custom/js/admin_script.js');
$bookingsModel = file_get_contents($root . '/application/models/Bookings_model.php');
$allBookings = file_get_contents($root . '/application/models/ajax/bookings/Bookings_ajax.php');
$arrivalsView = file_get_contents($root . '/application/views/admin/travellers/arrivals.php');

parcel_admin_ui_assert(strpos($moveModal, 'Keep Current Traveller') === false, 'Move Parcel must use the modal X as its only dismiss action.');
parcel_admin_ui_assert(strpos($cancelModal, 'Keep Booking') === false, 'Cancel Parcel must use the modal X as its only dismiss action.');
parcel_admin_ui_assert(strpos($cancelModal, 'id="confirmCancelParcel" disabled') !== false, 'Cancel Parcel must start disabled.');

foreach (array(
    'move_target_traveller_error',
    'move_parcel_reason_error',
    'confirm_move_parcel_error',
) as $errorId) {
    parcel_admin_ui_assert(strpos($moveModal, 'id="' . $errorId . '"') !== false, "Move Parcel is missing inline error {$errorId}.");
}

foreach (array(
    'cancellation_reason_error',
    'cancel_refund_amount_error',
    'cancel_refund_reference_error',
    'confirm_cancel_parcel_error',
) as $errorId) {
    parcel_admin_ui_assert(strpos($cancelModal, 'id="' . $errorId . '"') !== false, "Cancel Parcel is missing inline error {$errorId}.");
}

parcel_admin_ui_assert(strpos($script, 'function syncCancelParcelSubmitState()') !== false, 'Cancel Parcel must keep its submit state synchronized with required fields.');
parcel_admin_ui_assert(strpos($script, "$('#cancellation_reason').val()") !== false, 'Cancel Parcel state must include its reason.');
parcel_admin_ui_assert(strpos($script, "$('#move_parcel_reason').val()") !== false, 'Move Parcel state must include its reason.');
parcel_admin_ui_assert(strpos($script, 'traveller.travel_date_label') !== false, 'Move Parcel must render the human-readable travel date supplied by the server.');
parcel_admin_ui_assert(strpos($bookingsModel, "'travel_date_label'") !== false, 'Eligible traveller data must include a formatted travel date label.');

$viewPosition = parcel_admin_ui_position($allBookings, 'View Booking', 'All Bookings must expose View Booking.');
$invoicePosition = parcel_admin_ui_position($allBookings, 'View Invoice', 'All Bookings must expose View Invoice.');
$seenPosition = parcel_admin_ui_position($allBookings, 'Mark as Seen', 'All Bookings must expose Mark as Seen when applicable.');
$movePosition = parcel_admin_ui_position($allBookings, 'Move Parcel', 'All Bookings must expose Move Parcel to Super Admin.');
$cancelPosition = parcel_admin_ui_position($allBookings, 'Cancel Parcel', 'All Bookings must expose Cancel Parcel to Super Admin.');
parcel_admin_ui_assert($viewPosition < $invoicePosition && $invoicePosition < $seenPosition && $seenPosition < $movePosition && $movePosition < $cancelPosition, 'All Bookings actions are not in the approved order.');
parcel_admin_ui_assert(strpos($allBookings, "if (\$paymentStatus === 'canceled')") !== false, 'Cancelled records in All Bookings must have a dedicated View Booking-only branch.');

parcel_admin_ui_assert(strpos($arrivalsView, 'admin-lifecycle-guide') !== false, 'Arrivals lifecycle meanings must be presented in a distinct guide.');

fwrite(STDOUT, "PASS: parcel admin UI consistency contracts are present.\n");
