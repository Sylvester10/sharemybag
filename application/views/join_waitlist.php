<main>

    <div class="container margin_detail" id="apply">

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="main_title center">
                    <span><em></em></span>
                    <h2>Join Wait List</h2>
                    <p></p>
                </div>

                <div id="wizard_container">
                    <?php
                    $form_attributes = array("id" => "waitlist_form");
                    echo form_open('home/waitlist_ajax', $form_attributes); ?>

                    <input id="website">
                    <div id="middle-wizard">

                        <div class="step">
                            <h3 class="main_question text-center"><strong>Please fill the form and an agent will contact you
                                    when a traveler is available.</strong></h3>

                            <!-- Name -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input type="text" name="name" class="form-control" placeholder="Full Name">
                                    </div>
                                </div>

                            </div>

                            <!-- Email -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                            </div>

                            <!-- Phone number -->
                            <div class="row">

                                <div class="col-3">
                                    <div class="form-group">
                                        <div class="custom_select clearfix">
                                            <select class="wide required" name="c_code">
                                                <option value="">+1</option>
                                                <option value="+234">+234</option>
                                                <option value="+44">+44</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-9">
                                    <div class="form-group">
                                        <input type="tel" name="phone" class="form-control" placeholder="Telephone (Whatsapp)">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="form-messege mb-0 mt-20" id="status_msg"></div>

                    </div>

                    <!-- /middle-wizard -->
                    <div id="bottom-wizard">
                        <button class="btn_1 gradient" id="submit">
                            Join Wait List
                            <span class="spinner-border spinner-border-sm text-light d-none" role="status" aria-hidden="true" id="loading-spinner"></span>
                        </button>
                    </div>
                    <!-- /bottom-wizard -->

                    <?php echo form_close(); ?>

                </div>
                <!-- /Wizard container -->
            </div>
            <!-- /col -->
        </div>
        <!-- /row -->
    </div>
    <!-- /container -->

</main>
<!-- /main -->

<!--footer-->
<footer>
    <div class="wave footer"></div>
    <div class="container margin_60_40 fix_mobile">
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <h3 data-bs-target="#collapse_1"></h3>
                <div class="dont-collapse-sm links" id="collapse_1">
                    <ul>
                        <li><img src="assets/user/img/logo/png/colored_logo.png" width="162" height="75" alt="" class="logo_normal"></li>
                        <p><b>RC: 1736583</b></p>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <h3 data-bs-target="#collapse_2">Contacts</h3>
                <div class="collapse dont-collapse-sm contacts" id="collapse_2">
                    <ul>
                        <li><i class="fa-solid fa-phone"></i>
                            <?php echo business_phone_number; ?>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <a href="mailto:<?php echo business_contact_email; ?>" target="_blank"><?php echo business_contact_email; ?></a>
                        </li>
                        <li><i class="fa-solid fa-location"></i>
                            <?php echo business_address; ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <h3 data-bs-target="#collapse_3">Follow us</h3>
                <div class="collapse dont-collapse-sm" id="collapse_3">
                    <div class="follow_us">
                        <ul>
                            <li><a href="https://facebook.com/sharemybag/"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="assets/user/img/facebook_icon.svg" alt="" class="lazy"></a></li>
                            <li><a href="https://instagram.com/sharemybag/"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="assets/user/img/instagram_icon.svg" alt="" class="lazy"></a></li>
                            <li><a href="#"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="assets/user/img/twitter_icon.svg" alt="" class="lazy"></a></li>
                            <!-- <li><a href="#"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/youtube_icon.svg" alt="" class="lazy"></a></li> -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /row-->
        <hr>
        <div class="row add_bottom_25">
            <div class="col-lg-12">
                <ul class="additional_links">
                    <li><a href="#">Terms and conditions</a></li>
                    <li><a href="#">Privacy</a></li>
                    <li><span>© <?php echo business_name; ?>
                        </span></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<!--/footer-->