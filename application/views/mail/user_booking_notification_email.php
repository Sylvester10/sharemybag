<?php $email_template = basename(__FILE__, '.php'); $email_title = 'Booking Confirmation';
include 'email_header.php'; ?>

<?php
$booking_support_url = booking_support_whatsapp_url(array(
    'tracking_id' => $tracking_id ?? '',
    'user_fullname' => $user_fullname ?? ($agent_name ?? ''),
    'traveller_name' => $traveller_name ?? '',
    'traveller_current_state' => $traveller_current_state ?? '',
    'traveller_departure_state' => $traveller_departure_state ?? '',
    'traveller_destination' => $traveller_destination ?? '',
    'traveller_arrival_state' => $traveller_arrival_state ?? '',
    'total_amount' => $total_amount ?? '',
    'currency' => $currency ?? '',
    'currency_code' => $currency_code ?? '',
));
?>

<p class="booking-thanks" style="<?= email_style('booking-thanks') ?>">Thank you for your order!</p>
<table class="payment-summary" role="presentation" cellspacing="0" cellpadding="0" border="0" style="<?= email_style('payment-summary') ?>">
    <tr>
        <td style="padding:18px 12px 18px 20px;font-size:12px;line-height:1.8;color:#756a5f;<?= email_style() ?>">
            Payment received:
            <?php if (!empty($tracking_id)) { ?><br><span style="font-size:10px;">Booking reference: <?= html_escape($tracking_id) ?></span><?php } ?>
        </td>
        <td align="right" style="padding:18px 20px 18px 0;font-size:28px;line-height:1.5;font-weight:700;color:#292727;<?= email_style() ?>"><?= $currency ?><?= number_format($total_amount, 2) ?></td>
    </tr>
</table>

<p class="section-label" style="<?= email_style('paragraph') . email_style('section-label') ?>">Item Details</p>
<?php $item_details = json_decode($items); ?>
<table class="data-table" style="<?= email_style('data-table') ?>">
    <thead>
        <tr>
            <th style="<?= email_style('table-heading') ?>">Category</th>
            <th style="<?= email_style('table-heading') ?>">Item Name</th>
            <th style="<?= email_style('table-heading') ?>">Size</th>
            <th style="<?= email_style('table-heading') ?>">Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($item_details as $item):
            $unit_display = booking_category_unit($item->category);
        ?>
            <tr>
                <td style="<?= email_style('table-cell') ?>"><?= $item->category ?></td>
                <td style="<?= email_style('table-cell') ?>"><?= $item->item_name ?></td>
                <td style="<?= email_style('table-cell') ?>"><?= $item->size ?><?= $unit_display ?></td>
                <td style="<?= email_style('table-cell') ?>"><?= $currency ?><?= number_format($item->price, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="section-label" style="<?= email_style('paragraph') . email_style('section-label') ?>">Traveller Details</p>
<table class="info-table" style="<?= email_style('info-table') ?>">
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Traveller Name</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>">
            <?php
            $parts = array_values(array_filter(explode(' ', trim($traveller_name)), 'strlen'));
            $first = $parts[0] ?? '';
            $lastInitial = (count($parts) > 1) ? strtoupper($parts[count($parts) - 1][0]) . '.' : '';
            echo htmlspecialchars(trim($first . ' ' . $lastInitial));
            ?>
        </td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Contact Number</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= business_phone_number ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Departure Airport</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= $traveller_departure_state ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Arrival Airport</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= $traveller_arrival_airport ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Departure Date</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= $traveller_departure_date ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Arrival Date</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= $traveller_arrival_date ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Current Location</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= $traveller_current_state ?></td>
    </tr>
    <tr>
        <td class="label" style="<?= email_style('table-cell') . email_style('label') ?>">Final Destination</td>
        <td class="value" style="<?= email_style('table-cell') . email_style('value') ?>"><?= $traveller_arrival_state ?></td>
    </tr>
</table>

<p style="<?= email_style('paragraph') ?>font-size:13px; color:#6b7280;">
    First drop-off: <strong><?= $traveller_drop_address1 ?></strong> on <strong><?= $traveller_drop_date1 ?></strong>.
    Last drop-off: <strong><?= $traveller_drop_address2 ?></strong> on <strong><?= $traveller_drop_date2 ?></strong>.
</p>

<div class="warning-box" style="<?= email_style('warning-box') ?>">
    <strong>Please remember your drop-off date</strong>
    <p style="margin:6px 0 0;font-size:13px;line-height:1.8;<?= email_style() ?>">Please drop your items off with the traveller by your region's last drop-off date. There will be no refund or transfer of service to another traveller.</p>
</div>

<p style="<?= email_style('paragraph') ?>font-size:13px; color:#6b7280;">You can find your travellers details by clicking on the history button on your account.</p>

<?php if ($booking_support_url !== '') { ?>
    <div class="btn-wrap" style="<?= email_style('btn-wrap') ?>">
        <?= email_button($booking_support_url, 'Need help with this parcel?') ?>
    </div>
<?php } ?>

<p style="<?= email_style('paragraph') ?>font-size:13px; color:#6b7280;">Please inform your packer that illegal drugs are strictly prohibited. For a full list of prohibited items, check our <a href="<?php echo base_url(); ?>#faqss" class="text-link" style="<?= email_style('text-link') . email_style('text-link') ?>">FAQ section</a>.</p>

<hr class="divider" style="<?= email_style('divider') ?>">
<p class="sign-off" style="<?= email_style('paragraph') . email_style('sign-off') ?>">Best regards,<br><strong>The <?= business ?> Team</strong></p>

<?php include 'email_footer.php'; ?>
