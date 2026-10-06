# Personal phone-code delivery

## Approved behaviour

- Sign-in retains email by default, switch-to-phone, and password alternatives.
- Account verifies phone ownership only. Verify Phone Number appears when a number is entered; successful verification locks the number and displays the green check without enabling phone-code sign-in.
- Security has Change Password and Sign-in Codes cards opening separate modals.
- Sign-in Codes shows mutually exclusive Email (default), WhatsApp and SMS switches. Phone methods require an already verified number. Selecting Email disables phone-code sign-in while preserving verified phone ownership.
- Selecting a method saves immediately, with a toast on success/failure, loading state, and rollback to the saved selection on error. Existing saved phone methods are retained.
- Changing the method records before/after values in Activity History and revokes pending phone codes. Email challenges remain available.
- The global admin delivery selector is retired; old update requests return HTTP 410. No live Twilio settings are changed.
- WhatsApp delivery and any SMS fallback depend on Twilio sender/templates/service settings. The app does not send a second paid request after a generic failure; code delivery text does not claim a channel that may have fallen back.

## Database

Migration 024 adds users.phone_otp_channel. Existing users inherit the previous global method once. New users default to SMS. Replaying setup preserves personal choices.

Local setup: `php scripts/setup_phone_code_channel.php` (restricted to local development, predecessor version 23 required). For sandbox/live deployment, first apply `database/024_user_phone_code_channel.sql` in each application's database. The SQL does not advance the migrations ledger or replay older migrations.

## Dashboard

Active Bookings replaces Total Bookings on desktop and mobile. It counts owned, visible, paid bookings that are neither delivered nor cancelled/declined. The card opens `history?filter=active` using the same filter and provides View all bookings.

## Verification

`php tests/phone_code_channel_test.php` runs real models/query builder against disposable connection-local temporary tables. Covers preference validation/persistence/history/rollback, challenge binding/revocation, email preservation, and active-booking count/list isolation and legacy aliases.

Local profile browser preview uses the actual view/CSS/JS with synthetic user details and simulated AJAX. It is UI proof only, not an authenticated app or Twilio delivery test. No verification messages were sent during implementation. Changes are local; no release is performed.
