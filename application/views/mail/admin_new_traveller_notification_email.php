<?php $email_template = basename(__FILE__, '.php'); $email_title = 'New Traveller Registration';
include 'email_header.php'; ?>

<div style="<?= email_style('') ?>">
    <p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi Admin,</p>
    <p style="<?= email_style('paragraph') ?>"><strong><?= $fullname; ?></strong> has just signed up as a new traveller. Please log in to your admin dashboard to review their details and approve their account.</p>

    <div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
        <?= email_button('https://sharemybag.co.uk/admin', 'Review & Approve') ?>
    </div>
</div>

<?php include 'email_footer.php'; ?>
