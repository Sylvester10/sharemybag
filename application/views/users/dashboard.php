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
		<div class="page-titles mb-3">
			<div class="referal-link-btn ">
				<button type="button" id="referal-link-to-us" class="copy-referral" data-bs-placement="top" data-bs-toggle="tooltip" data-bs-original-title="Click To Copy Referral Link">
					<span class="r-link"><?= $referral_link ?></span>
					<span class="r-icon"><i class="ti ti-link"></i></span>
				</button>
			</div>
		</div>

		<?php
		// Confirm is user is verified 
		if ($is_verified == 0) { ?>

			<?php
			if ($user_details) { ?>

				<div class="col-lg-12">
					<div class="card !tw-bg-[#020713] overflow-hidden">
						<div class="card-body">
							<div class="row justify-content-between">
								<div class="col-sm-9">
									<h5 class="text-white">Complete Your Profile </h5>
									<p class="card-subtitle">Please update your profile details to proceed with identity verification.</p>
									<div class="ms-auto">
										<a class="btn btn-primary mb-4 mt-3" href="<?php echo base_url('profile'); ?>">
											Complete Profile
										</a>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="review-bg-col mb-n7 text-end">
										<div class="lottie_vid">
											<dotlottie-wc src="https://lottie.host/928aeecd-c6ec-44a1-b854-7aba3d17bbf7/YlYwMN8c8B.lottie" style="width: 170px;height: 180px"
												autoplay loop></dotlottie-wc>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			<?php } else { ?>

				<div class="col-lg-12">
					<div class="card !tw-bg-[#020713] overflow-hidden">
						<div class="card-body">
							<div class="row justify-content-between">
								<div class="col-sm-9">
									<h5 class="text-white">Initiate ID Verification </h5>
									<p class="card-subtitle">Your profile is complete. Click continue to begin the verification process.</p>
									<div class="ms-auto">
										<a class="btn btn-primary mb-4 mt-3" href="<?php echo base_url('kyc'); ?>">
											Begin Verification
										</a>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="lottie_idVerification mb-n7 text-end">
										<div class="lottie_vid">
											<dotlottie-wc src="https://lottie.host/b3d01a07-111f-4c8b-984f-effae09ea9da/mdwRLRcbfu.lottie" style="width: 180px;height: 200px"
												autoplay loop></dotlottie-wc>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			<?php } ?>

		<?php } elseif ($is_verified == 1) { ?>

			<div class="col-lg-12">
				<div class="card !tw-bg-[#020713] overflow-hidden">
					<div class="card-body">
						<div class="row justify-content-between">
							<div class="col-sm-9">
								<h5 class="text-white">Verification Under Review </h5>
								<p class="card-subtitle">Your documents have been submitted and are currently being reviewed. <br> For faster processing, contact us via WhatsApp using the icon below.</p>
							</div>
							<div class="col-sm-3">
								<div class="lottie_pending mb-n7 text-end">
									<div class="lottie_vid">
										<dotlottie-wc src="https://lottie.host/6bf620d7-b72a-4a2c-876f-2e8a025a53f1/SzcSAio8JH.lottie" style="width: 200px;height: 170px"
											autoplay loop></dotlottie-wc>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php } ?>

		<!-- welcome area -->
		<div class="col-lg-12">
			<div class="card bg-primary-gt text-white overflow-hidden shadow-none">
				<div class="card-body">
					<div class="row justify-content-between align-items-center">
						<div class="col-sm-8">
							<h5 class="fw-semibold mb-9 fs-7 text-white">Welcome back, <?= $firstname ?>!</h5>
							<p class="mb-2 opacity-75"><?= $random_quotes; ?></p>
						</div>
						<div class="col-sm-4">
							<div class="position-relative mb-n7 text-end">
								<img src="<?php echo base_url(); ?>assets/users/images/backgrounds/welcome-bg.svg" alt="flexy-img" class="img-fluid">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

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