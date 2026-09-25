<?php

$root = dirname(__DIR__);

function assert_file_not_contains_text($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

foreach ([
    $root . '/application/views/admin/travellers/add_traveller.php',
    $root . '/application/views/admin/travellers/update_traveller.php',
    $root . '/application/views/admin/travellers/recycle_traveller.php',
] as $form_file) {
    assert_file_not_contains_text($form_file, 'name="drop_area1"', 'Admin traveller forms should no longer collect first drop-off area.');
    assert_file_not_contains_text($form_file, 'name="drop_area2"', 'Admin traveller forms should no longer collect last drop-off area.');
    assert_file_not_contains_text($form_file, 'First Drop Off Area', 'Admin traveller forms should not show first drop-off area labels.');
    assert_file_not_contains_text($form_file, 'Last Drop Off Area', 'Admin traveller forms should not show last drop-off area labels.');
    assert_file_not_contains_text($form_file, 'name="unwanted_items[]" required', 'Unwanted items should be optional on admin traveller forms.');
}

$admin_controller = $root . '/application/controllers/Admin_travellers.php';
assert_file_not_contains_text($admin_controller, "set_rules('drop_area1'", 'Admin traveller validation should no longer validate drop_area1.');
assert_file_not_contains_text($admin_controller, "set_rules('drop_area2'", 'Admin traveller validation should no longer validate drop_area2.');
assert_file_not_contains_text($admin_controller, "set_rules('unwanted_items[]', 'Unwanted Items', 'trim|required')", 'Unwanted items validation should be optional.');

$travellers_model = $root . '/application/models/Travellers_model.php';
assert_file_not_contains_text($travellers_model, '$drop_area1', 'Traveller model should no longer read drop_area1 from submitted forms.');
assert_file_not_contains_text($travellers_model, '$drop_area2', 'Traveller model should no longer read drop_area2 from submitted forms.');
assert_file_not_contains_text($travellers_model, "\$data['drop_area1']", 'Traveller model should no longer persist drop_area1.');
assert_file_not_contains_text($travellers_model, "\$data['drop_area2']", 'Traveller model should no longer persist drop_area2.');

$admin_script = $root . '/assets/admin/custom/js/admin_script.js';
assert_file_not_contains_text($admin_script, 'drop_area1', 'Admin traveller JavaScript should no longer auto-fill drop_area1.');
assert_file_not_contains_text($admin_script, 'drop_area2', 'Admin traveller JavaScript should no longer auto-fill drop_area2.');

fwrite(STDOUT, "PASS: retired traveller drop-off area fields remain removed and unwanted items are optional.\n");
