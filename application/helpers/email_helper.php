<?php
defined('BASEPATH') or exit('No direct script access allowed');


/* ======== Email Buttons =========== */

function email_call2action_red($url, $caption)
{
    require_once APPPATH . 'helpers/email_design_helper.php';
    return email_button($url, $caption . ' »');
}

function email_call2action_blue($url, $caption)
{
    require_once APPPATH . 'helpers/email_design_helper.php';
    return email_button($url, $caption);
}


// Transactional calls stay synchronous; bulk messages are processed by the CLI queue worker.
function send_email_notification($CI, $email, $subject, $data, $email_type, &$outcome = null, $bulk = false)
{
    $outcome = 'unavailable';
    try {
        require_once APPPATH . 'third_party/mail.php';
        $mail = getMailer();
        $mail->addAddress(trim((string) $email));
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $body = $CI->load->view('mail/' . $email_type, $data, true);
        $mail->Body = $body;
        $mail->AltBody = html_entity_decode(strip_tags(preg_replace('/<br\\s*\\/?\\s*>/i', "\n", $body)), ENT_QUOTES, 'UTF-8');
        return send_prepared_email($CI, $mail, $email_type, $outcome, $bulk);
    } catch (Throwable $error) {
        log_message('error', 'Mail construction failed: ' . $error->getMessage());
        return false;
    }
}

/** Common gate also covers legacy prepared PHPMailer messages. */
function send_prepared_email($CI, $mail, $template, &$outcome = null, $bulk = false)
{
    $outcome = 'unavailable';
    $safety = null;
    $claim = null;
    try {
        $recipients = $mail->getToAddresses();
        // One reservation represents exactly one recipient; bulk is expanded by the queue.
        if (count($recipients) !== 1 || $mail->getCcAddresses() || $mail->getBccAddresses()) {
            log_message('error', 'Mail rejected: expected one individually budgeted recipient.');
            return false;
        }
        $CI->load->model('mail_safety_model');
        $safety = $CI->mail_safety_model;
        $claim = $safety->reserve($recipients[0][0], $template, $bulk);
        if (!$claim['status']) {
            $outcome = $claim['outcome'];
            log_message('error', 'Mail not attempted: ' . $outcome . ' for template ' . basename($template));
            return false;
        }
        $sent = $mail->send();
        $safety->finish($claim['id'], $sent);
        $outcome = $sent ? 'sent' : 'failed';
        return $sent;
    } catch (Throwable $error) {
        if ($safety && !empty($claim['id'])) { $safety->finish($claim['id'], false); $outcome = 'failed'; }
        log_message('error', 'Mail sending failed: ' . $error->getMessage());
        return false;
    }
}

function send_bulk_email_notification($CI, $emails, $subject, $data, $email_type)
{
    // Prevent old callers from bypassing the paced queue and its eligibility checks.
    log_message('error', 'Immediate bulk mail is disabled; use the admin bulk queue.');
    return false;
}
