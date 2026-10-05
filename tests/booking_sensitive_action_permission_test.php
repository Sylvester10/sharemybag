<?php

$root = dirname(__DIR__);

function sensitive_action_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$controller = file_get_contents($root . '/application/controllers/Admin_bookings.php');

foreach (array('remove_parcel_ajax', 'cancel_booking', 'delete_booking') as $method) {
    $pattern = '/public function ' . preg_quote($method, '/') . '\([^)]*\)\s*\{\s*\$this->admin_role_restricted\(\[\'super_admin\'\]\);/s';
    sensitive_action_assert(
        preg_match($pattern, $controller) === 1,
        "Admin_bookings::{$method} must reject non-super-admin callers."
    );
}

sensitive_action_assert(
    strpos($controller, "in_array(\$bulk_action_type, array('cancel', 'delete'), true)") !== false,
    'Sensitive bulk actions must be identified on the server.'
);
sensitive_action_assert(
    strpos($controller, "\$this->admin_role_restricted(array('super_admin'));") !== false,
    'Sensitive bulk actions must invoke the Super Admin guard.'
);
sensitive_action_assert(
    strpos($controller, "payment_status_normalize(\$booking->payment_status) === 'canceled'") !== false,
    'Reconfirming a cancelled booking must be rejected.'
);

foreach (array('Bookings_ajax.php', 'Completed_bookings_ajax.php', 'Canceled_bookings_ajax.php') as $file) {
    $contents = file_get_contents($root . '/application/models/ajax/bookings/' . $file);
    sensitive_action_assert(
        strpos($contents, '$isSuperAdmin') !== false,
        "{$file} must conditionally render sensitive booking actions."
    );
}

foreach (array(
    '/application/views/admin/bookings/all_bookings.php',
    '/application/views/admin/bookings/completed_bookings.php',
    '/application/views/admin/travellers/traveller_profile.php',
    '/application/views/admin/users/user_profile.php',
) as $relativePath) {
    $contents = file_get_contents($root . $relativePath);
    sensitive_action_assert(
        strpos($contents, '$is_super_admin') !== false,
        basename($relativePath) . ' must hide sensitive controls from other roles.'
    );
}

fwrite(STDOUT, "PASS: sensitive booking actions are restricted to Super Admin.\n");
