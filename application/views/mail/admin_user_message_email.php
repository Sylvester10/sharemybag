<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Notification'; include 'email_header.php'; ?>

<p style="<?= email_style('greeting') ?>">Hello <?= $lastname ?>, </p>
<?= email_message_html($message) ?>
<div style="<?= email_style('btn-wrap') ?>">
    <?= email_button(production_url('login'), 'Login') ?>
</div>

<?php include 'email_footer.php'; ?>
