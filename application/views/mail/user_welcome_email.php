<?php $email_template = basename(__FILE__, '.php');
$email_title = 'Welcome to ' . business;
include 'email_header.php'; ?>

<p class="greeting" style="<?= email_style('greeting') ?>">Hi <?= html_escape($firstname ?? '') ?>,</p>
<p style="<?= email_style('paragraph') ?>">Welcome to <?= business ?>!</p>
<p style="<?= email_style('paragraph') ?>">You now have full access to send and receive parcels through our trusted community of travellers.</p>

<div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
    <?= email_button(production_url('login'), 'Sign In') ?>
</div>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('sign-off') ?>">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
