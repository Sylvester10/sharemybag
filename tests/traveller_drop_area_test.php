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

$migration = $root . '/application/migrations/015_add_traveller_drop_areas.php';
if (!file_exists($migration)) {
    fwrite(STDERR, "FAIL: drop area migration is missing.\n");
    exit(1);
}

assert_file_contains_text($migration, "'drop_area1'", 'Migration should add drop_area1.');
assert_file_contains_text($migration, "'drop_area2'", 'Migration should add drop_area2.');
assert_file_contains_text($migration, "'after' => 'drop_address1'", 'drop_area1 should sit after first drop-off address.');
assert_file_contains_text($migration, "'after' => 'drop_address2'", 'drop_area2 should sit after last drop-off address.');

foreach ([
    $root . '/application/views/admin/travellers/add_traveller.php',
    $root . '/application/views/admin/travellers/update_traveller.php',
    $root . '/application/views/admin/travellers/recycle_traveller.php',
] as $form_file) {
    assert_file_contains_text($form_file, 'name="drop_area1"', 'Admin traveller forms should collect first drop-off area.');
    assert_file_contains_text($form_file, 'name="drop_area2"', 'Admin traveller forms should collect last drop-off area.');
    assert_file_contains_text($form_file, 'First Drop Off Area', 'Admin traveller forms should label the first drop-off area clearly.');
    assert_file_contains_text($form_file, 'Last Drop Off Area', 'Admin traveller forms should label the last drop-off area clearly.');
}

$admin_controller = $root . '/application/controllers/Admin_travellers.php';
assert_file_contains_text($admin_controller, "set_rules('drop_area1'", 'Admin traveller validation should accept drop_area1.');
assert_file_contains_text($admin_controller, "set_rules('drop_area2'", 'Admin traveller validation should accept drop_area2.');

$travellers_model = $root . '/application/models/Travellers_model.php';
assert_file_contains_text($travellers_model, "\$data['drop_area1']", 'Traveller model should persist drop_area1.');
assert_file_contains_text($travellers_model, "\$data['drop_area2']", 'Traveller model should persist drop_area2.');

fwrite(STDOUT, "PASS: traveller drop-off area fields are wired through admin forms and persistence.\n");
