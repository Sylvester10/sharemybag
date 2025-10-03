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


        <div class="order-details">
            <p>Hi <?= $firstname ?>, </p>

            <p>Thank you for submitting your <?= $id_type ?>.</p>

            <p>Your verification process is well underway! We're grateful for your patience as our team reviews your details. Rest
                assured, we'll notify you as soon as your account is fully approved.
            </p>

            <p>If you have any questions or need further assistance, don't hesitate to reach out. We are here to help.</p>

            <p>Thank you for choosing <?= business ?>.</p>

            <p class="mb-0">Best regards,</p>
            <p><strong><?= business ?> Team</strong></p>



        </div>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> <?= business_name ?>. All rights reserved.
        </div>
    </div>
</body>

</html>