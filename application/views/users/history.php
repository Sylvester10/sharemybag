<div class="container-fluid">

    <div class="card">
        <div class="card-header text-bg-primary">
            <h4 class="mb-0 text-white">Booking History</h4>
        </div>
        <div class="card-body">
            <div class="datatables">

                <div class="table-responsive">

                    <table id="default_order" class="table table-striped table-bordered text-nowrap align-middle">

                        <div>
                            <p class="!tw-absolute !tw-pt-[42px] max-sm:!tw-pt-[69px] !tw-text-[10px] max-sm:!tw-text-[13px]">Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
                        </div>

                        <thead>
                            <tr>
                                <th>Traveller Details</th>
                                <th>Agent Details</th>
                                <th>Item Details</th>
                                <th>Total</th>
                                <th>Payment Status</th>
                                <!--<th>Tracking Number</th>-->
                                <!--<th>Delivery Status</th>-->
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            foreach ($booking as $y) {  ?>

                                <?php

                                $traveller_details = ($y->payment_status == 'canceled' || $y->payment_status == '')
                                    ? '<i class="ti ti-user"></i> N/A <br />
                                    <i class="ti ti-location"></i> N/A <br />
                                    <i class="ti ti-calendar"></i> N/A'
                                    : '<i class="ti ti-user"></i> ' . $y->traveller_name . ' <br />
                                    <i class="ti ti-location"></i> ' . $y->traveller_drop_address1 . ' <b>(First Drop-off)</b> <br />
                                    <i class="ti ti-location"></i> ' . $y->traveller_drop_address2 . ' <b>(Second Drop-off)</b> <br />
                                    <i class="ti ti-calendar"></i> ' . x_date($y->traveller_drop_date1);

                                // agent details
                                $agent_details = '<i class="ti ti-user"></i> ' . $y->agent_name . ' <br />
                                                <i class="ti ti-phone"></i> ' . $y->agent_phone . ' <br />
                                                <i class="ti ti-mail"></i> ' . $y->agent_email . ' <br />
                                                <i class="ti ti-location"></i> ' . $y->agent_address . ', ' . $y->agent_locality . ', ' . $y->agent_postcode . '';

                                // item details
                                $items = ''; // Initialize $items variable

                                $items .= '<table class="table text-nowrap fs-2">';
                                $items .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
                                $items .= '<tbody>';

                                // Loop through each item to display its details
                                foreach (json_decode($y->items) as $item) {
                                    $items .= '<tr>';
                                    $items .= '<td>' . $item->item_name . '</td>';
                                    $items .= '<td>' . $item->category . '</td>';
                                    $items .= '<td>' . $item->size . 'KG</td>';
                                    $items .= '<td> &pound;' . number_format($item->price, 2) . '</td>';
                                    $items .= '</tr>';
                                }

                                // Add a new row to display the total amount
                                $items .= '<tr class="fw-bold">';
                                // Use colspan to merge the first three columns for the label
                                $items .= '<td colspan="3" class="text-end">Total Amount:</td>';
                                // Place the total amount in the last column
                                $items .= '<td> &pound;' . number_format($y->total_amount, 2) . '</td>';
                                $items .= '</tr>';

                                $items .= '</tbody>';
                                $items .= '</table>';

                                // payment status
                                $payment_status = ($y->payment_status == 'completed') ? '<span class="badge bg-success-subtle text-success">Completed</span>' : (($y->payment_status == 'canceled') ? '<span class="badge bg-danger-subtle text_danger">Canceled</span>' : '<span class="badge bg-warning-subtle text-warning">Pending</span>');

                                // delivery status
                                $delivery_status = ($y->delivery_status == 'Delivered') ? '<span class="text-success">Delivered <i class="ti ti-circle-check text-success fs-5"></i> </span>' : (($y->delivery_status == 'In Transit') ? '<span class="text-secondary">In Transit <i class="ti ti-clock text-secondary fs-5"></i> </span>' : (($y->delivery_status == 'Shipment Created') ? '<span class="text-primary">Shipment Created <i class="ti ti-checklist text-primary fs-5"></i></span>' : '<span class="text_danger">Pending <span class="spinner-border spinner-border-sm text_pending ms-1" role="status" aria-hidden="true"></span></span>'));

                                ?>

                                <tr class="fs-3">
                                    <td> <?= $traveller_details ?> </td>
                                    <td> <?= $agent_details ?> </td>
                                    <td> <?= $items ?> </td>
                                    <td> &pound;<?= number_format($y->total_amount, 2) ?> </td>
                                    <td> <?= $payment_status ?> </td>
                                    <!--<td> <?= $y->tracking_id ?> </td>-->
                                    <!--<td> <?= $delivery_status ?> </td>-->
                                    <td data-order="<?= $y->date_added ?>"> <?= x_date($y->date_added) ?> </td>
                                </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>