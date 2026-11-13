-- ShareMyBag payout deployment 028: paste this entire file into the selected live database's SQL tab.
-- Requires MySQL 5.7+/MariaDB 10.2+ and permission to create/drop a temporary deployment procedure.
-- Take a database backup first. No booking, traveller, customer or payment record is updated/deleted.
-- The owner declared all historical travellers paid except upcoming travellers and Vitalis Onyekachi.
-- The opening declaration is fixed to 13 November 2026, Africa/Lagos, not the deployment date.
-- A first run AFTER that date deliberately fails: SMB_PAYOUT_HISTORICAL_SNAPSHOT_REQUIRED.
-- A reviewed historical booking snapshot must be prepared before a delayed first deployment.
-- Do not remove that safeguard or change the cutoff to make a delayed deployment succeed.
-- Previously applied opening balances replay safely at any later date without reclassification.
-- Schema creation is additive/idempotent. Header, allocations and opening audit commit together.
-- A failed backfill may leave the empty schema installed with its opening status still pending.
-- Historical bank payment dates and staff identities are unknown and remain NULL.
-- Existing collations are preserved; internal operation keys are matched as exact bytes.

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

DROP PROCEDURE IF EXISTS smb_028_initialise_traveller_payouts;
DELIMITER $$
CREATE PROCEDURE smb_028_initialise_traveller_payouts()
payout_deployment: BEGIN
    DECLARE v_done TINYINT DEFAULT 0;
    DECLARE v_opening_status VARCHAR(16) DEFAULT NULL;
    DECLARE v_opening_date DATE DEFAULT NULL;
    DECLARE v_version INT DEFAULT 0;
    DECLARE v_snapshot_id INT DEFAULT 0;
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_booking_id INT;
    DECLARE v_traveller_id INT;
    DECLARE v_found_traveller_id INT;
    DECLARE v_reference VARCHAR(50);
    DECLARE v_name VARCHAR(100);
    DECLARE v_normalized_name VARCHAR(100);
    DECLARE v_travel_raw VARCHAR(100);
    DECLARE v_arrival_raw VARCHAR(100);
    DECLARE v_booking_raw VARCHAR(100);
    DECLARE v_commission_raw VARCHAR(100);
    DECLARE v_currency_raw VARCHAR(30);
    DECLARE v_traveller_deleted_at DATETIME;
    DECLARE v_travel_date DATE;
    DECLARE v_arrival_date DATE;
    DECLARE v_booking_date DATE;
    DECLARE v_currency CHAR(3);
    DECLARE v_state VARCHAR(16);
    DECLARE v_reason VARCHAR(191);
    DECLARE v_paid_ids LONGTEXT DEFAULT '[]';
    DECLARE v_outstanding_ids LONGTEXT DEFAULT '[]';
    DECLARE v_review_ids LONGTEXT DEFAULT '[]';
    DECLARE v_outstanding_reasons LONGTEXT DEFAULT '{}';
    DECLARE v_review_reasons LONGTEXT DEFAULT '{}';
    DECLARE v_recorded_at DATETIME;
    DECLARE booking_rows CURSOR FOR
        SELECT b.id, b.traveller_id, t.id, b.tracking_id, t.fullname,
            TRIM(COALESCE(t.travel_date, '')), TRIM(COALESCE(t.arrival_date, '')),
            TRIM(COALESCE(CAST(b.date_added AS CHAR), '')),
            TRIM(COALESCE(CAST(b.traveller_commission AS CHAR), '')),
            UPPER(TRIM(COALESCE(b.currency, ''))), t.deleted_at
        FROM bookings b
        LEFT JOIN travellers t ON t.id = b.traveller_id
        WHERE b.id <= v_snapshot_id AND b.payment_status = 'completed' AND b.deleted_at IS NULL
            AND (LOWER(COALESCE(b.payment_method, '')) IN ('paystack','stripe','offline','bank') OR b.payment_method IS NULL)
        ORDER BY b.id FOR UPDATE;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        DROP TEMPORARY TABLE IF EXISTS smb_028_payout_snapshot;
        RESIGNAL;
    END;

    SELECT COUNT(*) INTO v_count FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN
        ('bookings','travellers','traveller_payout_opening_balances','traveller_payouts','traveller_payout_items')
        AND ENGINE = 'InnoDB';
    IF v_count <> 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_INNODB_REQUIRED: source and payout tables must use InnoDB';
    END IF;

    START TRANSACTION;
    SELECT opening_date, status INTO v_opening_date, v_opening_status
    FROM traveller_payout_opening_balances WHERE id = 1 FOR UPDATE;
    IF v_opening_date IS NULL OR v_opening_date <> '2026-11-13' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_OPENING_CONFIGURATION_MISMATCH';
    END IF;
    SELECT COALESCE(MAX(version), 0) INTO v_version FROM migrations;
    IF v_version < 27 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_APPLY_PRECEDING_MIGRATIONS_THROUGH_027';
    END IF;
    IF v_opening_status = 'applied' THEN
        UPDATE migrations SET version = 28 WHERE version = 27;
        COMMIT;
        SELECT 'already_applied' AS payout_deployment_status, 'Opening balance preserved; no bookings reclassified.' AS message;
        LEAVE payout_deployment;
    END IF;
    SELECT COUNT(*) INTO v_count FROM traveller_payouts;
    IF v_opening_status <> 'pending' OR v_count <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_EXISTING_LEDGER_REQUIRES_REVIEW';
    END IF;
    IF DATE(UTC_TIMESTAMP() + INTERVAL 1 HOUR) > '2026-11-13' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_HISTORICAL_SNAPSHOT_REQUIRED: first initialization is after 13 November 2026';
    END IF;

    -- Match the declared exception without guessing a local database ID.
    -- Whitespace/case and surname-first formatting do not change the exclusion.
    SELECT COUNT(*) INTO v_count FROM travellers
    WHERE deleted_at IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(fullname, '')), ' ', ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), ''))
        IN ('vitalisonyekachi','onyekachivitalis');
    IF v_count = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_VITALIS_EXCEPTION_NOT_FOUND: confirm the unpaid traveller identity before initialization';
    END IF;

    CREATE TEMPORARY TABLE smb_028_payout_snapshot (
        booking_id INT NOT NULL PRIMARY KEY,
        booking_reference VARCHAR(50) DEFAULT NULL,
        traveller_id INT DEFAULT NULL,
        traveller_name VARCHAR(100) DEFAULT NULL,
        currency CHAR(3) DEFAULT NULL,
        commission DECIMAL(12,2) DEFAULT NULL,
        settlement_state VARCHAR(16) NOT NULL
    ) ENGINE=InnoDB;
    SELECT COALESCE(MAX(id), 0) INTO v_snapshot_id FROM bookings;
    SET v_recorded_at = UTC_TIMESTAMP();
    SET v_done = 0;
    OPEN booking_rows;
    booking_loop: LOOP
        FETCH booking_rows INTO v_booking_id, v_traveller_id, v_found_traveller_id, v_reference, v_name,
            v_travel_raw, v_arrival_raw, v_booking_raw, v_commission_raw, v_currency_raw, v_traveller_deleted_at;
        IF v_done = 1 THEN LEAVE booking_loop; END IF;

        -- Check the full optional timestamp and actual calendar day; STR_TO_DATE alone accepts some invalid days.
        SET v_travel_date = CASE WHEN v_travel_raw REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])?$'
            AND CAST(LEFT(v_travel_raw,4) AS UNSIGNED) > 0
            AND CAST(SUBSTRING(v_travel_raw,9,2) AS UNSIGNED) <= DAY(LAST_DAY(CONCAT(LEFT(v_travel_raw,7),'-01')))
            THEN CAST(LEFT(v_travel_raw,10) AS DATE) ELSE NULL END;
        SET v_arrival_date = CASE WHEN v_arrival_raw REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])?$'
            AND CAST(LEFT(v_arrival_raw,4) AS UNSIGNED) > 0
            AND CAST(SUBSTRING(v_arrival_raw,9,2) AS UNSIGNED) <= DAY(LAST_DAY(CONCAT(LEFT(v_arrival_raw,7),'-01')))
            THEN CAST(LEFT(v_arrival_raw,10) AS DATE) ELSE NULL END;
        SET v_booking_date = CASE WHEN v_booking_raw REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])?$'
            AND CAST(LEFT(v_booking_raw,4) AS UNSIGNED) > 0
            AND CAST(SUBSTRING(v_booking_raw,9,2) AS UNSIGNED) <= DAY(LAST_DAY(CONCAT(LEFT(v_booking_raw,7),'-01')))
            THEN CAST(LEFT(v_booking_raw,10) AS DATE) ELSE NULL END;
        SET v_currency = CASE WHEN v_currency_raw IN ('','GBP','POU','POUND','POUNDS') THEN 'GBP'
            WHEN v_currency_raw IN ('CAD','DOLLAR','DOLLARS') THEN 'CAD' ELSE NULL END;
        SET v_normalized_name = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(v_name,'')), ' ', ''), CHAR(9), ''), CHAR(10), ''), CHAR(13), ''));
        SET v_state = 'paid';
        SET v_reason = 'Historical settlement declared by owner.';
        IF v_currency IS NULL OR v_commission_raw NOT REGEXP '^[0-9]{1,10}([.][0-9]{1,2})?$' THEN
            SET v_state = 'review'; SET v_reason = 'Unsupported currency or invalid stored commission.';
        ELSEIF v_found_traveller_id IS NULL OR v_traveller_deleted_at IS NOT NULL OR TRIM(COALESCE(v_name,'')) = '' THEN
            SET v_state = 'review'; SET v_reason = 'Traveller is missing, archived or unnamed.';
        ELSEIF v_normalized_name IN ('vitalisonyekachi','onyekachivitalis') THEN
            SET v_state = 'outstanding'; SET v_reason = 'Vitalis Onyekachi explicitly excluded from historical paid declaration.';
        ELSEIF v_booking_date IS NULL THEN
            SET v_state = 'review'; SET v_reason = 'Booking date is missing or invalid.';
        ELSEIF v_booking_date > '2026-11-13' THEN
            SET v_state = 'outstanding'; SET v_reason = 'Booking created after the opening date.';
        ELSEIF v_travel_date IS NULL THEN
            SET v_state = 'review'; SET v_reason = 'Departure date is missing or invalid.';
        ELSEIF v_travel_date >= '2026-11-13' THEN
            SET v_state = 'outstanding'; SET v_reason = 'Upcoming on the opening date.';
        ELSEIF v_arrival_date >= '2026-11-13' THEN
            SET v_state = 'outstanding'; SET v_reason = 'Arrival on or after the opening date.';
        ELSEIF v_arrival_raw <> '' AND (v_arrival_date IS NULL OR v_arrival_date < v_travel_date) THEN
            SET v_state = 'review'; SET v_reason = 'Arrival date is invalid or inconsistent.';
        ELSEIF v_arrival_raw = '' THEN
            SET v_reason = 'Historical departure with blank arrival; owner explicitly declared all other historical travellers paid.';
        END IF;

        INSERT INTO smb_028_payout_snapshot (booking_id, booking_reference, traveller_id, traveller_name, currency, commission, settlement_state)
        VALUES (v_booking_id, v_reference, v_traveller_id, v_name, v_currency,
            CASE WHEN v_commission_raw REGEXP '^[0-9]{1,10}([.][0-9]{1,2})?$' THEN CAST(v_commission_raw AS DECIMAL(12,2)) ELSE NULL END, v_state);
        IF v_state = 'paid' THEN
            SET v_paid_ids = JSON_ARRAY_APPEND(v_paid_ids, '$', v_booking_id);
        ELSEIF v_state = 'outstanding' THEN
            SET v_outstanding_ids = JSON_ARRAY_APPEND(v_outstanding_ids, '$', v_booking_id);
            SET v_outstanding_reasons = JSON_SET(v_outstanding_reasons, CONCAT('$."',v_booking_id,'"'), v_reason);
        ELSE
            SET v_review_ids = JSON_ARRAY_APPEND(v_review_ids, '$', v_booking_id);
            SET v_review_reasons = JSON_SET(v_review_reasons, CONCAT('$."',v_booking_id,'"'), v_reason);
        END IF;
    END LOOP;
    CLOSE booking_rows;

    SELECT COUNT(*) INTO v_count FROM (
        SELECT traveller_id, currency FROM smb_028_payout_snapshot WHERE settlement_state = 'paid'
        GROUP BY traveller_id, currency HAVING SUM(commission) > 999999999999.99
    ) overflowing_groups;
    IF v_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SMB_PAYOUT_AMOUNT_EXCEEDS_LEDGER_LIMIT';
    END IF;

    INSERT INTO traveller_payouts (operation_key, opening_balance_id, traveller_id, traveller_name, currency, amount, source, admin_id, paid_at, recorded_at, note)
    SELECT CONCAT('opening:2026-11-13:',traveller_id,':',currency), 1, traveller_id, MAX(traveller_name), currency,
        SUM(commission), 'opening_balance', NULL, NULL, v_recorded_at,
        'Historical payout declared paid by owner as of 2026-11-13. Actual payment date unknown. Upcoming travellers and Vitalis Onyekachi excluded.'
    FROM smb_028_payout_snapshot WHERE settlement_state = 'paid' GROUP BY traveller_id, currency;

    INSERT INTO traveller_payout_items (payout_id, booking_id, booking_reference, traveller_id, currency, commission_snapshot, amount)
    SELECT p.id, s.booking_id, s.booking_reference, s.traveller_id, s.currency, s.commission, s.commission
    FROM smb_028_payout_snapshot s INNER JOIN traveller_payouts p
        -- Operation keys are exact ASCII identifiers, not human-language text.
        -- Compare bytes so an existing ledger/table collation cannot conflict
        -- with the database default inherited by the temporary snapshot.
        ON CAST(p.operation_key AS BINARY) = CAST(CONCAT('opening:2026-11-13:',s.traveller_id,':',s.currency) AS BINARY)
    WHERE s.settlement_state = 'paid';

    UPDATE traveller_payout_opening_balances SET status = 'applied', snapshot_booking_id = v_snapshot_id, applied_at = v_recorded_at,
        audit_snapshot = JSON_OBJECT('opening_date','2026-11-13','snapshot_booking_id',v_snapshot_id,
            'basis','Owner declared all historical travellers paid except upcoming travellers and Vitalis Onyekachi. Historical blank arrival dates may use that explicit declaration. Actual historical payment dates are unknown.',
            'excluded_traveller_name','Vitalis Onyekachi','source','production_sql',
            'paid_booking_ids',JSON_EXTRACT(v_paid_ids,'$'),
            'outstanding_booking_ids',JSON_EXTRACT(v_outstanding_ids,'$'),
            'review_booking_ids',JSON_EXTRACT(v_review_ids,'$'),
            'outstanding_reasons',JSON_EXTRACT(v_outstanding_reasons,'$'),
            'review_reasons',JSON_EXTRACT(v_review_reasons,'$'))
    WHERE id = 1;
    UPDATE migrations SET version = 28 WHERE version = 27;
    COMMIT;
    DROP TEMPORARY TABLE smb_028_payout_snapshot;
    SELECT 'applied' AS payout_deployment_status,
        JSON_LENGTH(v_paid_ids) AS historically_paid_bookings,
        JSON_LENGTH(v_outstanding_ids) AS outstanding_bookings,
        JSON_LENGTH(v_review_ids) AS bookings_requiring_review;
END$$
DELIMITER ;

CALL smb_028_initialise_traveller_payouts();
DROP PROCEDURE IF EXISTS smb_028_initialise_traveller_payouts;
