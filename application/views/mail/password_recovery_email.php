<?php $email_title = 'Password Recovery';
include 'email_header.php'; ?>

<p class="greeting">Hi <?= $firstname ?>,</p>
<p>We received a request to reset your password. Use the code below along with the button to create a new password.</p>

<div class="code-wrap">
    <div class="code-label">Password reset code</div>
    <div class="code-box"><?= $pass_reset_code ?></div>
</div>

<div class="btn-wrap">
    <?= $reset_link; ?>
</div>

<div class="security-note">
    🔒 If you didn't request a password reset, please ignore this email. Your account remains secure.
</div>

<hr class="divider">
<p class="sign-off">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
