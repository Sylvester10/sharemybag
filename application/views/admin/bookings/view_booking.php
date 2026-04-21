<div class="new-item admin-page-actions">
    <a class="btn btn-default btn-sm button-adjust" href="<?php echo base_url('admin_bookings'); ?>"><i
            class="las la-book"></i>
        All Bookings</a>
</div>


<table id="table" class="table table-bordered table-hover cell-text-middle admin-data-table">

    <thead>
        <tr>
            <th> Actions </th>
            <th class=""> Heading </th>
            <th class=""> Body </th>
            <th class=""> Delivery Status </th>
            <th class=""> Date </th>
        </tr>
    </thead>
    <tbody>

        <?php
        foreach ($shipping_details as $y) { ?>

            <tr>
                <?php require "application/views/admin/bookings/modal/edit.php"; ?>
                <?php require "application/views/admin/bookings/modal/delete.php"; ?>
                <td class="w-15-p text-center">
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit<?php echo $y->id; ?>">
                        <i class="las la-pen"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete<?php echo $y->id; ?>">
                        <i class="las la-trash"></i> Delete
                    </button>
                </td>
                <td> <?php echo $y->heading; ?> </td>
                <td> <?php echo $y->body; ?> </td>
                <td> <?php echo delivery_status_badge($y->delivery_status); ?> </td>
                <td> <?php echo $y->date_added; ?> </td>
            </tr>

        <?php } ?>

    </tbody>
</table>
