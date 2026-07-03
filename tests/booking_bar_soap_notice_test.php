<?php

$root = dirname(__DIR__);
$book_space = $root . '/application/views/users/book_space.php';
$contents = file_get_contents($book_space);

function assert_contains_text($haystack, $needle, $message)
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

assert_contains_text($contents, 'Bar Soaps', 'Sending restrictions should list bar soaps.');
assert_contains_text($contents, 'Bar soaps are not allowed', 'Booking item details should show a bar soap warning.');
assert_contains_text($contents, 'ti-ban', 'The restriction should use an existing restriction-style icon.');

fwrite(STDOUT, "PASS: booking page shows bar soap restriction notices.\n");
