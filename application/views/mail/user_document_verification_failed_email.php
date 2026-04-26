<?php $email_title = 'Verification Unsuccessful';
include 'email_header.php'; ?>

<p class="greeting">Hi <?= $firstname ?>,</p>

<div class="fail-banner">
    <span class="banner-icon">❌</span>
    <p class="banner-title-red">Identity Verification Unsuccessful</p>
</div>

<p>We were unable to verify your identity this time. Here are the most common reasons this happens:</p>

<p class="reasons-label">Common reasons</p>
<ul class="reasons-list">
    <li><span class="reason-dot">!</span> Your selfie or ID photos were not taken in clear, well-lit conditions.</li>
    <li><span class="reason-dot">!</span> Part of your face was obstructed in the selfie, or details were cut off on the ID.</li>
    <li><span class="reason-dot">!</span> The document submitted was not a valid or approved identification type.</li>
    <li><span class="reason-dot">!</span> The ID submitted has expired.</li>
</ul>

<p>Please log in to your account to try again, keeping the above guidelines in mind.</p>

<div class="btn-wrap">
    <a href="<?php echo base_url('login'); ?>" class="btn">Log In &amp; Try Again</a>
</div>
<p style="text-align:center; font-size:13px; color:#6b7280; margin-top:12px;">
    Still having trouble? <a href="https://wa.me/message/AWBY2J7LXISDM1" class="text-link">Chat with our support team →</a>
</p>

<hr class="divider">
<p class="sign-off">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
