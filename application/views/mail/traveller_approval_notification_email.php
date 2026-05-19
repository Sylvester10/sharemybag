<?php $email_title = 'Account Approved!';
include 'email_header.php'; ?>

<p class="greeting">Hi <?= $fullname ?>,</p>

<div class="approved-banner">
    <span class="banner-icon">✈️</span>
    <p class="banner-title-orange">You're a Verified Traveller!</p>
    <p class="banner-sub-orange">We'll be in touch as soon as we have a sender ready for your route.</p>
</div>

<p>Congratulations on joining our network of verified travellers. Here are a few things to keep in mind:</p>

<p style="font-size:13px; font-weight:700; color:#111827; margin-bottom:12px;">What to expect</p>
<ul class="tips-list">
    <li>
        <div class="tip-icon">🕐</div>
        <div class="tip-body">
            <span class="tip-title">Last Minute Bookings</span>
            You'll likely start receiving customers a few days before your trip, and up to 24 hours before your flight.
        </div>
    </li>
    <li>
        <div class="tip-icon">📋</div>
        <div class="tip-body">
            <span class="tip-title">Check the Content List</span>
            We'll send you a detailed list for every customer. Let us know immediately if the physical parcel doesn't match.
        </div>
    </li>
    <li>
        <div class="tip-icon">📌</div>
        <div class="tip-body">
            <span class="tip-title">Follow Our Guidelines</span>
            Your content list includes specific checking guidelines curated from NDLEA research and experienced travellers.
        </div>
    </li>
    <li>
        <div class="tip-icon">💳</div>
        <div class="tip-body">
            <span class="tip-title">Payouts</span>
            Your payment will be released 24 hours after your successful arrival.
        </div>
    </li>
</ul>

<p style="font-size:13px; font-weight:700; color:#111827; margin:22px 0 12px;">Packing Tips</p>
<ul class="tips-list">
    <li>
        <div class="tip-icon">📦</div>
        <div class="tip-body">
            <span class="tip-title">Respect Customer Parcels</span>
            Handle every parcel carefully and keep it in the same condition you received it.
        </div>
    </li>
    <li>
        <div class="tip-icon">👕</div>
        <div class="tip-body">
            <span class="tip-title">Separate Clothing and Food</span>
            Pack clothing separately from food items to reduce contamination and odour transfer.
        </div>
    </li>
    <li>
        <div class="tip-icon">🛡️</div>
        <div class="tip-body">
            <span class="tip-title">Do Not Accept Packed Bags</span>
            For your security, do not accept parcels already packed in bags. The contents should be transferred into a nylon or baco bag that you can inspect.
        </div>
    </li>
</ul>

<p>Have questions? Visit our <a href="<?php echo base_url('travellers'); ?>" target="_blank" class="text-link">FAQ section</a> or reach us on <a href="https://wa.me/2348149265396" target="_blank" class="text-link">WhatsApp</a>.</p>

<hr class="divider">
<p class="sign-off">Thanks for partnering with us.<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
