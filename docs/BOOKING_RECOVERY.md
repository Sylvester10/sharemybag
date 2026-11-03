# Booking recovery and payment attempts

## Customer flow

History shows **Resume booking** for an eligible interrupted online checkout. The action checks the provider, departure cutoff and full requested space, then opens the existing Book Space wizard with all parcel rows, sender/receiver details, radio choices, protection, payment method and prior tab restored. The booking ID, hash and tracking number remain unchanged. There is no additional summary screen, saved-details banner or expiry caption.

Recovery lasts up to 15 minutes after the first verified interruption, bounded by the original checkout window; retries and refreshes do not extend it. At expiry all recovery actions disappear, with no **Find space** replacement or expiry helper text. An unpaid booking displays only **Canceled**, both on the open page and after refreshing. Capacity is checked again under a database row lock before payment. Current server pricing and FX apply; a changed total is displayed in the existing wizard and requires another explicit confirmation.

An uncertain provider result shows **Check payment** during the recovery window and blocks another payment for that booking. After the deadline only its **Checking payment** status remains, while the worker/webhooks continue reconciliation. A received payment outside a valid allocation shows **Payment needs review**, with no resume or fresh-purchase shortcut. UI expiry does not change a provider/payment record or claim an unknown payment was canceled.

## Financial and capacity boundaries

- `booking_checkouts` holds the allowlisted editable form and fixed recovery deadline. Account ownership, CSRF and a session intent token protect writes; server-owned financial/identity fields are excluded from restoration.
- Each gateway attempt has its own immutable reference, exact minor-unit amount, currency, FX quote, owner and booking identity. Status and verification data can advance; old attempts are retained.
- A 15-minute lease reserves the complete requested space while paying. Confirmed bookings plus live leases determine availability. Manual confirmation, offline booking, parcel changes, capacity edits and traveller reassignment use the same capacity calculation.
- Callbacks, signed webhooks and the worker all verify the provider reference/session, amount, currency and metadata before confirmation. A verified winner consumes its own lease once; repeated events do not allocate space or send booking notifications again.
- Provider API calls and email sending occur outside allocation transactions. Unknown initialization or verification results block blind retries. Worker selection rotates uncertain attempts so they cannot starve later paid attempts.
- Late, superseded, mismatched or unallocatable payments do not overbook. Paid exceptions are persisted as `review`, logged with the attempt ID and trigger one admin notification. **Refunds are manual**, following provider reconciliation. There is no automatic refund or new refund dashboard in this change.

Paystack `abandoned`/`failed` establishes the currently unpaid result used to offer recovery; it does not revoke the old checkout URL. If an old URL later charges, its retained reference is recognized and sent to review. This prevents duplicate fulfillment, but cannot guarantee that a customer who uses multiple old provider URLs is never charged twice. Stripe open sessions are explicitly expired before offering an editable retry. Its automatic expiry has a 30-minute minimum; the worker attempts to close it at the application's 15-minute lease boundary. Worker/provider delays are covered by late-payment review.

## Deployment order

1. Back up the database and release files. Check `bookings` and `travellers` use **InnoDB**; row locks require it. Preserve the existing mail-queue migration 026 and configuration.
2. Apply [database/027_booking_checkout.sql](../database/027_booking_checkout.sql) **before deploying the application changes**. It only adds two tables and is safe to replay. With the existing CodeIgniter migration runner, apply migration 027 instead; do not run both paths independently without aligning the migration ledger. For a manual SQL deployment, advance the existing migrations row from 26 to 27 after both tables are created. Do not overwrite a different version. Automatic migration remains disabled.
3. Deploy this complete change, including controllers, libraries, models, helpers, views, assets and migration/SQL files. Clear the hosting opcode/asset caches through the normal release procedure.
4. In Stripe, configure a signed HTTPS webhook for `/booking_webhooks/stripe` and subscribe to `checkout.session.completed`, `checkout.session.async_payment_succeeded` and `checkout.session.expired`. Set `STRIPE_WEBHOOK_SECRET` for production; use `STRIPE_TEST_WEBHOOK_SECRET` for non-production. Existing API keys and environment selection are reused.
5. In Paystack, configure the HTTPS webhook `/booking_webhooks/paystack`. It validates the raw body's HMAC using the existing environment-specific Paystack secret key, then verifies the transaction through the API. Preserve or forward any existing webhook integrations when choosing the dashboard URL.
6. Reuse the **existing SMB worker**, scheduled every minute. There is no second cron job. For this CodeIgniter 3 checkout, the command is:

   ```sh
   cd /home/u543551821/public_html && /usr/bin/php index.php mail_worker run >> application/logs/email-queue.log 2>&1
   ```

   Use `* * * * *` for minute/hour/day/month/weekday and the actual SMB directory if different. This repository does not provide `spark email:process`; do not replace a cron serving another application. The hosting PHP CLI must read the same environment/database as the website. `application/cache` and `application/logs` must be writable. The worker handles the existing bulk mail queue and a bounded payment reconciliation batch under a separate nonblocking lock.

7. Check the worker log's `checkout` object: `checked`, `confirmed`, `review`, `pending`, `errors`. Missing schema returns `schema_ready:false`; another active worker returns `busy:true`. Investigate persistent pending/error counts and review alerts. Inspect attempts read-only by `reference`/`booking_id`, match provider records, then arrange support/refund handling without asking the customer to pay again.

Example production endpoints, when SMB is hosted at the domain root:

- `https://sharemybag.co.uk/booking_webhooks/stripe`
- `https://sharemybag.co.uk/booking_webhooks/paystack`

Only these exact webhook routes are excluded from CSRF; their provider signatures are required. They do not require a customer login. No provider webhook, secret, production SQL or live cron has been configured by this implementation.

### Older in-flight payments

Drain or reconcile old Stripe checkout sessions during rollout. The former code did not persist a Stripe session ID or bind its metadata, so an old success URL cannot safely prove payment; older unresolved Stripe payments require support/provider verification. Old Paystack callbacks remain supported when their stored reference, amount, FX, currency and metadata verify. Existing historical bookings are not backfilled into resumable drafts and receive no new recovery button.

## Local verification

Local additive setup (development environment and preceding migration 025 required; idempotently replays mail migration 026 when the local ledger is still 25):

```sh
php scripts/setup_booking_checkout.php
php scripts/setup_booking_checkout.php
```

The setup writes a fresh schema fingerprint to `evidence/booking-recovery/migration-state.json` and changes no business rows. Production should follow the release procedure above.

Focused automated checks:

```sh
php tests/booking_checkout_flow_test.php
php tests/booking_checkout_test.php
SMB_CHECKOUT_MYSQL=1 php tests/booking_checkout_test.php
php tests/booking_checkout_concurrency_test.php
php tests/booking_checkout_quote_test.php
php tests/booking_gateway_test.php
php tests/booking_webhook_test.php
node tests/booking_restore_test.js
php tests/booking_recovery_view_test.php
php tests/booking_mail_once_test.php
```

Run `php scripts/verify_booking_checkout.php --mysql --http` to repeat the focused regression tests, disposable MySQL tests, changed-file syntax checks and localhost HTTP boundaries, recording a command transcript and repository-derived build state under `evidence/booking-recovery/`. It does not run the worker against a real mail queue or make provider requests.

The MySQL model suite uses connection-local temporary tables and replays the actual SQL twice. The concurrency suite creates and removes a uniquely named throwaway local database, then races two real processes for the same last capacity. All payment and SMTP adapters are recorded/faked; these tests do not spend money or send mail.

Before a production release, use the providers' test environments to exercise both payment methods end to end: complete, cancel, browser back, lost return/session, valid and invalid webhooks, duplicate events, changed price/FX, insufficient capacity, expired departure and late payment. Verify desktop/mobile layouts in an authenticated browser. Local fixtures and tests do not establish live provider delivery, production activation, refunds or device behavior.
