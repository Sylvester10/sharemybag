<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="author" content="">
    <link href="<?php echo business_favicon; ?>" rel="icon" type="image/png" sizes="16x16" />
    <title> Continue Verification - <?php echo business_name; ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/main.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/login/css/custom.css">
</head>

<body>
    <div id="preloader" class="bg-light-subtle">
        <div class="preloader-wrap">
            <div class="loading-bar"></div>
        </div>
    </div>

    <div class="main-wrapper">
        <section class="sign-up-in-section bg-dark ptb-60" style="background: url('<?php echo base_url(); ?>assets/login/img/page-header-bg.svg')no-repeat right bottom">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-12">
                        <div class="pricing-content-wrap bg-custom-light rounded-custom shadow-lg">
                            <div class="price-feature-col pricing-feature-info testimonial-box text-white left-radius p-5 order-1 order-lg-0" style="background-image:url('<?php echo base_url(); ?>assets/login/img/bg/login2.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                                <div class="mask-overlay"></div>
                                <a href="<?php echo base_url(); ?>" class="mb-5 d-none d-xl-block d-lg-block"><img src="<?= business_logo_white ?>" alt="logo" class="img-fluid" width="150"></a>
                                <div class="customer-testimonial-wrap mt-200">
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
                                            Pick up your account setup where you left off. Enter the email address you used during sign up and we’ll send a fresh verification code.
                                        </blockquote>
                                        <div class="author-info mt-4">
                                            <h6 class="mb-0 text-white">ShareMyBag</h6>
                                            <span>Account verification</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="price-feature-col pricing-action-info p-5 right-radius bg-light-subtle order-0 order-lg-1">
                                <a href="<?php echo base_url(); ?>" class="mb-5 d-block d-xl-none d-lg-none"><img src="<?= business_logo ?>" alt="logo" width="150" class="img-fluid"></a>
                                <h1 class="h3">Continue verification</h1>
                                <p class="text-muted">Enter the email address linked to your unfinished account.</p>

                                <div class="mt-4 register-form">
                                    <?php
                                    $form_attributes = array("id" => "continue_verification_form");
                                    echo form_open('registration/continue_verification_ajax', $form_attributes); ?>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="email" class="mb-1">Email <span class="text-danger">*</span></label>
                                            <div class="input-group mb-3">
                                                <input type="email" class="form-control" placeholder="xyz@example.com" name="email" id="email" required aria-label="email">
                                            </div>
                                        </div>

                                        <div class="mb-0 mt-2" id="status_msg"></div>

                                        <div class="col-12">
                                            <button type="submit" id="submit" class="main-btn primary">
                                                Send verification code
                                                <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <?php echo form_close(); ?>

                                    <p class="text-center text-muted mt-4 mb-0 copyright">Already verified? <a href="<?php echo base_url('signin'); ?>" class="text-decoration-none">Sign in</a></p>
                                    <p class="text-center text-muted mt-2 mb-0 copyright">Need a new account? <a href="<?php echo base_url('registration'); ?>" class="text-decoration-none">Sign up</a></p>
                                </div>

                                <p class="text-center text-muted mt-5 mb-3 copyright">Copyright &copy; 2025 <a href="<?php echo base_url(); ?>"><?php echo business_name; ?></a>.<br> All Rights Reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="<?php echo base_url(); ?>assets/login/js/vendors/jquery-3.6.0.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/swiper-bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/jquery.magnific-popup.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/parallax.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/aos.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/vendors/massonry.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/login/js/app.js"></script>
    <script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
    <script src="<?php echo base_url(); ?>assets/website/js/home.js"></script>

    <script type="text/javascript">
        var base_url = "<?php echo base_url(); ?>";
    </script>
    <?php $this->load->view('partials/csrf_bootstrap'); ?>
</body>

</html>
