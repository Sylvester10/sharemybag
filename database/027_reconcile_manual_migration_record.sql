-- Reconcile an existing manual deployment of migrations 024-027.
-- This does NOT run migrations or edit users, bookings, mail, payments or payouts.
-- It changes only a single migrations.version row from 23 to 27, after checking
-- all required columns/types, indexes and InnoDB tables from the actual SQL definitions.
-- Explicit database names make this safe to paste even when phpMyAdmin has information_schema selected.
-- If a check fails, the version is left unchanged. Send the final check results for review.
SET @smb_reconcile_schema = 'u543551821_sharemybag';

SET @smb_columns_match = NOT EXISTS (
    SELECT 1 FROM (
SELECT 'users' AS table_name, 'phone_otp_channel' AS column_name, 'varchar' AS data_type, 8 AS char_length, NULL AS num_precision, NULL AS num_scale, 'NO' AS nullable, 0 AS unsigned_type, 0 AS auto_increment
UNION ALL
SELECT 'users', 'signin_code_methods', 'tinyint', NULL, NULL, NULL, 'NO', 1, 0
UNION ALL
SELECT 'mail_safety_state', 'sender_key', 'char', 64, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_safety_state', 'day_bucket', 'char', 10, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_safety_state', 'day_used', 'int', NULL, NULL, NULL, 'NO', 1, 0
UNION ALL
SELECT 'mail_safety_state', 'minute_bucket', 'bigint', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_safety_state', 'minute_used', 'int', NULL, NULL, NULL, 'NO', 1, 0
UNION ALL
SELECT 'mail_safety_state', 'bulk_day_used', 'int', NULL, NULL, NULL, 'NO', 1, 0
UNION ALL
SELECT 'mail_safety_state', 'bulk_last_at', 'bigint', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_send_events', 'id', 'int', NULL, NULL, NULL, 'NO', 1, 1
UNION ALL
SELECT 'mail_send_events', 'sender_key', 'char', 64, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_send_events', 'recipient_key', 'char', 64, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_send_events', 'template', 'varchar', 80, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_send_events', 'status', 'varchar', 16, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_send_events', 'created_at', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_send_events', 'finished_at', 'datetime', NULL, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'id', 'int', NULL, NULL, NULL, 'NO', 1, 1
UNION ALL
SELECT 'mail_bulk_queue', 'recipient', 'varchar', 254, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'subject', 'varchar', 255, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'body', 'mediumtext', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'template', 'varchar', 80, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'actor_id', 'int', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'dedupe_key', 'char', 64, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'status', 'varchar', 16, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'claimed_at', 'datetime', NULL, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'sent_at', 'datetime', NULL, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'created_at', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'mail_bulk_queue', 'last_error', 'varchar', 200, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'booking_id', 'int', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'user_id', 'int', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'request_key', 'char', 64, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'form_data', 'mediumtext', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'resume_until', 'datetime', NULL, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'created_at', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_checkouts', 'updated_at', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'id', 'bigint', NULL, NULL, NULL, 'NO', 1, 1
UNION ALL
SELECT 'booking_payment_attempts', 'booking_id', 'int', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'user_id', 'int', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'traveller_id', 'int', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'provider', 'varchar', 16, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'reference', 'varchar', 80, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'provider_session_id', 'varchar', 255, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'authorization_url', 'text', NULL, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'status', 'varchar', 24, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'amount_minor', 'bigint', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'currency', 'char', 3, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'exchange_rate', 'decimal', NULL, 18, 8, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'quote_data', 'mediumtext', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'hold_space', 'decimal', NULL, 12, 3, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'hold_until', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'created_at', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'updated_at', 'datetime', NULL, NULL, NULL, 'NO', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'confirmed_at', 'datetime', NULL, NULL, NULL, 'YES', 0, 0
UNION ALL
SELECT 'booking_payment_attempts', 'review_reason', 'varchar', 255, NULL, NULL, 'YES', 0, 0
    ) required_column
    LEFT JOIN information_schema.COLUMNS actual_column
        ON actual_column.TABLE_SCHEMA = @smb_reconcile_schema
        AND actual_column.TABLE_NAME = required_column.table_name
        AND actual_column.COLUMN_NAME = required_column.column_name
    WHERE actual_column.COLUMN_NAME IS NULL
        OR actual_column.DATA_TYPE <> required_column.data_type
        OR actual_column.IS_NULLABLE <> required_column.nullable
        OR (required_column.char_length IS NOT NULL AND NOT (actual_column.CHARACTER_MAXIMUM_LENGTH <=> required_column.char_length))
        OR (required_column.num_precision IS NOT NULL AND NOT (actual_column.NUMERIC_PRECISION <=> required_column.num_precision))
        OR (required_column.num_scale IS NOT NULL AND NOT (actual_column.NUMERIC_SCALE <=> required_column.num_scale))
        OR (LOWER(actual_column.COLUMN_TYPE) LIKE '%unsigned%') <> required_column.unsigned_type
        OR (LOWER(actual_column.EXTRA) LIKE '%auto_increment%') <> required_column.auto_increment
);

SET @smb_indexes_match = NOT EXISTS (
    SELECT 1 FROM (
SELECT 'mail_safety_state' AS table_name, 'PRIMARY' AS index_name, 'sender_key' AS column_names, 0 AS non_unique
UNION ALL
SELECT 'mail_send_events', 'PRIMARY', 'id', 0
UNION ALL
SELECT 'mail_send_events', 'mail_sender_window', 'sender_key,created_at', 1
UNION ALL
SELECT 'mail_send_events', 'mail_recipient_window', 'sender_key,recipient_key,created_at', 1
UNION ALL
SELECT 'mail_bulk_queue', 'PRIMARY', 'id', 0
UNION ALL
SELECT 'mail_bulk_queue', 'mail_bulk_dedupe', 'dedupe_key', 0
UNION ALL
SELECT 'mail_bulk_queue', 'mail_bulk_status', 'status,id', 1
UNION ALL
SELECT 'booking_checkouts', 'PRIMARY', 'booking_id', 0
UNION ALL
SELECT 'booking_checkouts', 'checkout_request', 'request_key', 0
UNION ALL
SELECT 'booking_checkouts', 'checkout_owner', 'user_id,booking_id', 1
UNION ALL
SELECT 'booking_payment_attempts', 'PRIMARY', 'id', 0
UNION ALL
SELECT 'booking_payment_attempts', 'checkout_reference', 'reference', 0
UNION ALL
SELECT 'booking_payment_attempts', 'checkout_provider_session', 'provider,provider_session_id', 0
UNION ALL
SELECT 'booking_payment_attempts', 'checkout_booking', 'booking_id,id', 1
UNION ALL
SELECT 'booking_payment_attempts', 'checkout_holds', 'traveller_id,status,hold_until', 1
UNION ALL
SELECT 'booking_payment_attempts', 'checkout_expiry', 'status,hold_until', 1
    ) required_index
    LEFT JOIN (
        SELECT TABLE_NAME, INDEX_NAME,
            GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') AS column_names,
            MIN(NON_UNIQUE) AS non_unique, MAX(SUB_PART IS NOT NULL) AS prefix_index
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @smb_reconcile_schema AND TABLE_NAME IN ('mail_safety_state','mail_send_events','mail_bulk_queue','booking_checkouts','booking_payment_attempts')
        GROUP BY TABLE_NAME, INDEX_NAME
    ) actual_index ON actual_index.TABLE_NAME = required_index.table_name AND actual_index.INDEX_NAME = required_index.index_name
    WHERE actual_index.INDEX_NAME IS NULL OR actual_index.column_names <> required_index.column_names
        OR actual_index.non_unique <> required_index.non_unique OR actual_index.prefix_index <> 0
);

SET @smb_engines_match = (
    SELECT COUNT(*) = 5 FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = @smb_reconcile_schema AND TABLE_NAME IN ('mail_safety_state','mail_send_events','mail_bulk_queue','booking_checkouts','booking_payment_attempts') AND ENGINE = 'InnoDB'
);
SET @smb_single_version_record = (SELECT COUNT(*) = 1 FROM `u543551821_sharemybag`.`migrations`);

UPDATE `u543551821_sharemybag`.`migrations`
SET version = 27
WHERE version = 23 AND @smb_single_version_record = 1
    AND @smb_columns_match = 1 AND @smb_indexes_match = 1 AND @smb_engines_match = 1;

SELECT version AS recorded_migration_version,
    @smb_columns_match AS required_columns_match,
    @smb_indexes_match AS required_indexes_match,
    @smb_engines_match AS required_engines_match,
    @smb_single_version_record AS single_version_record,
    CASE WHEN version >= 27 AND @smb_columns_match = 1 AND @smb_indexes_match = 1
        AND @smb_engines_match = 1 AND @smb_single_version_record = 1 THEN 'Ready for payout SQL'
         ELSE 'Not changed: send these results for review' END AS result
FROM `u543551821_sharemybag`.`migrations`;
