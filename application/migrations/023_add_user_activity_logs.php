<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_user_activity_logs extends CI_Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS user_activity_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            actor_type VARCHAR(10) NOT NULL,
            actor_id INT NOT NULL,
            actor_name VARCHAR(255) NOT NULL,
            event VARCHAR(40) NOT NULL,
            method VARCHAR(30) DEFAULT NULL,
            changes LONGTEXT DEFAULT NULL,
            date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user_activity_history (user_id, id),
            KEY idx_user_activity_event (user_id, event, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS user_activity_logs');
    }
}
