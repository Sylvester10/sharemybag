<?php
$status = shipping_status_normalize($record->status);
$pickupAddress = trim(implode(', ', array_filter(array($record->pickup_address, ''))));
$dropoffAddress = trim(implode(', ', array_filter(array($record->dropoff_address, ''))));
?>

<input type="hidden" id="csrf_hash" value="<?php echo html_escape($this->security->get_csrf_hash()); ?>" />

<div class="admin-page-actions">
    <a href="<?php echo base_url('shipping'); ?>" class="btn btn-default admin-back-btn">
        <i class="las la-arrow-left"></i> Back to Shipping
    </a>
    <button type="button" class="btn btn-default open-edit-shipping" data-booking-id="<?php echo (int) $record->booking_id; ?>">
        <i class="las la-pen"></i> Edit Shipping
    </button>
    <button type="button" class="btn btn-primary open-status-shipping" data-booking-id="<?php echo (int) $record->booking_id; ?>">
        <i class="las la-sync"></i> Add Tracking Update
    </button>
</div>

<div class="admin-summary-chip">
    <i class="las la-truck"></i>
    <span>Booking Reference: <strong><?php echo html_escape($record->booking_tracking_id); ?></strong></span>
    <span>Carrier Tracking ID: <strong><?php echo html_escape($record->carrier_tracking_id); ?></strong></span>
    <span><?php echo shipping_status_badge($status); ?></span>
</div>

<div class="row ">
    <div class="col-md-6 admin-detail-card">
        <div class="admin-panel-card">
            <h3>Shipping Record</h3>
            <table class="table table-striped admin-panel-table">
                <tr>
                    <th>Staff</th>
                    <td><?php echo html_escape($record->staff_name ?: 'Not Assigned'); ?></td>
                </tr>
                <tr>
                    <th>Courier</th>
                    <td><?php echo html_escape($record->courier ?: 'Not Assigned'); ?></td>
                </tr>
                <tr>
                    <th>Carrier Tracking ID</th>
                    <td><code><?php echo html_escape($record->carrier_tracking_id); ?></code></td>
                </tr>
                <tr>
                    <th>Pickup Country</th>
                    <td><?php echo html_escape($record->pickup_country ?: 'Nigeria'); ?></td>
                </tr>
                <tr>
                    <th>Date Added</th>
                    <td><?php echo x_datetime_full($record->date_added); ?></td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td><?php echo x_datetime_full($record->date_updated); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-6 admin-detail-card">
        <div class="admin-panel-card">
            <h3>Booking Contacts</h3>
            <table class="table table-striped admin-panel-table">
                <tr>
                    <th>User</th>
                    <td>
                        <?php echo html_escape($record->user_fullname ?: 'N/A'); ?><br>
                        <?php if (!empty($record->user_phone)) { ?><span><?php echo html_escape($record->user_phone); ?></span><br><?php } ?>
                        <?php if (!empty($record->user_email)) { ?><span><?php echo html_escape($record->user_email); ?></span><?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>Traveller</th>
                    <td>
                        <?php echo html_escape($record->traveller_name ?: 'N/A'); ?><br>
                        <?php if (!empty($record->traveller_contact)) { ?><span><?php echo html_escape($record->traveller_contact); ?></span><br><?php } ?>
                        <?php if (!empty($record->traveller_email)) { ?><span><?php echo html_escape($record->traveller_email); ?></span><?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>Pickup Address</th>
                    <td><?php echo nl2br(html_escape($pickupAddress)); ?></td>
                </tr>
                <tr>
                    <th>Drop-off Address</th>
                    <td><?php echo nl2br(html_escape($dropoffAddress)); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="admin-panel-card">
    <h3>Shipping Updates</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover admin-inline-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heading</th>
                    <th>Update Note</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($history)) { ?>
                    <?php foreach ($history as $item) { ?>
                        <tr>
                            <td><?php echo x_datetime_full($item->date_added); ?></td>
                            <td><?php echo html_escape($item->heading); ?></td>
                            <td><?php echo nl2br(html_escape($item->body)); ?></td>
                            <td><?php echo shipping_status_badge($item->delivery_status); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No shipping updates yet.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('admin/shipping/modal/manage_shipping', array(
    'staff_options' => $staff_options,
    'courier_options' => $courier_options,
)); ?>
