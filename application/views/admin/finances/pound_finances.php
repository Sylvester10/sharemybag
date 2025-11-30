<div class="new-item" style="margin-bottom: 20px;">
	<h4 class="text-bold"><b>Overview</b></h4>
	<div class="row">


		<?php

		// Use the total commission and tax retrieved from the model
		$traveller_pounds_payout = $total_pounds_commission;
		$gross_pounds_profit = $total_pounds_amount - $traveller_pounds_payout - $total_pounds_tax;

		?>

		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa fa-chart-line"></i></div>
				<div class="count">&pound;<?php echo number_format($total_pounds_amount, 2); ?></div>
				<h3 class="stats-title">Total Revenue</h3>
			</div>
		</div>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa-regular fa-money-bill-1"></i></div>
				<div class="count">&pound;<?php echo number_format($gross_pounds_profit, 2); ?></div>
				<h3 class="stats-title">Gross Profit</h3>
			</div>
		</div>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fas fa-money-check"></i></div>
				<div class="count">&pound;<?php echo number_format($traveller_pounds_payout, 2); ?></div>
				<h3 class="stats-title">Total Traveller Payout</h3>
			</div>
		</div>
	</div>
</div>


<div class="row mb-4" style="margin-bottom: 30px;">
	<div class="col-md-3">
		<label for="month_filter_gbp">Filter by Month:</label>
		<select id="month_filter_gbp" class="form-control">
			<option value="">All Months</option>
			<?php
			for ($i = 1; $i <= 12; $i++) {
				echo '<option value="' . sprintf('%02d', $i) . '">' . date('F', mktime(0, 0, 0, $i, 10)) . '</option>';
			}
			?>
		</select>
	</div>
	<div class="col-md-3">
		<label for="year_filter_gbp">Filter by Year:</label>
		<select id="year_filter_gbp" class="form-control">
			<option value="">All Years</option>
			<?php
			$currentYear = date('Y');
			for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
				echo '<option value="' . $y . '">' . $y . '</option>';
			}
			?>
		</select>
	</div>
	<div class="col-md-3">
		<label for="route_filter_gbp">Filter by Route:</label>
		<select id="route_filter_gbp" class="form-control">
			<option value="">All Routes</option>
			<option value="United Kingdom-Nigeria">United Kingdom - Nigeria</option>
			<option value="Nigeria-United Kingdom">Nigeria - United Kingdom</option>
		</select>
	</div>
</div>

<div class="table-scroll">
	<table id="finances_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<th> </th>
				<th class="min-w-200"> Travel Date </th>
				<th class="min-w-200"> Traveller </th>
				<th class=""> Total Amount</th>
				<th class="min-w-150"> Select Items Amount</th>
				<th class=""> Service Charge </th>
				<th class=""> Special Item? </th>
				<th class=""> Premium Item? </th>
				<th class=""> Insurance </th>
				<th class=""> Profit</th>
				<th class=""> Traveller Commission</th>
				<th class=""> Payment Method </th>
			</tr>
		</thead>
		<tbody>
		</tbody>

	</table>
</div>

<?php echo form_close(); ?>
