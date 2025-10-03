<?php echo flash_message_success('status_msg'); ?>
<?php echo flash_message_danger('status_msg_error'); ?>
<?php echo custom_validation_errors(); ?>



<?php
//select options bulk actions 
$options_array = array(
	//'value' => 'Caption'
	'activate' => 'Verify',
	'deactivate' => 'Un-verify',
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_users/bulk_actions_user', $options_array); ?>

<div class="table-scroll">
	<table id="users_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
				<th> Actions </th>
				<th class=""> Photo </th>
				<th class=""> Id Card </th>
				<th class=""> Utility Bill </th>
				<th class="min-w-200"> Name </th>
				<th class="min-w-300"> Contact Details </th>
				<th class="min-w-100"> Country </th>
				<th class=""> Account Status </th>
				<th class="min-w-100"> Last Login </th>
				<th class="min-w-100"> Date Registered </th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php echo form_close(); ?>