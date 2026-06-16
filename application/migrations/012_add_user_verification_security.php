<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_user_verification_security extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('users')) {
            return;
        }

        $fields = array();

        if (!$this->db->field_exists('verification_code_expires_at', 'users')) {
            $fields['verification_code_expires_at'] = array(
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'verification_code',
            );
        }

        if (!$this->db->field_exists('verification_attempts', 'users')) {
            $fields['verification_attempts'] = array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
                'after' => 'verification_code_expires_at',
            );
        }

        if (!$this->db->field_exists('verification_locked_until', 'users')) {
            $fields['verification_locked_until'] = array(
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'verification_attempts',
            );
        }

        if (!empty($fields)) {
            $this->dbforge->add_column('users', $fields);
        }
    }

    public function down()
    {
        $columns = array(
            'verification_locked_until',
            'verification_attempts',
            'verification_code_expires_at',
        );

        foreach ($columns as $column) {
            if ($this->db->field_exists($column, 'users')) {
                $this->dbforge->drop_column('users', $column);
            }
        }
    }
}
