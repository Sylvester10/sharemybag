<div class="modal fade admin-form-modal admin-form-modal--compact" id="edit<?php echo $y->id; ?>" role="dialog" aria-modal="true" aria-labelledby="editBookingTitle<?php echo $y->id; ?>">
    <div class="modal-dialog admin-form-modal__dialog">
        <div class="modal-content modal-form-sm admin-form-modal__content">
            <div class="modal-header ">
                <div class="pull-right">
                    <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close"
                        title="Close">&times;</button>
                </div>
                <h4 class="modal-title admin-form-modal__title" id="editBookingTitle<?php echo $y->id; ?>">Edit Booking</h4>
            </div><!--/.modal-header-->
            <?php echo form_open('admin_bookings/edit_shipping/' . $y->id, 'class="admin-form-modal__form"'); ?>
            <div class="modal-body admin-form-modal__body">
                <div class="admin-form-modal__section">

                    <div class="form-group">
                        <label class="form-control-label" for="bookingHeading<?php echo $y->id; ?>">Heading</label>
                        <input type="text" name="heading" value="<?php echo set_value('heading', $y->heading); ?>"
                            class="form-control" id="bookingHeading<?php echo $y->id; ?>" required />
                    </div>

                    <div class="form-group">
                        <label class="form-control-label" for="bookingBody<?php echo $y->id; ?>">Body</label>
                        <textarea class="form-control t100" name="body" id="bookingBody<?php echo $y->id; ?>" minlength="2" maxlength="500" required><?php echo set_value('body', strip_tags($y->body)); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label" for="bookingStatus<?php echo $y->id; ?>">Delivery Status*</label>
                        <select class="form-control" name="delivery_status" id="bookingStatus<?php echo $y->id; ?>" required>
                            <option value="<?php echo set_value('delivery_status', $y->delivery_status); ?>"> <?php echo $y->delivery_status; ?> </option>
                            <option value="In Transit"> In Transit </option>
                            <option value="Completed"> Completed </option>
                        </select>
                    </div>

                </div>
            </div>
            <div class="modal-footer admin-form-modal__footer">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
