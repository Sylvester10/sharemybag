<?php

$root = dirname(__DIR__);

function assert_file_contains_text($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

$migration = $root . '/application/migrations/014_add_traveller_destination_area.php';
if (!file_exists($migration)) {
    fwrite(STDERR, "FAIL: destination_area migration is missing.\n");
    exit(1);
}

assert_file_contains_text($migration, "'destination_area'", 'Migration should add destination_area to travellers.');
assert_file_contains_text($migration, "'after' => 'arrival_state'", 'destination_area should sit after final destination in the database.');

foreach ([
    $root . '/application/views/admin/travellers/update_traveller.php',
    $root . '/application/views/admin/travellers/recycle_traveller.php',
] as $form_file) {
    assert_file_contains_text($form_file, 'name="destination_area"', 'Admin traveller forms should collect destination_area.');
    assert_file_contains_text($form_file, 'Final Destination Area', 'Admin traveller forms should label the new field clearly.');
}

$admin_controller = $root . '/application/controllers/Admin_travellers.php';
assert_file_contains_text($admin_controller, "set_rules('destination_area'", 'Admin traveller validation should accept destination_area.');
assert_file_contains_text($admin_controller, 'traveller_destination_label($y->arrival_state, $y->destination,', 'Admin traveller tables should display destination_area with final destination.');

$travellers_model = $root . '/application/models/Travellers_model.php';
assert_file_contains_text($travellers_model, "\$data['destination_area']", 'Traveller model should persist destination_area.');

$home_controller = $root . '/application/controllers/Home.php';
assert_file_contains_text($home_controller, "'destination_area'", 'Homepage traveller search should return destination_area.');

$user_bookings_controller = $root . '/application/controllers/User_bookings.php';
assert_file_contains_text($user_bookings_controller, "'destination_area'", 'Logged-in traveller search should return destination_area.');

assert_file_contains_text($root . '/assets/website/js/home.js', 'response.destination_area', 'Homepage search UI should display destination_area.');
assert_file_contains_text($root . '/application/views/website/layout/header.php', 'response.destination_area', 'Header search template should display destination_area.');
assert_file_contains_text($root . '/assets/users/js/search.js', 'traveller.destination_area', 'Logged-in traveller search UI should display destination_area.');

assert_file_contains_text($root . '/application/helpers/app_helper.php', 'function traveller_destination_label', 'Destination display helper should exist.');

fwrite(STDOUT, "PASS: traveller destination area is wired through admin and customer displays.\n");
