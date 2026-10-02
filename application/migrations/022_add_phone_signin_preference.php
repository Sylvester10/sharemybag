<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_phone_signin_preference extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('users') || $this->db->field_exists('phone_signin_enabled', 'users')) {
            return;
        }

        $this->dbforge->add_column('users', array(
            'phone_signin_enabled' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
                'after' => 'phone_verified_at',
            ),
        ));

        // Preserve phone sign-in for people who already verified their number.
        $this->db->query("UPDATE users SET phone_signin_enabled = 1
            WHERE phone_verified_at IS NOT NULL
              AND verified_phone_e164 IS NOT NULL
              AND verified_phone_e164 <> ''");
    }

    public function down()
    {
        if ($this->db->table_exists('users') && $this->db->field_exists('phone_signin_enabled', 'users')) {
            $this->dbforge->drop_column('users', 'phone_signin_enabled');
        }
    }
}
