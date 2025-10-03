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
    <title> Password Reset - <?php echo business_name; ?></title>

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

                            <?php if (! $valid_code) { //invalid or expired code 
                            ?>
                                <div class="text-center p-5">
                                    <h3 class="fw-500">Expired Link</h3>
                                    <p class="text-3 text-danger text-center"><b>The password reset link is invalid or expired. If you submitted the password recovery request more than once, ensure you clicked the link of the most recent mail sent to you.</b></p>
                                </div>

                            <?php } else { //code is valid and not expired, show form 
                            ?>

                                <div class="price-feature-col pricing-feature-info testimonial-box text-white left-radius p-5 order-1 order-lg-0 " style="background-image:url('<?php echo base_url(); ?>assets/login/img/bg/login2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                                    <div class="mask-overlay"></div>
                                    <a href="<?php echo base_url(); ?>" class="mb-5 d-none d-xl-block d-lg-block"><img src="<?= business_logo_white ?>" alt="logo" class="img-fluid" width="150"></a>
                                    <div class="customer-testimonial-wrap mt-200">
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
                                    <h1 class="h3">Reset Password</h1>
                                    <p class="text-muted">Please provide the reset code sent to your email</p>

                                    <div class="mt-4 register-form">

                                        <?php
                                        //process form asynchronously using AJAX
                                        $form_attributes = array("id" => "change_pass_form");
                                        echo form_open('recover_password/change_password_ajax/' . $y->id . '/' . $y->pass_reset_code, $form_attributes); ?>

                                        <div class="row">
                                            <div class="col-sm-12 d-none">
                                                <label for="email" class="mb-1">Email<span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $y->email; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label for="password" class="mb-1">Reset Code <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input type="tel" class="form-control otp-input" placeholder="000000" id="resetCode" name="pass_reset_code" required >
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="password" class="mb-1">New Password <span class="text-danger">*</span></label>
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
                                        </div>

                                        <?php echo form_close(); ?>

                                        <p class="text-center text-muted mt-4 mb-0 copyright">Go Back <a href="<?php echo base_url('signin'); ?>" class="text-decoration-none">Login</a></p>

                                    </div>

                                    <p class="text-center text-muted mt-5 mb-3 copyright">Copyright &copy; 2025 <a href="<?php echo base_url(); ?>"><?php echo business_name; ?></a>.<br> All Rights Reserved.</p>
                                </div>

                            <?php } ?>

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
    <script src="<?php echo base_url(); ?>assets/website/js/home.js"></script>

    <!-- pass base_url to js -->
    <script type="text/javascript">
        var base_url = "<?php echo base_url(); ?>";
    </script>
</body>

</html>