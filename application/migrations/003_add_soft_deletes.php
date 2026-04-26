<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Add Soft Delete Columns
 * Phase 1 — Step 1.8 (DB-001, DB-013)
 *
 * Adds nullable deleted_at columns and indexes to the core mutable tables
 * so records can be archived instead of physically removed.
 */
class Migration_Add_soft_deletes extends CI_Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE bookings
            ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL,
            ADD INDEX idx_bookings_deleted_at (deleted_at)
        ");

        $this->db->query("
            ALTER TABLE travellers
            ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL,
            ADD INDEX idx_travellers_deleted_at (deleted_at)
        ");

        $this->db->query("
            ALTER TABLE users
            ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL,
            ADD INDEX idx_users_deleted_at (deleted_at)
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE bookings
            DROP INDEX idx_bookings_deleted_at,
            DROP COLUMN deleted_at
        ");

        $this->db->query("
            ALTER TABLE travellers
            DROP INDEX idx_travellers_deleted_at,
            DROP COLUMN deleted_at
        ");

        $this->db->query("
            ALTER TABLE users
            DROP INDEX idx_users_deleted_at,
            DROP COLUMN deleted_at
        ");
    }
}
