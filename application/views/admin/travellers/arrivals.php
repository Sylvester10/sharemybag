<?php
$country_options = array('' => 'All Countries');
foreach (countries() as $country) {
    $country_options[$country] = $country;
}

$filters = array(
    array(
        'id' => 'arrivals_destination_filter',
        'label' => 'Filter by Destination:',
        'options' => $country_options,
        'wrapper_class' => 'col-md-3',
    ),
);

$columns = array(
    array('label' => 'Actions', 'class' => 'min-w-120'),
    array('label' => 'Travel Date', 'class' => 'min-w-150'),
    array('label' => 'Name', 'class' => 'min-w-180'),
    array('label' => 'Phone', 'class' => 'min-w-150'),
    array('label' => 'Email', 'class' => 'min-w-200'),
    array('label' => 'Current Location', 'class' => 'min-w-180'),
    array('label' => 'Destination', 'class' => 'min-w-180'),
    array('label' => 'Arrival Airport', 'class' => 'min-w-180'),
    array('label' => 'Arrival Date', 'class' => 'min-w-150'),
    array('label' => 'Total Space', 'class' => 'min-w-110'),
    array('label' => 'Used Space', 'class' => 'min-w-110'),
    array('label' => 'Available Space', 'class' => 'min-w-120'),
    array('label' => 'Bookings', 'class' => 'min-w-100'),
    array('label' => 'Status', 'class' => 'min-w-100'),
);

$this->load->view('admin/partials/filter_row', array('filters' => $filters));
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'arrivals_travellers_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>
