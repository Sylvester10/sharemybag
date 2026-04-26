<div class="new-item admin-page-actions">
	<a class="btn btn-default btn-sm button-adjust"
		href="<?php echo base_url('admin_travellers/update_traveller/' . $y->id); ?>"><i class="las la-pen"></i> Edit
		Traveller</a>
	<a class="btn btn-default btn-sm button-adjust" href="#" data-toggle="modal" data-target="#addTravellerBagSpaceModal"><i class="las la-plus"></i> Add Bag Space</a>
	<a class="btn btn-default btn-sm button-adjust" href="#" data-toggle="modal" data-target="#removeTravellerBagSpaceModal"><i class="las la-minus"></i> Remove Bag Space</a>
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers'); ?>"><i
			class="las la-users"></i> Available Travellers</a>
</div>

<input type="hidden" id="csrf_hash" value="<?php echo html_escape($this->security->get_csrf_hash()); ?>" />


<p class="admin-status-line"><b>Status:</b>
	<?php

	if ($y->available_space > 0) {
		echo smb_badge('Available', 'badge-success');
	} else {
		echo smb_badge('Unavailable', 'badge-danger');
	}

	?>
</p>

<div class="admin-summary-chip">
	<i class="las la-briefcase"></i>
	<span><strong>Original:</strong> <?php echo (float) $y->original_bag_space; ?> KG</span>
	<span><strong>Used:</strong> <?php echo (float) $y->used_space; ?> KG</span>
	<span><strong>Available:</strong> <?php echo (float) $y->available_space; ?> KG</span>
</div>

<div class="row">

	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details admin-detail-card">
		<div class="well profile_view">

			<div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
				<div class="tw-ml-4">
					<p class="tw-text-[20px] tw-font-bold"><i class="las la-suitcase-rolling"></i> Traveller Information</p>
				</div>
			</div>

			<div class="col-xs-12 tw-mt-8">

				<p><b>Full Name:</b> <?= $y->fullname; ?></p>
				<p><b>Phone Number:</b> <?= $y->phone; ?></p>
				<p><b>Email Address:</b> <?= $y->email; ?></p>
				<p><b>Residential Country:</b> <?= $y->location; ?></p>
				<p><b>Residential State:</b> <?= $y->current_state; ?></p>
				<p><b>Residential Address:</b> <?= $y->address; ?></p>
				<p><b>1st Drop Off Date:</b> <?= x_date($y->drop_date1); ?></p>
				<p><b>1st Drop Off Address:</b> <?= $y->drop_address1; ?></p>
				<p><b>2nd Drop Off Date:</b> <?= ($y->drop_date2 == "") ? "N/A" : x_date($y->drop_date2); ?></p>
				<p><b>2nd Drop Off Address:</b> <?= ($y->drop_address2 == "") ? "N/A" : $y->drop_address2; ?></p>

			</div>

		</div>
	</div>

	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details admin-detail-card">
		<div class="well profile_view">

			<div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
				<div class="tw-ml-4">
					<p class="tw-text-[20px] tw-font-bold"><i class="las la-plane"></i> Travel Information</p>
				</div>
			</div>

			<div class="col-xs-12 tw-mt-8">

				<p><b>Original Bag Space:</b> <?= $y->original_bag_space; ?> KG </p>
				<p><b>Used Bag Space:</b> <?= $y->used_space; ?> KG </p>
				<p><b>Available Bag Space:</b> <?= $y->available_space; ?> KG </p>
				<p><b>Destination:</b> <?= $y->destination; ?></p>
				<p><b>Departure Airport:</b> <?= $y->departure_state; ?></p>
				<p><b>Airline:</b> <?= $y->airline; ?></p>
				<p><b>Travel Date:</b> <?= x_date($y->travel_date); ?></p>
				<p><b>Arrival Date:</b> <?= ($y->arrival_date == "") ? "N/A" : x_date($y->arrival_date); ?></p>
				<p><b>Unwanted Items:</b> <?= $y->unwanted_items; ?></p>

			</div>
		</div>
	</div>

</div>

<h3 class="text-bold"><i class="las la-shopping-bag f-s-30"></i> Booking Information</h3>

<?php

?>

<?php
$traveller = $y;
//select options bulk actions
$options_array = array(
	//'value' => 'Caption'
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_bookings/bulk_actions_booking', $options_array); ?>

<div class="table-scroll admin-inline-table">

	<table id="table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

		<thead>
			<tr>
				<th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
				<th> Actions </th>
				<th class="min-w-300">SMB User Details</th>
				<th class="min-w-300">Agent Details</th>
				<th class="min-w-300">Receiver Details</th>
				<th class="min-w-300">Item Details</th>
				<th class="min-w-150">Traveller Payout</th>
				<th class="min-w-150">Payment Method</th>
				<th class="min-w-150">Payment Status</th>
				<th class="min-w-150">Date</th>
			</tr>
		</thead>

		<tbody>
			<?php if (!empty($booking_details)) { ?>
			<?php foreach ($booking_details as $booking) { ?>

				<tr>
					<td> <?php echo checkbox_bulk_action($booking->id); ?></td>

					<?php echo '<td> <div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $booking->id . '" title="Options"> <i class="las la-bars"></i> </a></div>';

					echo '<div class="modal fade" id="options' . $booking->id . '" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content modal-width">
									<div class="modal-header">
										<div class="pull-right">
											<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
										</div>
										<h4 class="modal-title">Actions:' . $booking->tracking_id . '</h4>
									</div><!--/.modal-header-->
									<div class="modal-body">

										<p><a type="button" href="' . base_url('admin_bookings/view_booking/' . $booking->id) . '" class="btn btn-default btn-sm btn-block action-btn clickable"> <i class="las la-eye" style="color: green"></i> &nbsp; View Booking </a></p>

										<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" onclick="openAddParcelModal(' . $booking->id . ')" data-dismiss="modal"> <i class="las la-plus" style="color: green"></i> &nbsp; Add Parcel </a></p>

										<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" onclick="openRemoveParcelModal(' . $booking->id . ', \'' . htmlspecialchars(addslashes($booking->items), ENT_QUOTES, 'UTF-8') . '\')" data-dismiss="modal"> <i class="las la-minus" style="color: red"></i> &nbsp; Remove Parcel </a></p>

										<hr>

										<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $booking->id . '"> <i class="las la-trash" style="color: red"></i> &nbsp; Delete </a></p>

									</div>
								</div>
							</div>
						</div>

						<div class="modal fade" id="delete' . $booking->id . '" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<div class="pull-right">
											<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
										</div>
										<h4 class="modal-title">' . $booking->tracking_id . '</h4>
									</div><!--/.modal-header-->
									<div class="modal-body">
										Are you sure you want to permanently delete this transaction?
									</div>
									<div class="modal-footer">
										<a class="btn btn-sm btn-danger" role="button" href="' . base_url('admin_bookings/delete_booking/' . $booking->id) . '"> Yes, Delete </a>
										<button data-dismiss="modal" class="btn btn-sm"> No, Cancel </button>
									</div>
								</div>
							</div>
						</div></td>'


					?>

					<?php

					$user_details = $booking->payment_method == 'offline'
						? '<i class="las la-user"></i> ' . $booking->user_fullname . '<br />
							<i class="las la-at"></i> ' . $booking->user_email . ' <br /> <i class="las la-exclamation-circle"></i> This is an offline booking'
						: '<i class="las la-user"></i> ' . $booking->user_fullname . '<br />
							<i class="las la-at"></i> ' . $booking->user_email;

				$agent_details = '<i class="las la-user"></i> ' . $booking->agent_name . '<br />
							<i class="las la-phone"></i> ' . $booking->agent_phone . '<br />
							<i class="las la-at"></i> ' . $booking->agent_email . '<br />
							<i class="las la-map-marker-alt"></i> ' . $booking->agent_address;

				// receiver details
				$receiver_details = '<i class="las la-user"></i> ' . $booking->receiver_name . ' <br />
								<i class="las la-phone"></i> ' . $booking->receiver_phone . ' <br />
								<i class="las la-at"></i> ' . $booking->receiver_email . ' <br />
								<i class="las la-map-marker-alt"></i> ' . $booking->receiver_address . ', ' . $booking->receiver_locality . ', ' . $booking->receiver_postcode . '';

					// item details
					$items = ''; // Initialize $items variable

					$items .= '<table class="table text-nowrap fs-2">';
					$items .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
					$items .= '<tbody>';

					$decoded_items = json_decode($booking->items);

					if (is_array($decoded_items) || is_object($decoded_items)) {
						foreach ($decoded_items as $item) {
							$items .= '<tr>';
							$items .= '<td>' . $item->item_name . '</td>';
							$items .= '<td>' . $item->category . '</td>';
							$items .= '<td>' . $item->size . 'KG</td>';
							$items .= '<td> ' . currency_symbol($booking->currency) . number_format($item->price, 2) . '</td>';
							$items .= '</tr>';
						}
					} else {
						$items .= '<tr><td colspan="4">No items found</td></tr>';
					}

					$items .= '</tbody>';
					$items .= '</table>';

					switch ($booking->payment_method) {
						case 'stripe':
							$payment_method = '<img src="' . base_url('assets/general/stripe.svg') . '" alt="Stripe" width="40" height="20">';
							break;
						case 'paystack':
							$payment_method = '<img src="' . base_url('assets/general/paystack.svg') . '" alt="Paystack" width="80" height="20">';
							break;
						default:
							$payment_method = 'Offline';
							break;
					}

					// payment status
					$payment_status = (payment_status_normalize($booking->payment_status) == 'completed') ? '<span class="badge badge-success"><b>Paid</span>' : '<span class="badge badge-danger"><b>Canceled</b></span>';

					// delivery status
					$delivery_status = delivery_status_badge($booking->delivery_status);

					?>

					<td> <?= $user_details ?> </td>
					<td> <?= $agent_details ?> </td>
					<td> <?= $receiver_details ?> </td>
					<td> <?= $items ?> </td>
					<td> <?= currency_symbol($booking->currency) ?><?= number_format($booking->traveller_commission, 2) ?> </td>
					<td> <?= $payment_method ?></td>
					<td> <?= $payment_status ?></td>
					<td> <?= x_date($booking->date_added) ?> </td>
				</tr>

			<?php } ?>
			<?php } else { ?>
				<tr>
					<td colspan="10" class="text-center text-muted">No completed bookings are currently attached to this traveller.</td>
				</tr>
			<?php } ?>

		</tbody>

	</table>

</div>

<?php echo form_close(); ?>

<?php
$bag_options = '<option value="">Select</option>';
foreach (kilogram() as $space) {
	$bag_options .= '<option value="' . (int) $space . '">' . (int) $space . ' KG</option>';
}
?>

<div class="modal fade" id="addTravellerBagSpaceModal" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<div class="pull-right">
					<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
				</div>
				<h4 class="modal-title">Add Bag Space: <?php echo html_escape($traveller->fullname); ?></h4>
			</div>
			<?php echo form_open_multipart('admin_travellers/add_traveller_bag_space/' . $traveller->id); ?>
			<div class="modal-body">
				<div class="form-group">
					<label class="form-control-label">Select Bag Space</label>
					<br>
					<select class="form-control" name="selected_space" required>
						<?php echo $bag_options; ?>
					</select>
				</div>
				<br>
				<small>Selected space will be added to the traveller’s original and available bag space.</small>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-md btn-primary">Update Bag Space</button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<div class="modal fade" id="removeTravellerBagSpaceModal" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<div class="pull-right">
					<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
				</div>
				<h4 class="modal-title">Remove Bag Space: <?php echo html_escape($traveller->fullname); ?></h4>
			</div>
			<?php echo form_open_multipart('admin_travellers/remove_traveller_bag_space/' . $traveller->id); ?>
			<div class="modal-body">
				<div class="form-group">
					<label class="form-control-label">Select Bag Space</label>
					<br>
					<select class="form-control" name="selected_space" required>
						<?php echo $bag_options; ?>
					</select>
				</div>
				<br>
				<small>Selected space will be removed from the traveller’s original and available bag space.</small>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-md btn-primary">Update Bag Space</button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<?php $this->load->view('admin/bookings/modal/add_remove_parcel'); ?>
