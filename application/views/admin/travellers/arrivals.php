<?php
$country_options = array('' => 'All Countries');
foreach (countries() as $country) {
    $country_options[$country] = $country;
}

$filters = array(
    array(
        'id' => 'arrivals_lifecycle_filter',
        'label' => 'Filter by Lifecycle:',
        'options' => array(
            '' => 'All Arrivals',
            'Needs Shipping' => 'Needs Shipping',
            'Partially Arranged' => 'Partially Arranged',
            'Fully Arranged' => 'Fully Arranged',
            'Cleared' => 'Cleared Arrivals',
        ),
        'wrapper_class' => 'col-md-3',
    ),
    array(
        'id' => 'arrivals_destination_filter',
        'label' => 'Filter by Destination:',
        'options' => $country_options,
        'wrapper_class' => 'col-md-3',
    ),
);

$columns = array(
    array('label' => 'Actions', 'class' => 'min-w-120'),
    array('label' => 'Arrival Date', 'class' => 'min-w-150'),
    array('label' => 'Name', 'class' => 'min-w-180'),
    array('label' => 'Phone', 'class' => 'min-w-150'),
    array('label' => 'Email', 'class' => 'min-w-200'),
    array('label' => 'Destination', 'class' => 'min-w-180'),
    array('label' => 'Bookings', 'class' => 'min-w-100'),
);

$this->load->view('admin/partials/filter_row', array('filters' => $filters));
?>
<div class="admin-info-guide admin-lifecycle-guide" role="note" aria-label="Arrival lifecycle guide">
    <div class="admin-info-guide__title"><i class="las la-info-circle"></i> Lifecycle guide</div>
    <div class="admin-info-guide__grid admin-lifecycle-guide__grid">
        <div class="admin-info-guide__item admin-lifecycle-guide__needs">
            <b>Needs Shipping:</b>
            <span>No parcels arranged</span>
        </div>
        <div class="admin-info-guide__item admin-lifecycle-guide__partial">
            <b>Partially Arranged:</b>
            <span>Some parcels arranged</span>
        </div>
        <div class="admin-info-guide__item admin-lifecycle-guide__full">
            <b>Fully Arranged:</b>
            <span>Every parcel has shipping</span>
        </div>
        <div class="admin-info-guide__item admin-lifecycle-guide__cleared">
            <b>Cleared:</b>
            <span>Every parcel is in transit or completed</span>
        </div>
    </div>
</div>
<?php
$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'arrivals_travellers_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
?>
