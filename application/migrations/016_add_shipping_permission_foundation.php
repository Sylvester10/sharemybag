<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_shipping_permission_foundation extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('admins') && !$this->db->field_exists('can_manage_shipping', 'admins')) {
            $this->dbforge->add_column('admins', array(
                'can_manage_shipping' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'unsigned' => true,
                    'null' => false,
                    'default' => 0,
                    'after' => 'role',
                ),
            ));

            // Preserve the access provided by the previous role-only guard.
            $this->db->where_in('role', array('super_admin', 'customer_support'));
            $this->db->update('admins', array('can_manage_shipping' => 1));
        }

        if ($this->db->table_exists('shipping_records') && !$this->db->field_exists('carrier_tracking_id', 'shipping_records')) {
            $this->dbforge->add_column('shipping_records', array(
                'carrier_tracking_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'tracking_id',
                ),
            ));
        }

        if ($this->db->table_exists('shipping_records') && !$this->hasIndex('shipping_records', 'idx_shipping_records_carrier_tracking')) {
            $this->db->query('ALTER TABLE shipping_records ADD INDEX idx_shipping_records_carrier_tracking (carrier_tracking_id)');
        }
    }

    public function down()
    {
        if ($this->db->table_exists('shipping_records') && $this->hasIndex('shipping_records', 'idx_shipping_records_carrier_tracking')) {
            $this->db->query('ALTER TABLE shipping_records DROP INDEX idx_shipping_records_carrier_tracking');
        }

        if ($this->db->table_exists('shipping_records') && $this->db->field_exists('carrier_tracking_id', 'shipping_records')) {
            $this->dbforge->drop_column('shipping_records', 'carrier_tracking_id');
        }

        if ($this->db->table_exists('admins') && $this->db->field_exists('can_manage_shipping', 'admins')) {
            $this->dbforge->drop_column('admins', 'can_manage_shipping');
        }
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
