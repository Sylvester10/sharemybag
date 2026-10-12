<?php $email_template = basename(__FILE__, '.php');
$email_title = 'Account Approved!';
include 'email_header.php'; ?>

<?= email_image_card('traveller_approved') ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= $fullname ?>,</p>
<p style="<?= email_style('paragraph') ?>">You're a Verified Traveller!</p>
<p style="<?= email_style('paragraph') ?>">We'll be in touch as soon as we have a sender ready for your route.</p>

<p style="<?= email_style('paragraph') ?>">Congratulations on joining our network of verified travellers. Here are a few things to keep in mind:</p>

<p style="<?= email_style('paragraph') ?>font-size:13px; font-weight:700; color:#111827; margin-bottom:12px;">What to expect</p>
<ul class="tips-list" style="<?= email_style('tips-list') ?>">
    <li style="<?= email_style('tip-item') ?>">
        <div class="tip-body" style="<?= email_style('') ?>">
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Last Minute Bookings</span>
            You'll likely start receiving customers a few days before your trip, and up to 24 hours before your flight.
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Check the Content List</span>
            We'll send you a detailed list for every customer. Let us know immediately if the physical parcel doesn't match.
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Follow Our Guidelines</span>
            Your content list includes specific checking guidelines curated from NDLEA research and experienced travellers.
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Payouts</span>
            Your payment will be released 24 hours after your successful arrival.
        </div>
    </li>
</ul>

<p style="<?= email_style('paragraph') ?>font-size:13px; font-weight:700; color:#111827; margin:22px 0 12px;">Packing Tips</p>
<ul class="tips-list" style="<?= email_style('tips-list') ?>">
    <li style="<?= email_style('tip-item') ?>">
        <div class="tip-body" style="<?= email_style('') ?>">
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Respect Customer Parcels</span>
            Handle every parcel carefully and keep it in the same condition you received it.
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Separate Clothing and Food</span>
            Pack clothing separately from food items to reduce contamination and odour transfer.
            <span class="tip-title" style="<?= email_style('tip-title') ?>">Do Not Accept Packed Bags</span>
            For your security, do not accept parcels already packed in bags. Contents should arrive in bags without compartments e.g nylon/baco bag or Ghana-must-go.
        </div>
    </li>
</ul>

<p style="<?= email_style('paragraph') ?>">Have questions? Visit our <a href="<?php echo base_url('travellers'); ?>" target="_blank" class="text-link" style="<?= email_style('text-link') . email_style('text-link') ?>">FAQ section</a> or reach us on <a href="https://wa.me/2348149265396" target="_blank" class="text-link" style="<?= email_style('text-link') . email_style('text-link') ?>">WhatsApp</a>.</p>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Thanks for partnering with us.<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
