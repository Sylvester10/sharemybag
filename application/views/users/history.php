<div class="container-fluid">

    <div class="card">

        <?php if (count($booking) == 0) { ?>

            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <dotlottie-wc src="https://lottie.host/764ac1e3-50ac-465b-89a9-8259af46e54b/Qx6tMx1p9G.lottie" style="width: 300px;height: 300px"
                        autoplay loop></dotlottie-wc>
                </div>
                <div class="text-center">
                    <p class="card-title fs-5">No bookings found</p>
                </div>
            </div>

        <?php } else { ?>

            <div class="card-header text-bg-primary">
                <h4 class="mb-0 text-white">Booking History</h4>
            </div>

            <div class="card-body">

                <div class="card !tw-bg-[#020713]">
                    <div class="card-body">
                        <h1 class="card-text text-center fw-bolder" style="color: red;">
                            <i class="ti ti-alert-triangle fs-9"></i>
                        </h1>

                        <div class="the_list mt-3 text-white">

                            <div class="list-item">
                                <i class="ti ti-brand-chrome fs-4 flex-shrink-0"></i>
                                <span class="list-text text-white">If you are posting your items, label your parcel like this: name of traveller + SMB[sender’s name].</span>
                            </div>

                            <div class="list-item">
                                <i class="ti ti-brand-chrome fs-4 flex-shrink-0"></i>
                                <span class="list-text text-white">Remember to include a return address.</span>
                            </div>

                            <div class="list-item">
                                <i class="ti ti-brand-chrome fs-4 flex-shrink-0"></i>
                                <span class="list-text text-white">Send us tracking details if you are sending your items via Royal Mail or Evri</span>
                            </div>
                        </div>

                        <p class="text-center mt-3 mb-0" style="color: red;">There is no refund or transfer of service to another traveler </p>
                    </div>
                </div>

                <div class="datatables">

                    <div class="table-responsive">

                        <table id="default_order" class="table table-striped table-bordered text-nowrap align-middle">

                            <div>
                                <p
                                    class="!tw-absolute !tw-pt-[42px] max-sm:!tw-pt-[69px] !tw-text-[10px] max-sm:!tw-text-[13px]">
                                    Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
                            </div>

                            <thead>
                                <tr>
                                    <th>Traveller Details</th>
                                    <th>Agent Details</th>
                                    <th>Item Details</th>
                                    <th>Payment Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php

                                foreach ($booking as $y) {

                                    // Determine the currency symbol based on the currency_charged field stored in the booking record
                                    // 'dollars' corresponds to CAD ($)
                                    // 'pounds' corresponds to GBP (£)
                                    // NOTE: The currency field in the booking table is assumed to be 'currency_charged' based on previous context,
                                    // but is referenced here as $y->currency, which I will maintain.
                                    $symbol = ($y->currency == 'dollars') ? '$' : '&pound;';


                                    $traveller_details = ($y->payment_status == 'canceled' || $y->payment_status == '')
                                        ? '<i class="ti ti-user"></i> N/A <br />
                                    <i class="ti ti-location"></i> N/A <br />
                                    <i class="ti ti-calendar"></i> N/A'
                                        : '<i class="ti ti-user"></i> ' . $y->traveller_name . ' <br />
                                    <i class="ti ti-location"></i> ' . $y->traveller_drop_address1 . ' <b>(First Drop-off)</b> <br />
                                    <i class="ti ti-location"></i> ' . $y->traveller_drop_address2 . ' <b>(Second Drop-off)</b> <br />
                                    <i class="ti ti-calendar"></i> ' . x_date($y->traveller_drop_date1) . ' <br/>
                                    <i class="ti ti-phone"></i> ' . business_phone_number . ' <br />';

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
                                        // Determine the unit. Default to KG, but use the unit field if it exists (for 'Piece')
                                        $unit_display = isset($item->unit) ? $item->unit : 'KG';

                                        $items .= '<tr>';
                                        $items .= '<td>' . $item->item_name . '</td>';
                                        $items .= '<td>' . $item->category . '</td>';
                                        // Use the dynamically determined unit here
                                        $items .= '<td>' . $item->size . $unit_display . '</td>';
                                        // Use the correctly determined $symbol here for item price
                                        $items .= '<td> ' . $symbol . '' . number_format($item->unit_price * $item->size, 2) . '</td>';
                                        $items .= '</tr>';
                                    }

                                    // Add a new row to display the total amount
                                    $items .= '<tr class="fw-bold">';
                                    // Use colspan to merge the first three columns for the label
                                    $items .= '<td colspan="3" class="text-end">Total Amount:</td>';
                                    // Place the total amount in the last column, using the determined $symbol
                                    $items .= '<td> ' . $symbol . '' . number_format($y->total_amount, 2) . '</td>';
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
                                        <td> <?= $payment_status ?> </td>
                                        <td data-order="<?= $y->date_added ?>"> <?= x_date($y->date_added) ?> </td>
                                    </tr>

                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>

</div>
