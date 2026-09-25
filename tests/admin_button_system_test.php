<?php

$root = dirname(__DIR__);

function admin_button_assert_contains($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

function admin_button_assert_not_contains($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

$admin_buttons = $root . '/assets/admin/custom/css/style.css';
$pricing = $root . '/application/views/admin/pricing/index.php';
$authentication = $root . '/application/views/admin/settings/authentication.php';
$shipping_list = $root . '/application/views/admin/shipping/all_shipping.php';
$shipping_view = $root . '/application/views/admin/shipping/view_shipping.php';
$shipping_modal = $root . '/application/views/admin/shipping/modal/manage_shipping.php';
$parcel_modal = $root . '/application/views/admin/bookings/modal/add_remove_parcel.php';

admin_button_assert_contains($admin_buttons, '--admin-button-primary: #337ab7;', 'The admin button system must retain the existing Bootstrap admin blue.');
admin_button_assert_contains($admin_buttons, '.right_col .btn', 'The shared button system must be scoped to the admin content area.');
admin_button_assert_contains($admin_buttons, 'body.nav-md > .modal .modal_close_btn', 'The shared red modal close button must retain its dedicated sizing.');
admin_button_assert_contains($admin_buttons, '@media (max-width: 767px)', 'Admin buttons must include a mobile touch-target rule.');

admin_button_assert_contains($pricing, 'class="btn btn-primary"', 'Saving pricing must use the admin primary button.');
admin_button_assert_not_contains($pricing, 'type="submit" class="btn btn-success"', 'Ordinary pricing submission must not use the success colour.');
admin_button_assert_contains($authentication, 'type="submit" class="btn btn-primary"', 'Authentication settings must use the admin primary button.');
admin_button_assert_contains($shipping_list, 'btn btn-primary btn-lg open-create-shipping', 'Add Shipping must use the admin primary button.');
admin_button_assert_contains($shipping_view, 'btn btn-default open-edit-shipping', 'Edit Shipping must use the neutral edit action.');
admin_button_assert_contains($shipping_modal, 'btn btn-primary d-none" id="shipping_submit_btn"', 'Saving shipping must use the admin primary button.');
admin_button_assert_contains($parcel_modal, 'btn btn-primary" id="confirmAddParcel"', 'Adding a parcel must use the admin primary button.');
admin_button_assert_contains($parcel_modal, 'btn btn-danger btn-sm modal_close_btn', 'Parcel modals must retain the red X close button.');

fwrite(STDOUT, "PASS: admin buttons use the approved shared hierarchy.\n");
