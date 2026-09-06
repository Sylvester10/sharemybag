<?php $email_title = 'Document Submitted';
include 'email_header.php'; ?>

<div class="success-banner">
    <span class="banner-icon"><img src="<?php echo production_url('assets/general/penfing.png'); ?>" width="50" height="50" alt="<?= business ?>"></span>
    <p class="banner-title-green">Verification in progress</p>
</div>

<p class="greeting">Hi <?= $firstname ?>,</p>

<p>Thank you for submitting your <span class="id-badge"><?= $id_type ?></span> for verification.</p>

<p>Our compliance team is currently reviewing your documents. You'll receive a notification soon on your verification status update.</p>

<p>We appreciate your patience. If you have any questions or need assistance in the meantime, don't hesitate to reach out — we're always here to help.</p>

<hr class="divider">
<p class="sign-off">Thank you for choosing <?= business ?>.<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
