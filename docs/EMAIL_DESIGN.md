# Shared email design

Approved on 12 October 2026: the second, letter-style preview. The logo sits above
a rounded white card, and the contact/policy footer sits beneath it. Desktop side
spacing is 48px and the logo is 136px wide; mobile side spacing is 24px.

Keep existing template wording, values, warnings, codes, and action destinations.
Remove small notification labels, emoji, the decorative top border, footer slogan,
and link underlines. Do not copy sample preview wording into production templates.

`application/helpers/email_design_helper.php` owns shared inline typography,
components, paragraph formatting, and table-based buttons. The header/footer
provide the common table layout. Poppins is loaded where supported, with
Arial/Helvetica/sans-serif fallbacks and a specific Arial override for Outlook.
Custom font rendering cannot be guaranteed in every email client.

The old standalone admin message now uses `admin_user_message_email.php`, retaining
its greeting, message, and Login label. Its unrelated Cryptoma shell and links are
replaced with ShareMyBag branding and the ShareMyBag login/policy destinations.

## Verification

Run `php tests/email_design_test.php` for isolated rendering of all 14 current
templates, comparison with original wording snapshots, policy links, font fallback,
notification-label/emoji removal, and paragraph preservation. The snapshots come
from the pre-change templates, excluding only the requested labels and emoji.

Set `SMB_EMAIL_RENDER_DIR` to an output directory to save the rendered HTML using
fictional account/booking data. This does not connect to the database or send mail.

PHP syntax, the focused design test, and the existing parcel-support contract test
passed. The existing traveller-approval static test fails before these changes
because its expected `$was_approved_traveller` marker is absent; traveller approval
logic was not edited. Independent code review found no outstanding scoped issues.

Browser inspection is layout proof, not inbox proof. SMTP delivery, real Gmail/
Outlook/Apple Mail rendering, and deployment remain unverified. Older dormant
booking methods already reference missing `*_notify_email` views; those paths were
not recreated because their original content is unavailable.


## Final review refinements

The latest approved changes remove all large body titles. The booking thank-you
is centered; the payment amount/reference remain in one card and the drop-off
warning remains in a titled callout. Receiving-email explanations now render in
all real templates, rather than only in the review mockups.

Four image-only cards precede the greetings: documents under review, identity
verification successful, identity verification unsuccessful, and traveller
approved. Their status copy belongs in the body outside the card. Successful
verification uses the approved sentence: "Your identity verification was successful."

Paste public HTTPS URLs or project asset paths (`assets/general/...`) into `application/config/email_images.php`. The four
keys match the four statuses above. Empty entries show blank image placeholders.
PNG/JPG images around 1200 x 480px are recommended; images scale to the full card
width and retain their aspect ratio. Do not place verification codes, financial
amounts, or other essential dynamic information only in an image.

The local review pages now render actual templates on every request. Refresh the
relevant page after editing an image URL. They use fictional fixtures and disable
email action buttons; they do not send mail or connect to the database.

Uploaded project assets are converted to public HTTPS URLs for sent emails. The local review server serves the current uploaded files directly, so reviews do not depend on deployed asset versions. Pending, approval, and rejection artwork was verified in all four configured image cards at full width without cropping.

The image cards are now 200px tall and capped at 358px wide, centered on desktop to remove the excess background beside the artwork. Images keep their aspect ratio and shrink to fit narrow screens. The mobile card remains 200px tall.


## Welcome after signup verification

The approved welcome email is separate from the verification code and sends only
when signup completes successfully. It uses the same shell, Poppins typography,
policy/reason footer, and no large body title. It does not imply identity/KYC approval.

`Users_model::update_user_verification()` atomically consumes the matching pending
signup code/token and requires an unset password, non-deleted account, valid expiry,
and no verification lock. Only the request that updates one row may trigger welcome.
Repeated or stale verification requests, code resends, and later sign-ins do not
send it again. Failed persistence returns a verification error and sends no welcome.

Delivery follows the existing synchronous mail helper: one attempt after persistence.
SMTP failure is logged without undoing verified signup. There is no automatic retry;
a process interruption between database completion and sending can leave the welcome
unsent. No schema change or mail queue was introduced.

Run `php tests/signup_welcome_test.php` for the real CodeIgniter query builder against
disposable SQLite with mocked SMTP. This proves transition/replay/failure behavior
locally, not production MySQL behavior or real inbox delivery. The review gallery
includes `user_welcome_email.html`, rendered from the actual template with sample data.

Welcome flow verification passed: disposable SQLite persistence and replay/failure tests, isolated welcome copy/layout test, PHP syntax, and independent review. Of the 14 per-template design checks, 13 pass. The traveller-approval copy check fails because the current independently edited template repeats traveller tips; that file was preserved during welcome implementation. Real SMTP delivery and production MySQL/inbox behavior remain unverified.
