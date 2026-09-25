<?php

define('BASEPATH', __DIR__);
require_once dirname(__DIR__) . '/application/helpers/app_helper.php';

function assert_shipping_access($expected, $admin, $message)
{
    $actual = admin_shipping_access_allowed($admin);
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

assert_shipping_access(true, (object) array(
    'role' => 'super_admin',
    'can_manage_shipping' => 0,
), 'Super Admin shipping access cannot be disabled.');

assert_shipping_access(true, (object) array(
    'role' => 'customer_support',
    'can_manage_shipping' => 1,
), 'Customer Support should be allowed when explicitly enabled.');

assert_shipping_access(false, (object) array(
    'role' => 'customer_support',
    'can_manage_shipping' => 0,
), 'Customer Support should be denied when explicitly disabled.');

assert_shipping_access(true, array(
    'role' => 'traveller_support',
    'can_manage_shipping' => 1,
), 'Traveller Support should be allowed when explicitly enabled.');

assert_shipping_access(false, array(
    'role' => 'traveller_support',
    'can_manage_shipping' => 0,
), 'Traveller Support should be denied when explicitly disabled.');

assert_shipping_access(true, (object) array(
    'role' => 'customer_support',
), 'Legacy Customer Support access should be preserved before migration 016 runs.');

assert_shipping_access(false, (object) array(
    'role' => 'traveller_support',
), 'Legacy Traveller Support should remain denied before migration 016 runs.');

assert_shipping_access(false, null, 'A missing admin account must be denied.');

assert_shipping_access(false, (object) array(
    'role' => 'unknown_role',
    'can_manage_shipping' => 1,
), 'An unknown role must remain denied even if a permission value is present.');

fwrite(STDOUT, "PASS: Admin shipping permission decisions are role-safe and account-specific.\n");
