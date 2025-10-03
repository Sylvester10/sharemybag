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


        <div class="order-details" style="position: center;">
            <p>Hi <?= $firstname ?>, </p>

            <p> Congratulations! Your identity verification was successful.</p>

            <p> Now that your account has been verified, you can find travellers, buy space and have your parcels delivered. </p>

            <p> Welcome to <?= business ?>. </p>

        </div>

        <p class="mb-0 mt-2 ">Best regards,</p>
        <p><strong><?= business ?> Team</strong></p>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> <?= business ?>. All rights reserved.
        </div>
    </div>
</body>

</html>