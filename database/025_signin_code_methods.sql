-- Select the application database first. Repeat runs preserve users' saved choices.
-- Bits: Email=1, WhatsApp=2, SMS=4. Allowed values: 1,2,3,4,5,6.
SET @smb_add_methods = NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'signin_code_methods'
);
SET @smb_methods_sql = IF(@smb_add_methods,
    'ALTER TABLE users ADD COLUMN signin_code_methods TINYINT UNSIGNED NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE smb_methods_stmt FROM @smb_methods_sql;
EXECUTE smb_methods_stmt;
DEALLOCATE PREPARE smb_methods_stmt;
SET @smb_methods_sql = IF(@smb_add_methods,
    "UPDATE users SET signin_code_methods = CASE WHEN phone_signin_enabled = 1 AND phone_verified_at IS NOT NULL AND verified_phone_e164 IS NOT NULL AND verified_phone_e164 <> '' THEN CASE WHEN phone_otp_channel = 'whatsapp' THEN 3 ELSE 5 END ELSE 1 END", 'SELECT 1');
PREPARE smb_methods_stmt FROM @smb_methods_sql;
EXECUTE smb_methods_stmt;
DEALLOCATE PREPARE smb_methods_stmt;
