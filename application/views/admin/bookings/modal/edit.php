<div class="modal fade" id="edit<?php echo $y->id; ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content modal-form-sm">
            <div class="modal-header">
                <div class="pull-right">
                    <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" class="close"
                        title="Close"> &times;</button>
                </div>
                <h4 class="modal-title">Edit Booking</h4>
            </div><!--/.modal-header-->
            <div class="modal-body">
                <?php
                echo form_open('admin_bookings/edit_shipping/' . $y->id); ?>

                <div class="form-group">
                    <label class="form-control-label">Heading</label>
                    <input type="text" name="heading" value="<?php echo set_value('heading', $y->heading); ?>"
                        class="form-control" />
                </div>
				
				<div class="form-group">
					<label class="form-control-label">Body</label>
					<textarea class="form-control t100" name="body"><?php echo set_value('body', strip_tags($y->body)); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Delivery Status*</label>
                    <br />
                    <select class="form-control" name="delivery_status">
                        <option value="<?php echo set_value('delivery_status', $y->delivery_status); ?>"> <?php echo $y->delivery_status; ?> </option>
                        <option value="Shipment Created"> Shipment Created </option>
                        <option value="In Transit"> In Transit </option>
                        <option value="Delivered"> Delivered </option>
                    </select>
                </div>

                <div>
                    <button class="btn btn-primary">Update </button>
                </div>

                <?php echo form_close(); ?>

            </div>
        </div>
    </div>
</div>