<?php

$root = dirname(__DIR__);

function move_workflow_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$controller = file_get_contents($root . '/application/controllers/Admin_bookings.php');
$model = file_get_contents($root . '/application/models/Bookings_model.php');
$auditModel = file_get_contents($root . '/application/models/Booking_action_log_model.php');
$script = file_get_contents($root . '/assets/admin/custom/js/admin_script.js');
$bookingView = file_get_contents($root . '/application/views/admin/bookings/view_booking.php');
$arrivalView = file_get_contents($root . '/application/views/admin/travellers/arrival_profile.php');
$modalPath = $root . '/application/views/admin/bookings/modal/move_parcel.php';
$modal = is_file($modalPath) ? file_get_contents($modalPath) : '';

move_workflow_assert(strpos($controller, 'public function move_parcel_context_ajax(') !== false, 'Move context endpoint is missing.');
move_workflow_assert(strpos($controller, 'public function move_parcel_ajax()') !== false, 'Move submission endpoint is missing.');
move_workflow_assert(substr_count($controller, "admin_role_restricted(array('super_admin'))") >= 2, 'Both move endpoints must be Super Admin-only.');
move_workflow_assert(strpos($model, 'public function get_move_parcel_context(') !== false, 'Eligible-traveller context lookup is missing.');
move_workflow_assert(strpos($model, 'public function move_parcel(') !== false, 'Transactional move operation is missing.');
move_workflow_assert(strpos($model, 'Booking_action_log_model::ACTION_MOVE') !== false, 'Move operation must use the move audit action.');
move_workflow_assert(strpos($model, 'update_traveller_space($travellerId)') !== false, 'Both traveller capacities must be recalculated.');
move_workflow_assert(strpos($auditModel, "const ACTION_MOVE = 'move'") !== false, 'Move audit action is missing.');
move_workflow_assert(strpos($model, 'A shipping record already exists') !== false, 'Moves must be blocked once shipping is arranged.');

foreach (array('move_booking_id', 'move_target_traveller_id', 'move_parcel_reason', 'confirm_move_parcel') as $field) {
    move_workflow_assert(strpos($modal, 'id="' . $field . '"') !== false, "Move modal is missing {$field}.");
}

move_workflow_assert(strpos($script, "admin_bookings/move_parcel_context_ajax/") !== false, 'Move modal must load server-validated eligible travellers.');
move_workflow_assert(strpos($script, "admin_bookings/move_parcel_ajax") !== false, 'Move modal must submit to the controlled move endpoint.');
move_workflow_assert(strpos($bookingView, 'Parcel Move History') !== false, 'View Booking must expose the parcel move audit history.');
move_workflow_assert(strpos($arrivalView, 'open-move-parcel') !== false && strpos($arrivalView, '$is_super_admin') !== false, 'Arrivals must expose Move Parcel only to Super Admin.');

fwrite(STDOUT, "PASS: controlled parcel move workflow is structurally complete.\n");
