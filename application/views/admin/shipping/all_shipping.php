<div class="admin-section">
    <h4 class="text-bold admin-section-title"><b>Overview</b></h4>
    <?php $this->load->view('admin/partials/stat_tiles', array('tiles' => $tiles)); ?>
</div>

<div class="admin-section">
    <div class="admin-page-actions">
        <button type="button" class="btn btn-primary btn-lg open-create-shipping" data-booking-id="0">
            <i class="las la-plus-circle"></i> Add Shipping
        </button>
    </div>

    <h4 class="text-bold admin-section-title"><b>Shipping Records</b></h4>

    <?php
    $columns = array(
        array('label' => 'Actions', 'class' => 'min-w-220'),
        array('label' => 'Staff', 'class' => 'min-w-200'),
        array('label' => 'User', 'class' => 'min-w-250'),
        array('label' => 'Traveller', 'class' => 'min-w-200'),
        array('label' => 'Pickup Address', 'class' => 'min-w-300'),
        array('label' => 'Drop-off Address', 'class' => 'min-w-300'),
        array('label' => 'Country', 'class' => 'min-w-140'),
        array('label' => 'Courier', 'class' => 'min-w-140'),
        array('label' => 'Carrier Tracking ID', 'class' => 'min-w-160'),
        array('label' => 'Status', 'class' => 'min-w-120'),
        array('label' => 'Date Added', 'class' => 'min-w-200'),
    );

    $this->load->view('admin/partials/datatable_shell', array(
        'table_id' => 'shipping_records_table',
        'columns' => $columns,
        'csrf_hash' => $this->security->get_csrf_hash(),
    ));
    ?>
</div>

<?php $this->load->view('admin/shipping/modal/manage_shipping', array(
    'staff_options' => $staff_options,
    'courier_options' => $courier_options,
)); ?>
