<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Complete your registration';
include 'email_header.php'; ?>
<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= html_escape($firstname ?? '') ?>,</p>
<p style="<?= email_style('paragraph') ?>">Your ShareMyBag registration is started and your details are saved. Continue below whenever you are ready.</p>
<div class="btn-wrap" style="<?= email_style('btn-wrap') ?>"><?= email_button($resume_link, 'Continue registration') ?></div>
<p style="<?= email_style('paragraph') ?>">On the next page, select Get verification code. We will send a fresh code to this email address so you can verify your email and create your password.</p>
<p style="<?= email_style('paragraph') ?>">This link expires in 24 hours. If it expires, enter your email on the sign-in or signup page to resume. If you did not start this registration, you can ignore this email.</p>
<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('sign-off') ?>">Best regards,<br><strong>The <?= business ?> Team</strong></p>
<?php include 'email_footer.php'; ?>
