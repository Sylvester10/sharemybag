<?php

$root = dirname(__DIR__);
$travellers_model = $root . '/application/models/Travellers_model.php';
$contents = file_get_contents($travellers_model);

function assert_contains_text($haystack, $needle, $message)
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

assert_contains_text(
    $contents,
    '$existing_traveller = $this->traveller_read_model->get_traveller_details_by_id($id);',
    'Traveller edit should read the existing status before updating.'
);

assert_contains_text(
    $contents,
    '$was_approved_traveller',
    'Traveller edit should track whether the traveller was already approved.'
);

assert_contains_text(
    $contents,
    'if (!$was_approved_traveller) {',
    'Approval email should only be sent when the traveller was not already approved.'
);

assert_contains_text(
    $contents,
    "send_email_notification(\$this, \$email, 'Update Received', \$data, 'traveller_approval_notification_email');",
    'First-time approval through traveller edit should still send the approval email.'
);

fwrite(STDOUT, "PASS: traveller approval email is not resent on approved traveller edits.\n");
