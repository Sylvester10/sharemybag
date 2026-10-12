<?php $email_template = basename(__FILE__, '.php'); include 'email_header.php'; ?>

<p style="<?= email_style('greeting') ?>">Hello <?php echo html_escape($firstname ?? ''); ?>,</p>
<p style="<?= email_style('paragraph') ?>">Use this one-time code to sign in to your <?php echo html_escape(business_name); ?> account:</p>
<div style="<?= email_style('code-wrap') ?>"><div style="<?= email_style('code-box') ?>">
    <?php echo html_escape($login_code); ?>
</div></div>
<p style="<?= email_style('paragraph') ?>">This code expires in 10 minutes. If you did not request it, you can ignore this email.</p>

<?php include 'email_footer.php'; ?>
