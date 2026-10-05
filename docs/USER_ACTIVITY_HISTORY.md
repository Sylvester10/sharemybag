# User activity history

## Admin workflow

- View User → Update Details → Clear phone number → Update → confirmation.
- Undo restores the unsaved phone field. Closing the modal cancels an unsaved reset.
- A saved reset clears `number`, `verified_phone_e164`, `phone_verified_at`, and disables `phone_signin_enabled`.
- Pending phone challenges are consumed. Email sign-in remains available; existing bookings and identity approval are preserved.
- Direct admin phone edits also remove the old verification state. New numbers must be verified by the user.
- View User → Activity History opens the existing modal style, with Details Changes and Sign-in Activity tabs. Each page contains at most 20 events.

## Records

Details changes store previous/new values, actor identity and timestamp. Covered writes include admin profile edits, approval/blocking actions, user address edits, identity submissions, phone verification and phone sign-in preference changes. Passwords, reset codes and OTP values are excluded. Admin impersonation is identified separately and subsequent edits retain the real administrator's identity.

Successful password/email-code/phone-code sign-ins and explicit sign-outs are recorded. Browser closing and automatic session expiry are not reported as explicit sign-outs. Existing `last_login` presentation is unchanged. No older history is fabricated.

Account edits and their audit entries are committed together. Account/challenge locks prevent stale verification results from undoing an admin reset. Duplicate verified numbers fail without partially saving account state. History is append-only through the application and restricted to the existing user-management admin roles.

## Schema and release

Migration: `023_add_user_activity_logs.php`. Local schema installed at version 23.

The additive SQL is in `database/023_user_activity_logs.sql`. Apply it in the intended database before releasing the new code. The local-only `scripts/setup_user_activity.php` checks authentication columns and InnoDB tables; it advances the migration ledger only from 22 to 23.

No sandbox or main remote push was performed for this feature.

## Verification

- `php tests/user_activity_history_test.php`: real model and CodeIgniter query builder against connection-local temporary MySQL tables. Covers phone reset, actor identity after impersonation/sign-out, old-code rejection, new verification, email fallback, replay, unique-number failure, transactional rollback, per-account history, pagination and HTML escaping.
- PHP syntax and JavaScript syntax passed.
- Passwordless authentication, resend, shared phone input and OTP structural tests passed. An existing assertion was corrected to the current approved “Verify Number” button label.
- Local schema setup succeeded twice without changing real user records.
- An unauthenticated browser visit to admin users redirected to Admin Login. Authenticated admin modal interactions and external Twilio delivery remain unverified.
- Independent code review found an admin identity lookup issue after impersonation; constructors and the shared admin header now use the retained `admin_email`. The model regression passed and the fix was reviewed.
