<?php $email_title = 'Email Verification';
include 'email_header.php'; ?>

<p class="greeting">Hi <?= $firstname ?>,</p>
<p>Welcome to <?= business ?>! To get started, please verify your email address using the code below. This confirms you own this account.</p>

<div class="code-wrap">
    <div class="code-label">Your verification code</div>
    <div class="code-box"><?= $verification_code ?></div>
</div>

<p>If you didn't create an account, you can safely ignore this email.</p>
<p>If you have any questions or need further assistance, don't hesitate to reach out — we're here to help.</p>

<hr class="divider">
<p class="sign-off">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
