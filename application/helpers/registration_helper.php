<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Registration is complete when the verified user has created their password. */
function registration_can_resume($user)
{
    return $user && (int) ($user->account_status ?? 0) === 1
        && empty($user->deleted_at) && empty($user->password);
}

function registration_email_hint($email)
{
    $parts = explode('@', (string) $email, 2);
    return substr($parts[0], 0, 1) . '***@' . ($parts[1] ?? '');
}

function registration_email_action($user)
{
    if (!registration_can_resume($user)) { return ''; }
    return '<div class="admin-registration-email-action" data-url="' . html_escape(base_url('admin_users/send_registration_email/' . (int) $user->id)) . '">'
        . '<button type="button" class="btn btn-default btn-sm btn-block action-btn" data-send-registration-email>'
        . '<i class="las la-envelope" aria-hidden="true"></i> Send registration email</button>'
        . '<p class="small" data-registration-email-status role="status" aria-live="polite"></p>'
        . '</div>';
}
