<!-- main -->
<main>

	<!-- container -->
	<div class="container margin_detail" id="apply">
		<div id="status_msg"></div>
		<div class="main_title center">
			<span><em></em></span>
			<h2>Buy Bag Space</h2>
			<p></p>
		</div>
		<div class="row justify-content-center">
			<div class="col-lg-8 col-md-8">

				<div id="wizard_container">

					<div class="additional_info">
						<h6><i class="fa-solid fa-circle-exclamation"></i> This traveller will not accept <b
								class="text-danger">
								<?php echo $traveller_details->unwanted_items; ?>
							</b> items, please do not include these items in your parcel.</h6>
					</div>

					<div id="top-wizard">
						<div id="progressbar"></div>
					</div>
					<!-- /top-wizard -->

					<?php
					$form_attributes = array("id" => "booking_space");
					echo form_open_multipart('home/add_booking_ajax', $form_attributes); ?>

					<input id="website">
					<input type="text" name="traveller_id"
						value="<?php echo set_value('traveller_id', $traveller_details->id); ?>" class="form-control"
						placeholder="Traveller Id" hidden>
					<input type="text" name="traveller_name"
						value="<?php echo set_value('traveller_name', $traveller_details->name); ?>"
						class="form-control" placeholder="Traveller departure date" hidden>
					<input type="text" name="traveller_email"
						value="<?php echo set_value('traveller_email', $traveller_details->email); ?>"
						class="form-control" placeholder="Traveller Email" hidden>
					<input type="text" name="traveller_departure_date"
						value="<?php echo set_value('traveller_departure_date', $traveller_details->travel_date); ?>"
						class="form-control" placeholder="Traveller departure date" hidden>
					<input type="text" name="traveller_arrival_date"
						value="<?php echo set_value('traveller_arrival_date', $traveller_details->arrival_date); ?>"
						class="form-control" placeholder="Traveller contact" hidden>
					<input type="text" name="traveller_contact"
						value="<?php echo set_value('traveller_contact', $traveller_details->phone); ?>"
						class="form-control" placeholder="1st Drop address" hidden>
					<input type="text" name="traveller_drop_address1"
						value="<?php echo set_value('traveller_drop_address1', $traveller_details->drop_address1); ?>"
						class="form-control" placeholder="1st Drop date" hidden>
					<input type="text" name="traveller_drop_date1"
						value="<?php echo set_value('traveller_drop_date1', $traveller_details->drop_date1); ?>"
						class="form-control" placeholder="2nd Drop address" hidden>
					<input type="text" name="traveller_drop_address2"
						value="<?php echo set_value('traveller_drop_address2', $traveller_details->drop_address2); ?>"
						class="form-control" placeholder="Traveller Drop Address 2" hidden>
					<input type="text" name="traveller_drop_date2"
						value="<?php echo set_value('traveller_drop_date2', $traveller_details->drop_date2); ?>"
						class="form-control" placeholder="2nd Drop date" hidden>
					<input type="text" name="traveller_departure_state"
						value="<?php echo set_value('traveller_departure_state', $traveller_details->departure_state); ?>"
						class="form-control" placeholder="Departure State" hidden>

					<input type="hidden" name="rate" value="<?= $one_pound ?>">

					<input type="text" name="price_calculations" class="form-control" placeholder="Calculations"
						id="price_calculations" hidden>

					<!-- Hidden currency field -->
					<input type="hidden" name="currency" value="<?= $currency ?>" class="form-control" />

					<!-- Leave for security protection, read docs for details -->
					<div id="middle-wizard">

						<!-- step 1 About Item -->
						<div class="step">
							<h3 class="main_question"><strong><i class="fa-solid fa-cart-flatbed-suitcase"></i> About
									your item </strong></h3>
							<p>Provide details about the parcel</p>
							<div id="item-options">
								<div class="row">

									<div class="form-group col-lg-4">
										<div class="custom_select clearfix">
											<input type="text" name="items" class="form-control required"
												placeholder="Items" id="items_input" hidden>

											<select class="wide" name="category" id="select1">
												<option value="">Select Category</option>
												<option value="Normal"
													data-price="<?= ($currency == 'pounds') ? round(4500 / $one_pound, 2) : 4500 ?>">
													Normal</option>
												<option value="Fish/Oil"
													data-price="<?= ($currency == 'pounds') ? round(6500 / $one_pound, 2) : 6500 ?>">
													Fish/Oil</option>
												<option value="Medicine/Documents"
													data-price="<?= ($currency == 'pounds') ? round(12500 / $one_pound, 2) : 12500 ?>">
													Medicine/Documents
												</option>
												<option value="Electronics"
													data-price="<?= ($currency == 'pounds') ? round(25000 / $one_pound, 2) : 25000 ?>">
													Electronics</option>
											</select>
										</div>
									</div>

									<div class="form-group col-lg-3">
										<input type="text" name="item" id="item-name" class="form-control"
											placeholder="Item name">
									</div>

									<div class="form-group col-lg-3">
										<div class="custom_select clearfix">
											<select class="wide" name="size" id="select2">
												<option value="">Select Weight</option>
												<option value="1">1 kg</option>
												<option value="2">2 kg</option>
												<option value="3">3 kg</option>
												<option value="4">4 kg</option>
												<option value="5">5 kg</option>
												<option value="6">6 kg</option>
											</select>
										</div>
									</div>

									<div class="col-lg-2">
										<button type="button" class="btn_1 home_button gradient"
											id="add-me">Add</button>
									</div>
									<div class="error_msg_item"></div>

									<h6>Parcel insurance? (Optional)</h6>
									<p>You are automatically covered by a default insurance amounting to <b><?= $symbol ?><?= ($currency == 'pounds') ? number_format(20000 / $one_pound, 2) : number_format(20000, 2) ?></b>. However, if you require higher insurance for more valuable items, you can choose from the options provided below</p>
									<div class="form-group col-lg-6">
										<div class="custom_select clearfix">
											<select class="wide" name="insurance" id="insuranceBox">
												<option value="">Select</option>
												<option value="<?= ($currency == 'pounds') ? number_format(1500 / $one_pound, 2) : number_format(1500, 2) ?>" data-insurance="<?= ($currency == 'pounds') ? number_format(1500 / $one_pound, 2) : number_format(1500, 2) ?>">Lite Insurance
													<?= $symbol ?>
													<?= ($currency == 'pounds') ? number_format(1500 / $one_pound, 2) : number_format(1500, 2) ?>
												</option>
												<option value="<?= ($currency == 'pounds') ? number_format(50000 / $one_pound, 2) : number_format(50000, 2) ?>" data-insurance="<?= ($currency == 'pounds') ? number_format(50000 / $one_pound, 2) : number_format(50000, 2) ?>">Pro Insurance
													<?= $symbol ?>
													<?= ($currency == 'pounds') ? number_format(50000 / $one_pound, 2) : number_format(50000, 2) ?>
												</option>
											</select>
										</div>
									</div>

								</div>
							</div>
							<p><i class="fa-solid fa-circle-exclamation"></i>
								<b><?= $symbol ?><?= ($currency == 'pounds') ? number_format(2000 / $one_pound, 2) : number_format(2000, 2) ?></b> get up to <b><?= $symbol ?><?= ($currency == 'pounds') ? number_format(50000 / $one_pound, 2) : number_format(50000, 2) ?></b> cover in case of loss or damage  
							</p>
							<p><i class="fa-solid fa-circle-exclamation"></i>
								<b><?= $symbol ?><?= ($currency == 'pounds') ? number_format(4000 / $one_pound, 2) : number_format(4000, 2) ?></b> get up to <b><?= $symbol ?><?= ($currency == 'pounds') ? number_format(100000 / $one_pound, 2) : number_format(100000, 2) ?></b> cover in case of loss or damage 
							</p>
						</div>

						<!-- step 2 Sender Info -->
						<div class="step">
							<h3 class="main_question"><strong><i class="fa-solid fa-paper-plane"></i> Sender
									Information</strong></h3>
							<p>Provide details about the person currently in possession of the parcel</p>
							<div class="form-group">
								<input type="text" name="sender_name" id="sender-name" class="form-control required"
									placeholder="Full name">
							</div>
							<div class="row">
								<div class="col-2">
									<div class="form-group" readonly>
										<input type="text" name="sender_country_code" value="+234"
											class="form-control required" placeholder="Telephone" readonly>
									</div>
								</div>
								<div class="col-5">
									<div class="form-group">
										<input type="text" name="sender_phone" id="phoneNumber"
											class="form-control required" placeholder="Phone number">
									</div>
								</div>
								<div class="col-lg-5">
									<div class="form-group">
										<input type="email" name="sender_email" class="form-control required"
											placeholder="Email">
									</div>
								</div>
							</div>
							<div class="form-group">
								<input type="text" name="sender_address" id="sender-address"
									class="form-control required" placeholder="Address">
								<!--<li id="suggestions1"></li>
										<div class="loader"></div>-->
							</div>
							<!-- /row -->
							<br>
							<br>
							<div class="form-group">
								<label class="container_check version_2">
									By ticking this box, you agree that there are no <b>illegal drugs</b> or other
									<b>prohibited items</b> in the parcel. Your data may be shared with national and
									international drug agencies.
									<input type="checkbox" name="sender_insurance" class="required">
									<span class="checkmark"></span>
								</label>
							</div>
						</div>

						<!-- step 3 Receiver Info -->
						<div class="step">
							<h3 class="main_question"><strong><i class="fa-solid fa-person-circle-check"></i> Reciever
									Information</strong></h3>
							<p>Provide details about the person receiving the parcel</p>
							<p>The Receiver cannot be the same as the Sender</p>
							<div class="form-group">
								<input type="text" name="receiver_name" id="reciever-name" class="form-control required"
									placeholder="Full name">
							</div>
							<div class="row">
								<div class="col-2">
									<div class="form-group" readonly>
										<div class="custom_select clearfix">
											<input type="text" name="receiver_country_code" value="+44"
												class="form-control required" placeholder="Telephone" readonly>
										</div>
									</div>
								</div>
								<div class="col-5">
									<div class="form-group">
										<input type="text" name="receiver_phone" class="form-control required"
											placeholder="Telephone">
									</div>
								</div>
								<div class="col-5">
									<div class="form-group">
										<input type="email" name="receiver_email" class="form-control required"
											placeholder="Email">
									</div>
								</div>
							</div>
							<div class="form-group">
								<input type="text" name="receiver_address" id="reciever-address"
									class="form-control required" placeholder="Address">
								<!--<li id="suggestions2"></li>
											<div class="loader"></div>-->
							</div>
							<br>
							<br>
							<div class="form-group">
								<label class="container_check version_2">
									By ticking this box, you agree that there are no <b>illegal drugs</b> or other
									<b>prohibited items</b> in your package. Your data may be shared with national and
									international drug agencies.
									<input type="checkbox" name="receiver_insurance" class="required">
									<span class="checkmark"></span>
								</label>
							</div>
						</div>

						<!-- step 4 ID Info -->
						<div class="step">
							<h3 class="main_question"><strong><i class="fa-solid fa-id-card"></i> Reciever ID
									Information</strong></h3>
							<p>We will use these details to confirm the name on the proof of identity provided.</p>
							<!-- <div class="icon">
								<i class="fa-solid fa-circle-exclamation"></i> Important
								<p>
									We will use these details to confirm the name on the proof of
									identity provided.
								</p>
							</div> -->
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<input type="text" name="bank_name" class="form-control required"
											placeholder="Bank Name">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<input type="text" name="bank_acc" class="form-control required"
											placeholder="Account Number">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<input type="text" name="sort_code" class="form-control required"
											placeholder="Sort Code">
									</div>
								</div>
							</div>

							<p><i class="fa-solid fa-circle-exclamation"></i> Please ensure that the reciever's name
								matches the name on the ID Card Uploaded.</p>

							<div class="row">
								<div class="col-lg-6">
									<div class="custom_select form-group clearfix">
										<select class="wide required" name="id_type">
											<option value="">Select ID Type</option>
											<option value="Biometric Card">Biometric Card</option>
											<option value="British Passport">British Passport</option>
											<option value="Driver's License">Driver's License</option>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<div class="file_area">
											<input type="file" name="id_photo" id="the_image"
												class="form-control visible_image_input required"
												accept=".jpeg,.jpg,.png,.pdf" holder="idHolder"
												placeholder="Upload ID Card" />
										</div>
									</div>
								</div>
							</div>

							<div class="row">

								<div class="col-lg-6 image_container">
									<label>ID Card</label>
									<img src="assets/user/img/id_card.png" alt="" id="idHolder">
									<span class="reset_img_input inside_button">Remove ID</span>
								</div>

								<div class="col-lg-6 image_container">
									<label>Selfie</label>
									<img src="assets/user/img/user4.png" alt="" id="selfie_holder">
									<span class="reset_img_input inside_button">Remove Selfie</span>

									<!-- Invisible input field to hold the captured image file -->
									<input type="text" class="form-control required" id="image-input" hidden>
								</div>

							</div>
							<!-- /row -->

							<p id="selfie-paragraph" class="selfie-paragraph"><i class="fa fa-camera"></i> Click here to
								take a selfie!</p>

							<br>
							<br>
							<p><i class="fa-solid fa-circle-exclamation"></i> Only <b>JPG</b>, <b>JPEG</b>, <b>PDF</b>
								and <b>PNG</b> files allowed <b>(max 5MB)</b>.</p>
						</div>

						<!-- step 5 Payment Info -->
						<div class="submit step">
							<h3 class="main_question"><strong><i class="fa-solid fa-credit-card"></i> Payment Details</strong></h3>
							<p>Select a payment account</p>
							<div class="col-lg-6">
								<div class="custom_select custom_select2 clearfix">
									<select class="wide required" name="payment_acc" id="payment_acc_select">
										<option <?= ($currency == 'pounds') ? '' : 'selected' ?>
											value="United Bank of Africa">NG Account</option>
										<option <?= ($currency == 'pounds') ? 'selected' : '' ?> value="Metro Bank PLC">UK
											Account</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6 d-none" id="uba_account">
									<p>Please pay <span id="totalAmountNaira">$5000</span> into the account below:</p>
									<h6><b>United Bank of Africa</b></h6>
									<p><b>Account Number:</b> 1023775777<br />
										<b>Account Name:</b> ShareMyBag Limited
									</p>
								</div>
								<div class="col-lg-6 d-none" id="metro_account">
									<p>Please pay <span id="totalAmountPounds">$5000</span> into the account below:</p>
									<h6><b>Metro Bank PLC</b></h6>
									<p><b>Account Number:</b> 11655122<br />
										<b>Sort Code:</b> 230580<br />
										<b>Account Name:</b> Stella-Maris Edokpayi
									</p>
								</div>
							</div>
							<br>
							<p><i class="fa-solid fa-circle-exclamation"></i>
								Please ensure you have made payments before clicking the button below.</p>
						</div>

					</div>
					<!-- /middle-wizard -->
					<div id="bottom-wizard">
						<button type="button" name="backward" id="prev" class="backward">Prev</button>
						<button type="button" name="forward" class="forward">Next</button>
						<button type="submit" name="process" id="submit" class="submit">I have made payments</button>
						<button type="button" name="process" id="booking-spinner"
							class="align-items-center  justify-content-center forward d-none">
							Booking
							<span class="spinner-border spinner-border-sm text-light ms-2" role="status"
								aria-hidden="true"></span>
						</button>
					</div>
					<!-- /bottom-wizard -->

					<?php echo form_close(); ?>

				</div>
				<!-- /Wizard container -->
			</div>
			<!-- /col -->

			<!-- Booking summary -->
			<div class="col-lg-4" id="sidebar_fixed">
				<div class="box_order mobile_fixed" id="sign-in-dialog">
					<div class="head">
						<h3>Order Summary</h3>
					</div>
					<!-- /head -->
					<div class="main">

						<div class="des_order">
							<ul class="clearfix">
								<li class="total text-center">Available Space <h1 class="available_space"
										space="<?= $traveller_details->available_space ?>"><?php echo $traveller_details->available_space; ?>KG</h1>
								</li>
							</ul>
						</div>

						<ul class="des_order">
							<div id="item-list"></div>
						</ul>

						<div class="des_order">
							<div class="row clearfix">
								<div class="col-lg-6 col-md-6 col-sm-6">
									<h6>From:</h6>
									<p id="sendername-value"></p>
									<p id="senderaddress-value"></p>
								</div>
								<div class="col-lg-6 col-md-6 col-sm-6">
									<h6>To:</h6>
									<p id="recievername-value"></p>
									<p id="recieveraddress-value"></p>
								</div>
							</div>
						</div>

						<div class="item_details">
							<ul class="clearfix">
								<li class="total">Total Space: <strong><span> KG </span><span
											id="total-kg"></span></strong>
								</li>
								<li class="total service_charge"
									charge="<?= ($currency == 'pounds') ? round(2000 / $one_pound, 2) : 2000 ?>">Service
									Charge:
									<strong><span>
											<?= $symbol ?>
											<?= ($currency == 'pounds') ? number_format(2000 / $one_pound, 2) : number_format(2000, 2) ?>
										</span></strong>
								</li>
								<li class="total vat">VAT (7.5%): <strong><span id="vat-price"></span><span>
											<?= $symbol ?>
										</span></strong></li>
								<li class="total insurance">Insurance: <strong><span id="insurance-value"></span><span>
											<?= $symbol ?></strong></span>
								</li>
								<li class="total sub_total">Sub Total: <strong><span id="sub-total"></span><span>
											<?= $symbol ?>
										</span></strong></li>
								<li class="total">Total Amount: <strong><span id="total-price"></span><span>
											<?= $symbol ?>
										</span></strong></li>
							</ul>
						</div>

						<div class="btn_1_mobile">
							<div class="text-center"><small>Vat is 7.5% of total added item(s)</small></div>
						</div>
					</div>
				</div>
				<!-- /box_order -->
				<div class="btn_reserve_fixed"><a href="#sign-in-dialog" id="sign-in"
						class="btn_1 gradient full-width">Order Summary</a></div>
			</div>
			<!-- /col -->
		</div>
		<!-- /row -->
	</div>
	<!-- /container -->

	<span class="d-none" id="holdThisInfo" currency="<?= $currency ?>" naira_sign="&#8358;" pound_sign="&pound;"
		one_pound="<?= $one_pound ?>" one_naira="<?= $one_naira ?>" symbol="<?= $symbol ?>"></span>

</main>
<!-- /main -->

<!-- Vertically centered modal -->
<div class="modal fade" id="capture-video">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content modal-content-demo" style="background-color: unset; border: unset;">
			<div class="modal-body p-0">
				<div class="col-12 position-relative">
					<div id="video-container">
						<video id="video-preview" autoplay></video>
						<img id="image-preview" alt="Selfie Preview">
					</div>

					<div id="action-buttons">
						<span class="icon_p" id="snap-icon"><i class="fa-solid fa-camera"></i></span>
						<span class="icon_p" id="retake-icon"><i class="fa-solid fa-rotate-right"></i></span>
						<button class="icon_p" aria-label="Close" data-bs-dismiss="modal" type="button"
							id="close-icon"><i class="fa-solid fa-xmark"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--End Vertically centered modal -->

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
						<p>RN</p>
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
							<li><a href="#"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/twitter_icon.svg" alt="" class="lazy"></a></li>
							<li><a href="#"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/facebook_icon.svg" alt="" class="lazy"></a></li>
							<li><a href="#"><img
										src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
										data-src="assets/user/img/instagram_icon.svg" alt="" class="lazy"></a></li>
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