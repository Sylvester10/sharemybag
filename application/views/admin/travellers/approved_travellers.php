<!-- <div class="new-item">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers/add_traveller'); ?>">
		<i class="fa fa-user-plus"></i> Add Traveller
	</a>
</div> -->

<?php
//select options bulk actions 
$options_array = array(
	//'value' => 'Caption'
	'unapprove' => 'Unapprove',
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_travellers/bulk_actions_traveller', $options_array); ?>

<div class="table-scroll">
	<table id="approved_travellers_table" class="table table-bordered table-hover cell-text-middle"
		style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
				<th> Actions </th>
				<th class="min-w-150"> Travel Date </th>
				<th class=""> Itinerary </th>
				<th class="min-w-150"> Name </th>
				<th class="min-w-150"> Phone </th>
				<th class="min-w-150"> Alternative Phone </th>
				<th class="min-w-150"> Email </th>
				<th class="min-w-150"> Current Location </th>
				<th class="min-w-150"> Arrival Airport </th>
				<th class="min-w-150"> Final Destination </th>
				<th class="min-w-300"> Address </th>
				<th class="min-w-150"> Airline </th>
				<th class="min-w-150"> Arrival Date </th>
				<th class="min-w-100"> Available Bag Space </th>
				<th class="min-w-150"> Referred By </th>
				<!--<th class="min-w-100"> Payment Type </th>-->
				<th class="min-w-100"> Status </th>
				<th class="min-w-150"> Date Added</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php echo form_close(); ?>