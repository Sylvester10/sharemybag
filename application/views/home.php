

<main>

    <div class="header-video" style="background-image: url('assets/user/img/map.jpeg'); background-color: rgba(0, 0, 0, 0.6);">
        <div id="hero_video">
            <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.6)">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-xl-7 col-lg-8 col-md-8 home">
                            <h1>Send parcels to the UK</h1>
                            <!-- <p>Find a traveller</p> -->
                            <?php
							$form_attributes = array("id" => "search_form");
							echo form_open('home/search', $form_attributes); ?>

                                <div class="row">
                                    <div class="form-group col-lg-9 d-none">
                                        <div class="custom_select custom_select2 clearfix">
                                            <select class="wide form-control" name="destination" id="select_destination">
                                                <option value="United Kingdom (UK)">United Kingdom (UK)</option>    
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <a href="#"> <button class="align-items-center btn_1 d-flex gradient home_button justify-content-center" type="submit">Find a traveler <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span></button> </a>
                                    </div>
                                </div>

                            <?php echo form_close(); ?>

                            <p><small>Sending to Nigeria? <a href="coming_soon" target="_blank">Click here</a></small></p>
                        </div>

                        <!-- Search Results -->
                        <div class="col-lg-8 list_menu" id="search-results">
                            <section id="section-1" class="d-none">
                                <h5>Available Traveller</h5>
                                <div class="table_wrapper">
                                    <div class="cart-list menu-gallery parcel">
                                        <div class="parcel-box">
                                            <div class="box">
                                                <h4><i class="fa-solid fa-calendar"></i><br>Date</h4>
                                                <p>23th April, 2023</p>
                                            </div>
                                            <div class="box">
                                                <h4><i class="fa-solid fa-clock"></i><br>Time left</h4>
                                                <p> 4 Days </p>
                                            </div>
                                            <div class="box">
                                                <h4><i class="fa-solid fa-user"></i><br>Name</h4>
                                                <p>Julian Alvarez </p>
                                            </div>
                                            <!--<div class="box">
                                                <h4><i class="fa-solid fa-plane-departure"></i><br>Destination</h4>
                                                <p>United Kingdom</p>
                                            </div>-->
                                            <div class="box">
                                                <h4><i class="fa-solid fa-weight-hanging"></i><br>Available space</h4>
                                                <p>9.4 kg</p>
                                            </div>
                                            <div class="box">
                                                <h4></h4>
                                                <a href="<?php echo base_url('send'); ?>" class="btn_1 gradient" type="submit">Buy Space</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <!-- /section -->
                        </div>

                    </div>
                </div>
            </div>
            <!-- <img src="assets/img/video_fix.png" alt="" class="header-video--media" data-video-src="video/intro" data-teaser-source="video/intro" data-provider="" data-video-width="1920" data-video-height="960"> -->
        </div>
        <!-- /header-video -->
    </div>

    <div id="cookie-message" class="cookie-message">
        <p>We use cookies to improve your experience on our website.</p>
        <button id="accept-cookies" class="accept-cookies">Accept</button>
    </div>

</main>
<!-- /main -->