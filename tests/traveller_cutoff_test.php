<?php

define('BASEPATH', dirname(__DIR__));
require dirname(__DIR__) . '/application/helpers/app_helper.php';

function assert_cutoff_value($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("FAIL: %s Expected %s, got %s.\n", $message, var_export($expected, true), var_export($actual, true)));
        exit(1);
    }
}

$london = new DateTimeZone('Europe/London');

$before_cutoff = new DateTimeImmutable('2026-07-15 17:59:59', $london);
assert_cutoff_value('2026-07-16', traveller_minimum_bookable_date($before_cutoff), 'Tomorrow should remain bookable before 6 p.m. UK time.');
assert_cutoff_value(true, traveller_is_bookable_by_cutoff('2026-07-16', $before_cutoff), 'A traveller leaving tomorrow should be listed before 6 p.m.');

$at_cutoff = new DateTimeImmutable('2026-07-15 18:00:00', $london);
assert_cutoff_value('2026-07-17', traveller_minimum_bookable_date($at_cutoff), 'Tomorrow should close at exactly 6 p.m. UK time.');
assert_cutoff_value(false, traveller_is_bookable_by_cutoff('2026-07-16', $at_cutoff), 'A traveller leaving tomorrow should not be listed at 6 p.m.');
assert_cutoff_value(true, traveller_is_bookable_by_cutoff('2026-07-17', $at_cutoff), 'Later travellers should remain bookable after the cutoff.');

$summer_utc = new DateTimeImmutable('2026-07-15 17:00:00', new DateTimeZone('UTC'));
assert_cutoff_value(false, traveller_is_bookable_by_cutoff('2026-07-16', $summer_utc), 'The cutoff should observe British Summer Time.');

$winter_utc = new DateTimeImmutable('2026-12-15 18:00:00', new DateTimeZone('UTC'));
assert_cutoff_value(false, traveller_is_bookable_by_cutoff('2026-12-16', $winter_utc), 'The cutoff should observe GMT in winter.');

assert_cutoff_value(false, traveller_is_bookable_by_cutoff('', $before_cutoff), 'Missing travel dates should not be bookable.');

fwrite(STDOUT, "PASS: traveller booking cutoff uses 6 p.m. Europe/London time.\n");
