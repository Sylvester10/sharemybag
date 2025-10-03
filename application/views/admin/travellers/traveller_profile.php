<div class="new-item">
	<a class="btn btn-default btn-sm button-adjust"
		href="<?php echo base_url('admin_travellers/update_traveller/' . $y->id); ?>"><i class="fa fa-pencil"></i> Edit
		Traveller</a>
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers'); ?>"><i
			class="fa fa-users"></i> Available Travellers</a>
</div>


<p><b>Status:</b>
	<?php

	if ($y->available_space > 0) {
		echo '<span class="text-success"><b> Available </b></span>';
	} else {
		echo '<span class="text-danger"><b> Unavailable </b></span>';
	}

	?>
</p>

<div class="row">

	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details">
		<div class="well profile_view">

			<div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
				<div class="tw-ml-4">
					<p class="tw-text-[20px] tw-font-bold"><i class="fa-solid fa-person-walking-luggage"></i> Traveller Information</p>
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

	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details">
		<div class="well profile_view">

			<div class="col-xs-12 bottom tw-flex tw-items-center tw-mt-[-10px]">
				<div class="tw-ml-4">
					<p class="tw-text-[20px] tw-font-bold"><i class="fa-solid fa-plane"></i> Travel Information</p>
				</div>
			</div>

			<div class="col-xs-12 tw-mt-8">

				<p><b>Bag Space:</b> <?= $y->original_bag_space; ?> KG </p>
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

<h3 class="text-bold"><i class="fa fa-shopping-bag f-s-30"></i> Booking Information</h3>

<?php

?>

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
			<?php
			foreach ($booking_details as $y) { ?>

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

					$agent_details = $y->payment_method == 'offline'
						? 'N/A'
						: '<i class="fa-solid fa-user"></i> ' . $y->agent_name . '<br />
							<i class="fa-solid fa-phone"></i> ' . $y->agent_phone . '<br /> 
							<i class="fa-solid fa-at"></i> ' . $y->agent_email . '<br /> 
							<i class="fa-solid fa-location-dot"></i> ' . $y->agent_address;

					// receiver details
					$receiver_details = $y->payment_method == 'offline'
						? 'N/A'
						: '<i class="fa-solid fa-user"></i> ' . $y->receiver_name . ' <br />
								<i class="fa-solid fa-phone"></i> ' . $y->receiver_phone . ' <br /> 
								<i class="fa-solid fa-at"></i> ' . $y->receiver_email . ' <br /> 
								<i class="fa-solid fa-location-dot"></i> ' . $y->receiver_address . ', ' . $y->receiver_locality . ', ' . $y->receiver_postcode . '';

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
					$payment_status = ($y->payment_status == 'completed') ? '<span class="text-success">Paid</span>' : '<span class="text-danger">Canceled</span>';

					// delivery status
					$delivery_status = ($y->delivery_status == 'Delivered') ? '<span class="text-success">Delivered</span>' : (($y->delivery_status == 'In Transit') ? '<span class="text-primary">In Transit </span>' : (($y->delivery_status == 'Shipment Created') ? '<span class="text-primary">Shipment Created </span>' : '<span  class="text-danger">Pending </span>'));

					?>

					<td> <?= $agent_details ?> </td>
					<td> <?= $receiver_details ?> </td>
					<td> <?= $items ?> </td>
					<td> &pound; <?= number_format((75 / 100) * $y->selected_price, 2) ?> </td>
					<td> <?= $y->payment_method ?></td>
					<td> <?= $y->payment_status ?></td>
					<td> <?= x_date($y->date_added) ?> </td>
				</tr>


			<?php } ?>

		</tbody>

	</table>

</div>

<?php echo form_close(); ?>



<?php  ?>