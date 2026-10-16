# Personal phone-code delivery

## Approved behaviour

- Sign-in retains email by default, switch-to-phone, and password alternatives.
- Account verifies phone ownership only. Verify Phone Number appears when a number is entered; successful verification locks the number and displays the green check without enabling phone-code sign-in.
- Security has Change Password and Sign-in Codes cards opening separate modals.
- Sign-in Codes shows Email (default), WhatsApp and SMS switches. Users can keep one or two enabled; no limit explanation is shown. Phone methods require an already verified number. Phone-code sign-in stays enabled while either phone method is selected. Removing all phone methods preserves verified phone ownership.
- Selecting a method saves immediately, with a toast on success/failure, loading state, and rollback to the saved selection on error. Existing saved phone methods are retained.
- Changes record before/after values in Activity History and revoke outstanding login codes for removed methods. Codes for retained methods remain available. Login enforces the saved enabled methods again when consuming a challenge.
- The global admin delivery selector is retired; old update requests return HTTP 410. No live Twilio settings are changed.
- WhatsApp delivery and any SMS fallback depend on Twilio sender/templates/service settings. The app does not send a second paid request after a generic failure; code delivery text does not claim a channel that may have fallen back.

## Database

Migration 024 adds users.phone_otp_channel; migration 025 adds users.signin_code_methods (Email=1, WhatsApp=2, SMS=4). Existing phone-enabled users keep Email plus their enabled phone channel; new users default to Email. Existing users inherit the previous global method once. New users default to SMS. Replaying setup preserves personal choices.

Local setup for migration 025: `php scripts/setup_signin_code_methods.php` (restricted to local development, predecessor version 24 required). For sandbox/live deployment, first apply `database/025_signin_code_methods.sql` after migration 024 in each application's database. The SQL does not advance the migrations ledger or replay older migrations.

## Dashboard

Active Bookings replaces Total Bookings on desktop and mobile. It counts owned, visible, paid bookings that are neither delivered nor cancelled/declined. The card opens `history?filter=active` using the same filter and provides View all bookings.

## Verification

`php tests/signin_code_methods_test.php` runs real models/query builder against disposable connection-local temporary tables. Covers all six valid combinations, invalid selections, verified-phone guards, persistence/history/rollback, challenge binding/revocation, enabled-method preservation, and active-booking count/list isolation and legacy aliases.

Local profile browser preview uses the actual view/CSS/JS with synthetic user details and simulated AJAX. It is UI proof only, not an authenticated app or Twilio delivery test. No verification messages were sent during implementation. Changes are local; no release is performed.
