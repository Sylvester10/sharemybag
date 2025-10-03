<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <!--required meta tags-->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?php echo business_description; ?>">
    <meta name="robots" content="noindex, follow">
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
    <title> User Login - <?php echo business_name; ?></title>

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
        <section class="sign-up-in-section bg-dark ptb-60" style="background: url('<?php echo base_url(); ?>assets/login/img/page-header-bg.svg')no-repeat right bottom">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-12">
                        <div class="pricing-content-wrap bg-custom-light rounded-custom shadow-lg">
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
                                        <!-- <div class="tab-pane fade" id="testimonial-tab-2" role="tabpanel">
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
                                                    I’ve used the platform multiple times and it hasn’t failed me once. My packages always arrive safely and on time.
                                                </blockquote>
                                                <div class="author-info mt-4">
                                                    <h6 class="mb-0">Obinna E.</h6>
                                                    <span class="small">Lagos</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="testimonial-tab-3" role="tabpanel">
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
                                                    I was impressed by how easy it was to send my item. No hidden fees, no delays—just efficient and friendly service.
                                                </blockquote>
                                                <div class="author-info mt-4">
                                                    <h6 class="mb-0">Adebayo T.</h6>
                                                    <span class="small">Manchester</span>
                                                </div>
                                            </div>
                                        </div> -->
                                    </div>
                                    <!-- <ul class="nav testimonial-tab-list mt-5" id="nav-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="active" href="#testimonial-tab-1" data-bs-toggle="tab" data-bs-target="#testimonial-tab-1" role="tab" aria-selected="true">
                                                <img src="<?php echo base_url(); ?>assets/login/img/testimonial/user-2.jpg" class="img-fluid rounded-circle" width="60" alt="user">
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#testimonial-tab-2" data-bs-toggle="tab" data-bs-target="#testimonial-tab-2" role="tab" aria-selected="false">
                                                <img src="<?php echo base_url(); ?>assets/login/img/testimonial/user-1.jpg" class="img-fluid rounded-circle" width="60" alt="user">
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#testimonial-tab-3" data-bs-toggle="tab" data-bs-target="#testimonial-tab-3" role="tab" aria-selected="false">
                                                <img src="<?php echo base_url(); ?>assets/login/img/testimonial/user-3.jpg" class="img-fluid rounded-circle" width="60" alt="user">
                                            </a>
                                        </li>

                                    </ul> -->
                                </div>
                            </div>

                            <div class="price-feature-col pricing-action-info p-5 right-radius bg-light-subtle order-0 order-lg-1">
                                <a href="<?php echo base_url(); ?>" class="mb-5 d-block d-xl-none d-lg-none"><img src="<?= business_logo ?>" alt="logo" width="150" class="img-fluid"></a>
                                <h1 class="h3">Welcome Back!</h1>
                                <p class="text-muted">Login your account</p>

                                <div class="mt-4 register-form">

                                    <?php
                                    //process form asynchronously using AJAX
                                    $form_attributes = array("id" => "user_login_form");
                                    echo form_open('user_login/login_ajax', $form_attributes); ?>

                                    <?php
                                    //check if user requested a page before being forced to log in
                                    if ($this->session->is_requested) { ?>
                                        <input type="hidden" id="requested_page" value="<?php echo $this->session->requested_page; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden" id="requested_page" value="<?php echo base_url('dashboard'); ?>" />
                                    <?php } ?>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="email" class="mb-1">Email <span class="text-danger">*</span></label>
                                            <div class="input-group mb-3">
                                                <input type="email" class="form-control" placeholder="Email" name="email" id="email" required aria-label="email">
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <label for="password" class="mb-1">Password <span class="text-danger">*</span></label>
                                            <div class="input-group mb-3 eye">
                                                <input type="password" class="form-control" placeholder="Password" name="password" id="password" required aria-label="Password">
                                                <button type="button" class="eyes toggle-password" data-target="password">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-check d-flex justify-content-end">
                                                <a href="<?php echo base_url('forgot-password'); ?>" class="text-decoration-none copyright">Forgot Password?</a>
                                            </div>
                                        </div>

                                        <div class="mb-0 mt-2" id="status_msg"></div>

                                        <div class="col-12">
                                            <button type="submit" id="submit" class="main-btn primary">
                                                Log in
                                                <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <?php echo form_close(); ?>

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

    <!-- schema -->
    <script>
        fetch("<?= base_url('seo/schema') ?>")
            .then(response => response.text())
            .then(json => {
                const script = document.createElement("script");
                script.type = "application/ld+json";
                script.text = json;
                document.head.appendChild(script); // or document.body if you prefer
            });
    </script>

    <!-- custom scripts -->
    <script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
    <script src="<?php echo base_url(); ?>assets/website/js/home.js"></script>

    <!-- pass base_url to js -->
    <script type="text/javascript">
        var base_url = "<?php echo base_url(); ?>";
    </script>
</body>

</html>