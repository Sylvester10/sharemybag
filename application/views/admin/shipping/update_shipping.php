

<div class="new-item">
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_bookings'); ?>"><i
            class="fa fa-book"></i> All Bookings</a>
</div>



<?php
echo form_open_multipart('admin_bookings/update_shipping_ajax/' . $y->id); ?>

<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12">

        <div class="form-group">
            <label class="form-control-label">Tracking ID</label>
            <input type="text" name="tracking_id" class="form-control"
                value="<?php echo set_value('tracking_id', $y->tracking_id); ?>" readonly />
        </div>

        <div class="form-group">
            <label class="form-control-label">Heading*</label>
            <br />
            <select class="form-control" name="heading">
                <option value=""> Select </option>
                <option value="Shipment Created"> Shipment Created </option>
                <option value="In Transit"> In Transit </option>
                <option value="Delivered"> Delivered </option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-control-label">Body</label>
            <textarea id="email_message" name="body" class="form-control t200"></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary m-t-5">Update Shipping Info</button>
        </div>

    </div><!--/.col-md-6-->
</div><!--/.row-->

<?php echo form_close(); ?>