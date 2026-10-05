-- Additive account history schema. Run in the intended application database.
CREATE TABLE IF NOT EXISTS user_activity_logs (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
