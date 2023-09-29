

<div class="new-item">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('travellers/add_traveller'); ?>"><i
			class="fa fa-plus"></i> Add Traveller</a>
</div>

<?php
//select options bulk actions 
$options_array = array(
	//'value' => 'Caption'
	'unapprove' => 'Unapprove',
	'delete' => 'Delete'
);
echo modal_bulk_actions('travellers/bulk_actions_traveller', $options_array); ?>

<div class="table-scroll">
	<table id="approved_travellers_table" class="table table-bordered table-hover cell-text-middle"
		style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
				<th> Actions </th>
				<th class=""> Name </th>
				<th class=""> Phone </th>
				<th class=""> Alternative Phone </th>
				<th class=""> Email </th>
				<th class=""> Current Location </th>
				<th class=""> Destination </th>
				<th class="min-w-150"> Address </th>
				<th class=""> Airline </th>
				<th class=""> Travel Date </th>
				<th class=""> Arrival Date </th>
				<th class=""> Bag Space </th>
				<th class=""> Status </th>
				<th class=""> Date Added</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php echo form_close(); ?>