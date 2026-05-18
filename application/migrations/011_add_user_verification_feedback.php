<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_user_verification_feedback extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('users')) {
            return;
        }

        $fields = array();

        if (!$this->db->field_exists('verification_rejection_reason', 'users')) {
            $fields['verification_rejection_reason'] = array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'is_verified',
            );
        }

        if (!$this->db->field_exists('verification_rejection_note', 'users')) {
            $fields['verification_rejection_note'] = array(
                'type' => 'TEXT',
                'null' => true,
                'after' => 'verification_rejection_reason',
            );
        }

        if (!$this->db->field_exists('verification_rejected_at', 'users')) {
            $fields['verification_rejected_at'] = array(
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'verification_rejection_note',
            );
        }

        if (!$this->db->field_exists('verification_rejected_by', 'users')) {
            $fields['verification_rejected_by'] = array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'verification_rejected_at',
            );
        }

        if (!empty($fields)) {
            $this->dbforge->add_column('users', $fields);
        }
    }

    public function down()
    {
        $columns = array(
            'verification_rejected_by',
            'verification_rejected_at',
            'verification_rejection_note',
            'verification_rejection_reason',
        );

        foreach ($columns as $column) {
            if ($this->db->field_exists($column, 'users')) {
                $this->dbforge->drop_column('users', $column);
            }
        }
    }
}
