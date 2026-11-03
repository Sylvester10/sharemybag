-- Additive checkout recovery, immutable gateway attempts, and capacity leases.
CREATE TABLE IF NOT EXISTS booking_checkouts (
    booking_id INT NOT NULL PRIMARY KEY,
    user_id INT NOT NULL,
    request_key CHAR(64) DEFAULT NULL,
    form_data MEDIUMTEXT NOT NULL,
    resume_until DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY checkout_request (request_key),
    KEY checkout_owner (user_id, booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS booking_payment_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    traveller_id INT NOT NULL,
    provider VARCHAR(16) NOT NULL,
    reference VARCHAR(80) NOT NULL,
    provider_session_id VARCHAR(255) DEFAULT NULL,
    authorization_url TEXT DEFAULT NULL,
    status VARCHAR(24) NOT NULL,
    amount_minor BIGINT NOT NULL,
    currency CHAR(3) NOT NULL,
    exchange_rate DECIMAL(18,8) NOT NULL DEFAULT 1,
    quote_data MEDIUMTEXT NOT NULL,
    hold_space DECIMAL(12,3) NOT NULL,
    hold_until DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    confirmed_at DATETIME DEFAULT NULL,
    review_reason VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY checkout_reference (reference),
    UNIQUE KEY checkout_provider_session (provider, provider_session_id),
    KEY checkout_booking (booking_id, id),
    KEY checkout_holds (traveller_id, status, hold_until),
    KEY checkout_expiry (status, hold_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
