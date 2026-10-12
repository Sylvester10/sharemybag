<?php $email_template = basename(__FILE__, '.php'); $email_title = 'New Booking Alert';
include 'email_header.php'; ?>

<div style="<?= email_style('') ?>">
    <p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi Admin,</p>
    <p style="<?= email_style('paragraph') ?>">A new booking has been made by <strong><?= $agent_name ?></strong>. Please log in to your admin dashboard to review the details.</p>

    <div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
        <?= email_button('https://sharemybag.co.uk/admin', 'View in Dashboard') ?>
    </div>
</div>

<?php include 'email_footer.php'; ?>
