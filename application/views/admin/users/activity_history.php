<?php defined('BASEPATH') or exit('No direct script access allowed');
$events = array('details_updated' => 'Details updated', 'phone_cleared' => 'Phone cleared',
    'phone_verified' => 'Phone verified', 'phone_signin_updated' => 'Phone sign-in updated',
    'identity_updated' => 'Identity details updated', 'signed_in' => 'Signed in',
    'signed_out' => 'Signed out', 'admin_access' => 'Admin accessed account');
$methods = array('password' => 'Password', 'email_code' => 'Email code',
    'whatsapp_code' => 'WhatsApp code', 'sms_code' => 'SMS code');
$format = function ($field, $value) {
    if ($value === null || $value === '') { return '—'; }
    if ($field === 'phone_signin_enabled') { return (int) $value ? 'Enabled' : 'Disabled'; }
    if ($field === 'is_verified') { return array(0 => 'Unverified', 1 => 'Pending', 2 => 'Approved')[(int) $value] ?? (string) $value; }
    if ($field === 'account_status') { return (int) $value ? 'Active' : 'Blocked'; }
    return (string) $value;
}; ?>
<?php if (!$available): ?>
    <p class="text-muted">Activity History is not available yet.</p>
<?php elseif (!$rows): ?>
    <p class="text-muted">No activity recorded yet.</p>
<?php else: ?>
    <?php foreach ($rows as $entry): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><?= html_escape($events[$entry->event] ?? 'Account updated') ?></strong>
                <div class="small text-muted">
                    <?= html_escape($entry->date_added) ?> &middot;
                    <?= html_escape(ucfirst($entry->actor_type) . ': ' . $entry->actor_name) ?>
                    <?php if ($entry->method): ?> &middot; <?= html_escape($methods[$entry->method] ?? $entry->method) ?><?php endif; ?>
                </div>
            </div>
            <?php $changes = json_decode((string) $entry->changes, true); ?>
            <?php if (is_array($changes) && $changes): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" style="margin-bottom:0;table-layout:fixed">
                        <thead><tr><th scope="col">Field</th><th scope="col">Previous</th><th scope="col">New</th></tr></thead>
                        <tbody>
                        <?php foreach ($changes as $field => $values): ?>
                            <?php if (!isset(User_activity_model::FIELDS[$field])) { continue; } ?>
                            <tr style="overflow-wrap:anywhere">
                                <td><?= html_escape(User_activity_model::FIELDS[$field]) ?></td>
                                <td><?= html_escape($format($field, $values['before'] ?? null)) ?></td>
                                <td><?= html_escape($format($field, $values['after'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<div class="text-center">
    <?php if ($page > 1): ?><button type="button" class="btn btn-default btn-sm" data-activity-page="<?= $page - 1 ?>">Newer</button><?php endif; ?>
    <?php if ($more): ?><button type="button" class="btn btn-default btn-sm" data-activity-page="<?= $page + 1 ?>">Older</button><?php endif; ?>
</div>
