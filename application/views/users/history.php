<div class="container-fluid booking-history-view" id="booking-history">

    <div class="traveller-search-panel overflow-hidden">
        <div class="card-body p-4">
            <h4 class="mb-2 text-white"><?= !empty($active_only) ? 'Active Bookings' : 'Booking History' ?></h4>
            <p class="text-white mb-1 fs-3">
                <?= !empty($active_only) ? 'View your active bookings and details.' : 'View your complete booking history and details.' ?>
            </p>
            <?php if (!empty($active_only)): ?>
                <a href="<?= base_url('history') ?>" class="text-white fs-3">View all bookings <i class="ti ti-arrow-right" aria-hidden="true"></i></a>
            <?php endif; ?>
        </div>
    </div>

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

            <div class="card-body">

                <section class="history-posting-notes">
                    <h2>
                        <button type="button" class="history-posting-toggle fs-2" id="history-posting-toggle" data-bs-toggle="collapse" data-bs-target="#history-posting-content" aria-expanded="false" aria-controls="history-posting-content">
                            <i class="ti ti-info-circle" aria-hidden="true"></i>
                            <span>There is no refund or transfer of service to another traveler</span>
                        </button>
                    </h2>
                    <div class="collapse" id="history-posting-content" role="region" aria-labelledby="history-posting-toggle">
                        <div class="history-posting-content">
                            <ul class="the_list history-posting-list" role="list">
                                <li class="list-item"><i class="ti ti-brand-chrome fs-4 flex-shrink-0" aria-hidden="true"></i><span class="list-text text-white">If you are posting your items, label your parcel like this: name of traveller + SMB[sender’s name].</span></li>
                                <li class="list-item"><i class="ti ti-brand-chrome fs-4 flex-shrink-0" aria-hidden="true"></i><span class="list-text text-white">Remember to include a return address.</span></li>
                                <li class="list-item"><i class="ti ti-brand-chrome fs-4 flex-shrink-0" aria-hidden="true"></i><span class="list-text text-white">Send us tracking details if you are sending your items via Royal Mail or Evri</span></li>
                            </ul>
                        </div>
                    </div>
                </section>

                <div class="datatables">

                    <div class="table-responsive history-table-region">

                        <table id="default_order" class="table table-striped table-bordered text-nowrap align-middle fs-2 history-table">

                            <thead>
                                <tr>
                                    <th class="fs-2">Traveller Details</th>
                                    <th class="fs-2">Agent Details</th>
                                    <th class="fs-2">Item Details</th>
                                    <th class="fs-2">Payment Status</th>
                                    <th class="fs-2">Date</th>
                                    <th class="fs-2">Invoice</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php

                                foreach ($booking as $y) {

                                    // Determine the currency symbol based on the currency_charged field stored in the booking record
                                    $symbol = currency_symbol($y->currency);
                                    $parcel_items = json_decode((string) $y->items);
                                    $parcel_items = is_array($parcel_items) ? $parcel_items : [];
                                    $booking_reference = trim((string) ($y->tracking_id ?? '')) ?: 'Booking #' . (int) $y->id;
                                    // Match search results: area, city/state; countries supply the flags.
                                    $country_route = $booking_routes[(int) ($y->traveller_id ?? 0)] ?? null;
                                    $country_labels = ['united kingdom' => 'UK', 'uk' => 'UK', 'nigeria' => 'Nigeria', 'canada' => 'Canada'];
                                    $origin_country = trim((string) ($country_route->location ?? '')) ?: trim((string) ($y->traveller_current_state ?? ''));
                                    $destination_country = trim((string) ($country_route->destination ?? '')) ?: trim((string) ($y->traveller_destination ?? ''));
                                    $origin_label = $country_labels[strtolower($origin_country)] ?? '';
                                    $destination_label = $country_labels[strtolower($destination_country)] ?? '';
                                    $route_origin = implode(', ', array_filter(array_map('trim', [
                                        (string) ($country_route->area ?? ''), (string) ($country_route->current_state ?? '')
                                    ]), static function ($part) { return $part !== ''; })) ?: $origin_label;
                                    $route_destination = implode(', ', array_filter(array_map('trim', [
                                        (string) ($country_route->destination_area ?? ''), (string) ($country_route->arrival_state ?? '')
                                    ]), static function ($part) { return $part !== ''; })) ?: $destination_label;
                                    $booking_route = ($route_origin !== '' && $route_destination !== '')
                                        ? $route_origin . ' → ' . $route_destination : 'Route unavailable';
                                    $route_search = ($route_origin !== '' && $route_destination !== '') ? $origin_country . ' ' . $destination_country : '';
                                    $country_flags = ['UK' => 'gb', 'Nigeria' => 'ng', 'Canada' => 'ca'];

                                    $traveller_name = trim((string) $y->traveller_name);
                                    $traveller_name_parts = preg_split('/\s+/', $traveller_name, -1, PREG_SPLIT_NO_EMPTY);
                                    $traveller_display_name = $traveller_name;

                                    if (count($traveller_name_parts) > 1) {
                                        $last_name = end($traveller_name_parts);
                                        $last_initial = function_exists('mb_substr')
                                            ? mb_substr($last_name, 0, 1, 'UTF-8')
                                            : substr($last_name, 0, 1);
                                        $traveller_display_name = $traveller_name_parts[0] . ' ' . strtoupper($last_initial) . '.';
                                    }


                                    $normalized_status = payment_status_normalize($y->payment_status);
                                    $recovery = isset($booking_checkout[$y->id]) && is_array($booking_checkout[$y->id])
                                        ? $booking_checkout[$y->id] : null;
                                    $traveller_details = ($normalized_status == 'canceled' || $normalized_status == 'pending')
                                        ? '<i class="ti ti-user"></i> N/A <br />
                                    <i class="ti ti-location"></i> N/A <br />
                                    <i class="ti ti-calendar"></i> N/A'
                                        : '<i class="ti ti-user"></i> ' . html_escape($traveller_display_name) . ' <br />
                                    <i class="ti ti-location"></i> ' . html_escape($y->traveller_drop_address1) . ' <b>(First Drop-off)</b> <br />
                                    <i class="ti ti-location"></i> ' . html_escape($y->traveller_drop_address2) . ' <b>(Second Drop-off)</b> <br />
                                    <i class="ti ti-calendar"></i> ' . x_date($y->traveller_drop_date1) . ' <br/>
                                    <i class="ti ti-phone"></i> ' . business_phone_number . ' <br />';

                                    if ($normalized_status === 'completed') {
                                        $support_url = booking_support_whatsapp_url($y);
                                        if ($support_url !== '') {
                                            $traveller_details .= '<a class="booking-support-link fs-2" href="' . html_escape($support_url) . '" target="_blank" rel="noopener noreferrer"><i class="ti ti-brand-whatsapp" aria-hidden="true"></i> Need help with this parcel?</a>';
                                        }
                                    }

                                    // agent details
                                    $agent_details = '<i class="ti ti-user"></i> ' . html_escape($y->agent_name) . ' <br />
                                                <i class="ti ti-phone"></i> ' . html_escape($y->agent_phone) . ' <br />
                                                <i class="ti ti-mail"></i> ' . html_escape($y->agent_email) . ' <br />
                                                <i class="ti ti-location"></i> ' . html_escape($y->agent_address) . ', ' . html_escape($y->agent_locality) . ', ' . html_escape($y->agent_postcode) . '';

                                    // item details
                                    $items = ''; // Initialize $items variable

                                    $items .= '<table class="table text-nowrap fs-2">';
                                    $items .= '<thead><tr><th>Item</th><th>Category</th><th>Size</th><th>Price</th></tr></thead>';
                                    $items .= '<tbody>';

                                    $purchased_kg = 0.0;
                                    $purchased_pieces = 0.0;
                                    // Loop through each item to display its details
                                    foreach ($parcel_items as $item) {
                                        if (!is_object($item)) { continue; }
                                        // Determine the unit. Default to KG, but use the unit field if it exists (for 'Piece')
                                        $unit_display = isset($item->unit) ? $item->unit : 'KG';
                                        $item_size = max(0, (float) ($item->size ?? 0));
                                        if (in_array(strtolower(trim($unit_display)), ['pc', 'pcs', 'piece', 'pieces'], true)) {
                                            $purchased_pieces += $item_size;
                                        } else {
                                            $purchased_kg += $item_size;
                                        }

                                        $items .= '<tr>';
                                        $items .= '<td>' . html_escape($item->item_name) . '</td>';
                                        $items .= '<td>' . html_escape($item->category) . '</td>';
                                        // Use the dynamically determined unit here
                                        $items .= '<td>' . html_escape($item->size) . html_escape($unit_display) . '</td>';
                                        // Use the correctly determined $symbol here for item price
                                        $items .= '<td> ' . $symbol . '' . number_format((float) $item->unit_price * (float) $item->size, 2) . '</td>';
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
                                    if (empty($parcel_items)) {
                                        $purchased_kg = max(0, (float) ($y->selected_space ?? 0));
                                    }
                                    $space_parts = [];
                                    if ($purchased_kg > 0) { $space_parts[] = rtrim(rtrim(number_format($purchased_kg, 3, '.', ''), '0'), '.') . ' kg'; }
                                    if ($purchased_pieces > 0) { $space_parts[] = rtrim(rtrim(number_format($purchased_pieces, 3, '.', ''), '0'), '.') . ' pc'; }
                                    $booking_space = $space_parts ? implode(' · ', $space_parts) : '—';

                                    // payment status
                                    $payment_status = ($normalized_status == 'completed') ? '<span class="badge rounded-pill bg-success-subtle text-success fs-2">Completed</span>' : (($normalized_status == 'canceled') ? '<span class="badge rounded-pill bg-danger-subtle text_danger fs-2">Canceled</span>' : '<span class="badge rounded-pill bg-warning-subtle text-warning fs-2">Pending</span>');
                                    if ($recovery && $normalized_status !== 'completed') {
                                        $payment_status = !empty($recovery['review_required'])
                                            ? '<span class="badge rounded-pill bg-warning-subtle text-warning fs-2">Payment needs review</span>'
                                            : (!empty($recovery['checking_payment'])
                                            ? '<span class="badge rounded-pill bg-warning-subtle text-warning fs-2">Checking payment</span>'
                                            : (!empty($recovery['recovery_expired'])
                                            ? '<span class="badge rounded-pill bg-danger-subtle text_danger fs-2">Canceled</span>'
                                            : '<span class="badge rounded-pill bg-warning-subtle text-warning fs-2">Not completed</span>'));
                                    }
                                    $booking_search = $booking_reference . ' ' . $booking_route . ' ' . $route_search . ' ' . $booking_space . ' '
                                        . preg_replace('/\s+(kg|pc)\b/', '$1', $booking_space) . ' '
                                        . $symbol . number_format($y->total_amount, 2) . ' ' . strip_tags($payment_status);

                                    // delivery status
                                    $normalized_delivery_status = delivery_status_normalize($y->delivery_status);
                                    $delivery_status = ($normalized_delivery_status == 'Completed')
                                        ? '<span class="text-success">Completed <i class="ti ti-circle-check text-success fs-5"></i> </span>'
                                        : '<span class="text-secondary">In Transit <i class="ti ti-clock text-secondary fs-5"></i> </span>';

                                ?>

                                    <tr class="fs-2" data-history-row>
                                        <td class="fs-2"><div data-history-traveller><?= $traveller_details ?></div></td>
                                        <td class="fs-2"><div data-history-agent><?= $agent_details ?></div></td>
                                        <td class="fs-2"><div data-history-items><?= $items ?></div></td>
                                        <td class="fs-2 history-payment-cell" data-order="<?= html_escape($normalized_status) ?>" data-search="<?= html_escape($booking_search) ?>">
                                            <div class="history-card-content">
                                                <button type="button" class="history-card-open history-mobile-only fs-2" data-history-open data-history-reference="<?= html_escape($booking_reference) ?>" data-bs-toggle="modal" data-bs-target="#history-booking-details" aria-haspopup="dialog" aria-controls="history-booking-details" aria-label="<?= html_escape('View booking ' . $booking_reference) ?>">
                                                    <span class="history-route" data-history-route data-history-route-search="<?= html_escape($route_search) ?>">
                                                        <?php if ($route_origin !== '' && $route_destination !== ''): ?>
                                                            <span class="history-route-country"><?php if (isset($country_flags[$origin_label])): ?><i class="cf-16 cf-<?= $country_flags[$origin_label] ?>" aria-hidden="true"></i><?php endif; ?><span class="history-route-label"><?= html_escape($route_origin) ?></span></span>
                                                            <span class="history-route-arrow">→</span>
                                                            <span class="history-route-country"><?php if (isset($country_flags[$destination_label])): ?><i class="cf-16 cf-<?= $country_flags[$destination_label] ?>" aria-hidden="true"></i><?php endif; ?><span class="history-route-label"><?= html_escape($route_destination) ?></span></span>
                                                        <?php else: ?>
                                                            <?= html_escape($booking_route) ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </button>
                                                <div class="history-card-meta history-mobile-only">
                                                    <div><span class="history-card-space"><i class="ti ti-luggage" aria-hidden="true"></i><span data-history-space><?= html_escape($booking_space) ?></span></span><strong><?= $symbol . number_format($y->total_amount, 2) ?></strong></div>
                                                </div>
                                                <div class="history-card-footer">
                                                    <p class="history-mobile-only history-card-date"><i class="ti ti-calendar" aria-hidden="true"></i> <?= x_date($y->date_added) ?></p>
                                                    <div data-history-status><?= $payment_status ?></div>
                                                </div>
                                                <?php if ($recovery && $normalized_status !== 'completed' && empty($recovery['recovery_expired'])): ?>
                                                    <div class="d-flex flex-column align-items-start gap-2 mt-2" data-booking-recovery data-checkout-token="<?= html_escape($resume_token ?? '') ?>">
                                                        <?php if (empty($recovery['review_required']) && (!empty($recovery['resume_available']) || !empty($recovery['checking_payment']))): ?>
                                                            <button type="button" class="btn btn-sm btn-primary fs-2" data-booking-resume data-booking-mode="<?= !empty($recovery['checking_payment']) ? 'check' : 'resume' ?>" data-expiry-checking="<?= !empty($recovery['expiry_checking_payment']) ? 'true' : 'false' ?>" data-booking-hash="<?= html_escape($y->hash) ?>" <?= !empty($recovery['resume_until']) ? 'data-resume-until="' . (int) $recovery['resume_until'] . '"' : '' ?> disabled>
                                                                <span data-booking-resume-label><?= !empty($recovery['checking_payment']) ? 'Check payment' : 'Resume booking' ?></span>
                                                                <span class="spinner-border spinner-border-sm ms-1 d-none" data-booking-resume-spinner aria-hidden="true"></span>
                                                            </button>
                                                        <?php endif; ?>
                                                        <small class="text-muted text-wrap fs-2" data-booking-recovery-status role="status" aria-live="polite"><?= html_escape($recovery['message'] ?? '') ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="fs-2" data-order="<?= html_escape($y->date_added) ?>"><span data-history-date><?= x_date($y->date_added) ?></span></td>
                                        <td class="fs-2">
                                            <div data-history-invoice>
                                            <?php if ($normalized_status == 'completed') { ?>
                                                <div class="d-flex flex-column gap-2">
                                                    <a class="btn btn-sm btn-primary fs-2" href="<?= base_url('invoice/' . $y->id) ?>" target="_blank">View Invoice</a>
                                                    <!-- <a class="btn btn-sm btn-outline-primary" href="<?= base_url('invoice/download/' . $y->id) ?>">Download</a> -->
                                                </div>
                                            <?php } else { ?>
                                                <span class="text-muted">Not available</span>
                                            <?php } ?>
                                            </div>
                                        </td>
                                    </tr>

                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>

    <?php if (count($booking) > 0): ?>
        <div class="modal fade history-details-modal" id="history-booking-details" tabindex="-1" aria-labelledby="history-details-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-4 history-route" id="history-details-title" data-history-target="route">Booking details</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close booking details"></button>
                    </div>
                    <div class="modal-body fs-3" tabindex="0" role="region" aria-label="Booking details">
                        <section class="history-details-section">
                            <h3 class="fs-4">Traveller Details</h3>
                            <div data-history-target="traveller"></div>
                        </section>
                        <section class="history-details-section">
                            <h3 class="fs-4">Agent Details</h3>
                            <div data-history-target="agent"></div>
                        </section>
                        <section class="history-details-section">
                            <h3 class="fs-4">Item Details</h3>
                            <div data-history-target="items"></div>
                        </section>
                        <section class="history-details-section">
                            <h3 class="fs-4">Payment Details</h3>
                            <dl class="history-payment-details">
                                <div><dt>Payment status</dt><dd data-history-target="status"></dd></div>
                                <div><dt><i class="ti ti-calendar" aria-hidden="true"></i> Date</dt><dd data-history-target="date"></dd></div>
                                <div><dt>Booking ID</dt><dd data-history-target="reference"></dd></div>
                            </dl>
                        </section>
                    </div>
                    <div class="modal-footer" data-history-target="invoice" hidden></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
