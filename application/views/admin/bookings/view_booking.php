<?php
$valueOrDefault = static function ($value, $default = 'N/A') {
    $value = trim((string) $value);
    return $value !== '' ? $value : $default;
};

$formatAddress = static function ($address, $locality = '', $postcode = '') use ($valueOrDefault) {
    $parts = array_filter(array_map('trim', array((string) $address, (string) $locality, (string) $postcode)));
    return $valueOrDefault(implode(', ', $parts));
};

$formatLocation = static function ($locality, $country) use ($valueOrDefault) {
    $parts = array_filter(array_map('trim', array((string) $locality, (string) $country)));
    return $valueOrDefault(implode(', ', $parts));
};

$formatDate = static function ($date) use ($valueOrDefault) {
    return trim((string) $date) !== '' ? x_date($date) : $valueOrDefault('');
};

$formatDateTime = static function ($date) use ($valueOrDefault) {
    return trim((string) $date) !== '' ? x_datetime_full($date) : $valueOrDefault('');
};

$items = json_decode($y->items);
$items = (is_array($items) || is_object($items)) ? $items : array();
$currencySymbol = currency_symbol_text($y->currency);
$paymentStatus = $this->booking_presenter->format_payment_status_badge($y->payment_status);
$paymentMethod = $this->booking_presenter->format_payment_method($y->payment_method, 'Offline');
$parcelGuarantee = (float) $y->insurance > 0
    ? $currencySymbol . number_format((float) $y->insurance, 2)
    : 'Not selected';
$moveLogs = array();
foreach (($booking_action_logs ?? array()) as $actionLog) {
    if (($actionLog->action ?? '') === Booking_action_log_model::ACTION_MOVE) {
        $moveLogs[] = $actionLog;
    }
}
?>

<div class="new-item admin-page-actions">
    <button type="button"
        class="btn btn-default btn-sm button-adjust admin-back-btn"
        data-fallback-url="<?php echo base_url('admin_bookings'); ?>"
        onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = this.getAttribute('data-fallback-url'); }">
        <i class="las la-arrow-left"></i> Back
    </button>
</div>

<p class="admin-status-line"><b>Delivery Status:</b> <?php echo delivery_status_badge($y->delivery_status); ?></p>

<?php if (payment_status_normalize($y->payment_status) === 'canceled') {
    $refundStatusKey = property_exists($y, 'refund_status') ? (string) $y->refund_status : '';
    $refundStatusLabels = array(
        'pending' => 'Pending Manual Refund',
        'refunded' => 'Refunded Manually',
        'not_required' => 'No Refund Required',
    );
    $refundStatusLabel = isset($refundStatusLabels[$refundStatusKey])
        ? $refundStatusLabels[$refundStatusKey]
        : $valueOrDefault($refundStatusKey);
?>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-times-circle"></i> Cancellation &amp; Refund Record</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8">
                <p><b>Cancelled:</b> <?php echo $formatDateTime(property_exists($y, 'cancelled_at') ? $y->cancelled_at : ''); ?></p>
                <p><b>Reason:</b> <?php echo nl2br(html_escape($valueOrDefault(property_exists($y, 'cancellation_reason') ? $y->cancellation_reason : ''))); ?></p>
                <p><b>Refund Status:</b> <?php echo html_escape($refundStatusLabel); ?></p>
                <p><b>Refund Amount:</b> <?php echo html_escape($currencySymbol . number_format((float) (property_exists($y, 'refund_amount') ? $y->refund_amount : 0), 2)); ?></p>
                <p><b>Refund Reference:</b> <?php echo html_escape($valueOrDefault(property_exists($y, 'refund_reference') ? $y->refund_reference : '')); ?></p>
                <p><b>Processed By:</b> <?php echo property_exists($y, 'cancelled_by_admin_id') && (int) $y->cancelled_by_admin_id > 0 ? 'Admin #' . (int) $y->cancelled_by_admin_id : 'N/A'; ?></p>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php if (!empty($moveLogs)) { ?>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-exchange-alt"></i> Parcel Move History</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8 table-responsive">
                <table class="table table-bordered table-hover cell-text-middle" style="text-align:left">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From Traveller</th>
                            <th>To Traveller</th>
                            <th>Reason</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($moveLogs as $moveLog) {
                            $beforeMove = json_decode((string) $moveLog->before_snapshot);
                            $afterMove = json_decode((string) $moveLog->after_snapshot);
                            $fromName = isset($beforeMove->source_traveller->fullname)
                                ? $beforeMove->source_traveller->fullname
                                : 'Traveller #' . (int) $moveLog->from_traveller_id;
                            $toName = isset($afterMove->target_traveller->fullname)
                                ? $afterMove->target_traveller->fullname
                                : 'Traveller #' . (int) $moveLog->to_traveller_id;
                        ?>
                            <tr>
                                <td><?php echo $formatDateTime($moveLog->date_added); ?></td>
                                <td><?php echo html_escape($fromName); ?></td>
                                <td><?php echo html_escape($toName); ?></td>
                                <td><?php echo nl2br(html_escape($valueOrDefault($moveLog->reason))); ?></td>
                                <td><?php echo 'Admin #' . (int) $moveLog->admin_id; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-book-open"></i> Booking Overview</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8">
                <p><b>Date Booked:</b> <?php echo $formatDateTime($y->date_added); ?></p>
                <p><b>Payment Status:</b> <?php echo $paymentStatus; ?></p>
                <p><b>Payment Method:</b> <?php echo $paymentMethod; ?></p>
                <p><b>Booking Status:</b> <?php echo html_escape($valueOrDefault($y->status, 'Pending')); ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-plane"></i> Route &amp; Traveller</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8">
                <p><b>Traveller:</b> <?php echo html_escape($valueOrDefault($y->traveller_name)); ?></p>
                <p><b>Phone Number:</b> <?php echo html_escape($valueOrDefault($y->traveller_contact)); ?></p>
                <p><b>Email Address:</b> <?php echo html_escape($valueOrDefault($y->traveller_email)); ?></p>
                <p><b>Current Location:</b> <?php echo html_escape($valueOrDefault($y->traveller_current_state)); ?></p>
                <p><b>Departure Airport:</b> <?php echo html_escape($valueOrDefault($y->traveller_departure_state)); ?></p>
                <p><b>Destination:</b> <?php echo html_escape($formatLocation($y->traveller_arrival_state, $y->traveller_destination)); ?></p>
                <p><b>Arrival Airport:</b> <?php echo html_escape($valueOrDefault($y->traveller_arrival_airport)); ?></p>
                <p><b>Travel Date:</b> <?php echo $formatDate($y->traveller_departure_date); ?></p>
                <p><b>Arrival Date:</b> <?php echo $formatDate($y->traveller_arrival_date); ?></p>
                <p><b>Traveller Drop-off:</b> <?php echo nl2br(html_escape($valueOrDefault($y->traveller_drop_address1))); ?></p>
                <p><b>Drop-off Date:</b> <?php echo $formatDate($y->traveller_drop_date1); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-address-book"></i> Booking Contacts</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8 table-responsive">
                <table class="table table-bordered table-hover cell-text-middle" style="text-align: left">
                    <thead>
                        <tr>
                            <th>SMB User</th>
                            <th>Agent / Sender</th>
                            <th>Receiver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <p><b>Name:</b> <?php echo html_escape($valueOrDefault($y->user_fullname)); ?></p>
                                <p><b>Email:</b> <?php echo html_escape($valueOrDefault($y->user_email)); ?></p>
                            </td>
                            <td>
                                <p><b>Name:</b> <?php echo html_escape($valueOrDefault($y->agent_name)); ?></p>
                                <p><b>Phone:</b> <?php echo html_escape($valueOrDefault($y->agent_phone)); ?></p>
                                <p><b>Email:</b> <?php echo html_escape($valueOrDefault($y->agent_email)); ?></p>
                                <p><b>Address:</b> <?php echo nl2br(html_escape($formatAddress($y->agent_address, $y->agent_locality, $y->agent_postcode))); ?></p>
                            </td>
                            <td>
                                <p><b>Name:</b> <?php echo html_escape($valueOrDefault($y->receiver_name)); ?></p>
                                <p><b>Phone:</b> <?php echo html_escape($valueOrDefault($y->receiver_phone)); ?></p>
                                <p><b>Email:</b> <?php echo html_escape($valueOrDefault($y->receiver_email)); ?></p>
                                <p><b>Address:</b> <?php echo nl2br(html_escape($formatAddress($y->receiver_address, $y->receiver_locality, $y->receiver_postcode))); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-box"></i> Parcel Details</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8 table-responsive">
                <table class="table table-bordered table-hover cell-text-middle" style="text-align: left">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)) { ?>
                            <?php foreach ($items as $item) { ?>
                                <tr>
                                    <td><?php echo html_escape($valueOrDefault(isset($item->item_name) ? $item->item_name : '')); ?></td>
                                    <td><?php echo html_escape($valueOrDefault(isset($item->category) ? $item->category : '')); ?></td>
                                    <td>
                                        <?php echo html_escape(number_format((float) (isset($item->size) ? $item->size : 0), 2)); ?>
                                        <?php echo html_escape(isset($item->unit) && trim((string) $item->unit) !== '' ? $item->unit : 'KG'); ?>
                                    </td>
                                    <td><?php echo html_escape($currencySymbol . number_format((float) (isset($item->unit_price) ? $item->unit_price : 0), 2)); ?></td>
                                    <td><?php echo html_escape($currencySymbol . number_format((float) (isset($item->price) ? $item->price : 0), 2)); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No parcel details were recorded for this booking.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4">
                    <p class="tw-text-[20px] tw-font-bold"><i class="las la-receipt"></i> Payment Breakdown</p>
                </div>
            </div>
            <div class="col-xs-12 tw-mt-8 table-responsive">
                <table class="table table-bordered table-hover cell-text-middle" style="text-align: left">
                    <tbody>
                        <tr>
                            <td><b>Selected Bag Space</b></td>
                            <td><?php echo html_escape(number_format((float) $y->selected_space, 2)); ?> KG</td>
                        </tr>
                        <tr>
                            <td><b>Parcel Cost</b></td>
                            <td><?php echo html_escape($currencySymbol . number_format((float) $y->selected_price, 2)); ?></td>
                        </tr>
                        <tr>
                            <td><b>Service Charge</b></td>
                            <td><?php echo html_escape($currencySymbol . number_format((float) $y->service_charge, 2)); ?></td>
                        </tr>
                        <tr>
                            <td><b>VAT</b></td>
                            <td><?php echo html_escape($currencySymbol . number_format((float) $y->vat, 2)); ?></td>
                        </tr>
                        <tr>
                            <td><b>Parcel Guarantee</b></td>
                            <td><?php echo html_escape($parcelGuarantee); ?></td>
                        </tr>
                        <tr>
                            <td><b>Subtotal</b></td>
                            <td><?php echo html_escape($currencySymbol . number_format((float) $y->sub_total, 2)); ?></td>
                        </tr>
                        <tr>
                            <td><b>Traveller Commission</b></td>
                            <td><?php echo html_escape($currencySymbol . number_format(booking_stored_traveller_commission($y), 2)); ?></td>
                        </tr>
                        <tr>
                            <td><b>Total Amount</b></td>
                            <td><?php echo html_escape($currencySymbol . number_format((float) $y->total_amount, 2)); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
