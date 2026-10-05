<?php $activityUserId = (int) $y->id; ?>
<?php echo flash_message_success('status_msg'); ?>
<?php echo flash_message_danger('status_msg_error'); ?>
<?php echo custom_validation_errors(); ?>

<div class="new-item admin-page-actions">
    <a class="btn btn-default btn-sm button-adjust admin-back-btn" href="<?php echo base_url('admin_users'); ?>"><i class="las la-arrow-left"></i> Back to Users</a>
</div>


<div class="row">

    <div class="col-xs-12 profile_details admin-detail-card">
        <div class="well profile_view admin-user-profile">
            <div class="admin-user-profile__top">
                <span class="admin-user-profile__registered">
                    <i class="las la-calendar" aria-hidden="true"></i>
                    Registered <?= html_escape(x_date($y->date_registered)) ?>
                </span>
            </div>
            <div class="admin-user-profile__layout">
                <div class="admin-user-profile__identity">
                    <img class="img-circle admin-user-profile__avatar"
                        src="<?= html_escape($y->selfie ? base_url('assets/selfie/' . $y->selfie) : user_avatar) ?>"
                        alt="<?= html_escape(trim($y->firstname . ' ' . $y->lastname)) ?>">
                    <h2 class="admin-user-profile__name"><?= html_escape(trim($y->firstname . ' ' . $y->lastname)) ?></h2>
                    <div class="admin-user-profile__actions">
                        <button type="button" class="btn btn-default btn-sm btn-block action-btn" data-toggle="modal" data-target="#update<?= (int) $y->id ?>"><i class="las la-pen" aria-hidden="true"></i> &nbsp; Update Details</button>
                        <button type="button" class="btn btn-default btn-sm btn-block action-btn" data-toggle="modal" data-target="#userActivityHistory"><i class="las la-history" aria-hidden="true"></i> &nbsp; Activity History</button>
                    </div>
                </div>
                <dl class="admin-user-profile__details">
                    <?php foreach (array('Phone' => $y->number, 'Email' => $y->email,
                        'Country' => $y->country, 'Address' => $y->address,
                        'City' => $y->state, 'Postal Code' => $y->post_code) as $label => $value): ?>
                        <div class="admin-user-profile__field">
                            <dt><?= html_escape($label) ?></dt>
                            <dd><?= html_escape(trim((string) $value) !== '' ? $value : '—') ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="modal fade admin-form-modal admin-form-modal--wide admin-user-details-modal" id="update<?= $y->id ?>" role="dialog" aria-modal="true" aria-labelledby="updateUserTitle<?= $y->id ?>">
        <div class="modal-dialog modal-lg admin-form-modal__dialog">
            <div class="modal-content modal-widths admin-form-modal__content">
                <div class="modal-header ">
                    <div class="pull-right">
                        <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">&times;</button>
                    </div>
                    <h4 class="modal-title admin-form-modal__title" id="updateUserTitle<?= $y->id ?>">Update Details: <?= $y->firstname ?> </h4>
                </div>

                <?php echo form_open_multipart('admin_users/update_user_ajax/' . $y->id, 'id="user_update_form_' . (int) $y->id . '" class="admin-form-modal__form" data-user-details-form'); ?>

                <div class="modal-body admin-form-modal__body">

                    <div class="admin-form-modal__section">
                        <div class="row admin-form-modal__grid admin-user-details-grid">
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label>First Name *</label>

                                    <input type="text" name="firstname" value="<?php echo set_value('firstname', $y->firstname); ?>" class="form-controls" minlength="2" maxlength="500" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label>Last Name *</label>

                                    <input type="text" name="lastname" value="<?php echo set_value('lastname', $y->lastname); ?>" class="form-controls" minlength="2" maxlength="500" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label>Email *</label>

                                    <input type="email" name="email" value="<?php echo set_value('email', $y->email); ?>" class="form-controls" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <?php $this->load->view('partials/phone_input', array(
                                        'wrapper_class' => '',
                                        'field_name' => 'number',
                                        'country_code_name' => 'country_code',
                                        'country_code_id' => 'userCountryCode',
                                        'input_id' => 'userPhoneNumber',
                                        'value' => set_value('number', $y->number),
                                        'label' => 'Phone',
                                        'required' => !empty($y->number),
                                        'input_class' => 'form-controls smb-phone-input__number',
                                        'select_class' => 'form-controls smb-phone-input__country',
                                    )); ?>
                                    <input type="hidden" name="clear_phone" value="0">
                                    <button type="button" class="btn btn-link btn-sm text-danger" data-clear-phone>Clear phone number</button>
                                    <p class="small text-muted" data-phone-reset-notice hidden>Phone number will be cleared when you click Update.</p>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label class="form-control-label">Country*</label>
                                    <select class="form-control" name="country" required>
                                        <option selected value="<?php echo $y->country; ?>"><?php echo $y->country; ?></option>
                                        <?php
                                        $countries = countries();
                                        foreach ($countries as $country) { ?>
                                            <option value="<?php echo $country; ?>"><?php echo $country; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label>Address *</label>

                                    <input type="text" name="address" value="<?php echo set_value('address', $y->address); ?>" class="form-controls" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label>City *</label>

                                    <input type="text" name="state" value="<?php echo set_value('state', $y->state); ?>" class="form-controls" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <div class="form-group">
                                    <label>Postal Code *</label>

                                    <input type="text" name="post_code" value="<?php echo set_value('post_code', $y->post_code); ?>" class="form-controls" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer admin-form-modal__footer">

                    <div class="mt-3">
                        <button type="submit" id="send_mail_btn" class="btn btn-md btn-primary">
                            <span id="btn_text">Update</span>
                            <span id="loading_icon" style="display: none;"><i class="las la-spinner la-spin"></i></span>
                        </button>
                    </div>

                </div>

                <?php echo form_close(); ?>

            </div>
        </div>
    </div>

</div>

<div class="admin-summary-chip" title="Total Booking">
    <span>Total Bookings:</span>
    <span><?= $total_bookings ?></span>
</div>

<?php ?>

<?php
//select options bulk actions
$options_array = array(
    //'value' => 'Caption'
);
echo modal_bulk_actions('admin_bookings/bulk_actions_booking', $options_array); ?>

<div class="table-scroll admin-inline-table">

    <table id="" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

        <thead>
            <tr>
                <th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
                <th> Actions </th>
                <th class="min-w-300">Traveller Details</th>
                <th class="min-w-300">Agent Details</th>
                <th class="min-w-300">Receiver Details</th>
                <th class="min-w-300">Item Details</th>
                <th class="min-w-150">Payment Status</th>
                <!--<th class="min-w-150">Tracking Number</th>-->
                <!--<th class="min-w-150">Delivery Status</th>-->
                <th class="min-w-150">Date</th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ($bookings as $y) { ?>

                <tr>
                    <td> <?php echo checkbox_bulk_action($y->id); ?></td>
					<?php {
						$cancelAction = '';
						if (!empty($is_super_admin) && payment_status_normalize($y->payment_status) === 'completed') {
							$cancelAction = '<p><button type="button" class="btn btn-danger btn-sm btn-block action-btn clickable open-cancel-parcel" data-booking-id="' . (int) $y->id . '" data-booking-reference="' . html_escape($y->tracking_id) . '" data-refund-amount="' . html_escape(number_format((float) $y->total_amount, 2, '.', '')) . '" data-currency="' . html_escape(strtoupper((string) $y->currency)) . '"><i class="las la-times"></i> &nbsp; Cancel Parcel</button></p>
								' . (empty($y->shipping_record_id)
									? '<p><button type="button" class="btn btn-primary btn-sm btn-block action-btn clickable open-move-parcel" data-booking-id="' . (int) $y->id . '" data-booking-reference="' . html_escape($y->tracking_id) . '"><i class="las la-exchange-alt"></i> &nbsp; Move Parcel</button></p>'
									: '');
						}

						echo '<td> <div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $y->id . '" title="Options"> <i class="las la-bars"></i> </a></div>';

						echo '<div class="modal fade" id="options' . $y->id . '" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content modal-width">
									<div class="modal-header">
										<div class="pull-right">
											<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">&times;</button>
										</div>
										<h4 class="modal-title">Actions:' . $y->tracking_id . '</h4>
									</div><!--/.modal-header-->
									<div class="modal-body">
										<p><a type="button" href="' . base_url('admin_bookings/view_booking/' . $y->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"><i class="las la-eye" style="color:green"></i> &nbsp; View Booking</a></p>
										' . $cancelAction . '
									</div>
								</div>
							</div>
						</div></td>';
					} ?>

                    <?php

                    // traveller details
                    $traveller_details = '<i class="las la-user"></i> ' . $y->traveller_name . ' <br />
									<i class="las la-phone"></i> ' . $y->traveller_contact . ' <br />
									<i class="las la-map-marker-alt"></i> ' . $y->traveller_drop_address1 . '';

                    // agent details
                    $agent_details = '<i class="las la-user"></i> ' . $y->agent_name . ' <br />
											<i class="las la-phone"></i> ' . $y->agent_phone . ' <br />
											<i class="las la-envelope"></i> ' . $y->agent_email . ' <br />
											<i class="las la-map-marker-alt"></i> ' . $y->agent_address . ', ' . $y->agent_locality . ', ' . $y->agent_postcode . '';
                    // receiver details
                    $receiver_details = '<i class="las la-user"></i> ' . $y->receiver_name . ' <br />
											<i class="las la-phone"></i> ' . $y->receiver_phone . ' <br />
											<i class="las la-envelope"></i> ' . $y->receiver_email . ' <br />
											<i class="las la-map-marker-alt"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

                    // item details
                    $items = ''; // Initialize $items variable

                    $items .= '<table class="table text-nowrap fs-2">';
                    $items .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
                    $items .= '<tbody>';

                    $decoded_items = json_decode($y->items);

                    if (is_array($decoded_items) || is_object($decoded_items)) {
                        foreach ($decoded_items as $item) {
                            $items .= '<tr>';
                            $items .= '<td>' . $item->item_name . '</td>';
                            $items .= '<td>' . $item->category . '</td>';
                            $items .= '<td>' . $item->size . 'KG</td>';
                            $items .= '<td> ' . currency_symbol($y->currency) . number_format($item->price, 2) . '</td>';
                            $items .= '</tr>';
                        }
                    } else {
                        $items .= '<tr><td colspan="4">No items found</td></tr>';
                    }

                    $items .= '</tbody>';
                    $items .= '</table>';

                    // payment status
                    $payment_status = (payment_status_normalize($y->payment_status) == 'completed') ? '<span class="badge badge-success">Paid</span>' : '<span class="badge badge-danger">Canceled</span>';

                    // delivery status
                    $delivery_status = delivery_status_badge($y->delivery_status);

                    ?>

                    <td> <?= $traveller_details ?> </td>
                    <td> <?= $agent_details ?> </td>
                    <td> <?= $receiver_details ?> </td>
                    <td> <?= $items ?> </td>
                    <td> <?= $payment_status ?> </td>
                    <!-- <td> <?= $y->tracking_id ?> </td> -->
                    <!-- <td> <?= $delivery_status ?> </td> -->
                    <td> <?= x_date($y->date_added) ?> </td>
                </tr>


            <?php } ?>

        </tbody>

    </table>

</div>

<?php echo form_close(); ?>

<?php if (!empty($is_super_admin)) $this->load->view('admin/bookings/modal/cancel_parcel'); ?>
<?php if (!empty($is_super_admin)) $this->load->view('admin/bookings/modal/move_parcel'); ?>

<div class="modal fade admin-form-modal admin-form-modal--wide" id="userActivityHistory" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="userActivityTitle" data-user-activity-modal data-history-url="<?= html_escape(site_url('admin_users/activity_history/' . $activityUserId)) ?>">
    <div class="modal-dialog modal-lg admin-form-modal__dialog">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header">
                <button type="button" class="btn btn-danger btn-sm modal_close_btn pull-right" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title admin-form-modal__title" id="userActivityTitle">Activity History</h4>
            </div>
            <div class="modal-body admin-form-modal__body">
                <ul class="nav nav-tabs" role="tablist" aria-label="Activity type">
                    <li class="active" role="presentation"><a href="#" role="tab" aria-selected="true" data-activity-type="details">Details Changes</a></li>
                    <li role="presentation"><a href="#" role="tab" aria-selected="false" data-activity-type="signin">Sign-in Activity</a></li>
                </ul>
                <div class="admin-form-modal__section" data-activity-body role="tabpanel" aria-live="polite"></div>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/admin/custom/js/user_activity.js') ?>" defer></script>
