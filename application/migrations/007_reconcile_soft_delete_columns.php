<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Reconcile Missing Soft Delete Columns
 *
 * Repairs environments where the migrations table advanced beyond the actual
 * schema and the deleted_at columns from migration 003 were never created.
 */
class Migration_Reconcile_soft_delete_columns extends CI_Migration
{
    public function up()
    {
        $this->addDeletedAtIfMissing('bookings', 'idx_bookings_deleted_at');
        $this->addDeletedAtIfMissing('travellers', 'idx_travellers_deleted_at');
        $this->addDeletedAtIfMissing('users', 'idx_users_deleted_at');
    }

    public function down()
    {
        $this->dropDeletedAtIfPresent('bookings', 'idx_bookings_deleted_at');
        $this->dropDeletedAtIfPresent('travellers', 'idx_travellers_deleted_at');
        $this->dropDeletedAtIfPresent('users', 'idx_users_deleted_at');
    }

    private function addDeletedAtIfMissing($table, $index)
    {
        if (!$this->hasColumn($table, 'deleted_at')) {
            $this->db->query("ALTER TABLE {$table} ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL");
        }

        if (!$this->hasIndex($table, $index)) {
            $this->db->query("ALTER TABLE {$table} ADD INDEX {$index} (deleted_at)");
        }
    }

    private function dropDeletedAtIfPresent($table, $index)
    {
        if ($this->hasIndex($table, $index)) {
            $this->db->query("ALTER TABLE {$table} DROP INDEX {$index}");
        }

        if ($this->hasColumn($table, 'deleted_at')) {
            $this->db->query("ALTER TABLE {$table} DROP COLUMN deleted_at");
        }
    }

    private function hasColumn($table, $column)
    {
        return in_array($column, $this->db->list_fields($table), true);
    }

    private function hasIndex($table, $indexName)
    {
        $query = $this->db->query(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            array($indexName)
        );

        return $query->num_rows() > 0;
    }
}
