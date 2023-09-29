<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="">
    <meta name="keyword" content="<?php echo business_keywords; ?>">
    <title>
        <?php echo business_name; ?> UK -
        Coming Soon
    </title>
    <base href="<?= base_url() ?>">

    <!-- Favicons-->
    <link rel="shortcut icon" href="assets/user/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" type="image/x-icon" href="assets/user//img/apple-touch-icon-57x57-precomposed.png">
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

    <script type="text/javascript">
        function delayedRedirect() {
            window.location = "index.html"
        }
    </script>
</head>

<body>

    <main>
        <div class="header-video"
            style="background-image: url('assets/user/img/map.jpeg'); background-color: rgba(0, 0, 0, 0.6);">
            <div id="hero_video">
                <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.6)">
                    <div class="container">
                        <div class="row justify-content-center text-center">
                            <div class="col-xl-7 col-lg-8 col-md-8 home">
                                <img src="assets/user/img/logo/png/white_logo.png" width="162" height="75" alt="" class="logo_normal">
                                <h1>Coming Soon</h1>
                            </div>
                            <div class="row ikon">
                                <div class="col-lg-6 icons">
                                    <i class="fa-brands fa-whatsapp fa-3x" style="color: #ffffff;"></i>
                                    <br>
                                    <p><a href="https://wa.me/+2348149265396"> <?= business_phone_number; ?></a></p>
                                </div>
                                <div class="col-lg-6 icons">
                                    <i class="fa-solid fa-envelope fa-3x" style="color: #ffffff;"></i>
                                    <br>
                                    <p><a href="mailto:<?= business_contact_email; ?>"> <?= business_contact_email; ?></a></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- <img src="assets/img/video_fix.png" alt="" class="header-video--media" data-video-src="video/intro" data-teaser-source="video/intro" data-provider="" data-video-width="1920" data-video-height="960"> -->
            </div>
            <!-- /header-video -->
        </div>

    </main>
    <!-- /main -->
    <!-- JQuery -->
    <script src="<?php echo base_url('assets/user/js/jquery-3.3.1.min.js'); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

    <!-- COMMON SCRIPTS -->
    <script src="assets/user/js/common_scripts.min.js"></script>
    <script src="assets/user/js/common_func.js"></script>
    <script src="assets/user/js/validate.js"></script>
    <script type="text/javascript">
        jQuery.event.special.touchstart = {
            setup: function (_, ns, handle) {
                this.addEventListener("touchstart", handle, {
                    passive: !ns.includes("noPreventDefault")
                });
            }
        };
    </script>

    <!-- SPECIFIC SCRIPTS -->
    <script src="assets/user/js/my_functions.js"></script>
    <script src="assets/user/js/wizard/wizard_scripts.js"></script>
    <script src="assets/user/js/wizard/wizard_func.js"></script>
    <script src="assets/user/js/sticky_sidebar.min.js"></script>
    <script src="assets/user/js/sticky-kit.min.js"></script>
    <script src="assets/user/js/specific_detail.js"></script>
    <script src="assets/user/js/vegas.min.js"></script>

    <!-- Bootstrap -->
    <script src="assets/user/libraries/datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="assets/user/libraries/datepicker/dist/locales/bootstrap-datepicker.de.min.js"></script>

    <!-- Video header -->
    <script src="assets/user/js/modernizr.min.js"></script>

    <script type="text/javascript">
        //pass base_url to js
        var base_url = "<?php echo base_url(); ?>";
    </script>

</body>

</html>