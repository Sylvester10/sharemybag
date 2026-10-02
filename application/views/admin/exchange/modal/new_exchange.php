<div class="modal fade admin-form-modal admin-form-modal--compact" id="new_exchange" role="dialog" aria-modal="true" aria-labelledby="newExchangeTitle">
    <div class="modal-dialog admin-form-modal__dialog">
        <div class="modal-content modal-form-sm admin-form-modal__content">
            <div class="modal-header ">
                <div class="pull-right">
                    <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close"
                        title="Close">&times;</button>
                </div>
                <h4 class="modal-title admin-form-modal__title" id="newExchangeTitle">Update Exchange Rate</h4>
            </div><!--/.modal-header-->
            <?php echo form_open('admin_exchange/add_exchange_rate/', 'class="admin-form-modal__form"'); ?>
            <div class="modal-body admin-form-modal__body">
                <div class="admin-form-modal__section">

                    <div class="form-group">
                        <label class="form-control-label" for="exchangeCurrency">Select Currency</label>
                        <select class="form-control selectpicker" name="currency" id="exchangeCurrency" required>
                            <option value="">Select</option>
                            <option value="CAD">Canadian Dollar (CAD)</option>
                            <option value="GBP">British Pound (GBP)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label" for="exchangeRate">Exchange Rate</label>
                        <input type="text" name="rate" class="form-control" id="exchangeRate" required />
                    </div>
                </div>
            </div>
            <div class="modal-footer admin-form-modal__footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
