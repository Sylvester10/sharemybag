<?php $email_title = 'New Traveller Registration';
include 'email_header.php'; ?>

<div style="text-align:center;">
    <div class="alert-badge"><span class="alert-dot"></span> New Traveller</div>
    <p class="greeting">Hi Admin,</p>
    <p><strong><?= $fullname; ?></strong> has just signed up as a new traveller. Please log in to your admin dashboard to review their details and approve their account.</p>

    <div class="btn-wrap">
        <a href="https://sharemybag.co.uk/admin" class="btn">Review &amp; Approve</a>
    </div>
</div>

<?php include 'email_footer.php'; ?>
