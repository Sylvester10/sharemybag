-- Additive payout ledger. No booking amounts, payment statuses or traveller records are changed.
-- Historical payment dates are unknown: paid_at stays NULL for opening-balance entries.
CREATE TABLE IF NOT EXISTS traveller_payout_opening_balances (
    id TINYINT UNSIGNED NOT NULL,
    opening_date DATE NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pending',
    snapshot_booking_id INT(11) DEFAULT NULL,
    audit_snapshot LONGTEXT DEFAULT NULL,
    applied_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO traveller_payout_opening_balances (id, opening_date, status)
VALUES (1, '2026-11-13', 'pending');

CREATE TABLE IF NOT EXISTS traveller_payouts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    operation_key VARCHAR(191) NOT NULL,
    opening_balance_id TINYINT UNSIGNED DEFAULT NULL,
    traveller_id INT(11) NOT NULL,
    traveller_name VARCHAR(100) NOT NULL,
    currency CHAR(3) NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    source VARCHAR(30) NOT NULL,
    admin_id INT(11) DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_traveller_payout_operation (operation_key),
    KEY idx_traveller_payout_history (traveller_id, recorded_at),
    KEY idx_traveller_payout_opening (opening_balance_id),
    KEY idx_traveller_payout_currency (currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS traveller_payout_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    payout_id BIGINT UNSIGNED NOT NULL,
    booking_id INT(11) NOT NULL,
    booking_reference VARCHAR(50) DEFAULT NULL,
    traveller_id INT(11) NOT NULL,
    currency CHAR(3) NOT NULL,
    commission_snapshot DECIMAL(12,2) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_traveller_payout_booking (payout_id, booking_id),
    KEY idx_traveller_payout_allocation (booking_id, traveller_id, currency),
    KEY idx_traveller_payout_item_history (traveller_id, payout_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
