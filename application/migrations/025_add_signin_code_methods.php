<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_signin_code_methods extends CI_Migration
{
    public function up()
    {
        if ($this->db->field_exists('signin_code_methods', 'users')) { return; }
        $this->dbforge->add_column('users', array('signin_code_methods' => array(
            'type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false, 'default' => 1,
        )));
        // Email already worked for every user; preserve it alongside any enabled phone method.
        $this->db->query("UPDATE users SET signin_code_methods = CASE
            WHEN phone_signin_enabled = 1 AND phone_verified_at IS NOT NULL AND verified_phone_e164 IS NOT NULL AND verified_phone_e164 <> ''
            THEN CASE WHEN phone_otp_channel = 'whatsapp' THEN 3 ELSE 5 END ELSE 1 END");
    }

    public function down()
    {
        if ($this->db->field_exists('signin_code_methods', 'users')) {
            $this->dbforge->drop_column('users', 'signin_code_methods');
        }
    }
}
