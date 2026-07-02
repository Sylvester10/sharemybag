<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_traveller_destination_area extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('travellers')) {
            return;
        }

        if (!$this->db->field_exists('destination_area', 'travellers')) {
            $this->dbforge->add_column('travellers', array(
                'destination_area' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'arrival_state',
                ),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('destination_area', 'travellers')) {
            $this->dbforge->drop_column('travellers', 'destination_area');
        }
    }
}
