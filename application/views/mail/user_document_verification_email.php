<?php $email_title = 'Document Submitted';
include 'email_header.php'; ?>

<p class="greeting">Hi <?= $firstname ?>,</p>
<p>Thank you for submitting your <span class="id-badge"><?= $id_type ?></span> for verification.</p>

<div class="status-card">
    <div class="status-icon"><img src="<?php echo production_url('assets/general/penfing.png'); ?>" width="50" height="50" alt="<?= business ?>"></div>
    <div class="status-body">
        <p class="status-title">Verification in progress</p>
        <p class="status-text">Our compliance team is currently reviewing your documents. You'll receive a notification as soon as your account has been approved.</p>
    </div>
</div>

<p>We appreciate your patience. If you have any questions or need assistance in the meantime, don't hesitate to reach out — we're always here to help.</p>

<hr class="divider">
<p class="sign-off">Thank you for choosing <?= business ?>.<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
