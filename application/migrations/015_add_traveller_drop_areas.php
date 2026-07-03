<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_traveller_drop_areas extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('travellers')) {
            return;
        }

        if (!$this->db->field_exists('drop_area1', 'travellers')) {
            $this->dbforge->add_column('travellers', array(
                'drop_area1' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'drop_address1',
                ),
            ));
        }

        if (!$this->db->field_exists('drop_area2', 'travellers')) {
            $this->dbforge->add_column('travellers', array(
                'drop_area2' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'drop_address2',
                ),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('drop_area1', 'travellers')) {
            $this->dbforge->drop_column('travellers', 'drop_area1');
        }

        if ($this->db->field_exists('drop_area2', 'travellers')) {
            $this->dbforge->drop_column('travellers', 'drop_area2');
        }
    }
}
