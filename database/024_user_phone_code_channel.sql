-- Run in the application's database. Safe to repeat; existing personal choices are preserved.
SET @smb_add_channel = NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'phone_otp_channel'
);
SET @smb_channel_ddl = IF(@smb_add_channel,
    "ALTER TABLE users ADD COLUMN phone_otp_channel VARCHAR(8) NOT NULL DEFAULT 'sms'", 'SELECT 1');
PREPARE smb_channel_stmt FROM @smb_channel_ddl;
EXECUTE smb_channel_stmt;
DEALLOCATE PREPARE smb_channel_stmt;
SET @smb_has_auth_settings = EXISTS (
    SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'auth_settings'
);
SET @smb_channel_seed = IF(@smb_add_channel AND @smb_has_auth_settings,
    "UPDATE users SET phone_otp_channel = CASE WHEN (SELECT phone_otp_channel FROM auth_settings WHERE id = 1 LIMIT 1) = 'whatsapp' THEN 'whatsapp' ELSE 'sms' END", 'SELECT 1');
PREPARE smb_channel_stmt FROM @smb_channel_seed;
EXECUTE smb_channel_stmt;
DEALLOCATE PREPARE smb_channel_stmt;
