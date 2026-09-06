<?php
$formatAddress = function ($address, $locality = '', $postcode = '') {
    return implode(', ', array_filter(array_map('trim', array($address, $locality, $postcode))));
};
?>

<input type="hidden" id="csrf_hash" value="<?php echo html_escape($this->security->get_csrf_hash()); ?>">

<div class="admin-page-actions">
    <a class="btn btn-default btn-sm admin-back-btn" href="<?php echo base_url('shipping/arrivals'); ?>">
        <i class="las la-arrow-left"></i> Back to Arrivals
    </a>
</div>

<div class="admin-summary-chip">
    <span><strong>Completed Bookings:</strong> <?php echo (int) $y->booking_count; ?></span>
</div>
<div class="admin-summary-chip">
    <span><strong>Available Space:</strong> <?php echo (float) $y->available_space; ?> KG</span>
</div>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4"><p class="tw-text-[20px] tw-font-bold"><i class="las la-suitcase-rolling"></i> Traveller Information</p></div>
            </div>
            <div class="col-xs-12 tw-mt-8">
                <p><b>Full Name:</b> <?php echo html_escape($y->fullname); ?></p>
                <p><b>Phone Number:</b> <?php echo html_escape($y->phone); ?></p>
                <p><b>Email Address:</b> <?php echo html_escape($y->email); ?></p>
                <p><b>Current Location:</b> <?php echo html_escape(trim($y->area . ', ' . $y->current_state, ', ')); ?></p>
                <p><b>Residential Address:</b> <?php echo html_escape($y->address); ?></p>
                <p><b>Pickup Address:</b> <?php echo html_escape($y->drop_address1 ?: 'N/A'); ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12 profile_details admin-detail-card">
        <div class="well profile_view">
            <div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
                <div class="tw-ml-4"><p class="tw-text-[20px] tw-font-bold"><i class="las la-plane"></i> Travel Information</p></div>
            </div>
            <div class="col-xs-12 tw-mt-8">
                <p><b>Route:</b> <?php echo html_escape($y->location . ' to ' . $y->destination); ?></p>
                <p><b>Arrival Airport:</b> <?php echo html_escape($y->arrival_airport); ?></p>
                <p><b>Final Destination:</b> <?php echo html_escape(traveller_destination_label($y->arrival_state, $y->destination, $y->destination_area ?? '')); ?></p>
                <p><b>Airline:</b> <?php echo html_escape($y->airline); ?></p>
                <p><b>Travel Date:</b> <?php echo x_date($y->travel_date); ?></p>
                <p><b>Arrival Date:</b> <?php echo $y->arrival_date ? x_date($y->arrival_date) : 'N/A'; ?></p>
            </div>
        </div>
    </div>
</div>

<h3 id="arrival-bookings" class="text-bold"><i class="las la-shopping-bag f-s-30"></i> Booking Information</h3>

<div class="table-scroll admin-inline-table">
    <table class="table table-bordered table-hover cell-text-middle" style="text-align: left">
        <thead>
            <tr>
                <th>Actions</th>
                <th class="min-w-180">Booking Reference</th>
                <th class="min-w-220">SMB User</th>
                <th class="min-w-240">Traveler / Pickup</th>
                <th class="min-w-240">Receiver / Drop-off</th>
                <th class="min-w-200">Items</th>
                <th class="min-w-120">Amount</th>
                <th class="min-w-150">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($booking_details)) { ?>
                <?php foreach ($booking_details as $booking) { ?>
                    <?php
                    $shippingExists = !empty($booking->shipping_record_id);
                    $modalId = 'arrivalBookingOptions' . (int) $booking->id;
                    $decodedItems = json_decode($booking->items);
                    $itemNames = array();
                    if (is_array($decodedItems) || is_object($decodedItems)) {
                        foreach ($decodedItems as $item) {
                            $itemNames[] = trim(($item->item_name ?? 'Parcel') . ' (' . ($item->size ?? 0) . ' KG)');
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#<?php echo $modalId; ?>" aria-label="Actions for booking <?php echo html_escape($booking->tracking_id); ?>">
                                <i class="las la-bars"></i>
                            </button>
                            <div class="modal fade" id="<?php echo $modalId; ?>" role="dialog">
                                <div class="modal-dialog"><div class="modal-content modal-width">
                                    <div class="modal-header">
                                        <div class="pull-right"><button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">&times;</button></div>
                                        <h4 class="modal-title">Booking Actions: <?php echo html_escape($booking->tracking_id); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <p><a href="<?php echo base_url('admin_bookings/view_booking/' . $booking->id); ?>" class="btn btn-default btn-sm btn-block action-btn"><i class="las la-eye" style="color: green"></i> &nbsp; View Booking</a></p>
                                        <?php if ($shippingExists) { ?>
                                            <p><button type="button" class="btn btn-default btn-sm btn-block action-btn open-edit-shipping" data-booking-id="<?php echo (int) $booking->id; ?>" data-dismiss="modal"><i class="las la-truck" style="color: blue"></i> &nbsp; Edit Shipping</button></p>
                                        <?php } else { ?>
                                            <p><button type="button" class="btn btn-primary btn-sm btn-block action-btn open-create-shipping" data-booking-id="<?php echo (int) $booking->id; ?>" data-dismiss="modal"><i class="las la-truck"></i> &nbsp; Book Shipping</button></p>
                                        <?php } ?>
                                    </div>
                                </div></div>
                            </div>
                        </td>
                        <td><code><?php echo html_escape($booking->tracking_id); ?></code></td>
                        <td>
                            <strong><?php echo html_escape($booking->user_fullname); ?></strong><br>
                            <?php echo html_escape($booking->user_email); ?>
                        </td>
                        <td>
                            <strong><?php echo html_escape($booking->traveller_name); ?></strong><br>
                            <?php echo nl2br(html_escape($formatAddress($booking->agent_address, $booking->agent_locality, $booking->agent_postcode))); ?>
                        </td>
                        <td>
                            <strong><?php echo html_escape($booking->receiver_name); ?></strong><br>
                            <?php echo nl2br(html_escape($formatAddress($booking->receiver_address, $booking->receiver_locality, $booking->receiver_postcode))); ?>
                        </td>
                        <td><?php echo html_escape($itemNames ? implode(', ', $itemNames) : 'No item details'); ?></td>
                        <td><?php echo html_escape(currency_symbol_text($booking->currency) . number_format((float) $booking->total_amount, 2)); ?></td>
                        <td><?php echo x_datetime_full($booking->date_added); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="8" class="text-center text-muted">No completed bookings were found for this traveler.</td></tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php $this->load->view('admin/shipping/modal/manage_shipping', array(
    'staff_options' => $staff_options,
    'courier_options' => $courier_options,
    'current_admin_id' => $current_admin_id,
    'lock_staff_selection' => $lock_staff_selection,
)); ?>
