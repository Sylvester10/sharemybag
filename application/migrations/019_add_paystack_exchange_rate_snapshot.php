<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_paystack_exchange_rate_snapshot extends CI_Migration
{
    public function up()
    {
        if (
            $this->db->table_exists('bookings')
            && !$this->db->field_exists('paystack_exchange_rate', 'bookings')
        ) {
            $this->dbforge->add_column('bookings', array(
                'paystack_exchange_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '12,4',
                    'null' => true,
                    'after' => 'paystack_ref',
                ),
            ));
        }
    }

    public function down()
    {
        if (
            $this->db->table_exists('bookings')
            && $this->db->field_exists('paystack_exchange_rate', 'bookings')
        ) {
            $this->dbforge->drop_column('bookings', 'paystack_exchange_rate');
        }
    }
}
