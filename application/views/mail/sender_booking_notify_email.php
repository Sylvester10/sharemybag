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

        .heading {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
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
    <div class="container">
        <h1 class="heading">Thank you for your order</h1>

        <div class="order-details">
            <p>
                You have made a payment of
                <b><?= $currency_symbol ?><?= number_format($total_amount, 2) ?></b> to ShareMyBag.<br>
                <b>Tracking ID:</b> <?= $tracking_id ?><br>
                <b>Date & time:</b> <?= $date_added ?>
            </p>

            <p>
                <b>Item Details</b><br>
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
                            <td><?= $category ?></td>
                            <td><?= $item_name ?></td>
                            <td><?= $size ?>KG</td>
                            <td><?= $currency_symbol ?><?= number_format($price, 2) ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <br>

            <b>Insurance:</b> <?= $currency_symbol ?><?= number_format($insurance, 2) ?>
            </p>

            <p>
                <b>Traveller Details</b><br>
                <b>Name:</b> <?= $traveller_name ?><br>
                <b>Contact number:</b> <?= $traveller_contact ?><br>
                <b>Departure State:</b> <?= $traveller_departure_state ?><br>
                <b>1st Drop off Address:</b> <?= $traveller_drop_address1 ?><br>
                <b>1st Drop off Date:</b> <?= $traveller_drop_date1 ?><br>
                <b>2nd Drop off Address:</b> <?= ($traveller_drop_address2 == '') ? 'N/A' : $traveller_drop_address2 ?><br>
                <b>2nd Drop off Date:</b> <?= ($traveller_drop_date2 == '') ? 'N/A' : $traveller_drop_date2 ?><br>
                <b>Departure Date :</b> <?= $traveller_departure_date ?><br>
                <b>Arrival Date:</b> <?= $traveller_arrival_date ?>
            </p>

            <p>
                Please drop your items off with the traveler by your regions last drop-off date. There will be no refund
                or transfer of service to another traveler.
            </p>
        </div>

        <div class="footer">
            <img src="cid:logo" alt="ShareMyBag" class="logo"><br>
            &copy; 2023 ShareMyBag. All rights reserved.
        </div>
    </div>
</body>

</html>