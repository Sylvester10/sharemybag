<?php
$status_success = $this->session->flashdata('status_msg_success');
$status_error = $this->session->flashdata('status_msg_error');
?>

<?php if ($status_success): ?>
    <div class="alert alert-success"><?php echo html_escape($status_success); ?></div>
<?php endif; ?>

<?php if ($status_error): ?>
    <div class="alert alert-danger"><?php echo html_escape($status_error); ?></div>
<?php endif; ?>


<?php if (!$pricing_table_ready): ?>
    <div class="alert alert-warning">
        Pricing tables are not available yet. Run the setup SQL or migration before saving changes. The values below are currently the code fallback defaults.
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="x_content">
            <p class="text-muted">
                These values control booking prices, traveller payouts, the homepage estimate, and all route-based pricing displays. Changes are route-specific and super-admin only.
            </p>

            <div class="alert alert-info">
                <strong>How payouts work:</strong> each item uses one matching traveller payout only. Normal and duty-free items use the normal payout; fish/meat and medication use the special payout; documents and small electronics use the premium-small payout; laptops use the premium-laptop payout.
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered admin-panel-table">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Currency</th>
                            <th>Service Charge (per booking)</th>
                            <th>Normal Items (per kg)</th>
                            <th>Fish/Meat &amp; Medication (per kg)</th>
                            <th>Duty-Free Shopping (per kg)</th>
                            <th>Documents &amp; Small Electronics (per item)</th>
                            <th>Laptop (per item)</th>
                            <th>Normal Items Payout (per kg)</th>
                            <th>Fish/Meat &amp; Medication Payout (per kg)</th>
                            <th>Documents &amp; Small Electronics Payout (per item)</th>
                            <th>Laptop Payout (per item)</th>
                            <th>Last Updated</th>
                            <th>Updated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pricing_routes as $route): ?>
                            <?php $symbol = currency_symbol($route['currency']); ?>
                            <tr>
                                <td>
                                    <strong><?php echo html_escape($route['origin'] . ' - ' . $route['destination']); ?></strong>
                                </td>
                                <td><?php echo html_escape($route['currency']); ?></td>
                                <td><?php echo $symbol . number_format($route['service_charge'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['normal_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['special_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['duty_free_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['premium_small_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['premium_laptop_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['normal_payout_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['special_payout_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['premium_small_payout_rate'], 2); ?></td>
                                <td><?php echo $symbol . number_format($route['premium_laptop_payout_rate'], 2); ?></td>
                                <td>
                                    <?php echo $route['updated_at'] ? x_date_month_time_full($route['updated_at']) : '<span class="text-muted">Default</span>'; ?>
                                </td>
                                <td>
                                    <?php if (!empty($route['updated_by_name'])): ?>
                                        <?php echo html_escape($route['updated_by_name']); ?><br>
                                        <small class="text-muted"><?php echo html_escape(ucwords(str_replace('_', ' ', $route['updated_by_role']))); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System Default</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#pricing_<?php echo html_escape($route['route_key']); ?>">
                                        <i class="las la-pen"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php foreach ($pricing_routes as $route): ?>
    <?php $route_key = $route['route_key']; ?>
    <?php $symbol = currency_symbol($route['currency']); ?>
    <div class="modal fade admin-form-modal admin-form-modal--wide" id="pricing_<?php echo html_escape($route_key); ?>" role="dialog" aria-modal="true" aria-labelledby="pricingTitle_<?php echo html_escape($route_key); ?>">
        <div class="modal-dialog modal-lg admin-form-modal__dialog">
            <div class="modal-content admin-form-modal__content">
                <div class="modal-header ">
                    <div class="pull-right">
                        <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">&times;</button>
                    </div>
                    <h4 class="modal-title admin-form-modal__title" id="pricingTitle_<?php echo html_escape($route_key); ?>">Edit Pricing: <?php echo html_escape($route['origin'] . ' - ' . $route['destination']); ?></h4>
                </div>
                <?php echo form_open('admin_pricing/update/' . $route_key, 'class="admin-form-modal__form"'); ?>
                <div class="modal-body admin-form-modal__body">
                    <div class="alert alert-info">
                        Currency for this route is fixed to <strong><?php echo html_escape($route['currency']); ?></strong>. Customer-facing prices and traveller payouts below all use <?php echo $symbol; ?>.
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="well well-sm">
                                <strong>Quick summary</strong><br>
                                Customer normal rate: <?php echo $symbol . number_format($route['normal_rate'], 2); ?> per kg<br>
                                Traveller normal payout: <?php echo $symbol . number_format($route['normal_payout_rate'], 2); ?> per kg
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-modal__section">
                        <div class="row admin-form-modal__grid">
                            <div class="col-md-12">
                                <h4 class="admin-form-modal__section-title">Customer Charges</h4>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Service Charge (per booking)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="service_charge" value="<?php echo html_escape(number_format($route['service_charge'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Normal Items Rate (per kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="normal_rate" value="<?php echo html_escape(number_format($route['normal_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Fish/Meat &amp; Medication Rate (per kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="special_rate" value="<?php echo html_escape(number_format($route['special_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Duty-Free Shopping Rate (per kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="duty_free_rate" value="<?php echo html_escape(number_format($route['duty_free_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Documents &amp; Small Electronics Rate (per item)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="premium_small_rate" value="<?php echo html_escape(number_format($route['premium_small_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Laptop Rate (per item)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="premium_laptop_rate" value="<?php echo html_escape(number_format($route['premium_laptop_rate'], 2, '.', '')); ?>" required>
                            </div>
                        </div>

                        <div class="row admin-form-modal__grid">
                            <div class="col-md-12">
                                <h4 class="admin-form-modal__section-title">Traveller Payouts</h4>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Normal Items Payout (per kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="normal_payout_rate" value="<?php echo html_escape(number_format($route['normal_payout_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Fish/Meat &amp; Medication Payout (per kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="special_payout_rate" value="<?php echo html_escape(number_format($route['special_payout_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Documents &amp; Small Electronics Payout (per item)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="premium_small_payout_rate" value="<?php echo html_escape(number_format($route['premium_small_payout_rate'], 2, '.', '')); ?>" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Laptop Payout (per item)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="premium_laptop_payout_rate" value="<?php echo html_escape(number_format($route['premium_laptop_payout_rate'], 2, '.', '')); ?>" required>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer admin-form-modal__footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save"></i> Save Pricing
                    </button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
