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
            margin-bottom: -10px;
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
            <img src="cid:logo" style="width:100px;">
        </div>
    </div>

    <div class="container">

        <h1 class="heading">Thank you for your order!</h1>

        <div class="order-details">
            <p> You have made a payment of <b class="fs-4">&pound;300</b> to ShareMyBag.</p>
            <p> <b>Tracking ID:</b> SDFGHJK</p>
        </div>

        <div class="order-details">

            <h4 class="mt-2">ITEM DETAILS</h4>
            <div>

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
                        <tr>
                            <td> SDFGHJK </td>
                            <td> SDFGHJK </td>
                            <td> 20KG </td>
                            <td> &pound;300 </td>
                        </tr>
                    </tbody>
                </table>

                <br>
                <b>Insurance:</b> &pound;200
            </div>

        </div>

        <h4 class="mb-2">Traveller DETAILS</h4>
        <div class="order-details">
            <p class="mb-0"> <b>Name:</b>SDFGHJK</p>
            <p class="mb-0"> <b>Contact number:</b> SDFGHJK</p>
            <p class="mb-0"> <b>Departure State:</b> SDFGHJK</p>
            <p class="mb-0"> <b>1st Drop off Address:</b> SDFGHJK</p>
            <p class="mb-0"> <b>1st Drop off Date:</b> SDFGHJK</p>
            <p class="mb-0"> <b>2nd Drop off Address:</b> SDFGHJK</p>
            <p class="mb-0"> <b>2nd Drop off Date:</b> SDFGHJK</p>
            <p class="mb-0"> <b>Departure Date :</b> SDFGHJK</p>
            <p class="mb-3"> <b>Arrival Date:</b> SDFGHJK </p>

            <p>
                Please drop your items off with the traveller by your regions last drop-off date. There will be no refund
                or transfer of service to another traveller.
            </p>
        </div>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> <?= business_name ?>. All rights reserved.
        </div>
    </div>
</body>

</html>