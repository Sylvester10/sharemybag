-- Apply before releasing mail-safety code. Additive; no existing user or financial rows change.
CREATE TABLE IF NOT EXISTS mail_safety_state (
    sender_key CHAR(64) NOT NULL PRIMARY KEY,
    day_bucket CHAR(10) NOT NULL DEFAULT '',
    day_used INT UNSIGNED NOT NULL DEFAULT 0,
    minute_bucket BIGINT NOT NULL DEFAULT 0,
    minute_used INT UNSIGNED NOT NULL DEFAULT 0,
    bulk_day_used INT UNSIGNED NOT NULL DEFAULT 0,
    bulk_last_at BIGINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS mail_send_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sender_key CHAR(64) NOT NULL,
    recipient_key CHAR(64) NOT NULL,
    template VARCHAR(80) NOT NULL,
    status VARCHAR(16) NOT NULL,
    created_at DATETIME NOT NULL,
    finished_at DATETIME DEFAULT NULL,
    KEY mail_sender_window (sender_key, created_at),
    KEY mail_recipient_window (sender_key, recipient_key, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS mail_bulk_queue (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(254) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    template VARCHAR(80) NOT NULL,
    actor_id INT NOT NULL,
    dedupe_key CHAR(64) NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pending',
    claimed_at DATETIME DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    last_error VARCHAR(200) DEFAULT NULL,
    UNIQUE KEY mail_bulk_dedupe (dedupe_key),
    KEY mail_bulk_status (status, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
