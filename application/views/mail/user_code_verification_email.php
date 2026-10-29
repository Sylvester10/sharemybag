<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Email Verification';
include 'email_header.php'; ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= html_escape($firstname) ?>,</p>
<p style="<?= email_style('paragraph') ?>">Welcome to <?= business ?>! To get started, please verify your email address using the code below. This confirms you own this account.</p>

<div class="code-wrap" style="<?= email_style('code-wrap') ?>">
    <div class="code-label" style="<?= email_style('code-label') ?>">Your verification code</div>
    <div class="code-box" style="<?= email_style('code-box') ?>"><?= html_escape($verification_code) ?></div>
</div>

<?php if (!empty($resume_link)): ?>
<p style="<?= email_style('paragraph') ?>">This code expires in 15 minutes. If you closed the registration page, use the button below to continue.</p>
<div class="btn-wrap" style="<?= email_style('btn-wrap') ?>"><?= email_button($resume_link, 'Continue registration') ?></div>
<?php endif; ?>

<p style="<?= email_style('paragraph') ?>">If you didn't create an account, you can safely ignore this email.</p>
<p style="<?= email_style('paragraph') ?>">If you have any questions or need further assistance, don't hesitate to reach out — we're here to help.</p>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
