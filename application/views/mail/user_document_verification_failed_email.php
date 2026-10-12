<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Verification Unsuccessful';
include 'email_header.php'; ?>

<?= email_image_card('identity_verification_unsuccessful') ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= $firstname ?>,</p>

<p style="<?= email_style('paragraph') ?>">We could not verify your account yet.</p>

<p style="<?= email_style('paragraph') ?>"><strong>Reason:</strong> <?= html_escape($rejection_reason ?? 'Your documents need to be re-uploaded.') ?></p>

<?php if (!empty($rejection_note)) { ?>
    <p style="<?= email_style('paragraph') ?>"><strong>Additional note:</strong> <?= nl2br(html_escape($rejection_note)) ?></p>
<?php } ?>

<p style="<?= email_style('paragraph') ?>">Please log in to your account, update the affected document, and submit your verification again.</p>

<div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
    <?= email_button(base_url('login'), 'Log In & Try Again') ?>
</div>
<p style="<?= email_style('paragraph') ?>text-align:center; font-size:13px; color:#6b7280; margin-top:12px;">
    Still having trouble? <a href="https://wa.me/message/AWBY2J7LXISDM1" class="text-link" style="<?= email_style('text-link') . email_style('text-link') ?>">Chat with our support team →</a>
</p>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
