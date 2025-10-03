
<?php require "application/views/admin/exchange/modal/new_exchange.php";  ?>

	<div class="new-item">
		<a class="btn btn-default btn-sm button-adjust" data-toggle="modal" data-target="#new_exchange"><i class="fa fa-plus"></i> New Exchange Rate</a>
	</div>

<?php ?>

<div class="table-scroll">
	<table id="exchange_table" class="table table-bordered table-hover cell-text-middle"
		style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<th class="min-w-200"> rate </th>
				<th class=""> Date Added</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<?php echo form_close(); ?>