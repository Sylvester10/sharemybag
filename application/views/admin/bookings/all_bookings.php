<?php
//select options bulk actions 
$options_array = array(
	//'value' => 'Caption'
	'delete' => 'Delete'
);
echo modal_bulk_actions('bookings/bulk_actions_booking', $options_array); ?>

<div class="table-scroll">
	<table id="bookings_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
				<th> Actions </th>
				<th class="min-w-100"> ID Card</th>
				<th class="min-w-100"> Selfie</th>
				<th class="min-w-100"> Traveller </th>
				<th class="min-w-100"> Traveller Commission</th>
				<th class="min-w-100"> Sender Details </th>
				<th class="min-w-250"> Receiver Details </th>
				<th class="min-w-250"> Receiver ID Details</th>
				<th class="min-w-250"> Item Details</th>
				<th class="min-w-100"> Tracking Number </th>
				<th class="min-w-100"> Delivery Status </th>
				<th class="min-w-100"> Booking Status </th>
				<th class="min-w-100"> Date </th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php echo form_close(); ?>