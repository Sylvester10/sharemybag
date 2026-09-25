<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_passwordless_authentication extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('users')) {
            $fields = array();

            if (!$this->db->field_exists('email_verified_at', 'users')) {
                $fields['email_verified_at'] = array(
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'email',
                );
            }

            if (!$this->db->field_exists('verified_phone_e164', 'users')) {
                $fields['verified_phone_e164'] = array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'number',
                );
            }

            if (!$this->db->field_exists('phone_verified_at', 'users')) {
                $fields['phone_verified_at'] = array(
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'verified_phone_e164',
                );
            }

            if (!empty($fields)) {
                $this->dbforge->add_column('users', $fields);
            }

            $this->db->query(
                "UPDATE users
                 SET email_verified_at = COALESCE(last_login, date_registered)
                 WHERE email_verified_at IS NULL
                   AND password IS NOT NULL
                   AND TRIM(password) <> ''"
            );

            if (!$this->hasIndex('users', 'uniq_users_verified_phone_e164')) {
                $this->db->query(
                    'ALTER TABLE users ADD UNIQUE INDEX uniq_users_verified_phone_e164 (verified_phone_e164)'
                );
            }
        }

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS auth_login_challenges (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                token_hash CHAR(64) NOT NULL,
                session_hash CHAR(64) NOT NULL,
                user_id INT NOT NULL,
                purpose VARCHAR(30) NOT NULL,
                delivery_channel VARCHAR(20) NOT NULL,
                destination_hash CHAR(64) NOT NULL,
                code_hash VARCHAR(255) NULL,
                attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                expires_at DATETIME NOT NULL,
                consumed_at DATETIME NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_auth_challenge_token (token_hash),
                KEY idx_auth_challenge_user_purpose (user_id, purpose),
                KEY idx_auth_challenge_expiry (expires_at),
                CONSTRAINT fk_auth_challenge_user FOREIGN KEY (user_id)
                    REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS auth_settings (
                id TINYINT UNSIGNED NOT NULL,
                phone_otp_channel VARCHAR(20) NOT NULL DEFAULT 'whatsapp',
                updated_by INT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                CONSTRAINT fk_auth_settings_admin FOREIGN KEY (updated_by)
                    REFERENCES admins(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        $this->db->query(
            "INSERT INTO auth_settings (id, phone_otp_channel)
             VALUES (1, 'whatsapp')
             ON DUPLICATE KEY UPDATE id = VALUES(id)"
        );
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS auth_login_challenges');
        $this->db->query('DROP TABLE IF EXISTS auth_settings');

        if ($this->db->table_exists('users') && $this->hasIndex('users', 'uniq_users_verified_phone_e164')) {
            $this->db->query('ALTER TABLE users DROP INDEX uniq_users_verified_phone_e164');
        }

        foreach (array('phone_verified_at', 'verified_phone_e164', 'email_verified_at') as $field) {
            if ($this->db->table_exists('users') && $this->db->field_exists($field, 'users')) {
                $this->dbforge->drop_column('users', $field);
            }
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
