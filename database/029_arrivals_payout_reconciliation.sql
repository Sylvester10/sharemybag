-- One-time owner reconciliation, as of 14 November 2026 (Africa/Lagos).
-- Select the live ShareMyBag database, back it up, and paste this ENTIRE file into SQL.
-- All existing Arrivals are declared previously paid EXCEPT these two 13 November trips:
-- Vitalis Onyekachi (traveller ID 1458) and Chris Iheuwa (traveller ID 1457).
-- Other trips by either person are separate records; upcoming trips are not settled.
-- No bank transfer, booking/traveller/shipping change, or prior payout rewrite is performed.
-- Remaining positive booking commission only is appended to the immutable payout ledger.
-- A first run after 14 November deliberately requires a reviewed historical snapshot instead.
-- Replays after successful application are harmless, including on later dates.
-- This optional maintenance audit table is not a runtime dependency. Migration stays 28.
USE `u543551821_sharemybag`;

CREATE TABLE IF NOT EXISTS traveller_payout_reconciliations (
    operation_key VARCHAR(100) NOT NULL,
    cutoff_date DATE NOT NULL,
    status VARCHAR(16) NOT NULL,
    snapshot_booking_id INT DEFAULT NULL,
    audit_snapshot LONGTEXT DEFAULT NULL,
    applied_at DATETIME DEFAULT NULL,
    PRIMARY KEY (operation_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP PROCEDURE IF EXISTS smb_029_reconcile_arrival_payouts;
DELIMITER $$
CREATE PROCEDURE smb_029_reconcile_arrival_payouts()
reconciliation: BEGIN
    DECLARE v_done TINYINT DEFAULT 0;
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_status VARCHAR(16);
    DECLARE v_cutoff DATE;
    DECLARE v_snapshot INT DEFAULT 0;
    DECLARE v_vitalis INT;
    DECLARE v_chris INT;
    DECLARE v_booking INT;
    DECLARE v_traveller INT;
    DECLARE v_reference VARCHAR(50);
    DECLARE v_name VARCHAR(100);
    DECLARE v_travel_raw VARCHAR(100);
    DECLARE v_arrival_raw VARCHAR(100);
    DECLARE v_added_raw VARCHAR(100);
    DECLARE v_commission_raw VARCHAR(100);
    DECLARE v_currency_raw VARCHAR(30);
    DECLARE v_method VARCHAR(30);
    DECLARE v_travel DATE;
    DECLARE v_arrival DATE;
    DECLARE v_added DATE;
    DECLARE v_currency CHAR(3);
    DECLARE v_commission DECIMAL(12,2);
    DECLARE v_paid DECIMAL(20,2);
    DECLARE v_owing DECIMAL(12,2);
    DECLARE v_paid_ids LONGTEXT DEFAULT '[]';
    DECLARE v_excluded_ids LONGTEXT DEFAULT '[]';
    DECLARE v_blank_arrival_ids LONGTEXT DEFAULT '[]';
    DECLARE v_recorded DATETIME;
    DECLARE booking_rows CURSOR FOR
        SELECT b.id,b.traveller_id,b.tracking_id,t.fullname,
            TRIM(COALESCE(t.travel_date,'')),TRIM(COALESCE(t.arrival_date,'')),
            TRIM(COALESCE(CAST(b.date_added AS CHAR),'')),
            TRIM(COALESCE(CAST(b.traveller_commission AS CHAR),'')),
            UPPER(TRIM(COALESCE(b.currency,''))),LOWER(b.payment_method)
        FROM bookings b INNER JOIN travellers t ON t.id=b.traveller_id
        WHERE b.id<=v_snapshot AND b.deleted_at IS NULL AND t.deleted_at IS NULL
            AND b.payment_status='completed' AND t.travel_date<'2026-11-14'
        ORDER BY b.id FOR UPDATE;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done=1;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        DROP TEMPORARY TABLE IF EXISTS smb_029_payout_snapshot;
        RESIGNAL;
    END;

    SELECT COUNT(*) INTO v_count FROM information_schema.TABLES
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN
        ('bookings','travellers','traveller_payout_opening_balances','traveller_payouts','traveller_payout_items','traveller_payout_reconciliations')
        AND ENGINE='InnoDB';
    IF v_count<>6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_PAYOUT_SCHEMA_AND_INNODB_REQUIRED';
    END IF;
    START TRANSACTION;
    INSERT IGNORE INTO traveller_payout_reconciliations (operation_key,cutoff_date,status)
    VALUES ('arrivals-paid:2026-11-14','2026-11-14','pending');
    SELECT cutoff_date,status INTO v_cutoff,v_status FROM traveller_payout_reconciliations
    WHERE operation_key='arrivals-paid:2026-11-14' FOR UPDATE;
    IF v_cutoff<>'2026-11-14' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_RECONCILIATION_CONFIGURATION_MISMATCH';
    END IF;
    IF v_status='applied' THEN
        COMMIT;
        SELECT 'already_applied' AS reconciliation_status,'Existing reconciliation preserved; no new bookings settled.' AS message;
        LEAVE reconciliation;
    END IF;
    IF v_status<>'pending' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_RECONCILIATION_STATE_REQUIRES_REVIEW';
    END IF;
    SELECT COUNT(*) INTO v_count FROM migrations WHERE version>=28;
    IF v_count<>1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_APPLY_PAYOUT_MIGRATION_028_FIRST';
    END IF;
    SELECT status INTO v_status FROM traveller_payout_opening_balances WHERE id=1 FOR UPDATE;
    IF v_status IS NULL OR v_status<>'applied' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_APPLY_PAYOUT_OPENING_FIRST';
    END IF;
    IF DATE(UTC_TIMESTAMP()+INTERVAL 1 HOUR)<>'2026-11-14' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_HISTORICAL_SNAPSHOT_REQUIRED: first run must be on 14 November 2026';
    END IF;
    SELECT COUNT(*),MIN(id) INTO v_count,v_vitalis FROM travellers
    WHERE id=1458 AND deleted_at IS NULL AND travel_date='2026-11-13' AND arrival_date='2026-11-13'
        AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(fullname,'')),' ',''),CHAR(9),''),CHAR(10),''),CHAR(13),''))
        IN ('vitalisonyekachi','onyekachivitalis');
    IF v_count<>1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_VITALIS_TRIP_1458_REQUIRES_REVIEW';
    END IF;
    SELECT COUNT(*),MIN(id) INTO v_count,v_chris FROM travellers
    WHERE id=1457 AND deleted_at IS NULL AND travel_date='2026-11-12' AND arrival_date='2026-11-13'
        AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(fullname,'')),' ',''),CHAR(9),''),CHAR(10),''),CHAR(13),''))
        IN ('chrisiheuwa','iheuwachris');
    IF v_count<>1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_CHRIS_TRIP_1457_REQUIRES_REVIEW';
    END IF;
    SELECT COUNT(*) INTO v_count FROM traveller_payouts WHERE operation_key LIKE 'reconcile:2026-11-14:%';
    IF v_count<>0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_EXISTING_RECONCILIATION_HEADERS_REQUIRE_REVIEW';
    END IF;
    SELECT COALESCE(MAX(id),0) INTO v_snapshot FROM bookings;
    SET v_recorded=UTC_TIMESTAMP();
    CREATE TEMPORARY TABLE smb_029_payout_snapshot (
        booking_id INT PRIMARY KEY,booking_reference VARCHAR(50),traveller_id INT NOT NULL,
        traveller_name VARCHAR(100) NOT NULL,currency CHAR(3) NOT NULL,
        commission DECIMAL(12,2) NOT NULL,owing DECIMAL(12,2) NOT NULL,excluded TINYINT NOT NULL
    ) ENGINE=InnoDB;
    SET v_done=0;
    OPEN booking_rows;
    booking_loop: LOOP
        FETCH booking_rows INTO v_booking,v_traveller,v_reference,v_name,v_travel_raw,v_arrival_raw,v_added_raw,v_commission_raw,v_currency_raw,v_method;
        IF v_done=1 THEN LEAVE booking_loop; END IF;
        SET v_travel=CASE WHEN v_travel_raw REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])?$'
            AND CAST(LEFT(v_travel_raw,4) AS UNSIGNED)>0 AND CAST(SUBSTRING(v_travel_raw,9,2) AS UNSIGNED)<=DAY(LAST_DAY(CONCAT(LEFT(v_travel_raw,7),'-01')))
            THEN CAST(LEFT(v_travel_raw,10) AS DATE) ELSE NULL END;
        SET v_arrival=CASE WHEN v_arrival_raw REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])?$'
            AND CAST(LEFT(v_arrival_raw,4) AS UNSIGNED)>0 AND CAST(SUBSTRING(v_arrival_raw,9,2) AS UNSIGNED)<=DAY(LAST_DAY(CONCAT(LEFT(v_arrival_raw,7),'-01')))
            THEN CAST(LEFT(v_arrival_raw,10) AS DATE) ELSE NULL END;
        SET v_added=CASE WHEN v_added_raw REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])( (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9])?$'
            AND CAST(LEFT(v_added_raw,4) AS UNSIGNED)>0 AND CAST(SUBSTRING(v_added_raw,9,2) AS UNSIGNED)<=DAY(LAST_DAY(CONCAT(LEFT(v_added_raw,7),'-01')))
            THEN CAST(LEFT(v_added_raw,10) AS DATE) ELSE NULL END;
        SET v_currency=CASE WHEN v_currency_raw IN ('','GBP','POU','POUND','POUNDS') THEN 'GBP'
            WHEN v_currency_raw IN ('CAD','DOLLAR','DOLLARS') THEN 'CAD' ELSE NULL END;
        IF v_travel IS NULL OR v_travel>='2026-11-14' OR TRIM(COALESCE(v_name,''))='' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_ARRIVAL_TRAVELLER_REQUIRES_REVIEW';
        END IF;
        IF v_currency IS NULL OR v_commission_raw NOT REGEXP '^[0-9]{1,10}([.][0-9]{1,2})?$' OR (v_method IS NOT NULL AND v_method NOT IN ('paystack','stripe','offline','bank')) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_ARRIVAL_COMMISSION_OR_PAYMENT_METHOD_REQUIRES_REVIEW';
        END IF;
        IF v_added IS NULL OR v_added>'2026-11-14' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_ARRIVAL_BOOKING_DATE_REQUIRES_REVIEW';
        END IF;
        IF v_traveller NOT IN (v_vitalis,v_chris) AND v_arrival_raw<>''
            AND (v_arrival IS NULL OR v_arrival<v_travel OR v_arrival>'2026-11-14') THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_ARRIVAL_DATE_REQUIRES_REVIEW: no payouts were recorded';
        END IF;
        IF v_traveller NOT IN (v_vitalis,v_chris) AND v_arrival_raw='' THEN
            SET v_blank_arrival_ids=JSON_ARRAY_APPEND(v_blank_arrival_ids,'$',v_booking);
        END IF;
        SET v_commission=CAST(v_commission_raw AS DECIMAL(12,2));
        SELECT COALESCE(SUM(amount),0) INTO v_paid FROM traveller_payout_items
        WHERE booking_id=v_booking AND traveller_id=v_traveller AND CAST(currency AS BINARY)=CAST(v_currency AS BINARY) FOR UPDATE;
        SET v_owing=GREATEST(v_commission-v_paid,0);
        INSERT INTO smb_029_payout_snapshot VALUES (v_booking,v_reference,v_traveller,v_name,v_currency,v_commission,v_owing,v_traveller IN (v_vitalis,v_chris));
        IF v_traveller IN (v_vitalis,v_chris) THEN
            SET v_excluded_ids=JSON_ARRAY_APPEND(v_excluded_ids,'$',v_booking);
        ELSE
            SET v_paid_ids=JSON_ARRAY_APPEND(v_paid_ids,'$',v_booking);
        END IF;
    END LOOP;
    CLOSE booking_rows;
    SELECT COUNT(DISTINCT traveller_id) INTO v_count FROM smb_029_payout_snapshot WHERE excluded=1;
    IF v_count<>2 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_EXCEPTIONS_MUST_HAVE_EXISTING_ARRIVAL_BOOKINGS';
    END IF;
    SELECT COUNT(*) INTO v_count FROM (
        SELECT traveller_id FROM smb_029_payout_snapshot WHERE excluded=1
        GROUP BY traveller_id HAVING SUM(owing)=0
    ) already_paid_exceptions;
    IF v_count>0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_EXCEPTION_ALREADY_PAID_REQUIRES_ACCOUNTING_REVIEW';
    END IF;
    SELECT COUNT(*) INTO v_count FROM (
        SELECT traveller_id,currency FROM smb_029_payout_snapshot WHERE excluded=0
        GROUP BY traveller_id,currency HAVING SUM(owing)>999999999999.99
    ) overflowing_groups;
    IF v_count>0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SMB_029_AMOUNT_EXCEEDS_LEDGER_LIMIT';
    END IF;
    INSERT INTO traveller_payouts (operation_key,opening_balance_id,traveller_id,traveller_name,currency,amount,source,admin_id,paid_at,recorded_at,note)
    SELECT CONCAT('reconcile:2026-11-14:',traveller_id,':',currency),NULL,traveller_id,MAX(traveller_name),currency,SUM(owing),
        'owner_reconciliation',NULL,NULL,v_recorded,
        'Owner declared existing Arrivals paid as of 14 November 2026 except trips 1458 (Vitalis Onyekachi) and 1457 (Chris Iheuwa), both arriving 13 November. Actual bank payment date unknown; historical declaration does not fabricate shipping.'
    FROM smb_029_payout_snapshot WHERE excluded=0 AND owing>0 GROUP BY traveller_id,currency;
    INSERT INTO traveller_payout_items (payout_id,booking_id,booking_reference,traveller_id,currency,commission_snapshot,amount)
    SELECT p.id,s.booking_id,s.booking_reference,s.traveller_id,s.currency,s.commission,s.owing
    FROM smb_029_payout_snapshot s INNER JOIN traveller_payouts p
        ON CAST(p.operation_key AS BINARY)=CAST(CONCAT('reconcile:2026-11-14:',s.traveller_id,':',s.currency) AS BINARY)
    WHERE s.excluded=0 AND s.owing>0;
    UPDATE traveller_payout_reconciliations SET status='applied',snapshot_booking_id=v_snapshot,applied_at=v_recorded,
        audit_snapshot=JSON_OBJECT('cutoff_date','2026-11-14','snapshot_booking_id',v_snapshot,
            'basis','Owner declared all existing Arrivals previously paid except trips 1458 (Vitalis Onyekachi) and 1457 (Chris Iheuwa), both arriving 13 November. Existing allocations preserved; actual payment dates unknown.',
            'excluded_traveller_ids',JSON_ARRAY(CAST(v_vitalis AS UNSIGNED),CAST(v_chris AS UNSIGNED)),
            'paid_booking_ids',JSON_EXTRACT(v_paid_ids,'$'),'excluded_booking_ids',JSON_EXTRACT(v_excluded_ids,'$'),
            'blank_arrival_booking_ids',JSON_EXTRACT(v_blank_arrival_ids,'$'))
    WHERE operation_key='arrivals-paid:2026-11-14';
    COMMIT;
    DROP TEMPORARY TABLE smb_029_payout_snapshot;
    SELECT 'applied' AS reconciliation_status,JSON_LENGTH(v_paid_ids) AS paid_arrival_bookings,
        JSON_LENGTH(v_excluded_ids) AS excluded_booking_count,'Trips 1458 (Vitalis Onyekachi) and 1457 (Chris Iheuwa) remain outstanding.' AS message;
END$$
DELIMITER ;
CALL smb_029_reconcile_arrival_payouts();
DROP PROCEDURE IF EXISTS smb_029_reconcile_arrival_payouts;
