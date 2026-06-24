<?php $email_title = 'Verification Unsuccessful';
include 'email_header.php'; ?>

<div class="fail-banner">
    <span class="banner-icon"><img src="<?php echo production_url('assets/general/reject.png'); ?>" width="50" height="50" alt="<?= business ?>"></span>
    <p class="banner-title-red">Identity Verification Unsuccessful</p>
</div>

<p class="greeting">Hi <?= $firstname ?>,</p>

<p>We could not verify your account yet.</p>

<p><strong>Reason:</strong> <?= html_escape($rejection_reason ?? 'Your documents need to be re-uploaded.') ?></p>

<?php if (!empty($rejection_note)) { ?>
    <p><strong>Additional note:</strong> <?= nl2br(html_escape($rejection_note)) ?></p>
<?php } ?>

<p>Please log in to your account, update the affected document, and submit your verification again.</p>

<div class="btn-wrap">
    <a href="<?php echo base_url('login'); ?>" class="btn">Log In &amp; Try Again</a>
</div>
<p style="text-align:center; font-size:13px; color:#6b7280; margin-top:12px;">
    Still having trouble? <a href="https://wa.me/message/AWBY2J7LXISDM1" class="text-link">Chat with our support team →</a>
</p>

<hr class="divider">
<p class="sign-off">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
