<div class="modal fade" id="new_exchange" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content modal-form-sm">
            <div class="modal-header">
                <div class="pull-right">
                    <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close"
                        title="Close"> &times;</button>
                </div>
                <h4 class="modal-title">Update Exchange Rate</h4>
            </div><!--/.modal-header-->
            <div class="modal-body">
                <?php
                echo form_open('admin_exchange/add_exchange_rate/'); ?>

                <div class="form-group">
                    <label class="form-control-label">Exchange Rate</label>
                    <input type="text" name="rate" class="form-control" />
                </div>

                <div>
                    <button class="btn btn-primary">Sumbit </button>
                </div>

                <?php echo form_close(); ?>

            </div>
        </div>
    </div>
</div>