<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Verification Successful';
include 'email_header.php'; ?>

<?= email_image_card('identity_verification_successful') ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?= $firstname ?>,</p>

<p style="<?= email_style('paragraph') ?>">Your identity verification was successful.</p>

<p style="<?= email_style('paragraph') ?>">You now have full access to everything <?= business ?> has to offer:</p>

<table class="features" cellpadding="0" cellspacing="0" style="<?= email_style('features') ?>">
    <tr>
        <td class="feature-item" style="<?= email_style('table-cell') . email_style('feature-item') ?>">
            <span class="feature-text" style="<?= email_style('feature-text') ?>">Find Travellers</span>
        </td>
        <td class="feature-item" style="<?= email_style('table-cell') . email_style('feature-item') ?>border-left:none;">
            <span class="feature-text" style="<?= email_style('feature-text') ?>">Buy Luggage Space</span>
        </td>
        <td class="feature-item" style="<?= email_style('table-cell') . email_style('feature-item') ?>border-left:none;">
            <span class="feature-text" style="<?= email_style('feature-text') ?>">Send Parcels</span>
        </td>
    </tr>
</table>

<div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
    <?= email_button(base_url('home/search'), 'Find a Traveller') ?>
</div>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Welcome aboard,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
