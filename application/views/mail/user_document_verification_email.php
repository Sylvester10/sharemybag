<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Document Submitted';
include 'email_header.php'; ?>

<?= email_image_card('documents_under_review') ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= $firstname ?>,</p>

<p style="<?= email_style('paragraph') ?>">Thank you for submitting your <span class="id-badge" style="<?= email_style('id-badge') ?>"><?= $id_type ?></span> for verification.</p>

<p style="<?= email_style('paragraph') ?>">Our compliance team is currently reviewing your documents. You'll receive a notification as soon on your verification status update.</p>

<p style="<?= email_style('paragraph') ?>">We appreciate your patience. If you have any questions or need assistance in the meantime, don't hesitate to reach out — we're always here to help.</p>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Thank you for choosing <?= business ?>.<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
