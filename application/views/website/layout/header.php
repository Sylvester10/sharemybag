<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />

    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="ShareMyBag">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= current_url(); ?>">

    <title><?php echo $title; ?> - <?php echo sub_tagline; ?></title>

    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:description" content="<?php echo business_description; ?>" />
    <meta property="og:image" content="<?php echo base_url('assets/website/img/home.png'); ?>" />
    <meta property="og:url" content="<?php echo current_url(); ?>" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="<?php echo $title; ?>" />
    <meta name="twitter:description" content="<?php echo business_description; ?>" />
    <meta name="twitter:image" content="<?php echo base_url('assets/website/img/home.png'); ?>" />
    <meta name="twitter:url" content="<?php echo current_url(); ?>" />

    <meta name="mswebdialog-title" content="<?php echo $title; ?>" />
    <meta name="mswebdialog-logo" content="<?php echo business_logo; ?>" />
    <meta name="mswebdialog-header-color" content="#FFF" />
    <meta name="mswebdialog-newwindowurl" content="*" />


    <!--Favicon-->
    <link rel="icon" href="<?php echo business_favicon; ?>" type="image/png" />
    <!-- Bootstrap CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Line Awesome CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/line-awesome.min.css" rel="stylesheet" />
    <!-- Animate CSS-->
    <link href="<?php echo base_url(); ?>assets/website/css/animate.css" rel="stylesheet" />
    <!-- Bar Filler CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/barfiller.css" rel="stylesheet" />
    <!-- Magnific Popup Video -->
    <link href="<?php echo base_url(); ?>assets/website/css/magnific-popup.css" rel="stylesheet" />
    <!-- Flaticon CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/flaticon.css" rel="stylesheet" />
    <!-- Owl Carousel CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/owl.carousel.css" rel="stylesheet" />
    <!-- Slick CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/slick.css" rel="stylesheet" />
    <!-- Nice Select CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/nice-select.css" rel="stylesheet" />
    <!-- Style CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/style.css" rel="stylesheet" />
    <!-- Responsive CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/responsive.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="<?php echo base_url(); ?>assets/general/fontawesome/css/all.min.css" rel="stylesheet" />
    <!-- Date Picker -->
    <link href="<?php echo base_url(); ?>assets/website/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" />


    <!-- country flags -->
    <link href="<?php echo base_url(); ?>assets/general/countryflags/dist/flat.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/country-flags-css@1.1.2/dist/flat.min.css" rel="stylesheet">

    <!-- Custom css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/website/css/custom.css" />

    <!-- Tailwind -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/general/css/tw-output.css" />
</head>

<body>
    <!-- Pre-Loader -->
    <!-- <div class="preloader"></div> -->

    <!-- preloader start -->
    <div id="preloader" class="bg-light-subtle">
        <div class="preloader-wrap">
            <div class="loading-bar"></div>
        </div>
    </div>
    <!-- preloader end -->

    <!--Header Top Area -->
    <div class="header-top heaader-top-two">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-xs-12">
                    <marquee width="100%" direction="left">
                        <p class="text-4 text-white">
                            <i class="las la-bullhorn tw-mr-2"></i>
                            For best experience, please use Chrome browser.
                        </p>
                    </marquee>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Area -->
    <div class="header-area absolute-header">
        <div class="sticky-area">
            <div class="navigation">
                <div class="container-fluid">
                    <div class="header-inner-box">
                        <div class="logo">
                            <a class="navbar-Solar" href="<?php echo base_url(); ?>"><img src="<?php echo business_logo_white; ?>" width="101" height="63" alt="Sharemybag"></a>
                        </div>

                        <div class="main-menu">
                            <nav class="navbar navbar-expand-lg">
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                    <span class="navbar-toggler-icon"></span>
                                    <span class="navbar-toggler-icon"></span>
                                </button>

                                <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
                                    <ul class="navbar-nav m-auto">
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo base_url('travellers'); ?>">I am a Traveller</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo base_url('investors'); ?>">Investors</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo base_url('signin'); ?>" class="login-btn primary">Login</a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="search-popup" id="search-results">
        <span class="search-back-drop"></span>
        <section id="section-1" class="bg-white rounded shadow-md d-none">

            <div class="prohibited_items bg-white rounded shadow-md mt-3 p-4">
                <div class="prohibited-box">
                    <div class="prohibited_icon">
                        <img src="<?php echo base_url(); ?>assets/website/icons/Calendar.png" alt="">
                        <h4>Date</h4>
                        <p>${response.travel_date}</p>
                    </div>
                    <div class="prohibited_icon">
                        <img src="<?php echo base_url(); ?>assets/website/icons/location.png" alt="">
                        <h4>Current Location</h4>
                        <p>${response.current_state}</p>
                    </div>
                    <div class="prohibited_icon">
                        <img src="<?php echo base_url(); ?>assets/website/icons/destination.png" alt="">
                        <h4>Final Destination</h4>
                        <p>${response.arrival_state}</p>
                    </div>
                    <div class="prohibited_icon">
                        <img src="<?php echo base_url(); ?>assets/website/icons/weight.png" alt="">
                        <h4>Available space</h4>
                        <p>${response.available_space} kg</p>
                    </div>
                </div>

                <h6> <a href="${base_url}registration" class="main-btn primary" type="submit">Sign up to see all available travellers</a></h6>

            </div>

        </section>
    </div>