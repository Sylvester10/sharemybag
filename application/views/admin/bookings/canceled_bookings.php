<?php
//select options bulk actions 
$options_array = array(
    //'value' => 'Caption'
    'confirm' => 'Confirm',
    'cancel' => 'Cancel',
    'delete' => 'Delete'
);
echo modal_bulk_actions('admin_bookings/bulk_actions_booking', $options_array); ?>

<div class="table-scroll">
    <table id="canceled_bookings_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

        <input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

        <thead>
            <tr>
                <th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
                <th> Actions </th>
                <th class="min-w-200"> Date </th>
                <th class="min-w-300"> Traveller Details</th>
                <th class="min-w-150"> Traveller Commission</th>
                <th class="min-w-300"> SMB User Details </th>
                <th class="min-w-300"> Agent Details </th>
                <th class="min-w-300"> Receiver Details </th>
                <th class=""> Need Help with Parcel? </th>
                <th class="min-w-300"> Item Details</th>
                <th class="min-w-200"> Payment Details</th>
                <!-- <th class=""> Tracking Number </th> -->
                <th class=""> Payment Status </th>
                <!-- <th class=""> Delivery Status </th> -->
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<?php echo form_close(); ?>