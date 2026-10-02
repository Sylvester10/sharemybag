<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_booking_cancellation_metadata extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('bookings')) {
            return;
        }

        $fields = array();

        if (!$this->db->field_exists('cancelled_at', 'bookings')) {
            $fields['cancelled_at'] = array('type' => 'DATETIME', 'null' => true);
        }
        if (!$this->db->field_exists('cancelled_by_admin_id', 'bookings')) {
            $fields['cancelled_by_admin_id'] = array('type' => 'INT', 'constraint' => 11, 'null' => true);
        }
        if (!$this->db->field_exists('cancellation_reason', 'bookings')) {
            $fields['cancellation_reason'] = array('type' => 'TEXT', 'null' => true);
        }
        if (!$this->db->field_exists('refund_status', 'bookings')) {
            $fields['refund_status'] = array('type' => 'VARCHAR', 'constraint' => 30, 'null' => true);
        }
        if (!$this->db->field_exists('refund_reference', 'bookings')) {
            $fields['refund_reference'] = array('type' => 'VARCHAR', 'constraint' => 191, 'null' => true);
        }
        if (!$this->db->field_exists('refund_amount', 'bookings')) {
            $fields['refund_amount'] = array('type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true);
        }

        if (!empty($fields)) {
            $this->dbforge->add_column('bookings', $fields);
        }
    }

    public function down()
    {
        foreach (array('refund_amount', 'refund_reference', 'refund_status', 'cancellation_reason', 'cancelled_by_admin_id', 'cancelled_at') as $field) {
            if ($this->db->table_exists('bookings') && $this->db->field_exists($field, 'bookings')) {
                $this->dbforge->drop_column('bookings', $field);
            }
        }
    }
}
