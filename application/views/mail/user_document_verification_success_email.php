<?php $email_title = 'Verification Successful';
include 'email_header.php'; ?>

<div class="success-banner">
    <span class="banner-icon"><img src="<?php echo production_url('assets/general/approve.png'); ?>" width="50" height="50" alt="<?= business ?>"></span>
    <p class="banner-title-green">Identity Verification Successful!</p>
</div>

<p class="greeting">Hi <?= $firstname ?>,</p>

<p>You now have full access to everything <?= business ?> has to offer:</p>

<table class="features" cellpadding="0" cellspacing="0">
    <tr>
        <td class="feature-item">
            <span class="feature-icon">🔍</span>
            <span class="feature-text">Find Travellers</span>
        </td>
        <td class="feature-item" style="border-left:none;">
            <span class="feature-icon">🧳</span>
            <span class="feature-text">Buy Luggage Space</span>
        </td>
        <td class="feature-item" style="border-left:none;">
            <span class="feature-icon">📦</span>
            <span class="feature-text">Send Parcels</span>
        </td>
    </tr>
</table>

<div class="btn-wrap">
    <a href="<?php echo base_url('home/search'); ?>" class="btn">Find a Traveller</a>
</div>

<hr class="divider">
<p class="sign-off">Welcome aboard,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
