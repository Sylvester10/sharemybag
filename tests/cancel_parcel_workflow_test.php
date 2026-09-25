<?php

$root = dirname(__DIR__);

function cancel_workflow_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$controller = file_get_contents($root . '/application/controllers/Admin_bookings.php');
$model = file_get_contents($root . '/application/models/Bookings_model.php');
$modal = file_get_contents($root . '/application/views/admin/bookings/modal/cancel_parcel.php');
$bookingView = file_get_contents($root . '/application/views/admin/bookings/view_booking.php');
$script = file_get_contents($root . '/assets/admin/custom/js/admin_script.js');
$migration = file_get_contents($root . '/application/migrations/021_add_booking_cancellation_metadata.php');

cancel_workflow_assert(strpos($controller, 'public function cancel_parcel_ajax()') !== false, 'Controlled cancellation endpoint is missing.');
cancel_workflow_assert(strpos($controller, "admin_role_restricted(array('super_admin'))") !== false, 'Cancellation must be Super Admin-only.');
cancel_workflow_assert(strpos($controller, "in_array(\$bulk_action_type, array('cancel', 'delete'), true)") !== false, 'Bulk cancellation and deletion must be blocked.');
cancel_workflow_assert(strpos($model, "array('In Transit', 'Completed')") !== false, 'In Transit and Completed shipping records must block cancellation.');
cancel_workflow_assert(strpos($model, 'update_traveller_space') !== false, 'Traveller capacity must be recalculated.');
cancel_workflow_assert(strpos($model, 'booking_action_log_model->record') !== false, 'Cancellation must write an audit record.');
cancel_workflow_assert(strpos($model, "delete('shipping_records')") !== false, 'Awaiting Collection shipping must be removed from the live queue during cancellation.');
cancel_workflow_assert(strpos($model, "'shipping_record' => \$shipping ? (array) \$shipping : null") !== false, 'Removed shipping details must remain in the cancellation audit snapshot.');
cancel_workflow_assert(strpos($model, "'shipping_history' => \$shippingHistory") !== false, 'Removed tracking history must remain in the cancellation audit snapshot.');
cancel_workflow_assert(strpos($model, 'trans_rollback') !== false && strpos($model, 'trans_commit') !== false, 'Cancellation and audit changes must be transactional.');

foreach (array('cancelled_at', 'cancelled_by_admin_id', 'cancellation_reason', 'refund_status', 'refund_reference', 'refund_amount') as $field) {
    cancel_workflow_assert(strpos($migration, "'{$field}'") !== false, "Migration is missing {$field}.");
}

foreach (array('cancellation_reason', 'cancel_refund_status', 'cancel_refund_amount', 'confirm_cancel_parcel') as $field) {
    cancel_workflow_assert(strpos($modal, 'id="' . $field . '"') !== false, "Cancellation modal is missing {$field}.");
}

cancel_workflow_assert(strpos($script, "url: base_url + 'admin_bookings/cancel_parcel_ajax'") !== false, 'Modal must submit to the controlled endpoint.');
cancel_workflow_assert(strpos($script, "location.reload();") !== false, 'Successful cancellation must refresh the current record view.');
cancel_workflow_assert(strpos($script, "#cancel_parcel_error") !== false, 'Cancellation failures must remain visible inside the modal.');
cancel_workflow_assert(strpos($bookingView, 'Cancellation &amp; Refund Record') !== false, 'Cancelled booking details must expose the retained refund record.');

fwrite(STDOUT, "PASS: controlled parcel cancellation workflow is structurally complete.\n");
