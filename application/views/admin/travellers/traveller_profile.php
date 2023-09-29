<div class="new-item">
	<a class="btn btn-default btn-sm button-adjust"
		href="<?php echo base_url('travellers/update_traveller/' . $y->id); ?>"><i class="fa fa-pencil"></i> Edit
		Traveller</a>
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('travellers'); ?>"><i
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
			<div class="col-md-12 text-center">
				<h3 class="text-bold"><i class="fa fa-user f-s-30"></i> Traveller Information</h3>
			</div>
			<hr />
			<div class="bottom">
				<div class="emphasis">
					<p><b>Full Name:</b> <?= $y->name; ?></p>
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
	</div>

	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 profile_details">
		<div class="well profile_view">
			<div class="col-md-12 text-center">
				<h3 class="text-bold"><i class="fa fa-plane f-s-30"></i> Travel Information</h3>
			</div>
			<hr />
			<div class="bottom">
				<div class="emphasis">
					<p><b>Bag Space:</b> <?= $y->original_bag_space; ?> KG </p>
					<p><b>Destination:</b> <?= $y->destination; ?></p>
					<p><b>Departure Airport:</b> <?= $y->departure_state; ?></p>
					<p><b>Airline:</b> <?= $y->airline; ?></p>
					<p><b>Travel Date:</b> <?= x_date($y->travel_date); ?></p>
					<p><b>Arrival Date:</b> <?= x_date($y->arrival_date); ?></p>
					<p><b>Unwanted Items:</b> <?= $y->unwanted_items; ?></p>
				</div>
			</div>
		</div>
	</div>

</div>


<div class="row">

	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 profile_details">
		<div class="well profile_view">
			<div class="col-md-12 text-center">
				<h3 class="text-bold"><i class="fa fa-shopping-bag f-s-30"></i> Booking Information</h3>
			</div>
			<hr />
			<div class="bottom">
				<div class="emphasis">
					<?php
					foreach ($booking_details as $b) { ?>
						<div class="row">
							<div class="col-lg-3">
								<h4><b>Sender</b></h4>
								<p><b>Name:</b> <?= $b->sender_name; ?> </p>
								<p><b>Phone:</b> <?= $b->sender_phone; ?> </p>
								<p><b>Email:</b> <?= $b->sender_email; ?> </p>
								<p><b>Address:</b> <?= $b->sender_address; ?> </p>
							</div>
							<div class="col-lg-3">
								<h4><b>Parcel Holder</b></h4>
								<p><b>Name:</b> <?= $b->receiver_name; ?> </p>
								<p><b>Phone:</b> <?= $b->receiver_phone; ?> </p>
								<p><b>Email:</b> <?= $b->receiver_email; ?> </p>
								<p><b>Address:</b> <?= $b->receiver_address; ?> </p>
							</div>
							<div class="col-lg-4">
								<h4><b>Item Details</b></h4>
								<?php
								// Parse the JSON data
								$item_details = json_decode($b->items);
								?>

								<table style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
									<thead>
										<tr style="background-color: #f2f2f2;">
											<th style="padding: 8px; border: 1px solid #ddd;">Category</th>
											<th style="padding: 8px; border: 1px solid #ddd;">Item Name</th>
											<th style="padding: 8px; border: 1px solid #ddd;">Size</th>
											<th style="padding: 8px; border: 1px solid #ddd;">Price</th>
										</tr>
									</thead>
									<tbody>
										<?php
										// Iterate over each item
										foreach ($item_details as $item) {
											$category = $item->category;
											$item_name = $item->item_name;
											$size = $item->size;
											$price = $item->price;

											// Display the item details
											?>
											<tr>
												<td style="padding: 8px; border: 1px solid #ddd;"><?= $category ?></td>
												<td style="padding: 8px; border: 1px solid #ddd;"><?= $item_name ?></td>
												<td style="padding: 8px; border: 1px solid #ddd;"><?= $size ?>KG</td>
												<td style="padding: 8px; border: 1px solid #ddd;">
													<?= ($b->currency == 'naira') ? '&#8358;' : '&pound;' ?><?= number_format($price, 2) ?>
												</td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="col-lg-2">
								<h4><b>Traveller Payout</b></h4>
								<p><?= ($b->currency == 'naira') ? '&#8358;' : '&pound;' ?><?= number_format((75 / 100) * $b->selected_price, 2) ?></p>
							</div>
						</div>
						<div class="dline"></div>
					<?php } //endforeach ?>
				</div>
			</div>
		</div>
	</div>

</div>