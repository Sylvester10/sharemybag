<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="ShareMyBag">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?= current_url(); ?>">

    <title>Sandbox | <?php echo $title; ?> - <?php echo sub_tagline; ?></title>

    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:description" content="<?php echo business_description; ?>" />
    <meta property="og:image" content="<?php echo base_url('assets/website/img/home.jpg'); ?>" />
    <meta property="og:url" content="<?php echo current_url(); ?>" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="<?php echo $title; ?>" />
    <meta name="twitter:description" content="<?php echo business_description; ?>" />
    <meta name="twitter:image" content="<?php echo base_url('assets/website/img/home.jpg'); ?>" />
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
    <link href="<?php echo base_url(); ?>assets/general/css/phone-input.css?v=<?php echo filemtime(FCPATH . 'assets/general/css/phone-input.css'); ?>" rel="stylesheet">

    <!-- Custom css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/website/css/custom.css" />

    <!-- Tailwind -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/general/css/tw-output.css" />
    <link rel="stylesheet" href="<?php echo base_url('assets/website/css/landing-refresh.css'); ?>?v=<?php echo filemtime(FCPATH . 'assets/website/css/landing-refresh.css'); ?>" />

    <!-- schema -->
    <?php if (isset($schema)): ?>
        <script type="application/ld+json">
            <?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
        </script>
    <?php endif; ?>
</head>

<body>
    <style>
        .sandbox-badge--public {
            position: fixed;
            left: 16px;
            bottom: 16px;
            z-index: 1040;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border: 1px solid rgba(243, 107, 36, .45);
            border-radius: 999px;
            background: #fff0e6;
            color: #8a3900;
            font: 700 12px/1.2 'Figtree', sans-serif;
            letter-spacing: .08em;
            text-transform: uppercase;
            pointer-events: none;
        }
        .sandbox-badge--public i { font-size: 14px; }
    </style>
    <span class="sandbox-badge sandbox-badge--public"><i class="las la-flask" aria-hidden="true"></i> Sandbox</span>

    <!-- Pre-Loader -->
    <!-- <div class="preloader"></div> -->

    <!-- preloader start -->
    <div id="preloader" class="bg-light-subtle">
        <div class="preloader-wrap">
            <div class="loading-bar"></div>
        </div>
    </div>
    <!-- preloader end -->

    <?php if (!empty($policy_page) || !empty($traveller_page)): ?>
    <header class="smb-policy-header">
        <div class="container">
            <a class="smb-policy-logo" href="<?= base_url(); ?>"><img src="<?= business_logo; ?>" width="101" height="63" alt="ShareMyBag home"></a>
            <nav aria-label="Main navigation">
                <a class="smb-policy-traveller" href="<?= base_url('travellers'); ?>">I'm a Traveller</a>
                <a class="smb-policy-login" href="<?= base_url('signin'); ?>">Login <i class="las la-sign-in-alt" aria-hidden="true"></i></a>
            </nav>
        </div>
    </header>
    <?php else: ?>
    <!-- Header Area -->
    <?php if (empty($traveller_page) && ($this->router->fetch_class() !== 'home' || !in_array($this->router->fetch_method(), ['index', 'success'], true))): ?>
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
                                            <a class="nav-link" href="<?php echo base_url('travellers'); ?>">I'm a Traveller</a>
                                        </li>
                                        <!-- <li class="nav-item">
                                            <a class="nav-link" href="<?php echo base_url('investors'); ?>">Investors</a>
                                        </li> -->
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

    <?php endif; ?>

    <?php endif; ?>

    <!-- Traveller search shares the estimate dialog shell. -->
    <div class="modal fade smb-estimate-modal" id="search-results" tabindex="-1" aria-labelledby="smb-traveller-results-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="smb-traveller-results-title">Search Results</h5>
                    <button type="button" class="smb-estimate-close" data-bs-dismiss="modal" aria-label="Close traveller results"><i class="las la-times" aria-hidden="true"></i></button>
                </div>
                <div class="modal-body" id="smb-traveller-results-body"></div>
            </div>
        </div>
    </div>
