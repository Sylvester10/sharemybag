<?php
require __DIR__ . '/support/email_render.php';

function email_assert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$renderer = new EmailPreviewRenderer(APPPATH . 'views/mail');
$copy = json_decode(file_get_contents(__DIR__ . '/fixtures/email_copy.json'), true);
// Explicit copy changes authorized during the latest design review.
$copy['user_document_verification_email'] = str_replace('Verificationinprogress', '', $copy['user_document_verification_email']);
$copy['user_document_verification_success_email'] = str_replace('IdentityVerificationSuccessful!HiAmara,', 'HiAmara,Youridentityverificationwassuccessful.', $copy['user_document_verification_success_email']);
$copy['user_document_verification_failed_email'] = str_replace('IdentityVerificationUnsuccessful', '', $copy['user_document_verification_failed_email']);
$copy['user_booking_notification_email'] = str_replace('Paymentreceived:', 'Paymentreceived:Bookingreference:SMB-DEMO-1042', $copy['user_booking_notification_email']);
$copy['user_booking_notification_email'] = str_replace('Pleasedropyouritems', 'Pleaserememberyourdrop-offdatePleasedropyouritems', $copy['user_booking_notification_email']);
$onlyTemplate = getenv('SMB_EMAIL_TEMPLATE');
if ($onlyTemplate !== false && $onlyTemplate !== '') {
    email_assert(isset($copy[$onlyTemplate]), 'Unknown email template selected for verification');
    $copy = array($onlyTemplate => $copy[$onlyTemplate]);
}
$renderDirectory = getenv('SMB_EMAIL_RENDER_DIR');
$imageSettings = require APPPATH . 'config/email_images.php';
$imageKeys = array('user_document_verification_email'=>'documents_under_review','user_document_verification_success_email'=>'identity_verification_successful','user_document_verification_failed_email'=>'identity_verification_unsuccessful','traveller_approval_notification_email'=>'traveller_approved');
if ($renderDirectory && !is_dir($renderDirectory)) {
    mkdir($renderDirectory, 0777, true);
}

foreach ($copy as $template => $expected) {
    $html = $renderer->view($template, email_preview_data(), true);
    $xpath = new DOMXPath(email_document($html));
    $body = $xpath->query('//td[@class="email-content"]');
    email_assert($body->length === 1, "$template: missing shared content card");
    email_assert(email_copy_normalize($body->item(0)->textContent) === $expected, "$template: original wording changed");
    $links = $xpath->query('//a[@class="email-footer-link"]');
    email_assert($links->length === 3, "$template: missing policy links");
    foreach ($links as $link) {
        email_assert(strpos($link->getAttribute('href'), 'https://sharemybag.co.uk/') === 0, "$template: policy URL must be public HTTPS");
        email_assert(strpos($link->getAttribute('style'), 'text-decoration:none') !== false, "$template: footer links must have no underline");
    }
    email_assert(strpos($html, 'width="136"') !== false, "$template: selected logo width changed");
    email_assert(strpos($html, "font-family:'Poppins'") !== false && strpos($html, 'Arial,Helvetica,sans-serif') !== false, "$template: missing Poppins and fallback");
    email_assert($xpath->query('//h1')->length === 0, "$template: large body title remains");
    email_assert($xpath->query('//p[@class="email-reason"]')->length === 1, "$template: receiving-email explanation is missing");
    if (in_array($template, array('user_document_verification_email', 'user_document_verification_success_email', 'user_document_verification_failed_email', 'traveller_approval_notification_email'))) {
        $cards = $xpath->query('//table[@class="email-image-card"]');
        email_assert($cards->length === 1, "$template: image card missing");
        if (trim($imageSettings[$imageKeys[$template]] ?? '') !== '') {
            email_assert($cards->item(0)->getElementsByTagName('img')->length === 1, "$template: configured image was not rendered");
        }
        email_assert(email_copy_normalize($cards->item(0)->textContent) === '', "$template: image card must not contain status copy");
        email_assert($xpath->query('//table[@class="email-image-card"]/following-sibling::p[contains(@class,"greeting")]')->length === 1, "$template: image card must precede greeting");
    }
    email_assert(!preg_match('/[\x{1F300}-\x{1FAFF}]/u', $html), "$template: emoji remain");
    email_assert(strpos($html, 'top-bar') === false && strpos($html, 'border-top:4px') === false, "$template: decorative top border remains");
    email_assert(strpos($html, 'notif-label') === false && strpos($html, 'alert-badge') === false, "$template: small notification label remains");
    email_assert(strpos($html, 'Here to help, every step') === false, "$template: footer slogan remains");
    if ($renderDirectory) {
        file_put_contents($renderDirectory . '/' . $template . '.html', $html);
    }
}

$message = email_message_html(nl2br("First paragraph.\nSecond line.\n\nNext paragraph."));
email_assert(substr_count($message, '<p ') === 2, 'Blank lines should become separate paragraphs');
email_assert(strpos($message, "First paragraph.<br />\nSecond line.") !== false, 'Single line breaks must survive');
email_assert(strpos($message, 'Next paragraph.') !== false, 'Paragraph wording must survive');
email_assert(strpos(email_message_html('<p>Already formatted.</p>'), '<p style=') === false, 'Existing blocks must not be nested in paragraphs');
email_assert(strpos(email_call2action_blue('https://example.com/?a=1&b=2', 'Reset Password'), 'background-color:#f36b24') !== false, 'Generated buttons should use the shared style');

echo 'PASS: ' . count($copy) . " email templates preserve approved copy, image placement, footer reasons, and Poppins styling.\n";
