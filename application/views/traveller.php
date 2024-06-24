<main>

	<div class="container margin_detail">

		<div class="row justify-content-center">
			<div class="col-lg-6 col-md-8">
				<div class="main_title center">
					<span><em></em></span>
					<h2>HOW IT WORKS</h2>
					<p>It’s simple. Sharemybag connects verified senders to you. </p>
				</div>
			</div>

			<div id="wizard_container">
				<div id="middle-wizard">

					<div class="row">
						<div class="col-lg-3">
							<div class="featured-box style-3">
								<div class="featured-box-icon text-light"><span class="w-100 text-20 fw-500">1</span></div>
								<div class="font-icon">
									<i class="fa-solid fa-file-signature fa-2xl" style="color: #f36b24;"></i>
								</div>
								<h4>Register with us</h4>
								<p class="text-3">We’ll ask you about your upcoming trip, the drop off address and account where we will pay your money.</p>
							</div>
						</div>

						<div class="col-lg-3">
							<div class="featured-box style-3">
								<div class="featured-box-icon text-light"><span class="w-100 text-20 fw-500">2</span></div>
								<div class="font-icon">
									<i class="fa-solid fa-people-arrows fa-2xl" style="color: #f36b24;"></i>
								</div>
								<h4>Receive connections</h4>
								<p class="text-3">We will connect vetted senders to you. Senders will drop off their parcels at your chosen drop off point.</p>
							</div>
						</div>

						<div class="col-lg-3">
							<div class="featured-box style-3">
								<div class="featured-box-icon text-light"><span class="w-100 text-20 fw-500">3</span></div>
								<div class="font-icon">
									<i class="fa-solid fa-box-open fa-2xl" style="color: #f36b24;"></i>
								</div>
								<h4>Check packages</h4>
								<p class="text-3">Check parcel content and weight to ensure you are happy to travel with them.</p>
							</div>
						</div>

						<div class="col-lg-3">
							<div class="featured-box style-3">
								<div class="featured-box-icon text-light"><span class="w-100 text-20 fw-500">4</span></div>
								<div class="font-icon">
									<i class="fa-solid fa-truck-fast fa-2xl" style="color: #f36b24;"></i>
								</div>
								<h4>Delivery</h4>
								<p class="text-3">Once you arrive at your destination, we will arrange local couriers to collect the parcels from you and deliver to the receiver. We will release payment to your chosen account. </p>
							</div>
						</div>

					</div>

				</div>
			</div>
			<!-- /Wizard container -->
		</div>
		<!-- /row -->
	</div>
	<!-- /container -->

	<div class="container margin_details" id="apply">

		<div class="row justify-content-center">
			<div class="col-lg-6 col-md-8">
				<div class="main_title center">
					<span><em></em></span>
					<h2>Traveler's Form</h2>
					<!-- <p>Are you traveling to the UK or Nigeria? Share your luggage and make some money</p> -->
				</div>

				<div id="wizard_container">
					<?php
					$form_attributes = array("id" => "traveller_form");
					echo form_open('home/add_traveller_ajax', $form_attributes); ?>

					<input id="website">
					<div id="middle-wizard">

						<div class="step">
							<h3 class="main_question"><strong>Please fill the form and an agent will contact you
									shortly.</strong></h3>

							<!-- Destination -->
							<div class="row">

								<div class="col-lg-6">
									<i class="fa-solid fa-plane-departure"></i> From
									<div class="form-group">
										<input type="text" name="location" value="Nigeria" class="form-control" placeholder="From Nigeria" readonly>
									</div>
								</div>
								<div class="col-lg-6">
									<i class="fa-solid fa-plane-arrival"></i> To
									<div class="form-group">
										<input type="text" name="destination" value="United Kingdom (UK)" class="form-control" placeholder="To United Kingdom (UK)" readonly>
									</div>
								</div>

							</div>

							<!-- Name and Travel date -->
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<input type="text" name="name" class="form-control" placeholder="Full Name">
									</div>
								</div>

								<div class="col-lg-6">
									<div class="form-group">
										<input type="text" name="travel_date" value="<?php echo set_value('event_date'); ?>" id="datepicker" class="form-control" placeholder="Travel Date" readonly>
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
											<select class="wide required" name="c_code1">
												<option value="">+1</option>
												<option value="+234">+234</option>
												<option value="+44">+44</option>
											</select>
										</div>
									</div>
								</div>

								<div class="col-9">
									<div class="form-group">
										<input type="tel" name="phone" class="form-control" placeholder="Telephone">
									</div>
								</div>
							</div>

							<!-- Alternate Phone number -->
							<div class="row">
								<div class="col-3">
									<div class="form-group">
										<div class="custom_select clearfix">
											<select class="wide" name="c_code2">
												<option value="">+1</option>
												<option value="+234">+234</option>
												<option value="+44">+44</option>
											</select>
										</div>
									</div>
								</div>

								<div class="col-9">
									<div class="form-group">
										<input type="tel" name="alt_phone" class="form-control" placeholder="Alternative Telephone">
									</div>
								</div>

							</div>

						</div>

						<div class="form-messege mb-0 mt-20" id="status_msg"></div>

					</div>

					<!-- /middle-wizard -->
					<div id="bottom-wizard">
						<button class="btn_1 gradient" id="submit">Share my Bag</button>
						<button type="button" name="process" id="booking-spinner" class="align-items-center  justify-content-center forward d-none">
							<span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
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