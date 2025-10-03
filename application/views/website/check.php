<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--favicon icon-->
    <link rel="icon" href="<?= business_favicon ?>" type="image/png" sizes="16x16">

    <title>Document</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Montserrat', sans-serif;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        .bg-primary {
            background-color: #000;
        }

        .tx-primary {
            color: #000;
        }

        .bg-white {
            background-color: #fff;
        }

        .bg-gray-100 {
            background-color: #f9f9fb;
        }

        .bg-gray-200 {
            background-color: #f0f0f8;
        }

        .bg-gray-300 {
            background-color: #e1e1ef;
        }

        .bg-gray-400 {
            background-color: #d6d6e6;
        }

        .bg-gray-500 {
            background-color: #949eb7;
        }

        .bg-gray-600 {
            background-color: #7987a1;
        }

        .bg-gray-700 {
            background-color: #4d5875;
        }

        .bg-gray-800 {
            background-color: #383853;
        }

        .bg-gray-900 {
            background-color: #323251;
        }

        .tx-gray-100 {
            color: #f9f9fb;
        }

        .tx-gray-200 {
            color: #f0f0f8;
        }

        .tx-gray-300 {
            color: #e1e1ef;
        }

        .tx-gray-400 {
            color: #d6d6e6;
        }

        .tx-gray-500 {
            color: #949eb7;
        }

        .tx-gray-600 {
            color: #7987a1;
        }

        .tx-gray-700 {
            color: #4d5875;
        }

        .tx-gray-800 {
            color: #383853;
        }

        .tx-gray-900 {
            color: #323251;
        }

        .mx-auto {
            margin: 0 auto;
        }

        .d-block {
            display: block;
        }

        .main-logo {
            display: block;
            height: 3.5rem;
            margin: 0 auto;
        }

        .top-cap {
            background-color: white;
            width: 90%;
            height: 2.5rem;
            margin: 0 auto;
            margin-top: 1rem;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .bottom-cap {
            background-color: white;
            width: 90%;
            height: 2.5rem;
            margin: 0 auto;
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }

        .body-section {
            background-color: white;
            width: 90%;
            height: 100%;
            min-height: 22rem;
            margin: 0 auto;
        }

        .body-section>p:last-child {
            margin-bottom: 0 !important;
        }

        h1 {
            font-size: 1.1rem;
            text-align: left;
            text-transform: uppercase;
        }

        .sub {
            font-size: .7rem;
            font-weight: 400;
            background-color: #4d4d4d;
        }

        p,
        ol li,
        ul li {
            text-align: left;
            font-size: .9rem;
            color: #4d4d4d;
            margin-bottom: .6rem;
        }

        ol,
        ul {
            font-size: .85rem;
            list-style-position: inside;
        }

        ol li::marker,
        ul li::marker {
            color: #000;
            font-weight: 600;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .mb-1 {
            margin-bottom: 1rem;
        }

        .foooter-note {
            color: white;
            text-align: center;
            display: block;
            font-size: .8rem;
            font-weight: 300;
            margin-top: 1rem;
        }

        p>a,
        li>a {
            color: #000;
            font-weight: 600;
            text-decoration: underline;
        }

        .link-btn {
            display: inline-block;
            text-decoration: none;
            font-size: .8rem;
            background-color: #000;
            color: white;
            padding: .7rem 1rem;
            border-radius: .3rem;
            margin: .6rem 0 1rem 0;
            font-weight: 600;
        }

        .link-btn.secondary {
            background-color: #9f9f9f92;
            color: #000;
        }

        .transaction-details>p {
            font-size: .8rem;
            margin-bottom: 0;
        }

        .transaction-details>span {
            font-size: .9rem;
            font-weight: 600;
            word-break: break-all;
        }

        .transaction-details {
            margin-bottom: 1rem;
        }

        .transaction-card {
            padding: 1rem;
            border-radius: 1rem;
            background-color: #70707008;
            margin-bottom: 0.3rem;
        }

        .trans {
            display: flex;
            justify-content: space-between;
        }

        .amount {
            font-size: 2rem;
            text-align: center;
            font-weight: 600;
            word-break: break-all;
            margin-bottom: 20px;
        }

        .tx-end {
            text-align: end;
        }
    </style>

</head>

<body>
    <div class="bg-gray-200" style="border-radius: 1.1rem; overflow: hidden; width:100%;max-width: 500px; margin:0 auto;">
        <div class="bg-primary" style="padding-top: 1rem;">
            <img src="<?php echo production_url('assets/general/logo/colored_logo.png'); ?>" alt="" class="main-logo">

            <div class="top-cap"></div>
        </div>
        <div class="body-section mb-0" style="padding: 0 1.2rem;">

            <p class="tx-primary"><b>Hi Joshua,</b></p>

            <p>
                You made a withdrawal request from your <b class="tx-primary"> <?= business_name ?></b> account. Your confirmation is required to process this transaction.
            </p>

            <div class="transaction-card">

                <div class="amount">-$<?= number_format($transfer->amount, 2) ?></div>

                <div class="trans">
                    <div class="transaction-details">
                        <p>Cryptocurrency</p>
                        <span>BTC</span>
                    </div>
                    <div class="transaction-details tx-end">
                        <p>Wallet Address</p>
                        <span>SDFGHJK</span>
                    </div>
                </div>

                <div class="trans">
                    <div class="transaction-details">
                        <p>Withdrawal Type</p>
                        <span>External</span>
                    </div>
                    <div class="transaction-details tx-end">
                        <p>Reference Number</p>
                        <span>WITH-2345678UY</span>
                    </div>
                </div>

                <div class="trans">
                    <div class="transaction-details">
                        <p>Withdrawal Date</p>
                        <span>10/10/2024</span>
                    </div>
                    <div class="transaction-details tx-end">
                        <p>Withdrawal Status</p>
                        <span>Pending</span>
                    </div>
                </div>

                <table style="width:100%;">
                    <tr>
                        <td><a href="#" class="link-btn danger" style="float:left;">Decline</a></td>
                        <td><a href="#" class="link-btn" style="float:right; background: #3F3FD8;">Confirm</a></td>
                    </tr>
                </table>
            </div>

            <p>If you did not initiate this request, please click the "Decline" button. If you wish to proceed with your withdrawal request, click the "Confirm" button to confirm your action.</p>

            <p>If you have any questions or require further assistance, please do not hesitate to contact our Customer Support team at support@arcoassets.com. We are happy to assist you.</p>

            <p>Your account security is a priority for us, and we appreciate your attention to verifying your transactions.</p>

        </div>

        <div class="bg-primary" style="padding-bottom: 2rem;">
            <div class="bottom-cap"></div>
            <span class="foooter-note">&copy; <?= date('Y', time()) ?> <?= business_name ?>.</span>
        </div>


    </div>
</body>

</html>