<?php

$root = dirname(__DIR__);

function phase2_assert_contains($contents, $needle, $message)
{
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
}

$migrationPath = $root . '/application/migrations/016_add_shipping_permission_foundation.php';
if (!file_exists($migrationPath)) {
    fwrite(STDERR, "FAIL: Phase 2 migration is missing.\n");
    exit(1);
}

$migration = file_get_contents($migrationPath);
$migrationConfig = file_get_contents($root . '/application/config/migration.php');
$controller = file_get_contents($root . '/application/core/MY_Controller.php');
$shippingController = file_get_contents($root . '/application/controllers/Shipping.php');
$adminController = file_get_contents($root . '/application/controllers/Admin.php');
$adminModel = file_get_contents($root . '/application/models/Admin_model.php');
$adminHeader = file_get_contents($root . '/application/views/admin/layout/header.php');
$addAdmin = file_get_contents($root . '/application/views/admin/admins/add_admin.php');
$editAdmin = file_get_contents($root . '/application/views/admin/admins/edit_admin.php');

phase2_assert_contains($migration, "'can_manage_shipping'", 'Migration should add account-level shipping access to admins.');
phase2_assert_contains($migration, "'carrier_tracking_id'", 'Migration should separate carrier tracking from the booking reference.');
phase2_assert_contains($migration, "where_in('role', array('super_admin', 'customer_support'))", 'Existing shipping access should be preserved during migration.');
if (!preg_match('/\$config\[\'migration_version\'\]\s*=\s*(\d+);/', $migrationConfig, $versionMatch) || (int) $versionMatch[1] < 16) {
    fwrite(STDERR, "FAIL: Migration target should include the Phase 2 migration.\n");
    exit(1);
}

phase2_assert_contains($controller, 'function admin_can_manage_shipping', 'Shipping permission should be resolved centrally.');
phase2_assert_contains($controller, 'function admin_shipping_restricted', 'Shipping routes should have a dedicated server-side guard.');
phase2_assert_contains($shippingController, '$this->admin_shipping_restricted();', 'Every Shipping controller action should use the account permission guard.');
phase2_assert_contains($adminHeader, '$ci->admin_can_manage_shipping()', 'Shipping navigation should use the same server-side permission decision.');

phase2_assert_contains($addAdmin, 'name="can_manage_shipping"', 'Super admins should be able to grant shipping access when creating staff.');
phase2_assert_contains($editAdmin, 'name="can_manage_shipping"', 'Super admins should be able to update shipping access for staff.');
phase2_assert_contains($adminModel, "'can_manage_shipping'", 'Staff shipping permission should be persisted.');

$protectedMethods = array('admins', 'add', 'add_ajax', 'edit', 'edit_ajax', 'delete');
foreach ($protectedMethods as $method) {
    $pattern = '/public function ' . preg_quote($method, '/') . '\\([^)]*\\)\\s*\\{\\s*\\$this->admin_role_restricted\\(\\[\'super_admin\'\\]\\);/s';
    if (!preg_match($pattern, $adminController)) {
        fwrite(STDERR, "FAIL: Admin::{$method} must reject direct access by non-super-admin staff.\n");
        exit(1);
    }
}

fwrite(STDOUT, "PASS: Phase 2 shipping permission and schema foundation are present.\n");
