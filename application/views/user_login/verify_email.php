<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <!--required meta tags-->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="">

    <!--favicon icon-->
    <link href="<?php echo business_favicon; ?>" rel="icon" type="image/png" sizes="16x16" />

    <!--title-->
    <title> Verify Account - <?php echo business_name; ?></title>

    <!--build:css-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/main.css">
    <!-- endbuild -->

    <!--custom css start-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/custom.css">
    <!--custom css end-->

</head>

<body>

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
                                <!-- <h1 class="h3">Create an Account</h1> -->
                                <p class="text-muted">We sent a verification code to your email</p>

                                <div class="mt-4 register-form">

                                    <?php
                                    $form_attributes = array("id" => "verify_email_form");
                                    echo form_open('registration/verify_email_ajax', $form_attributes); ?>

                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <label for="verificationCode" class="mb-1">Verification Code<span class="text-danger">*</span></label>
                                            <div class="otp-input-container">
                                                <input type="tel" class="form-control otp-input" id="otp1" maxlength="1" autofocus>
                                                <input type="tel" class="form-control otp-input" id="otp2" maxlength="1">
                                                <input type="tel" class="form-control otp-input" id="otp3" maxlength="1">
                                                <input type="tel" class="form-control otp-input" id="otp4" maxlength="1">
                                                <input type="tel" class="form-control otp-input" id="otp5" maxlength="1">
                                                <input type="tel" class="form-control otp-input" id="otp6" maxlength="1">
                                            </div>
                                            <input type="hidden" name="verification_code" id="verificationCode" value="">
                                        </div>

                                        <div class="col-sm-6">
                                            <label for="password" class="mb-1">Password <span class="text-danger">*</span></label>
                                            <div class="input-group mb-3">
                                                <input type="password" class="form-control" placeholder="******" name="password" id="password" required aria-label="password">
                                                <button type="button" class="eyes toggle-password" data-target="password">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <label for="confirmPassword" class="mb-1">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="input-group mb-3">
                                                <input type="password" class="form-control" placeholder="******" name="confirm_password" id="confirmPassword" required aria-label="confirmPassword">
                                                <button type="button" class="eyes toggle-password" data-target="confirmPassword">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-0 mt-2" id="status_msg"></div>

                                        <div class="col-12">
                                            <button type="submit" id="submit" class="main-btn primary">
                                                Continue
                                                <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div

                                        <?php echo form_close(); ?>

                                        <p class="text-center text-muted copyright mt-3" id="resend_verification_email">
                                    Didn't get the code?
                                    <span class="btn-link" style="cursor: pointer;">Resend Email</span>
                                    <span class="spinner-border spinner-border-sm ms-2 d-none tw-text-color-primary" id="search-spinners" role="status" aria-hidden="true"></span>
                                    </p>

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
    <script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
    <!-- <script src="<?php echo base_url(); ?>assets/website/js/home.js"></script> -->
    <script src="<?php echo base_url(); ?>assets/login/js/custom.js"></script>

    <!-- pass base_url to js -->
    <script type="text/javascript">
        var base_url = "<?php echo base_url(); ?>";
    </script>
</body>

</html>