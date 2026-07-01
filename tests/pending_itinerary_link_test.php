<?php

define('BASEPATH', dirname(__DIR__));
define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

if (!defined('pdf_icon')) {
    define('pdf_icon', 'https://example.test/assets/pdf.png');
}

if (!defined('user_avatar')) {
    define('user_avatar', 'https://example.test/assets/avatar.png');
}

function base_url($uri = '')
{
    return 'https://example.test/' . ltrim($uri, '/');
}

require dirname(__DIR__) . '/application/helpers/app_helper.php';

function assert_contains_text($needle, $haystack, $message)
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\nHTML: {$haystack}\n");
        exit(1);
    }
}

function assert_not_contains_text($needle, $haystack, $message)
{
    if (strpos($haystack, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\nHTML: {$haystack}\n");
        exit(1);
    }
}

if (!function_exists('traveller_itinerary_table_link')) {
    fwrite(STDERR, "FAIL: traveller_itinerary_table_link helper is missing.\n");
    exit(1);
}

$itinerary_dir = FCPATH . 'assets/itinerary/';
if (!is_dir($itinerary_dir)) {
    mkdir($itinerary_dir, 0775, true);
}

file_put_contents($itinerary_dir . 'sample-ticket.jpg', 'fake image');
file_put_contents($itinerary_dir . 'sample-ticket.pdf', 'fake pdf');

$image_html = traveller_itinerary_table_link('sample-ticket.jpg', base_url('assets/itinerary/sample-ticket.jpg'), user_avatar);
assert_contains_text('target="_blank"', $image_html, 'Image itineraries should open in a new tab.');
assert_contains_text('href="https://example.test/assets/itinerary/sample-ticket.jpg"', $image_html, 'Image itinerary should link to the original file.');
assert_not_contains_text('smb-file-preview', $image_html, 'Image itinerary should not use the modal preview class.');

$pdf_html = traveller_itinerary_table_link('sample-ticket.pdf', base_url('assets/itinerary/sample-ticket.pdf'), user_avatar);
assert_contains_text('target="_blank"', $pdf_html, 'PDF itineraries should open in a new tab.');
assert_contains_text('href="https://example.test/assets/itinerary/sample-ticket.pdf"', $pdf_html, 'PDF itinerary should link to the original file.');

$missing_html = traveller_itinerary_table_link('', '', user_avatar);
assert_contains_text('<img class="avatar" src="' . user_avatar . '" />', $missing_html, 'Missing itineraries should show the default avatar.');

unlink($itinerary_dir . 'sample-ticket.jpg');
unlink($itinerary_dir . 'sample-ticket.pdf');

fwrite(STDOUT, "PASS: pending traveller itinerary links open in a new tab.\n");
