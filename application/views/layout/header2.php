<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="">
    <meta name="keyword" content="<?php echo business_keywords; ?>">
    <title><?php echo business_name; ?> - <?php echo $title; ?></title>
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
        ! function (e, n, t) {
            "use strict";
            var o = "https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap",
                r = "__3perf_googleFonts_c2536";

            function c(e) {
                (n.head || n.body).appendChild(e)
            }

            function a() {
                var e = n.createElement("link");
                e.href = o, e.rel = "stylesheet", c(e)
            }

            function f(e) {
                if (!n.getElementById(r)) {
                    var t = n.createElement("style");
                    t.id = r, c(t)
                }
                n.getElementById(r).innerHTML = e
            }
            e.FontFace && e.FontFace.prototype.hasOwnProperty("display") ? (t[r] && f(t[r]), fetch(o).then(function (e) {
                return e.text()
            }).then(function (e) {
                return e.replace(/@font-face {/g, "@font-face{font-display:swap;")
            }).then(function (e) {
                return t[r] = e
            }).then(f).catch(a)) : a()
        }(window, document, localStorage);
    </script>

    <!-- Font Awesome -->
    <link href="assets/user/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="assets/user/fontawesome/css/brands.css" rel="stylesheet">
    <link href="assets/user/fontawesome/css/solid.css" rel="stylesheet">

    <!-- BASE CSS -->
    <link href="assets/user/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/user/css/style.css" rel="stylesheet">

    <!-- SPECIFIC CSS -->
    <link href="assets/user/css/order-sign_up.css" rel="stylesheet">
    <link href="assets/user/css/detail-page.css" rel="stylesheet">
    <link href="assets/user/css/error.css" rel="stylesheet">
    <link href="assets/user/css/help.css" rel="stylesheet">
    <link href="assets/user/css/home.css" rel="stylesheet">
    <link href="assets/user/css/listing.css" rel="stylesheet">
    <link href="assets/user/css/modal-popup.css" rel="stylesheet">
    <link href="assets/user/css/order-sign_up.css" rel="stylesheet">
    <link href="assets/user/css/review.css" rel="stylesheet">
    <link href="assets/user/css/submit.css" rel="stylesheet">
    <link href="assets/user/css/wizard.css" rel="stylesheet">
    <link href="assets/user/css/vegas.min.css" rel="stylesheet">

    <!-- bootstrap -->
    <link rel="stylesheet" href="assets/user/libraries/datepicker/dist/css/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="assets/user/libraries/datepicker/dist/css/bootstrap-datepicker3.min.css" />

    <!-- YOUR CUSTOM CSS -->
    <link href="assets/user/css/custom.css" rel="stylesheet">
</head>

<body>

    <header class="header clearfix element_to_stick">
        <div class="container">
            <div id="logo">
                <a href="<?php echo base_url(); ?>">
                    <img src="assets/user/img/logo/png/white_logo.png" width="162" height="75" alt="" class="logo_normal">
                    <img src="assets/user/img/logo/png/colored_logo.png" width="162" height="75" alt="" class="logo_sticky">
                </a>
            </div>
            <div class="layer"></div>
            <!-- /top_menu -->
            <a class="open_close">
                <i class="fa-solid fa-bars"></i><span>Menu</span>
            </a>
            <nav class="main-menu">
                <div id="header_menu">
                    <a class="open_close">
                        <i class="fa-solid fa-circle-xmark"></i><span>Menu</span>
                    </a>
                    <a href="<?php echo base_url(); ?>"><img src="assets/user/img/logo/png/white_logo.png" width="162"
                            height="75" alt=""></a>
                </div>
                <ul>
                    <li><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li><a href="<?php echo base_url('track'); ?>">Track your parcel</a></li>
                    <li><a href="<?php echo base_url('traveller'); ?>">I am a Traveler</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <!-- /header -->