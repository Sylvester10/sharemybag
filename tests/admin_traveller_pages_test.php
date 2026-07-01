<?php

$root = dirname(__DIR__);

function assert_file_contains($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

function assert_file_not_contains($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

$admin_header = $root . '/application/views/admin/layout/header.php';
$admin_travellers = $root . '/application/controllers/Admin_travellers.php';
$all_travellers_model = $root . '/application/models/ajax/travellers/Approved_travellers_ajax.php';

assert_file_contains($admin_header, 'Upcoming Travellers', 'Traveller menu should keep Upcoming Travellers.');
assert_file_contains($admin_header, 'All Travellers', 'Traveller menu should relabel the approved page as All Travellers.');
assert_file_contains($admin_header, 'Pending Travellers', 'Traveller menu should keep Pending Travellers.');
assert_file_not_contains($admin_header, 'Unapproved Travellers', 'Traveller menu should not expose Unapproved Travellers as a separate page.');
assert_file_not_contains($admin_header, "admin_travellers/unapproved_travellers", 'Traveller menu should remove the unapproved route link.');

$controller_contents = file_get_contents($admin_travellers);
$helper_usage_count = substr_count($controller_contents, 'traveller_itinerary_table_link($y->itinerary_photo');
if ($helper_usage_count < 3) {
    fwrite(STDERR, "FAIL: Upcoming, All, and Pending traveller tables should use the new-tab itinerary helper. Found {$helper_usage_count} usages.\n");
    exit(1);
}

assert_file_contains($all_travellers_model, "\$this->db->where_in('travellers.status', array('Approved', 'Unapproved'));", 'All Travellers should include approved and unapproved travellers.');
assert_file_not_contains($all_travellers_model, "\$this->db->where('travellers.status', 'Approved');", 'All Travellers should not be approved-only.');
assert_file_not_contains($all_travellers_model, "\$this->db->where('travellers.travel_date <', date('Y-m-d'));", 'All Travellers should not be limited to past approved travellers.');

assert_file_contains($admin_travellers, "\$inner_page_title = 'All Travellers';", 'Admin page title should show All Travellers.');
assert_file_contains($admin_travellers, "redirect('admin_travellers/approved_travellers');", 'The old unapproved travellers page should redirect to All Travellers.');

fwrite(STDOUT, "PASS: admin traveller pages use the expected statuses, menu, and itinerary links.\n");
