<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="">
    <meta name="keyword" content="<?php echo business_keywords; ?>">
    <title>Booking Success</title>
    <base href="<?= base_url() ?>">

    <!-- Favicons-->
    <link rel="shortcut icon" href="assets/user/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" type="image/x-icon" href="assets/user/img/apple-touch-icon-57x57-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72"
        href="assets/user/img/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114"
        href="assets/user/img/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144"
        href="assets/user/img/apple-touch-icon-144x144-precomposed.png">

    <!-- GOOGLE WEB FONT -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap"
        as="fetch" crossorigin="anonymous">
    <script type="text/javascript">
        !function (e, n, t) { "use strict"; var o = "https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap", r = "__3perf_googleFonts_c2536"; function c(e) { (n.head || n.body).appendChild(e) } function a() { var e = n.createElement("link"); e.href = o, e.rel = "stylesheet", c(e) } function f(e) { if (!n.getElementById(r)) { var t = n.createElement("style"); t.id = r, c(t) } n.getElementById(r).innerHTML = e } e.FontFace && e.FontFace.prototype.hasOwnProperty("display") ? (t[r] && f(t[r]), fetch(o).then(function (e) { return e.text() }).then(function (e) { return e.replace(/@font-face {/g, "@font-face{font-display:swap;") }).then(function (e) { return t[r] = e }).then(f).catch(a)) : a() }(window, document, localStorage);
    </script>

    <!-- BASE CSS -->
    <link href="assets/user/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/user/css/style.css" rel="stylesheet">

    <!-- SPECIFIC CSS -->
    <link href="assets/user/css/wizard.css" rel="stylesheet">

    <!-- YOUR CUSTOM CSS -->
    <link href="assets/user/css/custom.css" rel="stylesheet">

    <script type="text/javascript">
        function delayedRedirect() {
            window.location = "<?php echo base_url(); ?>"
        }
    </script>

</head>

<body class="body" onLoad="setTimeout('delayedRedirect()',100000)" style="background-color:#fff;">


    <div id="success">
        <div class="icon icon--order-success svg">
            <svg xmlns="http://www.w3.org/2000/svg" width="72px" height="72px">
                <g fill="none" stroke="#8EC343" stroke-width="2">
                    <circle cx="36" cy="36" r="35" style="stroke-dasharray:240px, 240px; stroke-dashoffset: 480px;">
                    </circle>
                    <path d="M17.417,37.778l9.93,9.909l25.444-25.393"
                        style="stroke-dasharray:50px, 50px; stroke-dashoffset: 0px;"></path>
                </g>
            </svg>
        </div>
        <h4><b>Booking successful!</b></h4><br>
        <p><b>We are verifying your payments and ID provided. One of our agents will contact you shortly.</b></p>
        <p><b>Thank you for choosing ShareMyBag.</b></p>
        <a href="<?php echo base_url(); ?>"> <button
                class="align-items-center btn_1 d-flex gradient home_button justify-content-center">Home</button> </a>
    </div>
</body>

</html>