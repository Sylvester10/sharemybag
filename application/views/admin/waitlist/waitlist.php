<?php
//select options bulk actions 
$options_array = array(
    //'value' => 'Caption'
    'delete' => 'Delete'
);
echo modal_bulk_actions('waitlist/bulk_actions_waitlist', $options_array); ?>

<div class="table-scroll">
    <table id="waitlist_table" class="table table-bordered table-hover cell-text-middle" style="text-align: left">

        <input type="hidden" id="csrf_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />

        <thead>
            <tr>
                <th class="w-15-p"> <input type="checkbox" class="radio-box select_all" /> </th>
                <th> Actions </th>
                <th class=""> Name </th>
                <th class=""> Phone </th>
                <th class=""> Email </th>
                <th class=""> Date Added</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<?php echo form_close(); ?>