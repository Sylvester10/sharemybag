
<?php echo form_open($form_url); ?>

	<div class="row bulk-section">
		<div class="col-md-5">
			With selected: <br/> 
			<select class="form-control bulk-element bulk_action_type" name="bulk_action_type">
				<option value="" class="no_item">Bulk Action</option>
				<?php echo bulk_select_options($options); ?>
			</select>
			<input type="button" class="btn btn-primary btn-sm bulk-element bulk_action_btn" data-toggle="modal" data-target="#bulk_action_confirm" title="Options" value="Apply" disabled />
		</div>
	</div>

	<div class="modal fade" id="bulk_action_confirm" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<div class="pull-right">
						<button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close" title="Close"> &times;</button>
					</div>
					<h4 class="modal-title"> <i class="fa fa-database"></i> Bulk Actions</h4>
				</div><!--/.modal-header-->
				<div class="modal-body">
					Note that all selected records will be affected. Are you sure you want to continue? 
				</div>
				<div class="modal-footer">
					<input type="submit" class="btn btn-primary" href="<?php echo $form_url; ?>" value="Yes, Continue" />
					<button data-dismiss="modal" class="btn btn-default"> No, Cancel </button>
				</div>
			</div>
		</div>
	</div>