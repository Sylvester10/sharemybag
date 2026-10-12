<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Password Recovery';
include 'email_header.php'; ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= $firstname ?>,</p>
<p style="<?= email_style('paragraph') ?>">We received a request to reset your password. Use the code below along with the button to create a new password.</p>

<div class="code-wrap" style="<?= email_style('code-wrap') ?>">
    <div class="code-label" style="<?= email_style('code-label') ?>">Password reset code</div>
    <div class="code-box" style="<?= email_style('code-box') ?>"><?= $pass_reset_code ?></div>
</div>

<div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
    <?= $reset_link; ?>
</div>

<div class="security-note" style="<?= email_style('security-note') ?>">
    If you didn't request a password reset, please ignore this email. Your account remains secure.
</div>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
