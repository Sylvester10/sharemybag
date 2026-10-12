<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Your Bag Has Been Updated';
include 'email_header.php'; ?>

<p class="greeting" style="<?= email_style('paragraph') . email_style('greeting') ?>">Hi <?php echo htmlspecialchars($traveller_name); ?>,</p>
<p style="<?= email_style('paragraph') ?>">A parcel has been <strong><?php echo htmlspecialchars($action_word); ?></strong> your booking. Here's a summary of what changed:</p>

<p class="section-label" style="<?= email_style('paragraph') . email_style('section-label') ?>">Parcel Details</p>
<table class="info-table" style="<?= email_style('info-table') ?>">
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Tracking ID</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?php echo htmlspecialchars($tracking_id); ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Item</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?php echo htmlspecialchars($item_name); ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Category</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?php echo htmlspecialchars($item_category); ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Size / Weight</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?php echo htmlspecialchars($item_size); ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Action</td>
        <td class="accent" style="<?= email_style('table-cell') . email_style('accent') ?>"><?php echo htmlspecialchars($action_word); ?></td>
    </tr>
</table>

<p class="section-label" style="<?= email_style('paragraph') . email_style('section-label') ?>">Updated Financials</p>
<table class="payout-table" style="<?= email_style('payout-table') ?>">
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Updated Booking Total</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?php echo htmlspecialchars($new_total); ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Your Updated Payout</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?php echo htmlspecialchars($new_commission); ?></td>
    </tr>
</table>

<p style="<?= email_style('paragraph') ?>margin-top:24px;">If you have questions about this change, contact us at <a href="mailto:<?php echo business_contact_email; ?>" class="text-link" style="<?= email_style('text-link') . email_style('text-link') ?>"><?php echo business_contact_email; ?></a> or call <strong><?php echo business_phone_number; ?></strong>.</p>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Thank you for being a <?php echo business_name; ?> traveller.<br><strong>— The <?php echo business_name; ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
