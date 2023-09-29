<div class="new-item">
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('bookings'); ?>"><i
            class="fa fa-book"></i> All Bookings</a>
</div>

<?php
$form_attributes = array(
    // 'target' => '_blank'
);
echo form_open_multipart('bookings/edit_booking_ajax/' . $y->id, $form_attributes); ?>

<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12">

        <div class="form-group">
            <label class="form-control-label">Category*</label>
            <?php 
                $options = array(
                    'Normal' => 'Normal',
                    'Fish/Oil' => 'Fish/Oil',
                    'Medicine/Documents' => 'Medicine/Documents',
                    'Electronics' => 'Electronics',
                );

                $selected = set_value('category', explode(', ', $y->category));
            $attr = array(
                'class' => 'form-control selectpicker',
                'multiple' => 'multiple',
            );
                echo form_dropdown('category[]', $options, $selected, $attr);
            ?>
        </div>
        <br />

        <div class="form-group">
            <label class="form-control-label">Item (<small>Add each item separated with a comma</small>)</label>
            <p>Listed Items(s): <?php echo $y->item_name; ?></p>
            <input type="text" name="item_name" class="form-control"
                value="<?php echo set_value('item_name', $y->item_name); ?>" required/>
        </div>
        <br />

        <div class="form-group">
            <label class="form-control-label">Total Size</label>
            <?php 
                $options = array(
                    '1' => '1 KG',
                    '1.5' => '1.5 KG',
                    '2' => '2 KG',
                    '2.5' => '2.5 KG',
                    '3' => '3 KG',
                    '3.5' => '3.5 KG',
                    '4' => '4 KG',
                    '4.5' => '4.5 KG',
                    '5' => '5 KG',
                    '5.5' => '5.5 KG',
                    '6' => '6 KG',
                    '6.5' => '6.5 KG',
                    '7' => '7 KG',
                    '7.5' => '7.5 KG',
                    '8' => '8 KG',
                    '8.5' => '8.5 KG',
                    '9' => '9 KG',
                    '9.5' => '9.5 KG',
                    '10' => '10 KG',
                );

                $selected = set_value('size',  $y->size);
            $attr = array(
                'class' => 'form-control',
            );
                echo form_dropdown('size', $options, $selected, $attr);
            ?>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary m-t-5">Update Booking</button>
        </div>

    </div><!--/.col-md-6-->
</div><!--/.row-->

<?php echo form_close(); ?>