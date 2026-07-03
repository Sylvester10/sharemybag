<?php

$root = dirname(__DIR__);
$controller = $root . '/application/controllers/Admin_travellers.php';
$contents = file_get_contents($controller);

function assert_contains_text($haystack, $needle, $message)
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

function assert_not_contains_text($haystack, $needle, $message)
{
    if (strpos($haystack, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\n");
        exit(1);
    }
}

assert_contains_text(
    $contents,
    'private function render_update_traveller_form($id, $submitted_data = array())',
    'Traveller edit should have a reusable renderer that can receive submitted data.'
);

assert_contains_text(
    $contents,
    '$this->apply_traveller_submitted_values($traveller_details, $submitted_data);',
    'Traveller edit should overlay submitted values before rendering.'
);

assert_contains_text(
    $contents,
    '$this->render_update_traveller_form($id, $this->input->post(NULL, TRUE));',
    'Traveller update failures should re-render with submitted POST values.'
);

assert_not_contains_text(
    $contents,
    "redirect('admin_travellers/update_traveller/' . \$id);",
    'Traveller update failure should not redirect and lose submitted form values.'
);

fwrite(STDOUT, "PASS: traveller update failures preserve submitted form data.\n");
