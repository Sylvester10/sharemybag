<?php $email_title = 'Your Bag Has Been Updated';
include 'email_header.php'; ?>

<p class="greeting">Hi <?php echo htmlspecialchars($traveller_name); ?>,</p>
<p>A parcel has been <strong><?php echo htmlspecialchars($action_word); ?></strong> your booking. Here's a summary of what changed:</p>

<p class="section-label">Parcel Details</p>
<table class="info-table">
    <tr>
        <td class="label">Tracking ID</td>
        <td class="value"><?php echo htmlspecialchars($tracking_id); ?></td>
    </tr>
    <tr>
        <td class="label">Item</td>
        <td class="value"><?php echo htmlspecialchars($item_name); ?></td>
    </tr>
    <tr>
        <td class="label">Category</td>
        <td class="value"><?php echo htmlspecialchars($item_category); ?></td>
    </tr>
    <tr>
        <td class="label">Size / Weight</td>
        <td class="value"><?php echo htmlspecialchars($item_size); ?></td>
    </tr>
    <tr>
        <td class="label">Action</td>
        <td class="accent"><?php echo htmlspecialchars($action_word); ?></td>
    </tr>
</table>

<p class="section-label">Updated Financials</p>
<table class="payout-table">
    <tr>
        <td class="label">Updated Booking Total</td>
        <td class="value"><?php echo htmlspecialchars($new_total); ?></td>
    </tr>
    <tr>
        <td class="label">Your Updated Payout</td>
        <td class="value"><?php echo htmlspecialchars($new_commission); ?></td>
    </tr>
</table>

<p style="margin-top:24px;">If you have questions about this change, contact us at <a href="mailto:<?php echo business_contact_email; ?>" class="text-link"><?php echo business_contact_email; ?></a> or call <strong><?php echo business_phone_number; ?></strong>.</p>

<hr class="divider">
<p class="sign-off">Thank you for being a <?php echo business_name; ?> traveller.<br><strong>— The <?php echo business_name; ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
