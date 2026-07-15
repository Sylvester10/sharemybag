<?php $email_title = 'Booking Confirmation';
include 'email_header.php'; ?>

<p class="greeting">Thank you for your order!</p>
<p style="margin-bottom:4px;">Payment received:</p>
<p class="amount-display"><?= $currency ?><?= number_format($total_amount, 2) ?></p>

<p class="section-label">Item Details</p>
<?php $item_details = json_decode($items); ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Category</th>
            <th>Item Name</th>
            <th>Size</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($item_details as $item):
            $unit_display = booking_category_unit($item->category);
        ?>
            <tr>
                <td><?= $item->category ?></td>
                <td><?= $item->item_name ?></td>
                <td><?= $item->size ?><?= $unit_display ?></td>
                <td><?= $currency ?><?= number_format($item->price, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="section-label">Traveller Details</p>
<table class="info-table">
    <tr>
        <td class="label">Traveller Name</td>
        <td class="value">
            <?php
            $parts = array_values(array_filter(explode(' ', trim($traveller_name)), 'strlen'));
            $first = $parts[0] ?? '';
            $lastInitial = (count($parts) > 1) ? strtoupper($parts[count($parts) - 1][0]) . '.' : '';
            echo htmlspecialchars(trim($first . ' ' . $lastInitial));
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">Contact Number</td>
        <td class="value"><?= business_phone_number ?></td>
    </tr>
    <tr>
        <td class="label">Departure Airport</td>
        <td class="value"><?= $traveller_departure_state ?></td>
    </tr>
    <tr>
        <td class="label">Arrival Airport</td>
        <td class="value"><?= $traveller_arrival_airport ?></td>
    </tr>
    <tr>
        <td class="label">Departure Date</td>
        <td class="value"><?= $traveller_departure_date ?></td>
    </tr>
    <tr>
        <td class="label">Arrival Date</td>
        <td class="value"><?= $traveller_arrival_date ?></td>
    </tr>
    <tr>
        <td class="label">Current Location</td>
        <td class="value"><?= $traveller_current_state ?></td>
    </tr>
    <tr>
        <td class="label">Final Destination</td>
        <td class="value"><?= $traveller_arrival_state ?></td>
    </tr>
</table>

<p style="font-size:13px; color:#6b7280;">
    First drop-off: <strong><?= $traveller_drop_address1 ?></strong> on <strong><?= $traveller_drop_date1 ?></strong>.
    Last drop-off: <strong><?= $traveller_drop_address2 ?></strong> on <strong><?= $traveller_drop_date2 ?></strong>.
</p>

<div class="warning-box">
    ⚠️ Please drop your items off with the traveller by your region's last drop-off date. There will be no refund or transfer of service to another traveller.
</div>

<p style="font-size:13px; color:#6b7280;">You can find your travellers details by clicking on the history button on your account.</p>

<p style="font-size:13px; color:#6b7280;">Please inform your packer that illegal drugs are strictly prohibited. For a full list of prohibited items, check our <a href="<?php echo base_url(); ?>#faqss" class="text-link">FAQ section</a>.</p>

<hr class="divider">
<p class="sign-off">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
