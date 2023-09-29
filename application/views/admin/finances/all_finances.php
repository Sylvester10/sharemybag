<div class="new-item" style="margin-bottom: 20px;">
	<h4 class="text-bold"><b>Overview</b></h4>
	<div class="row">


		<?php

		//naira
		$traveller_naira_payout = (75 / 100) * $total_naira_selected_items;
		$gross_naira_profit = $total_naira_amount - $traveller_naira_payout - $total_naira_tax;

		//pound
		// $traveller_pounds_payout = (25 / 100) * $total_pounds_selected_items;
		// $gross_pounds_profit = $total_pounds_amount - $traveller_pounds_payout - $total_pounds_tax;

		?>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa fa-chart-line"></i></div>
				<div class="count">&#8358;<?php echo number_format($total_naira_amount, 2); ?></div>
				<h3 class="stats-title">Total Revenue</h3>
			</div>
		</div>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa-regular fa-money-bill-1"></i></div>
				<div class="count">&#8358;<?php echo number_format($gross_naira_profit, 2); ?></div>
				<h3 class="stats-title">Gross Profit</h3>
			</div>
		</div>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fas fa-money-check"></i></div>
				<div class="count">&#8358;<?php echo number_format($traveller_naira_payout, 2); ?></div>
				<h3 class="stats-title">Total Traveller Payout</h3>
			</div>
		</div>
		<!-- <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa fa-chart-line"></i></div>
				<div class="count">&pound;<?php echo number_format($total_pounds_amount, 2); ?></div>
				<h3 class="stats-title">Total Pounds Revenue</h3>
			</div>
		</div>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fa-regular fa-money-bill-1"></i></div>
				<div class="count">&pound;<?php echo number_format($gross_pounds_profit, 2); ?></div>
				<h3 class="stats-title">Gross Pounds Profit</h3>
			</div>
		</div>
		<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<div class="tile-stats custom-bg-blue">
				<div class="icon"><i class="fas fa-money-check"></i></div>
				<div class="count">&pound;<?php echo number_format($traveller_pounds_payout, 2); ?></div>
				<h3 class="stats-title">Total Traveller Pounds Payout</h3>
			</div>
		</div> -->
	</div>
</div>


<div class="table-scroll">
	<table id="finances_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

		<input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

		<thead>
			<tr>
				<!-- <th> Actions </th> -->
				<th>  </th>
				<th class="min-w-100"> Traveller </th>
				<th class="min-w-100"> Sender </th>
				<th class="min-w-250"> Receiver </th>
				<th class="min-w-250"> Total Amount</th>
				<th class="min-w-250"> Select Items Amount</th>
				<th class="min-w-100"> Service Charge </th>
				<th class="min-w-100"> VAT </th>
				<th class="min-w-100"> Insurance </th>
				<th class="min-w-250"> Profit</th>
				<th class="min-w-100"> Traveller Commission</th>
				<th class="min-w-100"> Status </th>
				<th class="min-w-100"> Date </th>
			</tr>
		</thead>
		<tbody>
		</tbody>

	</table>
</div>

<?php echo form_close(); ?>