<?php

define('BASEPATH', dirname(__DIR__));
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

$field_name = 'agent_phone';
$country_code_name = 'agent_country_code';
$value = '+2348011140017';
$label = 'Agent phone';
$required = true;
$id = 'agent_phone';
$help_text = 'Enter the local phone number without the country code.';

ob_start();
require dirname(__DIR__) . '/application/views/partials/phone_input.php';
$html = ob_get_clean();

assert_contains_text('data-smb-phone-input', $html, 'Phone partial should expose the JS hook.');
assert_contains_text('name="agent_country_code"', $html, 'Phone partial should render the country code field.');
assert_contains_text('name="agent_phone"', $html, 'Phone partial should render the local phone field.');
assert_contains_text('value="+234"', $html, 'Phone partial should select the split country code.');
assert_contains_text('value="8011140017"', $html, 'Phone partial should split the local number.');
assert_contains_text('data-flag="cf-16 cf-ng"', $html, 'Phone partial should render Nigeria flag metadata.');
$nigeria_flag = html_entity_decode('&#127475;&#127468;', ENT_QUOTES, 'UTF-8');
assert_contains_text($nigeria_flag . ' +234', $html, 'Phone partial should render a compact flag and country code option label.');
assert_not_contains_text('data-smb-phone-flag', $html, 'Phone partial should not render a separate overlay flag.');
assert_not_contains_text('smb-phone-input__inline-flag', $html, 'Phone partial should not use the removed inline flag element.');
assert_not_contains_text('+234 Nigeria', $html, 'Phone partial should not show country names in the compact selector.');
assert_not_contains_text('data-country="United States"', $html, 'Phone partial should only render supported country options.');
assert_contains_text('Agent phone', $html, 'Phone partial should render the provided label.');
assert_contains_text('required', $html, 'Phone partial should support required fields.');

preg_match_all('/<option\b/', $html, $option_matches);
if (count($option_matches[0]) !== 3) {
    fwrite(STDERR, "FAIL: Phone partial should render exactly three supported country options.\nHTML: {$html}\n");
    exit(1);
}

fwrite(STDOUT, "PASS: phone input partial renders a reusable country-code phone field.\n");
