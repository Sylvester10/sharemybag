<?php

$root = dirname(__DIR__);

function arrivals_lifecycle_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$model = file_get_contents($root . '/application/models/ajax/travellers/Arrivals_ajax.php');
$controller = file_get_contents($root . '/application/controllers/Shipping.php');
$view = file_get_contents($root . '/application/views/admin/travellers/arrivals.php');
$profile = file_get_contents($root . '/application/views/admin/travellers/arrival_profile.php');
$helper = file_get_contents($root . '/application/helpers/app_helper.php');
$javascript = file_get_contents($root . '/assets/admin/custom/js/admin_script.js');

arrivals_lifecycle_assert(strpos($model, "join('shipping_records'") !== false, 'Arrivals must calculate lifecycle from booking shipping records.');
arrivals_lifecycle_assert(strpos($model, 'arrival_lifecycle') !== false, 'Arrivals must expose a calculated lifecycle state.');
arrivals_lifecycle_assert(strpos($model, 'Needs Shipping') !== false, 'Needs Shipping state is required.');
arrivals_lifecycle_assert(strpos($model, 'Partially Arranged') !== false, 'Partially Arranged state is required.');
arrivals_lifecycle_assert(strpos($model, 'Fully Arranged') !== false, 'Fully Arranged state is required.');
arrivals_lifecycle_assert(strpos($model, 'Cleared') !== false, 'Cleared state is required.');
arrivals_lifecycle_assert(strpos($view, 'arrivals_lifecycle_filter') !== false, 'Arrivals must provide a lifecycle filter including cleared records.');
arrivals_lifecycle_assert(strpos($javascript, 'd.lifecycle') !== false, 'The lifecycle filter must be sent to the Arrivals endpoint.');
arrivals_lifecycle_assert(strpos($view, "'label' => 'Arrival Lifecycle'") === false, 'The simplified Arrivals table must not display a lifecycle column.');
arrivals_lifecycle_assert(strpos($helper, 'function arrival_lifecycle_badge') !== false, 'Lifecycle states must use a shared badge presenter.');
arrivals_lifecycle_assert(strpos($profile, "'admin/travellers/traveller_profile'") !== false, 'Arrivals must reuse the existing traveler profile.');

fwrite(STDOUT, "PASS: Arrivals lifecycle contracts are present.\n");
