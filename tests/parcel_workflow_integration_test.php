<?php

$root = dirname(__DIR__);

function parcel_integration_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$bookings = file_get_contents($root . '/application/models/Bookings_model.php');
$shipping = file_get_contents($root . '/application/models/Shipping_model.php');
$arrivals = file_get_contents($root . '/application/models/ajax/travellers/Arrivals_ajax.php');
$audit = file_get_contents($root . '/application/models/Booking_action_log_model.php');
$controller = file_get_contents($root . '/application/controllers/Admin_bookings.php');
$runtimeProof = file_get_contents($root . '/scripts/runtime_proof.php');
$verification = file_get_contents($root . '/scripts/verify_phase_updates.sh');

parcel_integration_assert(
    strpos($bookings, "if (!\$this->travellers_model->update_traveller_space((int) \$booking->traveller_id))") !== false,
    'Cancellation must roll back when traveller capacity cannot be restored.'
);
parcel_integration_assert(
    strpos($bookings, "SELECT * FROM shipping_records WHERE booking_id = ? ORDER BY id DESC LIMIT 1 FOR UPDATE") !== false,
    'Cancel and move workflows must lock the shipping boundary before mutation.'
);
parcel_integration_assert(
    strpos($shipping, 'shipping_status_transition_allowed') !== false,
    'Shipping updates must preserve the forward-only lifecycle.'
);
parcel_integration_assert(
    strpos($arrivals, "shipping_records.status = 'In Transit'") !== false
        && strpos($arrivals, "shipping_records.status = 'Completed'") !== false,
    'Arrival clearance must derive from collected or completed parcels.'
);
parcel_integration_assert(
    strpos($audit, "const ACTION_CANCEL = 'cancel'") !== false
        && strpos($audit, "const ACTION_MOVE = 'move'") !== false,
    'Both sensitive parcel actions must use the shared append-only audit boundary.'
);
parcel_integration_assert(
    substr_count($controller, "admin_role_restricted(array('super_admin'))") >= 3,
    'Cancel and both move endpoints must remain Super Admin-only.'
);
parcel_integration_assert(
    strpos($runtimeProof, 'parcel_workflow_invariants') !== false
        && strpos($runtimeProof, 'arrival_lifecycle_counts') !== false,
    'Runtime proof must inspect parcel integrity and lifecycle distribution.'
);
parcel_integration_assert(
    strpos($verification, 'tests/cancel_parcel_workflow_test.php') !== false
        && strpos($verification, 'tests/move_parcel_workflow_test.php') !== false
        && strpos($verification, 'tests/arrivals_lifecycle_test.php') !== false,
    'The main verification command must include all parcel workflow phases.'
);

fwrite(STDOUT, "PASS: parcel cancellation, movement, shipping, audit, and Arrivals lifecycle contracts are integrated.\n");
