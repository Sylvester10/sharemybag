<?php $email_template = basename(__FILE__, '.php'); $email_title = 'System Notification';
include 'email_header.php'; ?>

<?= email_message_html($message) ?>

<?php include 'email_footer.php'; ?>
