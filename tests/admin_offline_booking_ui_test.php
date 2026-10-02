<?php

$root = dirname(__DIR__);
$upcoming = file_get_contents($root . '/application/models/ajax/travellers/Upcoming_travellers_ajax.php');
$approved = file_get_contents($root . '/application/models/ajax/travellers/Approved_travellers_ajax.php');
$css = file_get_contents($root . '/assets/admin/custom/css/custom.css');

function offline_ui_assert_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

foreach (array('Upcoming Travellers' => $upcoming, 'Approved Travellers' => $approved) as $surface => $contents) {
    offline_ui_assert_contains($contents, 'admin-offline-booking-modal', "{$surface} should use the scoped offline-booking modal styling.");
    offline_ui_assert_contains($contents, 'admin-offline-booking-form', "{$surface} should identify the offline-booking form.");
    offline_ui_assert_contains($contents, 'admin-offline-booking-section-title', "{$surface} should use consistent section headings.");
    offline_ui_assert_contains($contents, 'admin-offline-booking-autofill', "{$surface} should style autofill controls consistently.");
}

offline_ui_assert_contains($css, '.admin-offline-booking-modal .smb-phone-input', 'Offline phone controls should have a scoped horizontal layout.');
offline_ui_assert_contains($css, 'display: flex !important;', 'Offline phone controls should override the conflicting input-group display.');
offline_ui_assert_contains($css, '.admin-offline-booking-modal .smb-phone-input__country', 'The country-code field should have a controlled width.');
offline_ui_assert_contains($css, '.admin-offline-booking-modal .smb-phone-input__number', 'The phone-number field should fill the remaining width.');
offline_ui_assert_contains($css, '@media only screen and (max-width: 767px)', 'Offline booking UI should remain responsive.');

fwrite(STDOUT, "PASS: offline booking modal uses a consistent responsive form layout.\n");
