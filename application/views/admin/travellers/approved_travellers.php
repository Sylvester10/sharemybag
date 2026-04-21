<!-- <div class="new-item">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers/add_traveller'); ?>">
		<i class="las la-user-plus"></i> Add Traveller
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
<?php
$country_options = array('' => 'All Countries');
foreach (countries() as $country) {
    $country_options[$country] = $country;
}
$filters = array(
    array(
        'id' => 'destination_filter',
        'label' => 'Filter by Destination:',
        'options' => $country_options,
        'wrapper_class' => 'col-md-3',
    ),
);
$columns = array(
    array('label' => '<input type="checkbox" class="radio-box select_all" />', 'class' => 'w-15-p'),
    array('label' => 'Actions'),
    array('label' => 'Travel Date', 'class' => 'min-w-150'),
    array('label' => 'Itinerary'),
    array('label' => 'Name', 'class' => 'min-w-150'),
    array('label' => 'Phone', 'class' => 'min-w-150'),
    array('label' => 'Alternative Phone', 'class' => 'min-w-150'),
    array('label' => 'Email', 'class' => 'min-w-150'),
    array('label' => 'Current Location', 'class' => 'min-w-150'),
    array('label' => 'Arrival Airport', 'class' => 'min-w-150'),
    array('label' => 'Final Destination', 'class' => 'min-w-150'),
    array('label' => 'Address', 'class' => 'min-w-300'),
    array('label' => 'Airline', 'class' => 'min-w-150'),
    array('label' => 'Arrival Date', 'class' => 'min-w-150'),
    array('label' => 'Total Bag Space', 'class' => 'min-w-100'),
    array('label' => 'Used Bag Space', 'class' => 'min-w-100'),
    array('label' => 'Available Bag Space', 'class' => 'min-w-100'),
    array('label' => 'Referred By', 'class' => 'min-w-150'),
    array('label' => 'Status', 'class' => 'min-w-100'),
    array('label' => 'Date Added', 'class' => 'min-w-150'),
);
$this->load->view('admin/partials/filter_row', array('filters' => $filters));
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'approved_travellers_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>

<?php echo form_close(); ?>
