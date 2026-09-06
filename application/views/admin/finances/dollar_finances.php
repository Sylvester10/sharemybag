<?php
$traveller_payout = $total_cad_commission;
$gross_profit = $total_cad_amount - $traveller_payout - $total_cad_tax;
$tiles = array(
    array('icon' => 'las la-chart-line', 'value' => '$' . number_format($total_cad_amount, 2), 'label' => 'Total Revenue', 'col' => 4),
    array('icon' => 'las la-money-bill', 'value' => '$' . number_format($gross_profit, 2), 'label' => 'Gross Profit', 'col' => 4),
    array('icon' => 'las la-money-check', 'value' => '$' . number_format($traveller_payout, 2), 'label' => 'Total Traveller Payout', 'col' => 4),
);
?>
<div class="admin-section">
    <h4 class="text-bold admin-section-title"><b>Overview</b></h4>
    <?php $this->load->view('admin/partials/stat_tiles', array('tiles' => $tiles)); ?>
</div>

<?php
$month_options = array('' => 'Month');
for ($i = 1; $i <= 12; $i++) {
    $month_options[sprintf('%02d', $i)] = date('F', mktime(0, 0, 0, $i, 10));
}
$year_options = array('' => 'Year');
$currentYear = date('Y');
for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
    $year_options[$y] = $y;
}
$route_options = array(
    '' => 'Route',
    'Canada-Nigeria' => 'Canada - Nigeria',
    'Nigeria-Canada' => 'Nigeria - Canada',
);
$filters = array(
    array('id' => 'month_filter_cad', 'options' => $month_options),
    array('id' => 'year_filter_cad', 'options' => $year_options),
    array('id' => 'route_filter_cad', 'options' => $route_options),
);
$columns = array(
    array('label' => ''),
    array('label' => 'Travel Date', 'class' => 'min-w-200'),
    array('label' => 'Traveller', 'class' => 'min-w-200'),
    array('label' => 'Total KG'),
    array('label' => 'Total Amount'),
    array('label' => 'Service Charge'),
    array('label' => 'Special Fee'),
    array('label' => 'Premium Item Amount', 'class' => 'min-w-150'),
    array('label' => 'Insurance'),
    array('label' => 'Commission per KG', 'class' => 'min-w-150'),
    array('label' => 'Total Commission', 'class' => 'min-w-150'),
    array('label' => 'Traveller Commission'),
    array('label' => 'Exchange Rate', 'class' => 'min-w-150'),
    array('label' => 'Payment Method'),
);
$this->load->view('admin/partials/filter_row', array('label' => 'Filter', 'filters' => $filters));
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'finances_cad_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>

<?php echo form_close(); ?>
