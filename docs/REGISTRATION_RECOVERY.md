# Registration recovery

Approved flow: a pending user resumes their saved registration, requests a fresh email code, verifies it and creates their password, then signs in to the dashboard. Identity-document approval remains a separate process.

## Entry points and states

| Entry | Result |
| --- | --- |
| Signup email, new address | Reveal the existing details form; submission saves details and attempts verification email |
| Signup email, unfinished active account | Continue registration page; no automatic email |
| Sign-in email, unfinished active account | Same recovery page in both code and password modes |
| Admin Users action or user profile | Super Admin / Customer Support can send a continuation email |
| Valid continuation link | Welcome back, masked email, Get verification code |
| Missing, expired or consumed link | Email-only recovery, with sign-in exit |
| Get verification code | POST issues and sends a fresh code, then opens the existing code/password page |
| Correct code | Server precheck; hide code entry and reveal password creation with lock icon and Enter your password heading |
| Matching password confirmation after code check | Revalidate code/token/expiry and atomically complete registration, consume credentials, regenerate session, audit sign-in, open dashboard |
| Failed initial email | Preserve saved details, remain on the form with an error and Retry sending code; retry uses the saved resume token |
| Failed code request | Keep recovery available, report failure, revoke the unsent code |

The existing auth shell, controls, OTP input, error patterns and responsive styling are reused. After the email check, remaining details slide down over 250 ms, with an immediate reveal for reduced motion. Change email returns to the email step. Loading disables submission; errors remain in-page. Successful signup, recovery and verification navigate without a success banner or redirect delay. Resend uses its cooldown feedback without a success banner. Verification and recovery show a centered static email icon; no Lottie assets are used.

## Security and persistence

- Recovery requires an active, nondeleted account without a password. Completed accounts retain normal sign-in.
- Links contain a random 32-byte token. A valid token is reused rather than replaced by a public lookup; issuance renews its 24-hour expiry. GET requests do not update users or send messages.
- Verification codes last 15 minutes. Database-scoped issuance enforces a 30-second cooldown and excludes active verification locks. Existing application throttles also limit account and IP requests.
- Completion matches the code, token, account, email snapshot and expiry in one conditional update. Only the winning request creates a session and sends welcome. Account state is rechecked before session creation.
- The code precheck does not persist verification or create a session. The hidden code is retained for final revalidation; a changed, expired or locked code returns the UI to code entry. Password validation errors stay on the password step. Existing combined clients remain compatible.
- Wrong-code attempts increment atomically; an active lock cannot be cleared by resend. Email edits revoke old signup tokens and codes without logging their contents.
- Admin sending requires the existing role restrictions, POST and CSRF. Success/failure audit events retain actor and time. Profile shows the last successful send. Missing audit schema fails closed.
- Admin cooldown uses the existing cache throttle. It is not a distributed atomic guarantee against simultaneous duplicate reminder emails.
- Sensitive auth pages use no-store and no-referrer. Email bodies escape names; credentials are excluded from activity history.

## Schema and release

The registration flow itself uses the existing verification-security, signup-resume, email-verification and user-activity schema (migrations 012, 013, 018 and 023). The subsequent shared email-safety release additionally requires SQL 026 before deployment; see [MAIL_SAFETY.md](MAIL_SAFETY.md). SQL 026 was applied locally on 2026-11-02 after a sign-in readiness failure. The admin action does not appear for blocked, deleted or completed accounts. No hosting deployment or real email send was performed.

## Verification

- `php tests/registration_recovery_test.php`: real models/controllers and CI query builder on in-memory SQLite; recorded mail/session adapters.
- `SMB_REGISTRATION_MYSQL=1 php tests/registration_recovery_test.php`: same flow checks on connection-local temporary MySQL tables in local development; no persistent user changes.
- `php tests/registration_recovery_email_test.php`: real continuation/verification views, links, escaping and code expiry copy.
- `node tests/signup_feedback_test.js`: real shared form callback proves silent success, immediate redirects, visible failures and unchanged defaults for other forms.
- `php tests/signup_welcome_test.php`: one-time completion, stale/replayed requests, failed writes, expired credentials and welcome-email failure.
- `php tests/signin_code_methods_test.php`: existing phone, email-code, audit and reset regressions; includes email-change credential revocation on temporary MySQL tables.
- Existing passwordless contracts, resend, OTP, phone helpers, PHP/JavaScript syntax and CRLF-aware whitespace checks.
- Local browser: email-first signup, reveal/change-email, expired-link recovery, mobile layout without horizontal overflow, and console check. Screenshots are local ignored artifacts under `evidence/registration-recovery/`.
- Independent source review found no blocking issues. SMTP is simulated in automated tests; live inbox delivery and authenticated admin browser sending remain unverified. Local sign-in browser inspection encountered a certificate error; no warning was bypassed.
