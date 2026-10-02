<?php $this->load->view('mail/email_header'); ?>

<p>Hello <?php echo html_escape($firstname ?? ''); ?>,</p>
<p>Use this one-time code to sign in to your <?php echo html_escape(business_name); ?> account:</p>
<p style="font-size:28px;font-weight:700;letter-spacing:8px;margin:24px 0;">
    <?php echo html_escape($login_code); ?>
</p>
<p>This code expires in 10 minutes. If you did not request it, you can ignore this email.</p>

<?php $this->load->view('mail/email_footer'); ?>
