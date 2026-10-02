<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_signup_resume_tokens extends CI_Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->field_exists('signup_resume_token', 'users')) {
            $fields['signup_resume_token'] = [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'verification_locked_until',
            ];
        }

        if (!$this->db->field_exists('signup_resume_expires_at', 'users')) {
            $fields['signup_resume_expires_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'signup_resume_token',
            ];
        }

        if (!empty($fields)) {
            $this->dbforge->add_column('users', $fields);
        }

        if (
            $this->db->field_exists('signup_resume_token', 'users')
            && !$this->hasIndex('users', 'users_signup_resume_token_idx')
        ) {
            $this->db->query('CREATE INDEX users_signup_resume_token_idx ON users (signup_resume_token)');
        }
    }

    public function down()
    {
        if ($this->db->field_exists('signup_resume_expires_at', 'users')) {
            $this->dbforge->drop_column('users', 'signup_resume_expires_at');
        }

        if ($this->hasIndex('users', 'users_signup_resume_token_idx')) {
            $this->db->query('DROP INDEX users_signup_resume_token_idx ON users');
        }

        if ($this->db->field_exists('signup_resume_token', 'users')) {
            $this->dbforge->drop_column('users', 'signup_resume_token');
        }
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
