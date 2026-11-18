<div class="new-item admin-page-actions" role="group" aria-label="Arrivals destination">
    <button type="button" class="btn btn-primary btn-sm button-adjust" data-arrivals-destination="Nigeria" aria-pressed="true" aria-controls="arrivals_travellers_table">Nigeria Arrivals</button>
    <button type="button" class="btn btn-default btn-sm button-adjust" data-arrivals-destination="United Kingdom" aria-pressed="false" aria-controls="arrivals_travellers_table">UK Arrivals</button>
    <button type="button" class="btn btn-default btn-sm button-adjust" data-arrivals-destination="" aria-pressed="false" aria-controls="arrivals_travellers_table">All Arrivals</button>
</div>
<input type="hidden" id="arrivals_destination_filter" value="Nigeria">
<?php
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

$this->load->view('admin/partials/datatable_shell', array(
    'table_id' => 'arrivals_travellers_table',
    'columns' => $columns,
    'csrf_hash' => $this->security->get_csrf_hash(),
));
if (!empty($can_record_payout)) {
    $this->load->view('admin/travellers/modal/traveller_payout');
}
?>
