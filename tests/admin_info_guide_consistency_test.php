<?php

$root = dirname(__DIR__);

function admin_info_guide_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$arrivals = file_get_contents($root . '/application/views/admin/travellers/arrivals.php');
$pricing = file_get_contents($root . '/application/views/admin/pricing/index.php');
$styles = file_get_contents($root . '/assets/admin/custom/css/custom.css');

admin_info_guide_assert(strpos($arrivals, 'admin-info-guide admin-lifecycle-guide') !== false, 'Arrivals must use the shared information guide card.');
admin_info_guide_assert(strpos($arrivals, 'admin-info-guide__grid') !== false, 'Arrivals lifecycle definitions must use the shared grid.');
admin_info_guide_assert(substr_count($arrivals, 'admin-info-guide__item') === 4, 'Arrivals must show exactly four lifecycle definitions.');
admin_info_guide_assert(strpos($arrivals, '<b>Partially Arranged:</b>') !== false, 'Arrivals must retain the Partially Arranged lifecycle name.');

admin_info_guide_assert(strpos($pricing, 'admin-info-guide admin-payout-guide') !== false, 'Pricing must use the same information guide card.');
admin_info_guide_assert(strpos($pricing, 'admin-info-guide__title') !== false, 'Pricing must retain How payouts work as a card title.');
admin_info_guide_assert(strpos($pricing, '<strong>How payouts work:</strong>') === false, 'The payout guide must no longer use the old inline alert heading.');

admin_info_guide_assert(strpos($styles, '.admin-info-guide__grid') !== false, 'The shared information guide must define its grid layout.');
admin_info_guide_assert(strpos($styles, '.admin-lifecycle-guide > span') === false, 'Lifecycle definitions must not retain pill styling.');

fwrite(STDOUT, "PASS: admin information guide cards are consistent.\n");
