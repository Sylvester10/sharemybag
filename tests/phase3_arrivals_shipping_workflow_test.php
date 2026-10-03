<?php

$root = dirname(__DIR__);

function phase3_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

function phase3_not_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\n");
        exit(1);
    }
}

$arrivalsModelPath = $root . '/application/models/ajax/travellers/Arrivals_ajax.php';
$arrivalsViewPath = $root . '/application/views/admin/travellers/arrivals.php';
$arrivalProfilePath = $root . '/application/views/admin/travellers/arrival_profile.php';

foreach (array($arrivalsModelPath, $arrivalsViewPath, $arrivalProfilePath) as $requiredFile) {
    if (!file_exists($requiredFile)) {
        fwrite(STDERR, "FAIL: Required Phase 3 file is missing: {$requiredFile}\n");
        exit(1);
    }
}

$arrivalsModel = file_get_contents($arrivalsModelPath);
$arrivalsView = file_get_contents($arrivalsViewPath);
$arrivalProfile = file_get_contents($arrivalProfilePath) . file_get_contents($root . '/application/views/admin/travellers/traveller_profile.php');
$shippingController = file_get_contents($root . '/application/controllers/Shipping.php');
$shippingModel = file_get_contents($root . '/application/models/Shipping_model.php');
$shippingReadModel = file_get_contents($root . '/application/models/Shipping_read_model.php');
$shippingAjax = file_get_contents($root . '/application/models/ajax/shipping/Shipping_records_ajax.php');
$shippingModal = file_get_contents($root . '/application/views/admin/shipping/modal/manage_shipping.php');
$shippingView = file_get_contents($root . '/application/views/admin/shipping/view_shipping.php');
$adminHeader = file_get_contents($root . '/application/views/admin/layout/header.php');
$adminJs = file_get_contents($root . '/assets/admin/custom/js/admin_script.js');
$statusMigration = file_get_contents($root . '/application/migrations/017_update_shipping_workflow_status.php');

phase3_contains($arrivalsModel, "travellers.travel_date <", 'Arrivals must use the expired travel date.');
phase3_contains($arrivalsModel, "bookings.payment_status', 'completed'", 'Arrivals must require at least one completed booking.');
phase3_contains($arrivalsModel, "COUNT(DISTINCT bookings.id) AS booking_count", 'Arrivals should expose the number of completed bookings.');
phase3_contains($shippingController, 'public function arrivals()', 'Shipping-authorized staff should have an Arrivals page.');
phase3_contains($shippingController, 'public function arrival_traveller($id)', 'Arrivals should expose an eligible traveler profile.');
phase3_contains($arrivalsView, 'arrivals_travellers_table', 'Arrivals should use the established admin DataTable shell.');
phase3_contains($arrivalProfile, 'Book Shipping', 'Completed bookings should expose Book Shipping.');
phase3_contains($arrivalProfile, 'open-create-shipping', 'Book Shipping should open the existing modal directly with booking context.');
phase3_contains($adminHeader, '>Arrivals</a>', 'Arrivals should appear in the Travelers submenu.');
phase3_contains($adminJs, "'#arrivals_travellers_table'", 'Arrivals DataTable should be initialized.');

phase3_contains($shippingModal, 'id="shipping_carrier_tracking_id"', 'Carrier tracking ID should be a shipping form field.');
phase3_contains($shippingModal, "Traveler's Pickup Address", 'Pickup address should use the approved label.');
phase3_contains($shippingModal, "Receiver's Drop-off Address", 'Drop-off address should use the approved label.');
phase3_contains($shippingController, "set_rules('carrier_tracking_id'", 'Carrier tracking ID should be validated server-side.');
phase3_contains($shippingController, "method(TRUE) !== 'POST'", 'Shipping deletion must reject non-POST requests.');
phase3_contains($shippingModel, "'carrier_tracking_id'", 'Carrier tracking ID should be persisted separately from booking reference.');
phase3_contains($shippingReadModel, 'shipping_records.carrier_tracking_id', 'Carrier tracking ID should be returned in shipping context.');
phase3_contains($shippingView, 'Carrier Tracking ID', 'Shipping details should distinguish carrier tracking from booking reference.');

phase3_contains($shippingController, 'resolveShippingStaffAdminId', 'Non-super staff assignment should be enforced server-side.');
phase3_contains($shippingReadModel, 'get_staff_options($currentAdminId', 'Eligible shipping staff should be account-aware.');
phase3_contains($adminJs, 'shippingRenderStatusOptions', 'Shipping status choices should be rendered from the current state.');
phase3_contains($shippingModel, 'shipping_status_transition_allowed', 'Status transitions should be enforced in persistence code.');
phase3_contains($statusMigration, 'uniq_shipping_records_courier_tracking', 'Carrier tracking IDs should be unique within a courier.');

phase3_contains($shippingAjax, "\$this->db->from('shipping_records')", 'Shipping table should begin with manually created shipping records.');
phase3_not_contains($shippingAjax, "bookings.need_help', 'Yes'", 'Shipping table should no longer depend on the legacy Need Help flag.');

fwrite(STDOUT, "PASS: Phase 3 Arrivals and booking-linked shipping contracts are present.\n");
