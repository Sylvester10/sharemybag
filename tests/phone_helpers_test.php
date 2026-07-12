<?php

define('BASEPATH', dirname(__DIR__));
require dirname(__DIR__) . '/application/helpers/app_helper.php';

function assert_same_value($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("FAIL: %s Expected %s, got %s.\n", $message, var_export($expected, true), var_export($actual, true)));
        exit(1);
    }
}

assert_same_value('+44', phone_country_code_normalize('+44'), 'UK country code should stay normalized.');
assert_same_value('+1268', phone_country_code_normalize('+1-268'), 'Hyphenated country codes should normalize for storage.');
assert_same_value('', phone_country_code_normalize(''), 'Empty country code should stay empty.');

assert_same_value('+447911123456', normalize_phone_number('+44', '07911 123456'), 'UK local trunk prefix should be removed before storing.');
assert_same_value('+2348011140017', normalize_phone_number('+234', '08011140017'), 'Nigeria local trunk prefix should be removed before storing.');
assert_same_value('+447911123456', normalize_phone_number('+44', '+44 7911 123456'), 'Already international numbers should not duplicate the country code.');
assert_same_value('+447911123456', normalize_phone_number('+44', '0044 7911 123456'), 'International 00 prefix should normalize to plus format.');
assert_same_value('07911123456', normalize_phone_number('', '07911 123456'), 'Numbers without a country code should not get a guessed prefix or lose local digits.');

$uk_phone = split_phone_number('+447911123456');
assert_same_value('+44', $uk_phone['country_code'], 'Split should detect UK country code.');
assert_same_value('7911123456', $uk_phone['local_number'], 'Split should extract UK local number.');
assert_same_value('+447911123456', $uk_phone['full_number'], 'Split should preserve normalized full number.');

$ng_phone = split_phone_number('2348011140017');
assert_same_value('+234', $ng_phone['country_code'], 'Split should detect Nigeria from stored digits.');
assert_same_value('8011140017', $ng_phone['local_number'], 'Split should extract Nigeria local number.');

$empty_phone = split_phone_number('', '+234');
assert_same_value('+234', $empty_phone['country_code'], 'Empty split should use provided default country code.');
assert_same_value('', $empty_phone['local_number'], 'Empty split should have no local number.');

$options = phone_country_options();
$option_names = array_keys($options);
assert_same_value('United Kingdom', $option_names[0], 'Phone options should prioritize United Kingdom.');
assert_same_value('Nigeria', $option_names[1], 'Phone options should prioritize Nigeria.');
assert_same_value('Canada', $option_names[2], 'Phone options should prioritize Canada.');
assert_same_value(3, count($option_names), 'Phone options should only include the currently supported countries.');
assert_same_value(false, isset($options['United States']), 'Phone options should not include unsupported countries by default.');

fwrite(STDOUT, "PASS: phone helpers normalize and split phone numbers consistently.\n");
