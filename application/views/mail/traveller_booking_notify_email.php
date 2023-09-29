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

        <div class="order-details">
            <p>Hello <?= $traveller_name ?>, </p>

            <p><?= $receiver_name ?> just bought a space in your bag.</p>

            <!-- <p>You currently have <?= $traveller_available_space ?>KG space in your bag.</p> -->

            <p>Here is a summary of items in your bag:</p>

            <p>
                <?php
                // Parse the JSON data
                $item_details = json_decode($items);
                ?>

                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Iterate over each item
                        foreach ($item_details as $item) {
                            $item_name = $item->item_name;
                            $size = $item->size;

                            // Display the item details
                            ?>
                            <tr>
                                <td><?= $item_name ?></td>
                                <td><?= $size ?>KG</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <br>

                <b>Your Payout is:</b> <?= $currency_symbol ?><?= number_format($selected_amount, 2) ?>
            </p>

        </div>

        <div class="footer">
            <img src="cid:logo" alt="ShareMyBag" class="logo"><br>
            &copy; 2023 ShareMyBag. All rights reserved.
        </div>
    </div>
</body>

</html>