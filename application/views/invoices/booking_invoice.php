<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($title); ?></title>
    <style>
        <?php echo $invoice_css; ?>
    </style>
</head>

<body>
    <div class="invoice-page">
        <div class="invoice-wrap">
            <?php if ($show_actions) { ?>
                <div class="invoice-toolbar d-print-none">
                    <a class="invoice-btn invoice-btn--dark" href="<?php echo $back_url; ?>"><?php echo $back_label; ?></a>
                    <!-- <a class="invoice-btn invoice-btn--dark" href="<?php echo $download_url; ?>">Download PDF</a> -->
                    <button class="invoice-btn invoice-btn--accent" type="button" onclick="window.print()">Download/Print</button>
                </div>
            <?php } ?>

            <div class="invoice-card">
                <table class="invoice-hero-table" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="invoice-hero-table__brand">
                            <table class="invoice-brand-table" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="invoice-brand-table__logo-cell">
                                        <?php if ($invoice_logo_uri) { ?>
                                            <img src="<?php echo $invoice_logo_uri; ?>" alt="<?php echo business_name; ?>" class="invoice-brand-table__logo">
                                        <?php } else { ?>
                                            <img src="<?php echo base_url('assets/general/logo/favicon.ico'); ?>" alt="<?php echo business_name; ?>" class="invoice-brand-table__logo">
                                        <?php } ?>
                                    </td>
                                    <td class="invoice-brand-table__text">
                                        <div class="invoice-brand-table__name"><?php echo business_name; ?></div>
                                        <div class="invoice-brand-table__tag"><?php echo sub_tagline; ?></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="invoice-hero-table__meta">
                            <div class="invoice-meta-box invoice-meta-box--method">
                                <span>CUSTOMER INVOICE</span>
                            </div>
                            <div class="invoice-meta-box">
                                <span>Invoice No: <strong><?php echo html_escape($invoice_number); ?></strong></span>
                            </div>
                            <div class="invoice-meta-box">
                                <span>Date: <strong><?php echo x_date($booking->date_added); ?></strong></span>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="invoice-card__body">
                    <table class="invoice-address-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="invoice-address-table__cell">
                                <div class="invoice-label">Invoice to</div>
                                <div class="invoice-heading"><?php echo html_escape($booking->user_fullname ?: $booking->agent_name); ?></div>
                            </td>
                            <td class="invoice-address-table__cell">
                                <div class="invoice-label">Shipment Details</div>
                                <div class="invoice-heading"><?php echo html_escape($route_label); ?></div>
                                <div class="invoice-subtle">Weight: <?php echo html_escape($weight_label); ?></div>
                            </td>
                            <td class="invoice-address-table__cell">
                                <div class="invoice-label">Traveller</div>
                                <div class="invoice-heading"><?php echo html_escape($booking->traveller_name ?: 'N/A'); ?></div>
                            </td>
                            <td class="invoice-address-table__cell">
                                <?php if (!empty($invoice_paid_stamp_uri)) { ?>
                                    <img src="<?php echo $invoice_paid_stamp_uri; ?>" alt="Paid" class="invoice-paid-stamp">
                                <?php } ?>
                            </td>
                        </tr>
                    </table>

                    <table class="invoice-table" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th class="invoice-table__wide">Charges Breakdown</th>
                                <th>Rate × Weight</th>
                                <th>Type</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="invoice-row-title">Delivery Fee (Paid to Traveller)</div>
                                    <div class="invoice-row-copy">This amount is collected on behalf of the traveller.</div>
                                </td>
                                <td><?php echo $currency_symbol . number_format($delivery_fee_rate, 2); ?> x <?php echo html_escape($weight_label); ?></td>
                                <td>Traveller</td>
                                <td class="text-right"><?php echo $currency_symbol . number_format($delivery_fee_total, 2); ?></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="invoice-row-title">ShareMyBag Commission</div>
                                    <div class="invoice-row-copy">Platform commission charged on this booking.</div>
                                </td>
                                <td><?php echo $currency_symbol . number_format($sharemybag_commission_rate, 2); ?> x <?php echo html_escape($weight_label); ?></td>
                                <td>Platform</td>
                                <td class="text-right"><?php echo $currency_symbol . number_format($sharemybag_commission_total, 2); ?></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="invoice-row-title">Service Fee</div>
                                    <div class="invoice-row-copy">Operational platform fee for booking support.</div>
                                </td>
                                <td>-</td>
                                <td>Platform</td>
                                <td class="text-right"><?php echo $currency_symbol . number_format($service_fee_total, 2); ?></td>
                            </tr>
                            <?php if ((float) $insurance_total > 0) { ?>
                                <tr>
                                    <td>
                                        <div class="invoice-row-title">Insurance</div>
                                        <div class="invoice-row-copy">Insurance selected for this shipment.</div>
                                    </td>
                                    <td>-</td>
                                    <td>Optional</td>
                                    <td class="text-right"><?php echo $currency_symbol . number_format($insurance_total, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($vat_applies) { ?>
                                <tr>
                                    <td>
                                        <div class="invoice-row-title">VAT (7.5%)</div>
                                        <div class="invoice-row-copy">VAT applies ONLY to ShareMyBag charges: <?php echo $currency_symbol . number_format($sharemybag_commission_total, 2); ?> + <?php echo $currency_symbol . number_format($service_fee_total, 2); ?> = <?php echo $currency_symbol . number_format($platform_charge_total, 2); ?></div>
                                    </td>
                                    <td><?php echo $currency_symbol . number_format($platform_charge_total, 2); ?> base</td>
                                    <td>Tax (FIRS)</td>
                                    <td class="text-right"><?php echo $currency_symbol . number_format($vat_total, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <tr class="invoice-table__summary">
                                <td class="invoice-table__blank" colspan="2"></td>
                                <td>Delivery Fee (Traveller)</td>
                                <td class="text-right"><?php echo $currency_symbol . number_format($delivery_fee_total, 2); ?></td>
                            </tr>
                            <tr class="invoice-table__summary">
                                <td class="invoice-table__blank" colspan="2"></td>
                                <td>ShareMyBag Charges</td>
                                <td class="text-right"><?php echo $currency_symbol . number_format($sharemybag_commission_total + $service_fee_total, 2); ?></td>
                            </tr>
                            <?php if ($vat_applies) { ?>
                                <tr class="invoice-table__summary">
                                    <td class="invoice-table__blank" colspan="2"></td>
                                    <td>VAT (7.5%)</td>
                                    <td class="text-right"><?php echo $currency_symbol . number_format($vat_total, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <tr class="invoice-table__grand-total">
                                <td class="invoice-table__blank" colspan="2"></td>
                                <td><b>TOTAL</b></td>
                                <td class="text-right"><b><?php echo $currency_symbol . number_format((float) $booking->total_amount, 2); ?></b></td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="invoice-footer-table" cellspacing="0" cellpadding="0">
                        <tr>
                            <td class="invoice-footer-table__terms">
                                <div class="invoice-footer-title">⚠️ Important Notes :</div>
                                <ul class="invoice-terms-list">
                                    <li>ShareMyBag acts as a platform connecting senders with travellers.</li>
                                    <li>Delivery fee is earned by the traveller and collected on their behalf.</li>
                                    <?php if ($vat_applies) { ?>
                                        <li>VAT is charged in accordance with regulations by the Federal Inland Revenue Service (FIRS) on platform fees only.</li>
                                    <?php } else { ?>
                                        <li>No VAT was applied to this booking because the selected payment method does not attract platform VAT.</li>
                                    <?php } ?>
                                </ul>
                            </td>
                            <td class="invoice-footer-table__signoff">
                                <div class="invoice-signoff-label">Operations Team</div>
                                <div class="invoice-signoff-line"></div>
                                <div class="invoice-signoff-name"><?php echo business_name; ?></div>
                            </td>
                        </tr>
                    </table>

                    <div class="invoice-thanks">
                        Thank you for using ShareMyBag ✈️📦
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
