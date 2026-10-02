<?php

$root = dirname(__DIR__);

function arrival_workflow_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function arrival_workflow_position($contents, $needle, $message)
{
    $position = strpos($contents, $needle);
    arrival_workflow_assert($position !== false, $message);
    return $position;
}

$arrivalsAjax = file_get_contents($root . '/application/models/ajax/travellers/Arrivals_ajax.php');
$arrivalProfile = file_get_contents($root . '/application/views/admin/travellers/arrival_profile.php');
$cancelModal = file_get_contents($root . '/application/views/admin/bookings/modal/cancel_parcel.php');
$script = file_get_contents($root . '/assets/admin/custom/js/admin_script.js');
$controller = file_get_contents($root . '/application/controllers/Admin_bookings.php');
$bookingsModel = file_get_contents($root . '/application/models/Bookings_model.php');
$bookingView = file_get_contents($root . '/application/views/admin/bookings/view_booking.php');

arrival_workflow_assert(strpos($arrivalsAjax, 'View Bookings') === false, 'Arrivals must not expose a duplicate View Bookings action.');
arrival_workflow_assert(strpos($arrivalsAjax, 'View Traveller') !== false, 'Arrivals must retain View Traveller.');
arrival_workflow_assert(strpos($arrivalProfile, '<b>Additional Information:</b>') !== false, 'Arrival traveller details must display Additional Information.');

$viewPosition = arrival_workflow_position($arrivalProfile, 'View Booking', 'Arrival booking actions must include View Booking.');
$shippingPosition = arrival_workflow_position($arrivalProfile, 'Book Shipping', 'Arrival booking actions must include Book Shipping.');
$movePosition = arrival_workflow_position($arrivalProfile, 'Move Parcel', 'Arrival booking actions must include Move Parcel for Super Admin.');
$cancelPosition = arrival_workflow_position($arrivalProfile, 'Cancel Parcel', 'Arrival booking actions must include Cancel Parcel for Super Admin.');
arrival_workflow_assert(
    $viewPosition < $shippingPosition && $shippingPosition < $movePosition && $movePosition < $cancelPosition,
    'Arrival booking actions are not in the approved order.'
);
arrival_workflow_assert(strpos($arrivalProfile, 'data-success-url=') === false, 'Move Parcel must use the same current-page refresh behavior from every entry point.');

arrival_workflow_assert(strpos($cancelModal, '<option value="">Select refund status</option>') !== false, 'Cancellation must require an explicit refund-status choice.');
arrival_workflow_assert(strpos($cancelModal, '<option value="pending">') === false, 'New cancellations must not offer Pending Manual Refund.');
arrival_workflow_assert(strpos($cancelModal, '<option value="refunded">Refunded Manually</option>') !== false, 'Refunded Manually must remain available.');
arrival_workflow_assert(strpos($cancelModal, '<option value="not_required">No Refund Required</option>') !== false, 'No Refund Required must remain available.');
arrival_workflow_assert(strpos($script, "var validStatuses = ['refunded', 'not_required'];") !== false, 'Client validation must accept only the two approved refund statuses.');
arrival_workflow_assert(strpos($script, "$('#cancel_refund_status').val('');") !== false, 'Cancellation must reset to an unselected refund status.');
arrival_workflow_assert(strpos($controller, "array('refunded', 'not_required')") !== false, 'The cancellation endpoint must accept only the approved refund statuses.');
arrival_workflow_assert(strpos($bookingsModel, "array('refunded', 'not_required')") !== false, 'The cancellation transaction must enforce the approved refund statuses.');
arrival_workflow_assert(strpos($bookingView, "'pending' => 'Pending Manual Refund'") !== false, 'Historical pending refund records must remain readable.');

fwrite(STDOUT, "PASS: Arrivals traveller and cancellation workflows are consistent.\n");
