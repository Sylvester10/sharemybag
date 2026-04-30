<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <!--required meta tags-->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?= current_url(); ?>">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="Login" />
    <meta property="og:description" content="<?php echo business_description; ?>" />
    <meta property="og:image" content="<?php echo base_url(); ?>assets/website/img/home.png" />
    <meta property="og:url" content="<?php echo current_url(); ?>" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="Login" />
    <meta name="twitter:description" content="<?php echo business_description; ?>" />
    <meta name="twitter:image" content="<?php echo base_url(); ?>assets/website/img/home.png" />
    <meta name="twitter:url" content="<?php echo current_url(); ?>" />

    <meta name="mswebdialog-title" content="Login" />
    <meta name="mswebdialog-logo" content="<?php echo business_logo; ?>" />
    <meta name="mswebdialog-header-color" content="#FFF" />
    <meta name="mswebdialog-newwindowurl" content="*" />

    <!--favicon icon-->
    <link href="<?php echo business_favicon; ?>" rel="icon" type="image/png" sizes="16x16" />

    <!--title-->
    <title> Login using number - <?php echo business_name; ?></title>

    <!-- Nice Select CSS -->
    <link href="<?php echo base_url(); ?>assets/website/css/nice-select.css" rel="stylesheet" />

    <!-- country flags -->
    <link href="<?php echo base_url(); ?>assets/general/countryflags/dist/flat.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/country-flags-css@1.1.2/dist/flat.min.css" rel="stylesheet">

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
                                <h1 class="h3">Welcome Back!</h1>
                                <p class="text-muted">Login your account</p>

                                <div class="mt-4 register-form">

                                    <?php
                                    $form_attributes = array("id" => "verify_otp_form");
                                    echo form_open('user_login/verify_otp', $form_attributes); ?>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class=" p-0">
                                                    <!-- Make sure 'name' attribute is set -->
                                                    <select id="country_code" class="nice-select country-code" name="country_code">
                                                        <option value="+1" data-flag="cf-16 cf-ca ms-1" selected>+1 </option>
                                                        <option value="+234" data-flag="cf-16 cf-ng ms-1">+234 </option>
                                                        <option value="+44" data-flag="cf-16 cf-gb ms-1">+44 </option>
                                                    </select>
                                                </div>
                                                <input class="form-control" type="tel" name="phone" id="phone_number" placeholder="1234567890" required maxlength="11" pattern="\d{9,11}" title="Enter your phone number (e.g., 1234567890)">
                                            </div>

                                            <p class="text-black copyright mt-2 mb-3" id="send_otp_wrapper">
                                                <span class="btn-link" id="send_otp_btn" style="cursor: pointer;">Click to get verification code <i class="fa fa-mobile-screen"></i></span>
                                                <span class="spinner-border spinner-border-sm ms-2 d-none tw-text-color-primary" id="send_otp_spinner" role="status" aria-hidden="true"></span>
                                            </p>
                                        </div>

                                        <div class="col-sm-12 mb-4">
                                            <label for="otp1" class="mb-1">Verification Code<span class="text-danger">*</span></label>
                                            <div class="otp-input-container2">
                                                <input type="tel" class="form-control otp-input" id="otp1" name="otp[]" maxlength="1" pattern="\d" autofocus required>
                                                <input type="tel" class="form-control otp-input" id="otp2" name="otp[]" maxlength="1" pattern="\d" required>
                                                <input type="tel" class="form-control otp-input" id="otp3" name="otp[]" maxlength="1" pattern="\d" required>
                                                <input type="tel" class="form-control otp-input" id="otp4" name="otp[]" maxlength="1" pattern="\d" required>
                                            </div>
                                            <!-- This hidden input will hold the combined OTP -->
                                            <input type="hidden" name="otp" id="full_otp_code" value="">
                                        </div>

                                        <div class="mb-0 mt-2" id="status_msg"></div>

                                        <div class="col-12">
                                            <button type="submit" id="submit_otp_btn" class="main-btn primary w-100">
                                                Login
                                                <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="login_spinner" role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <?php echo form_close(); ?>

                                    <!-- ... [your "Or", "Log in using email", "Sign up" links] ... -->
                                    <p class="text-center text-muted mt-4 mb-4 copyright">Or</p>

                                    <div class="col-12">
                                        <a href="<?php echo base_url('signin'); ?>" class="main-btn primary">Log in using email <i class="fa fa-envelope-circle-check"></i></a>
                                    </div>

                                    <p class="text-center text-muted mt-4 mb-0 copyright">Don't have an account? <a href="<?php echo base_url('registration'); ?>" class="text-decoration-none">Sign up</a></p>

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

    <!-- Nice Select  -->
    <script src="<?php echo base_url(); ?>assets/website/js/jquery.nice-select.min.js"></script>

    <!-- custom scripts -->
    <script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
    <script src="<?php echo base_url(); ?>assets/website/js/custom.js"></script>
    <script src="<?php echo base_url(); ?>assets/website/js/home.js"></script>

    <!-- pass base_url to js -->
    <script type="text/javascript">
        var base_url = "<?php echo base_url(); ?>";
        var csrf_token_name = "<?php echo $this->security->get_csrf_token_name(); ?>";
        var csrf_token_hash = "<?php echo $this->security->get_csrf_hash(); ?>";

        // Function to update CSRF hash
        function update_csrf(new_hash) {
            csrf_token_hash = new_hash;
            // Update any hidden CSRF fields if they exist
            $('input[name="' + csrf_token_name + '"]').val(new_hash);
        }
    </script>
</body>

</html>
