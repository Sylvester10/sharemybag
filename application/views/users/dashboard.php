<div class="container-fluid">

	<!-- Row -->
	<div class="row">

		<?php
		// Array of encouraging messages
		$quotes = [
			"Believe in yourself and all that you are. You are stronger than you know.",
			"Every small step forward is progress. Keep going!",
			"You bring light and warmth to those around you—never forget that.",
			"The best is yet to come. Keep pushing forward with hope!",
			"You are capable of amazing things, just as you are.",
			"Your kindness leaves a mark on everyone you meet.",
			"Embrace today with an open heart; great things are on their way.",
			"Even the smallest effort can make the biggest difference.",
			"Trust in the magic of beginnings, and start where you are.",
			"You're stronger than you think and loved more than you know.",
			"Every day is a chance to grow, learn, and shine.",
			"The world is better with you in it.",
			"Keep your heart open and your spirit strong; you've got this!",
			"You deserve joy, love, and all the good that life has to offer.",
			"Kindness is a strength, and you have it in abundance.",
			"Don’t forget to celebrate the small wins along the way.",
			"You're creating something beautiful with each step forward.",
			"May your day be filled with warmth, love, and endless possibilities.",
			"Believe that you are worthy of all good things.",
			"Your courage, resilience, and kindness inspire those around you."
		];

		// Pick a random message
		$random_quotes = $quotes[array_rand($quotes)];
		?>

		<!-- tracking form -->
		<!--<div class="page-titles mb-3">-->
		<!--	<div class="row">-->
		<!--		<div class="col-lg-5 col-md-6 col-12 align-self-center">-->

		<!--			<?php-->
		<!--			$form_attributes = array("id" => "tracking_form");-->
		<!--			echo form_open('dashboard/track_parcel', $form_attributes); ?>-->

		<!--			<div class="input-group mb-2">-->
		<!--				<input name="parcel" id="parcel-input" type="text" class="required form-control border border-primary" placeholder="Enter tracking number" />-->
		<!--				<button class="btn btn-rounded btn-primary justify-content-center" type="submit" data-bs-toggle="modal" data-bs-target="#tracking-detail" id="submit-me">-->
		<!--					Track parcel <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>-->
		<!--				</button>-->
		<!--			</div>-->

		<!--			<?php echo form_close(); ?>-->

		<!--		</div>-->
		<!--	</div>-->
		<!--</div>-->
		
		<!-- tracking form -->
		<div class="page-titles mb-3">
			<div class="">

				<!--<?php-->
				<!--$form_attributes = array("id" => "tracking_form");-->
				<!--echo form_open('dashboard/track_parcel', $form_attributes); ?>-->

				<!--<div class="input-group mb-2">-->
				<!--	<input name="parcel" id="parcel-input" type="text" class="required form-control border border-primary" placeholder="Enter tracking number" />-->
				<!--	<button class="btn btn-rounded btn-primary justify-content-center" type="submit" data-bs-toggle="modal" data-bs-target="#tracking-detail" id="submit-me">-->
				<!--		Track parcel <span class="spinner-border spinner-border-sm text-light ms-2 d-none" id="search-spinner" role="status" aria-hidden="true"></span>-->
				<!--	</button>-->
				<!--</div>-->

				<!--<?php echo form_close(); ?>-->

			</div>

			<div class="referal-link-btn ">
				<button type="button" id="referal-link-to-us" class="copy-referral" data-bs-placement="top" data-bs-toggle="tooltip" data-bs-original-title="Click To Copy Referral Link">
					<span class="r-link"><?= $referral_link ?></span>
					<span class="r-icon"><i class="ti ti-link"></i></span>
				</button>
			</div>
		</div>

		<div class="col-lg-12">
			<div class="card bg-primary-gt text-white overflow-hidden shadow-none">
				<div class="card-body">
					<div class="row justify-content-between align-items-center">
						<div class="col-sm-8">
							<h5 class="fw-semibold mb-9 fs-7 text-white">Welcome back, <?= $firstname ?>!</h5>
							<p class="mb-2 opacity-75"><?= $random_quotes; ?></p>
							<!-- <p class="mb-9 opacity-75"> You have earned 54% more than last month which is great thing. </p> -->
						</div>
						<div class="col-sm-4">
							<div class="position-relative mb-n7 text-end">
								<img src="<?php echo base_url(); ?>assets/users/images/backgrounds/welcome-bg.svg" alt="flexy-img" class="img-fluid">
								<!-- <img src="<?php echo base_url(); ?>assets/users/images/backgrounds/school.png" alt="flexy-img" class="img-fluid"> -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Confirm is user is verified -->
		<?php
		if ($is_verified == 0) { ?>

			<?php
			if ($user_details) { ?>

				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-1">
								<span class="btn round-50 fs-6 text-primary rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center tw-mr-3">
									<i class="ti ti-users"></i>
								</span>
								<div>
									<h4 class="card-title">75%</h4>
									<p class="card-subtitle mb-1">Complete</p>
								</div>
								<div class="ms-auto">
									<a class="btn btn-primary mb-4 mt-3" href="<?php echo base_url('profile'); ?>">
										Update profile
									</a>
								</div>
							</div>
							<div class="progress text-bg-light">
								<div class="progress-bar text-bg-primary" role="progressbar" style="width: 75%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-1">
								<span class="btn round-50 fs-6 text-primary rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center tw-mr-3">
									<i class="ti ti-shield-lock"></i>
								</span>
								<div>
									<h4 class="card-title">0%</h4>
									<p class="card-subtitle mb-1">Complete</p>
								</div>
								<div class="ms-auto">
									<a href="<?php echo base_url('kyc'); ?>" class="btn btn-primary position-relative disabled">Begin verification</a>
								</div>
							</div>
							<div class="progress text-bg-light">
								<div class="progress-bar text-bg-primary" role="progressbar" style="width: 0%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
				</div>

			<?php } else { ?>

				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-1">
								<span class="btn round-50 fs-6 text-success rounded-circle bg-success-subtle d-flex align-items-center justify-content-center tw-mr-3">
									<i class="ti ti-users"></i>
								</span>
								<div>
									<h4 class="card-title">100%</h4>
									<p class="card-subtitle mb-1">Completed</p>
								</div>
								<div class="ms-auto">
									<a class="btn btn-success mb-4 mt-3 disabled" href="<?php echo base_url('profile'); ?>">
										Update profile
									</a>
								</div>
							</div>
							<div class="progress text-bg-light">
								<div class="progress-bar text-bg-success" role="progressbar" style="width: 100%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-1">
								<span class="btn round-50 fs-6 text-primary rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center tw-mr-3">
									<i class="ti ti-shield-lock"></i>
								</span>
								<div>
									<h4 class="card-title">0%</h4>
									<p class="card-subtitle mb-1">Complete</p>
								</div>
								<div class="ms-auto">
									<a href="<?php echo base_url('kyc'); ?>" class="btn btn-primary position-relative">Begin verification</a>
								</div>
							</div>
							<div class="progress text-bg-light">
								<div class="progress-bar text-bg-primary" role="progressbar" style="width: 0%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
				</div>

			<?php } ?>


		<?php } elseif ($is_verified == 1) { ?>

				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center mb-1">
								<span class="btn round-50 fs-6 text-warning rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center tw-mr-3">
									<i class="ti ti-shield-lock"></i>
								</span>
								<div>
									<h4 class="card-title">90%</h4>
									<p class="card-subtitle mb-1">Complete</p>
								</div>
								<div class="ms-auto">
									<a href="#" class="btn btn-warning position-relative disabled">Verification Pending</a>
								</div>
							</div>
							<div class="progress text-bg-light">
								<div class="progress-bar text-bg-warning" role="progressbar" style="width: 90%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-lg-12 d-grid justify-content-center">
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center">
								<div class="text-center">
									<p class="card-subtitle">Need faster verification? Chat to us on whatsapp. Click the whatsapp icon below</p>
								</div>
							</div>
						</div>
					</div>
				</div>

		<?php } ?>



		<!-- Column -->
		<div class="col-lg-12">
			<div class="row">

				<div class="col-md-6">
					<!-- earnings card -->
					<a href="<?php echo base_url('history'); ?>">
						<div class="card text-bg-dark">
							<div class="card-body p-4">
								<span>
									<i class="ti ti-briefcase fs-8"></i>
								</span>
								<h4 class="card-title mt-3 mb-0 text-white"><?= $total_bookings ?></h4>
								<p class="card-text text-white opacity-75 fs-3 fw-normal">
									Total Bookings
								</p>
							</div>
						</div>
					</a>
				</div>

				<div class="col-md-6">
					<!-- earnings card -->
					<a href="<?php echo base_url('user_bookings'); ?>">
						<div class="card text-bg-primary">
							<div class="card-body p-4">
								<span>
									<i class="ti ti-plane-departure fs-8"></i>
								</span>
								<h4 class="card-title mt-3 mb-0 text-white"><?= $approved_travellers ?></h4>
								<p class="card-text text-white opacity-75 fs-3 fw-normal">
									Available Travellers
								</p>
							</div>
						</div>
					</a>
				</div>

			</div>
		</div>


		<!-- Tracking results modal -->
		<div class="modal fade" id="tracking-detail" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered tracking-details">
				<div class="modal-content" id="trackingIdContainer">
					<div class="modal-header d-flex text-center text-bg-primary">
						<h4 class="modal-title text-white" id="m-trackingNumber">
							No Information
						</h4>
					</div>
					<div class="modal-body">
						<hr>
						<div class="px-3" id="trackingHistory">

						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
							Close
						</button>
					</div>
				</div>
			</div>
		</div>

	</div>
	<!-- Row -->
</div>
