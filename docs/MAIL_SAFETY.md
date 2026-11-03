# Outgoing email safeguards

This release adds mailbox-wide attempt limits, public request limits, once-only booking-payment notifications, escaped transactional content, and a paced admin bulk queue. It reduces application abuse; it does not establish why Hostinger suspended the mailbox or prove inbox delivery.

## Deploy in this order

1. Back up the database and apply `database/026_mail_safety.sql` to the SMB database **before** releasing the dependent PHP code. The SQL creates three InnoDB tables and can be applied repeatedly. It does not modify user or booking records. The matching CI migration is 026; automatic migrations remain disabled.
2. Release the changed application files and SQL file together. Without the new safety tables, outgoing email fails closed and the bulk form reports a queue error.
3. Set the optional environment limits below for the confirmed mailbox plan. The current defaults are conservative application limits, not Hostinger plan limits.
4. Configure one cron entry every minute, using the directory containing this application's `index.php`. If that directory is `/home/u543551821/public_html`, the command is:

   ```sh
   cd /home/u543551821/public_html && /usr/bin/php index.php mail_worker run >> application/logs/email-queue.log 2>&1
   ```

   This is a CodeIgniter 3 application. The supplied `php spark email:process` command belongs to a different command interface; confirm which application its existing cron serves before replacing it. If SMB lives in a subdirectory, use that directory instead. The PHP CLI must use a compatible PHP version/extensions and have access to the same environment and database as the web app. `application/cache` and `application/logs` must be writable by the hosting user.
5. Queue a small admin message and verify worker output, its queue row, the mail audit, Hostinger activity and the recipient's inbox before increasing limits. These activation steps have not been performed locally.

| Environment setting | Default | Meaning |
| --- | ---: | --- |
| `SMTP_APP_DAILY_LIMIT` | 100 | All SMTP attempts per sender per UTC calendar day |
| `SMTP_APP_MINUTE_LIMIT` | 10 | All attempts per UTC minute |
| `SMTP_APP_RECIPIENT_DAILY_LIMIT` | 20 | Attempts to one address across templates per UTC day |
| `SMTP_APP_BULK_DAILY_LIMIT` | 50 | Bulk attempts per UTC day; 0 pauses bulk sending |

Bulk sends are additionally separated by at least 60 seconds. The bulk allowance is part of the total allowance, leaving capacity for transactional mail. Failed SMTP attempts consume the same budget as successful sends. These windows differ from provider rolling 24-hour limits; configure headroom and account for other applications/devices using the same mailbox. Separate applications do not share these database counters.

## Behavior and recovery

- Signup/recovery, password reset and sign-in emails stay synchronous and report send failures accurately. Public reset/sign-in requests have account-wide and IP limits using the existing cache throttle; those limits are best-effort under simultaneous requests. The mailbox budget is reserved transactionally in the database. Only CI-configured trusted proxies may supply the client IP. The traveller form now checks a server-held CAPTCHA and applies a request limit.
- A booking-payment transition is claimed by a conditional database update. Only the winning completion changes capacity and attempts the notification pair, after commit. Completed callbacks and admin replays do not send again. A failure after commit can leave a notification unsent; payment remains confirmed. This is intentional to avoid automatic duplicate sends.
- Admin bulk submission reports queued/skipped/error counts. Active, nondeleted, completed accounts are eligible, including legacy completed accounts with a password and consumed verification code. Eligibility is checked again before sending. Duplicate addresses and the same recipient/content combination queued on the same day are skipped. At most 1,000 recipients are accepted per submission.
- The CLI-only worker claims one row transactionally, processes at most five rows per run and uses a nonblocking process lock. The shared limiter normally permits one bulk send per run/minute. A safety-limit or pre-SMTP availability failure leaves the message pending. A reported SMTP failure becomes `failed` and requires review; there is no automatic SMTP retry.
- A worker interrupted after claiming a message may have sent it. After 15 minutes its `sending` row becomes `review`; reconcile provider records before any manual retry. Likewise, a `reserved` mail event is an attempt with an unknown final outcome. Do not infer non-delivery from missing audit completion.
- `mail_send_events` stores hashed sender/recipient keys, template, status and UTC timestamps, without OTPs or message bodies. `mail_bulk_queue` stores recipient and message content needed for delivery; restrict database/log access. Retain rows according to the site's data policy. Do not remove today's event rows because recipient limits depend on them, or today's queue dedupe rows while accepting new submissions. No automatic retention/deletion job is added.
- The mailbox-wide guard covers the application's shared mail helper and legacy prepared-mail calls. Mail sent outside SMB is outside its budget. It does not fix compromised mailbox credentials, DNS authentication or provider reputation.

## Verification

Run the project-owned tests with simulated SMTP:

```sh
php tests/mail_request_safety_test.php
php tests/mail_content_safety_test.php
php tests/booking_mail_once_test.php
php tests/mail_safety_test.php
php tests/bulk_mail_queue_test.php
```

`SMB_MAIL_MYSQL=1 php tests/mail_safety_test.php` exercises the actual SQL twice and the guard against connection-local temporary MySQL tables in local development; it does not migrate the persistent local database. Payment tests use `SMB_MAIL_MYSQL=1`; bulk tests use `SMB_BULK_MYSQL=1`. Registration, welcome, sign-in and email-rendering regressions are also part of the release checks. The full email-copy suite has a known pre-existing `traveller_approval_notification_email` wording mismatch; this release changes only escaping in that template and preserves its wording. Live cron execution, SMTP acceptance, inbox delivery and the Hostinger suspension cause require separate hosting evidence.

Payment authenticity is a separate outstanding review: the legacy Stripe callback trusts a URL status flag, and the Paystack callback requires a scoped review of reference/amount/currency binding. Notification deduplication does not validate provider payments.

Local verification on 2026-11-02: all five safeguard tests passed on their applicable SQLite/rendering adapters; mailbox, bulk queue and payment tests also passed on temporary MySQL tables, including the `FOR UPDATE` path and idempotent SQL. Registration recovery and sign-in regressions passed on temporary MySQL; welcome, recovery-email, signup feedback and passwordless contract checks passed. PHP syntax and CRLF-aware whitespace checks passed. The actual local HTTP worker URL returned framework 404. No persistent migration, real email send, production release or cron activation was performed.

Subsequent local sign-in diagnosis on 2026-11-02 found all three SQL 026 tables missing; the log reported `Mail not attempted: unavailable for template user_login_code_email`. SQL 026 was then applied to the persistent local development database. The real mail guard successfully reserved a send inside an outer transaction that was rolled back; the three new tables remained empty and SMTP was never called. Production schema, cron activation and SMTP/inbox delivery remain unverified. A generic sign-in failure can also represent account eligibility or provider failure; identify the matching log entry before assigning a cause in another environment.
