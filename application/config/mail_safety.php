<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Application limits, not a claim about the mailbox plan. Attempts include SMTP failures.
$config['daily_limit'] = max(1, (int) ($_ENV['SMTP_APP_DAILY_LIMIT'] ?? 100));
$config['minute_limit'] = max(1, (int) ($_ENV['SMTP_APP_MINUTE_LIMIT'] ?? 10));
$config['recipient_daily_limit'] = max(1, (int) ($_ENV['SMTP_APP_RECIPIENT_DAILY_LIMIT'] ?? 20));
$config['bulk_daily_limit'] = max(0, (int) ($_ENV['SMTP_APP_BULK_DAILY_LIMIT'] ?? 50));
$config['bulk_interval_seconds'] = 60;
