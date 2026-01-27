<?php echo flash_message_success('status_msg'); ?>
<?php echo flash_message_danger('status_msg_error'); ?>
<?php echo custom_validation_errors(); ?>

<div class="new-item">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_users'); ?>"><i class="fa-solid fa-users"></i> All Users</a>
</div>


<div class="row">

	<div class="col-md-4 col-sm-12 col-xs-12 profile_details">
		<div class="well profile_view">

			<div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
				<div class="profile-pic">
					<?php
					if ($y->selfie != NULL) { ?>
						<img class="img-circle img-responsive" src="<?php echo base_url('assets/selfie/' . $y->selfie); ?>" />
					<?php } else { ?>
						<img class="img-circle img-responsive" src="<?php echo user_avatar; ?>" />
					<?php } ?>
				</div>
				<div class="tw-ml-4">
					<p class="tw-text-[20px] tw-font-bold"><?php echo $y->firstname; ?> <?php echo $y->lastname; ?></p>
					<p class="tw-mt-[-15px]"><?php echo $y->email; ?> </p>
				</div>
			</div>

			<div class="col-xs-12 tw-mt-8">

				<ul class="list-unstyled">
					<li> <b> Phone:</b> <span> <?php echo $y->number; ?> </span> </li>
					<li> <b>Country:</b> <span> <?php echo $y->country; ?> </span> </li>
					<li> <b>Address:</b> <span> <?php echo $y->address; ?> </span> </li>
					<li> <b>State:</b> <span> <?php echo $y->state; ?> </span> </li>
					<li> <b>Post Code:</b> <span> <?php echo $y->post_code; ?> </span> </li>
					<li> <b>Registered:</b> <span> <?php echo x_date($y->date_registered); ?> </span> </li>
				</ul>
				<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable tw-mt-8" data-toggle="modal" data-target="#update<?= $y->id ?>"> <i class="fa fa-pencil" style="color: blue"></i> &nbsp; Update Details </a></p>

			</div>


		</div>
	</div>

	<div class="modal fade" id="update<?= $y->id ?>" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content modal-widths">
				<div class="modal-header">
					<div class="pull-right">
						<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" title="Close"> &times;</button>
					</div>
					<h4 class="modal-title">Update Details: <?= $y->firstname ?> </h4>
				</div>

				<?php echo form_open_multipart('admin_users/update_user_ajax/' . $y->id, 'id="offline_booking_form"'); ?>

				<div class="modal-body">

					<div class="row">
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>First Name *</label>
							<br>
							<input type="text" name="firstname" value="<?php echo set_value('firstname', $y->firstname); ?>" class="form-controls" required>
							</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>Last Name *</label>
							<br>
							<input type="text" name="lastname" value="<?php echo set_value('lastname', $y->lastname); ?>" class="form-controls" required>
							</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>Email *</label>
							<br>
							<input type="email" name="email" value="<?php echo set_value('email', $y->email); ?>" class="form-controls" required>
							</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>Phone *</label>
							<br>
							<input type="tel" name="number" value="+<?php echo set_value('number', $y->number); ?>" class="form-controls" maxlength="13" pattern="\d*" title="Enter a valid phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
							</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
								<label class="form-control-label">Country*</label>
								<select class="form-control" name="country">
									<option selected value="<?php echo $y->country; ?>"><?php echo $y->country; ?></option>
									<?php
									$countries = countries();
									foreach ($countries as $country) { ?>
										<option value="<?php echo $country; ?>"><?php echo $country; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>Address *</label>
							<br>
							<input type="text" name="address" value="<?php echo set_value('address', $y->address); ?>" class="form-controls" required>
									</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>City *</label>
							<br>
							<input type="text" name="state" value="<?php echo set_value('state', $y->state); ?>" class="form-controls" required>
									</div>
						</div>
						<div class="col-lg-6 mb-2">
							<div class="form-group">
							<label>Postal Code *</label>
							<br>
							<input type="text" name="post_code" value="<?php echo set_value('post_code', $y->post_code); ?>" class="form-controls" required>
									</div>
						</div>
					</div>
				</div>

				<div class="modal-footer">

					<div class="mt-3">
						<button type="submit" id="send_mail_btn" class="btn btn-md btn-primary">
							<span id="btn_text">Update</span>
							<span id="loading_icon" style="display: none;"><i class="fa fa-spinner fa-spin"></i></span>
						</button>
					</div>

				</div>

				<?php echo form_close(); ?>

			</div>
		</div>
	</div>

</div>

<div class="m-b-20">
	<div class="btn btn-lg" title="Total Booking">
		<p>Total Bookings: </i> <?= $total_bookings ?> </p>
	</div>
</div>

<?php ?>

<?php
//select options bulk actions
$options_array = array(
	//'value' => 'Caption'
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_bookings/bulk_actions_booking', $options_array); ?>

<div class="table-scroll" style="margin-top: 20px!important;">

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
					<?php echo '<td> <div class="text-center"><a type="button" href="#" class="btn btn-primary btn-sm modal-toggle-btn clickable" data-toggle="modal" data-target="#options' . $y->id . '" title="Options"> <i class="fa fa-navicon"></i> </a></div>';

					echo '<div class="modal fade" id="options' . $y->id . '" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content modal-width">
									<div class="modal-header">
										<div class="pull-right">
											<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
										</div>
										<h4 class="modal-title">Actions:' . $y->tracking_id . '</h4>
									</div><!--/.modal-header-->
									<div class="modal-body">

										<p><a type="button" href="#" class="btn btn-default btn-sm btn-block action-btn clickable" data-toggle="modal" data-target="#delete' . $y->id . '"> <i class="fa fa-trash" style="color: red"></i> &nbsp; Delete </a></p>

									</div>
								</div>
							</div>
						</div>

						<div class="modal fade" id="delete' . $y->id . '" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<div class="pull-right">
											<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
										</div>
										<h4 class="modal-title">' . $y->tracking_id . '</h4>
									</div><!--/.modal-header-->
									<div class="modal-body">
										Are you sure you want to permanently delete this transaction?
									</div>
									<div class="modal-footer">
										<a class="btn btn-sm btn-danger" role="button" href="' . base_url('admin_bookings/delete_booking/' . $y->id) . '"> Yes, Delete </a>
										<button data-dismiss="modal" class="btn btn-sm"> No, Cancel </button>
									</div>
								</div>
							</div>
						</div></td>'


					?>

					<?php

					// traveller details
					$traveller_details = '<i class="fa-solid fa-user"></i> ' . $y->traveller_name . ' <br />
									<i class="fa-solid fa-phone"></i> ' . $y->traveller_contact . ' <br />
									<i class="fa-solid fa-location"></i> ' . $y->traveller_drop_address1 . '';

					// agent details
					$agent_details = '<i class="fa-solid fa-user"></i> ' . $y->agent_name . ' <br />
											<i class="fa-solid fa-phone"></i> ' . $y->agent_phone . ' <br />
											<i class="fa-solid fa-envelope"></i> ' . $y->agent_email . ' <br />
											<i class="fa-solid fa-location"></i> ' . $y->agent_address . ', ' . $y->agent_locality . ', ' . $y->agent_postcode . '';
					// receiver details
					$receiver_details = '<i class="fa-solid fa-user"></i> ' . $y->receiver_name . ' <br />
											<i class="fa-solid fa-phone"></i> ' . $y->receiver_phone . ' <br />
											<i class="fa-solid fa-envelope"></i> ' . $y->receiver_email . ' <br />
											<i class="fa-solid fa-location"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

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
							$items .= '<td> &pound;' . number_format($item->price, 2) . '</td>';
							$items .= '</tr>';
						}
					} else {
						$items .= '<tr><td colspan="4">No items found</td></tr>';
					}

					$items .= '</tbody>';
					$items .= '</table>';

					// payment status
					$payment_status = ($y->payment_status == 'completed') ? '<span class="badge badge-success">Paid</span>' : '<span class="badge badge-danger">Canceled</span>';

					// delivery status
					$delivery_status = ($y->delivery_status == 'Delivered') ? '<span class="badge badge-success">Delivered</span>' : (($y->delivery_status == 'In Transit') ? '<span class="badge badge-primary">In Transit </span>' : (($y->delivery_status == 'Shipment Created') ? '<span class="badge badge-primary">Shipment Created </span>' : '<span  class="badge badge-danger">Pending </span>'));

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

<?php  ?>
