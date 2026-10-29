<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Inline styles shared by every email; custom-font support varies by client. */
function email_style($component = '')
{
    $font = "font-family:'Poppins',Arial,Helvetica,sans-serif;";
    $styles = array(
        'paragraph' => 'margin:0 0 18px;font-size:14px;line-height:1.85;color:#514c49;',
        'greeting' => 'margin:0 0 18px;font-size:17px;font-weight:600;line-height:1.6;color:#292727;',
        'booking-thanks' => 'margin:0 0 28px;font-size:17px;font-weight:600;line-height:1.6;color:#292727;text-align:center;',
        'payment-summary' => 'width:100%;table-layout:fixed;border-collapse:separate;background-color:#f7f6f4;border-radius:6px;margin:26px 0 28px;',
        'section-label' => 'margin:28px 0 12px;font-size:15px;font-weight:600;line-height:1.5;color:#292727;',
        'divider' => 'border:0;border-top:1px solid #eee9e4;margin:28px 0;',
        'sign-off' => 'margin:26px 0 0;font-size:13px;line-height:1.8;color:#645e58;',
        'text-link' => 'color:#f36b24;text-decoration:none;font-weight:600;',
        'btn-wrap' => 'text-align:left;margin:26px 0;',
        'btn' => 'display:inline-block;background-color:#f36b24;color:#ffffff;text-decoration:none;border-radius:6px;padding:10px 24px;font-size:12px;line-height:1.5;font-weight:500;',
        'info-table' => 'width:100%;border-collapse:collapse;table-layout:fixed;margin:0 0 20px;',
        'data-table' => 'width:100%;border-collapse:collapse;table-layout:fixed;margin:0 0 8px;',
        'payout-table' => 'width:100%;border-collapse:collapse;table-layout:fixed;margin:0 0 20px;',
        'table-cell' => 'padding:11px 4px;border-bottom:1px solid #eee9e4;font-size:13px;line-height:1.6;color:#514c49;vertical-align:top;overflow-wrap:anywhere;word-wrap:break-word;',
        'table-heading' => 'padding:11px 4px;border-bottom:1px solid #eee9e4;font-size:11px;line-height:1.6;font-weight:500;text-align:left;color:#756a5f;vertical-align:top;overflow-wrap:anywhere;word-wrap:break-word;',
        'label' => 'width:42%;color:#756a5f;text-align:left;',
        'value' => 'color:#292727;font-weight:600;text-align:right;',
        'accent' => 'color:#f36b24;font-weight:600;text-align:right;',
        'warning-box' => 'background-color:#f7f6f4;border-radius:6px;padding:18px 20px;margin:22px 0;font-size:13px;line-height:1.8;color:#64594f;',
        'security-note' => 'background-color:#f7f6f4;border-radius:6px;padding:18px 20px;margin:22px 0;font-size:13px;line-height:1.8;color:#64594f;',
        'code-wrap' => 'background-color:#f7f6f4;border-radius:6px;text-align:center;padding:22px 12px;margin:24px 0;',
        'code-label' => 'font-size:12px;line-height:1.5;color:#756a5f;margin:0 0 12px;',
        'code-box' => 'font-size:32px;line-height:1.3;font-weight:700;letter-spacing:7px;color:#f36b24;word-wrap:break-word;',
        'success-banner' => 'background-color:#f7f6f4;border-radius:6px;padding:22px 20px;text-align:left;margin:0 0 24px;',
        'fail-banner' => 'background-color:#f7f6f4;border-radius:6px;padding:22px 20px;text-align:left;margin:0 0 24px;',
        'approved-banner' => 'background-color:#f7f6f4;border-radius:6px;padding:22px 20px;text-align:left;margin:22px 0;',
        'banner-icon' => 'display:block;margin:0 0 14px;',
        'banner-title-green' => 'font-size:17px;line-height:1.5;font-weight:600;color:#292727;margin:0;',
        'banner-title-red' => 'font-size:17px;line-height:1.5;font-weight:600;color:#292727;margin:0;',
        'banner-title-orange' => 'font-size:17px;line-height:1.5;font-weight:600;color:#292727;margin:0 0 8px;',
        'banner-sub-orange' => 'font-size:13px;line-height:1.8;color:#64594f;margin:0;',
        'features' => 'width:100%;border-collapse:collapse;table-layout:fixed;margin:24px 0;',
        'feature-item' => 'width:33.33%;text-align:center;padding:16px 8px;background-color:#f7f6f4;border:0;vertical-align:top;',
        'feature-text' => 'font-size:12px;line-height:1.6;font-weight:600;color:#514c49;',
        'tips-list' => 'list-style:none;padding:0;margin:0 0 24px;',
        'tip-item' => 'background-color:#f7f6f4;border-radius:6px;padding:14px 16px;margin:0 0 8px;font-size:13px;line-height:1.8;color:#514c49;',
        'tip-title' => 'display:block;font-size:13px;font-weight:600;line-height:1.6;margin:10px 0 2px;color:#292727;',
        'amount-display' => 'font-size:28px;font-weight:700;line-height:1.5;color:#292727;margin:0 0 24px;',
        'id-badge' => 'font-size:12px;font-weight:600;color:#514c49;',
        'notification-message' => 'font-size:14px;line-height:1.85;color:#514c49;overflow-wrap:anywhere;word-wrap:break-word;',
    );
    return $font . ($styles[$component] ?? '');
}

/** Preserve supplied wording and single line breaks; turn blank lines into spaced paragraphs. */
function email_message_html($message)
{
    $message = (string) $message;
    if (preg_match('/<(?:p|div|table|ul|ol|h[1-6])\b/i', $message)) {
        return '<div style="' . email_style('notification-message') . '">' . $message . '</div>';
    }
    $html = '';
    foreach (preg_split('/(?:<br\s*\/?>(?:\r\n|\r|\n|[ \t])*){2,}/i', $message) as $paragraph) {
        $html .= '<p style="' . email_style('paragraph') . '">' . $paragraph . '</p>';
    }
    return $html;
}

/** Put button padding on a table cell for clients that ignore padding on links. */
function email_button($url, $caption)
{
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');
    return '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate;">'
        . '<tr><td bgcolor="#f36b24" style="background-color:#f36b24;border-radius:6px;padding:14px 24px;mso-padding-alt:14px 24px;'
        . email_style() . '"><a href="' . $url . '" style="display:inline-block;color:#ffffff;text-decoration:none;font-size:13px;line-height:1.5;font-weight:600;'
        . 'mso-text-raise:0;mso-padding-alt:0;' . email_style() . '">' . $caption . '</a></td></tr></table>';
}

/** An image-only card before the greeting; an unset image leaves a blank placeholder. */
function email_image_card($key)
{
    static $images;
    if ($images === null) {
        $images = require APPPATH . 'config/email_images.php';
    }
    $url = trim($images[$key] ?? '');
    // Uploaded project assets need absolute public URLs in delivered emails.
    if (strpos($url, 'assets/') === 0 && strpos($url, '..') === false && strpos($url, '\\') === false) {
        $url = production_url($url);
    }
    $valid = filter_var($url, FILTER_VALIDATE_URL) && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    $content = $valid
        ? '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="" height="200" style="display:block;width:auto;height:auto;max-width:100%;max-height:200px;margin:0 auto;border:0;border-radius:6px;">'
        : '&nbsp;';
    return '<table class="email-image-card" role="presentation" width="358" align="center" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:358px;border-collapse:separate;margin:0 auto 24px;">'
        . '<tr><td bgcolor="#f7f6f4" height="200" align="center" valign="middle" style="height:200px;padding:0;text-align:center;vertical-align:middle;background-color:#f7f6f4;border-radius:6px;line-height:0;font-size:0;">'
        . $content . '</td></tr></table>';
}

function email_receiving_reason($template)
{
    $reasons = array(
        'user_booking_notification_email' => 'You’re receiving this email because you made a booking with ShareMyBag.',
        'admin_general_notification_email' => 'You’re receiving this service update because you have a ShareMyBag account.',
        'user_code_verification_email' => 'You’re receiving this email because this address was used to register with ShareMyBag.',
        'user_welcome_email' => 'You’re receiving this email because you completed signup with ShareMyBag.',
        'user_registration_continuation_email' => 'You’re receiving this email to help you finish your ShareMyBag registration.',
        'user_login_code_email' => 'You’re receiving this email because a sign-in code was requested for your ShareMyBag account.',
        'password_recovery_email' => 'You’re receiving this email because a password reset was requested for your ShareMyBag account.',
        'user_document_verification_email' => 'You’re receiving this email about your ShareMyBag identity verification.',
        'user_document_verification_success_email' => 'You’re receiving this email about your ShareMyBag identity verification.',
        'user_document_verification_failed_email' => 'You’re receiving this email about your ShareMyBag identity verification.',
        'traveller_approval_notification_email' => 'You’re receiving this email about your ShareMyBag traveller account or booking.',
        'traveller_bag_update_email' => 'You’re receiving this email about your ShareMyBag traveller account or booking.',
        'admin_booking_notification_email' => 'You’re receiving this email as a ShareMyBag administrator.',
        'admin_new_traveller_notification_email' => 'You’re receiving this email as a ShareMyBag administrator.',
    );
    return $reasons[$template] ?? 'You’re receiving this email because you have a ShareMyBag account.';
}
