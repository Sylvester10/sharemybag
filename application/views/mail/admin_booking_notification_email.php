<?php $email_title = 'New Booking Alert';
include 'email_header.php'; ?>

<div style="text-align:center;">
    <div class="alert-badge"><span class="alert-dot"></span> New Booking</div>
    <p class="greeting">Hi Admin,</p>
    <p>A new booking has been made by <strong><?= $agent_name ?></strong>. Please log in to your admin dashboard to review the details.</p>

    <div class="btn-wrap">
        <a href="https://sharemybag.co.uk/admin" class="btn">View in Dashboard</a>
    </div>
</div>

<?php include 'email_footer.php'; ?>
