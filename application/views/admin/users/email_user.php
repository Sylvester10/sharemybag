
<?php echo flash_message_success('status_msg'); ?>
<?php echo flash_message_danger('status_msg_error'); ?>
<?php echo custom_validation_errors(); ?>

	
	
	<?php 
	//select options bulk actions 
	$options_array = array(
		//'value' => 'Caption'
		'activate' => 'Activate',
		'deactivate' => 'De-activate',
		'delete' => 'Delete'
	); 
	echo modal_bulk_actions('email_user/bulk_actions_user', $options_array); ?>
	
		<div class="table-scroll">
			<table id="email_users_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">
				
				<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />
				
				<thead>
					<tr>
						<th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
						<th> Actions </th>
						<th class=""> Photo </th>
						<th class="min-w-200"> Name </th>
						<th class="min-w-200"> Email Address </th>
						<th class="min-w-150"> Country </th>
						<th class="min-w-150"> Bitcoin </th>	
						<th class="min-w-150"> Perfect Money </th>	
						<th class="min-w-150"> Ethereum </th>		
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	
<?php echo form_close(); ?>