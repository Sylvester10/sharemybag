<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_user_phone_code_channel extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('users') || $this->db->field_exists('phone_otp_channel', 'users')) { return; }
        $legacy = $this->db->table_exists('auth_settings')
            ? $this->db->where('id', 1)->get('auth_settings')->row() : null;
        $channel = ($legacy->phone_otp_channel ?? 'sms') === 'whatsapp' ? 'whatsapp' : 'sms';
        $this->dbforge->add_column('users', array('phone_otp_channel' => array(
            'type' => 'VARCHAR', 'constraint' => 8, 'null' => false, 'default' => 'sms',
        )));
        // Keep existing delivery preferences during the move from global to personal settings.
        $this->db->update('users', array('phone_otp_channel' => $channel));
    }

    public function down()
    {
        if ($this->db->field_exists('phone_otp_channel', 'users')) {
            $this->dbforge->drop_column('users', 'phone_otp_channel');
        }
    }
}
