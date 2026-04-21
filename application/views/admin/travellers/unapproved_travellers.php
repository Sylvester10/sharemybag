<!-- <div class="new-item">
	<a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_travellers/add_traveller'); ?>"><i
			class="las la-plus"></i> Add Traveller</a>
</div> -->

<?php
//select options bulk actions 
$options_array = array(
	//'value' => 'Caption'
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_travellers/bulk_actions_traveller', $options_array); ?>
<?php
$columns = array(
    array('label' => '<input type="checkbox" class="radio-box select_all" />', 'class' => 'w-15-p'),
    array('label' => 'Actions'),
    array('label' => 'Itinerary'),
    array('label' => 'Name', 'class' => 'min-w-150'),
    array('label' => 'Phone', 'class' => 'min-w-150'),
    array('label' => 'Alternate Phone', 'class' => 'min-w-150'),
    array('label' => 'Email', 'class' => 'min-w-150'),
    array('label' => 'Current Location', 'class' => 'min-w-150'),
    array('label' => 'Destination', 'class' => 'min-w-150'),
    array('label' => 'Travel Date', 'class' => 'min-w-150'),
    array('label' => 'Status', 'class' => 'min-w-150'),
    array('label' => 'Date Added', 'class' => 'min-w-150'),
);
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'unapproved_travellers_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>

<?php echo form_close(); ?>
