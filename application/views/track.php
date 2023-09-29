<main>

    <div class="track-header"
        style="background-image: url('assets/user/img/map.jpeg'); background-color: rgba(0, 0, 0, 0.6); max-height: 450px;">
        <div id="hero_video">
            <div class="opacity-mask d-flex align-items-center" data-opacity-mask="rgba(0, 0, 0, 0.6)">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-xl-7 col-lg-8 col-md-8">
                            <h1>Enter your tracking ID</h1>
                            <p></p>

                            <?php
                            $form_attributes = array("id" => "tracking_form");
                            echo form_open('home/track_parcel', $form_attributes); ?>

                            <div class="row g-0 custom-search-input">
                                <div class="col-lg-10">
                                    <div class="form-group">
                                        <input class="form-control no_border_r" name="parcel" id="parcel-input"
                                            type="text" placeholder="SMB839549346347">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <button href="#modal-dialog"
                                        class="align-items-center justify-content-center btn_1 home_button gradient modal_dialog"
                                        type="submit" id="submit-me">
                                        Search
                                        <span class="spinner-border spinner-border-sm text-light ms-2 d-none"
                                            id="search-spinner" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>

                            <?php echo form_close(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <img src="assets/img/video_fix.png" alt="" class="header-video--media" data-video-src="video/intro" data-teaser-source="video/intro" data-provider="" data-video-width="1920" data-video-height="960"> -->
        <div class="wave hero"></div>
    </div>
    <!-- /header-video -->

    <!-- Adverts -->
    <div class="container margin_60">
        <div class="main_title">
            <span><em></em></span>
            <h2>Ads and Promotions</h2>
            <p>Take advantage of our exclusive deals and limited-time promotions.</p>
            <a href="#0"></a>
        </div>
        <div class="owl-carousel owl-theme carousel_4">
            <?php foreach ($adverts as $a) { ?>
                <div class="item">
                    <div class="strip">
                        <figure>
                            <span class="ribbon off"> </span>
                            <img src="assets/user/img/lazy-placeholder.jpg"
                                data-src="<?php echo base_url('assets/adverts/' . $a->photo); ?>" class="owl-lazy" alt=""
                                width="370" height="370">
                            <a href="<?php echo base_url('assets/adverts/' . $a->photo); ?>" class="strip_info"
                                target="_blank">
                                <div class="item_title">
                                    <h3> </h3>
                                    <small> </small>
                                </div>
                            </a>
                        </figure>
                    </div>
                </div>
            <?php } //endforeach ?>
        </div>

        <!-- Add the Owl Carousel library and initialize the carousel -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
        <script>
            $(document).ready(function () {
                // Initialize the carousel
                $('.owl-carousel').owlCarousel({
                    items: 1, // Number of items to show
                    autoplay: true, // Enable autoplay
                    autoplayTimeout: 5000, // Autoplay interval in milliseconds
                    autoplayHoverPause: true, // Pause autoplay when hovering over the carousel
                    loop: true, // Enable infinite loop
                    nav: true, // Show navigation buttons
                    dots: false, // Hide pagination dots
                });
            });
        </script>

        <!-- /carousel -->
    </div>
    <!-- /bg_gray -->


</main>
<!-- /main -->

<!-- Modal dialog -->
<div id="modal-dialog" class="zoom-anim-dialog d-none">
    <div class="small-dialog-header">
        <h3>Tracking Information</h3>
    </div>
    <div class="table_wrapper" id="trackingIdContainer">
        <div class="cart-list menu-gallery parcel">
            <div class="parcel-box">
                <div class="box">
                    <h4>Tracking Number </h4>
                    <p id="m-trackingNumber">Please input a tracking number</p>
                </div>
            </div>
        </div>
    </div>
    <div class="small-dialog-header sub">
        <h3>Tracking History</h3>
    </div>
    <div class="table_wrapper">
        <div class="cart-list menu-gallery parcel">
            <div class="parcel-box">
                <div class="content" id="trackingHistory">

                </div>
            </div>
        </div>
    </div>
</div>

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
							<li><a href="https://facebook.com/sharemybag/"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/facebook_icon.svg" alt="" class="lazy"></a></li>
							<li><a href="https://instagram.com/sharemybag/"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/instagram_icon.svg" alt="" class="lazy"></a></li>
							<li><a href="#"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/twitter_icon.svg" alt="" class="lazy"></a></li>
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