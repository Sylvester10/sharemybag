<?php echo flash_message_success('status_msg'); ?>
<?php echo flash_message_danger('status_msg_error'); ?>
<?php echo custom_validation_errors(); ?>



<?php
//select options bulk actions
$options_array = array(
	//'value' => 'Caption'
	'verify' => 'Verify',
	'unverify' => 'Un-verify',
	'block' => 'Block',
	'unblock' => 'Unblock',
	'delete' => 'Delete'
);
echo modal_bulk_actions('admin_users/bulk_actions_user', $options_array); ?>
<?php
$columns = array(
    array('label' => '<input type="checkbox" class="radio-box select_all" />', 'class' => 'w-15-p'),
    array('label' => 'Actions'),
    array('label' => 'Photo'),
    array('label' => 'Id Card'),
    array('label' => 'Utility Bill'),
    array('label' => 'Name', 'class' => 'min-w-200'),
    array('label' => 'Contact Details', 'class' => 'min-w-300'),
    array('label' => 'Country', 'class' => 'min-w-100'),
    array('label' => 'Verification Status'),
    array('label' => 'Account Status'),
    array('label' => 'Last Login', 'class' => 'min-w-100'),
    array('label' => 'Date Registered', 'class' => 'min-w-100'),
);
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'pending_users_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>

<?php echo form_close(); ?>
