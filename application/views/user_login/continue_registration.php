<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <!--required meta tags-->
    <meta charset="utf-8">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="">

    <!--favicon icon-->
    <link href="<?php echo business_favicon; ?>" rel="icon" type="image/png" sizes="16x16" />

    <!--title-->
    <title> Continue registration - <?php echo business_name; ?></title>

    <!--build:css-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/main.css">
    <!-- endbuild -->

    <!--custom css start-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/custom.css?v=<?php echo filemtime(FCPATH . 'assets/login/css/custom.css'); ?>">
    <!--custom css end-->

    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/auth-refresh.css?v=<?php echo filemtime(FCPATH . 'assets/login/css/auth-refresh.css'); ?>">

</head>

<body class="smb-auth-page">
    <?php $this->load->view('partials/sandbox_badge', array('sandbox_badge_fixed' => true)); ?>

    <!-- preloader start -->
    <div id="preloader" class="bg-light-subtle">
        <div class="preloader-wrap">
            <div class="loading-bar"></div>
        </div>
    </div>
    <!-- preloader end -->

    <!--main content wrapper start-->
    <div class="main-wrapper">

        <!--register section start-->
        <section class="sign-up-in-section bg-dark ptb-60" style="background: url('assets/login/img/page-header-bg.svg')no-repeat right bottom">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-12">
                        <div class="pricing-content-wrap bg-custom-light rounded-custom shadow-lg">
                            <div class="price-feature-col pricing-feature-info testimonial-box text-white left-radius p-5 order-1 order-lg-0 " style="background-image:url('<?php echo base_url(); ?>assets/login/img/bg/login2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                                <div class="mask-overlay"></div>
                                <a href="<?php echo base_url(); ?>" class="mb-5 d-none d-xl-block d-lg-block"><img src="<?= business_logo_white ?>" alt="logo" class="img-fluid" width="150"></a>
                                <div class="customer-testimonial-wrap mt-150">
                                    <div class="tab-content" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="testimonial-tab-1" role="tabpanel">
                                            <div class="testimonial-tab-content mb-4">
                                                <div class="mb-2">
                                                    <ul class="review-rate mb-0 mt-2 list-unstyled list-inline">
                                                        <li class="list-inline-item"><i class="fas fa-star text-warning"></i></li>
                                                        <li class="list-inline-item"><i class="fas fa-star text-warning"></i></li>
                                                        <li class="list-inline-item"><i class="fas fa-star text-warning"></i></li>
                                                        <li class="list-inline-item"><i class="fas fa-star text-warning"></i></li>
                                                        <li class="list-inline-item"><i class="fas fa-star text-warning"></i></li>
                                                    </ul>
                                                </div>
                                                <blockquote>
                                                    Great alternative to traditional courier services. The process is fast, reliable, and affordable. It's a sure plug!
                                                </blockquote>
                                                <div class="author-info mt-4">
                                                    <h6 class="mb-0 text-white">Tolu A.</h6>
                                                    <span>London</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="price-feature-col pricing-action-info p-5 right-radius bg-light-subtle order-0 order-lg-1">
                                <a href="<?php echo base_url(); ?>" class="mb-5 d-block d-xl-none d-lg-none"><img src="<?= business_logo ?>" alt="logo" width="150" class="img-fluid"></a>
                                <div class="mb-3" style="color:#f36b24" aria-hidden="true"><i class="fas fa-envelope-open-text fa-2x"></i></div>
                                <h1 class="h3"><?= $resume_token ? 'Welcome back' : 'Continue registration' ?></h1>
                                <p class="text-muted"><?= $resume_token ? 'Your details are saved. Get a new verification code to finish your registration.' : ($expired_link ? 'This link has expired or is no longer available. Enter your email to continue, or sign in if you have already finished.' : 'Enter the email you used to start registration.') ?></p>
                                <div class="mt-4 register-form">
                                    <?= form_open($resume_token ? 'registration/resend_verification_email_ajax' : 'registration/start_ajax', ['id'=>'registration_recovery_form']) ?>
                                    <?php if ($resume_token): ?>
                                        <input type="hidden" name="resume_token" value="<?= html_escape($resume_token) ?>">
                                        <p>We will send the code to <strong><?= html_escape($email_hint) ?></strong>.</p>
                                    <?php else: ?>
                                        <label for="recovery_email" class="mb-1">Email <span class="text-danger">*</span></label>
                                        <div class="input-group mb-3"><input type="email" class="form-control" name="email" id="recovery_email" required autocomplete="email"></div>
                                    <?php endif; ?>
                                    <div id="status_msg" role="status" aria-live="polite"></div>
                                    <button type="submit" id="submit" class="main-btn primary">
                                        <?= $resume_token ? 'Get verification code' : 'Continue' ?>
                                        <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>
                                    </button>
                                    <?= form_close() ?>
                                    <p class="text-center mt-4 copyright"><a href="<?= base_url('signin') ?>" class="text-decoration-none">Back to sign in</a></p>
                                </div>

                                <p class="text-center text-muted mt-5 mb-3 copyright">Copyright &copy; 2025 <a href="<?php echo base_url(); ?>"><?php echo business_name; ?></a>.<br> All Rights Reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--register section end-->

    </div>
    <!--main content wrapper end-->


    <!--build:js-->
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/jquery-3.6.0.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/swiper-bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/jquery.magnific-popup.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/parallax.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/aos.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/massonry.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/app.js"></script>
    <!--endbuild-->

    <!-- custom scripts -->
    <script src="<?php echo base_url(); ?>assets/general/js/my_functions.js?v=<?php echo filemtime(FCPATH . 'assets/general/js/my_functions.js'); ?>"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/login.js?v=<?php echo filemtime(FCPATH . 'assets/login/js/login.js'); ?>"></script>
    <script src="<?php echo base_url(); ?>assets/website/js/home.js?v=<?php echo filemtime(FCPATH . 'assets/website/js/home.js'); ?>"></script>


    <!-- pass base_url to js -->
    <script type="text/javascript">
        var base_url = "<?php echo base_url(); ?>";
    </script>
    <?php $this->load->view('partials/csrf_bootstrap'); ?>
</body>

</html>
