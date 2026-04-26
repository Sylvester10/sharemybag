<?php
//select options bulk actions
$options_array = array(
	//'value' => 'Caption'
	'confirm' => 'Confirm',
	'cancel' => 'Cancel',
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_bookings/bulk_actions_booking', $options_array); ?>
<?php
$columns = array(
    array('label' => '<input type="checkbox" class="radio-box select_all" />', 'class' => 'w-15-p'),
    array('label' => 'Actions'),
    array('label' => 'Date', 'class' => 'min-w-200'),
    array('label' => 'Need Help with Parcel?', 'class' => 'min-w-100'),
    array('label' => 'Traveller Details', 'class' => 'min-w-300'),
    array('label' => 'Traveller Commission', 'class' => 'min-w-150'),
    array('label' => 'SMB User Details', 'class' => 'min-w-300'),
    array('label' => 'Agent Details', 'class' => 'min-w-300'),
    array('label' => 'Receiver Details', 'class' => 'min-w-300'),
    array('label' => 'Item Details', 'class' => 'min-w-300'),
    array('label' => 'Item Size', 'class' => 'min-w-100'),
    array('label' => 'Payment Details', 'class' => 'min-w-200'),
    array('label' => 'Payment Status'),
);
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'bookings_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>

<?php echo form_close(); ?>

<?php $this->load->view('admin/bookings/modal/add_remove_parcel'); ?>
