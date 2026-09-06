<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Update_shipping_workflow_status extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('shipping_records')) {
            return;
        }

        $this->db->query(
            "UPDATE shipping_records
             SET status = 'Awaiting Collection'
             WHERE status IS NULL
                OR TRIM(status) = ''
                OR LOWER(TRIM(status)) IN ('pending', 'booking pending', 'shipment created')"
        );

        $this->db->query(
            "ALTER TABLE shipping_records
             MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Awaiting Collection'"
        );

        if (!$this->hasIndex('shipping_records', 'uniq_shipping_records_courier_tracking')) {
            $this->db->query(
                'ALTER TABLE shipping_records ADD UNIQUE INDEX uniq_shipping_records_courier_tracking (courier, carrier_tracking_id)'
            );
        }
    }

    public function down()
    {
        if (!$this->db->table_exists('shipping_records')) {
            return;
        }

        if ($this->hasIndex('shipping_records', 'uniq_shipping_records_courier_tracking')) {
            $this->db->query('ALTER TABLE shipping_records DROP INDEX uniq_shipping_records_courier_tracking');
        }

        $this->db->query(
            "UPDATE shipping_records
             SET status = 'In Transit'
             WHERE status = 'Awaiting Collection'"
        );

        $this->db->query(
            "ALTER TABLE shipping_records
             MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'In Transit'"
        );
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
