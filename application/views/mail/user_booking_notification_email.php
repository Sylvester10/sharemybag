<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .header-logo {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
            text-align: center;
            background-color: #f5f5f5;
        }

        .heading {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .fs-4 {
            font-size: 14px;
            font-weight: bold;
        }

        .mt-2 {
            margin-top: 25px;
        }

        .mb-0 {
            margin-bottom: -15px;
        }

        .mb-2 {
            margin-bottom: 20px;
        }

        .mb-3 {
            margin-bottom: 30px;
        }

        .order-details {
            margin-bottom: 20px;
        }

        .order-details b {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
        }

        .logo {
            display: inline-block;
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .item-table {
            border-collapse: collapse;
            width: 100%;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .item-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="header-logo">
        <!--Logo-->
        <div id="icon" style="position: center;">
            <img src="<?php echo production_url('assets/general/logo/colored_logo.png'); ?>" style="width:100px;">
        </div>
    </div>

    <div class="container">

        <h1 class="heading">Thank you for your order!</h1>

        <div class="order-details">
            <p> You have made a payment of <b class="fs-4">&pound;<?= number_format($total_amount, 2) ?></b> to <?= business ?>.</p>
            <!--<p> <b>Tracking ID:</b> <?= $tracking_id ?> </p>-->
        </div>

        <hr>

        <div class="order-details">

            <h4 class="mb-2">ITEM DETAILS</h4>

            <div>

                <?php
                // Parse the JSON data
                $item_details = json_decode($items);
                ?>

                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Item Name</th>
                            <th>Size</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Iterate over each item
                        foreach ($item_details as $item) {
                            $category = $item->category;
                            $item_name = $item->item_name;
                            $size = $item->size;
                            $price = $item->price;

                            // Display the item details
                        ?>
                            <tr>
                                <td> <?= $category ?> </td>
                                <td> <?= $item_name ?> </td>
                                <td> <?= $size ?>KG </td>
                                <td> &pound;<?= number_format($price, 2) ?> </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>

                <br>
                <b>Insurance:</b> &pound;<?= number_format($insurance, 2) ?>
            </div>

        </div>

        <hr>

        <div class="order-details">

            <h4 class="mb-2 mt-2">Traveller DETAILS</h4>

            <p class="mb-0"> <b>Traveller Name:</b>
                <?php
                $parts = explode(' ', $traveller_name);
                $firstName = $parts[0];
                $lastNameInitial = isset($parts[count($parts) - 1][0]) ? strtoupper($parts[count($parts) - 1][0]) . '.' : '';
                echo $firstName . ' ' . $lastNameInitial;
                ?>
            </p>
            <p class="mb-0"> <b>Contact number:</b> <?= business_phone_number ?> </p>
            <p class="mb-0"> <b>Departure Airport:</b> <?= $traveller_departure_state ?> </p>
            <p class="mb-0"> <b>Arrival Airport:</b> <?= $traveller_arrival_airport ?> </p>
            <p class="mb-0"> <b>Departure Date :</b> <?= $traveller_departure_date ?> </p>
            <p class="mb-0"> <b>Arrival Date:</b> <?= $traveller_arrival_date ?> </p>
            <p class="mb-0"> <b>Current Location:</b> <?= $traveller_current_state ?> </p>
            <p class="mb-3"> <b>Final Destination:</b> <?= $traveller_arrival_state ?> </p>
            <!--<p class="mb-0"> <b>1st Drop off Address:</b> <?= $traveller_drop_address1 ?> </p>-->
            <!--<p class="mb-0"> <b>1st Drop off Date:</b> <?= $traveller_drop_date1 ?> </p>-->
            <!--<p class="mb-0"> <b>Last Drop off Address:</b> <?= $traveller_drop_address2 ?> </p>-->
            <!--<p class="mb-3"> <b>Last Drop off Date:</b> <?= $traveller_drop_date2 ?> </p>-->
        </div>

        <p>
            The first drop-off will be at <b><?= $traveller_drop_address1 ?></b> on <b><?= $traveller_drop_date1 ?></b>, and the last drop-off will be at <b><?= $traveller_drop_address2 ?></b> on <b><?= $traveller_drop_date2 ?></b>.
        </p>

        <p>
            Please drop your items off with the traveller by your regions last drop-off date. There will be no refund
            or transfer of service to another traveller.
        </p>

        <p>Please inform your packer that illegal drugs are prohibited.</p>

        <p>For a full list of prohibited items, Checkout our <a href="<?php echo base_url(); ?>#faqss">FAQ</a> section.</p>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> <?= business ?>. All rights reserved.
        </div>
    </div>
</body>

</html>