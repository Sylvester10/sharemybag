<!DOCTYPE html>
<html
	lang="en"
	dir="ltr"
	data-bs-theme="light"
	data-color-theme="Blue_Theme"
	data-layout="vertical">

<head>
	<!-- Required meta tags -->
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<meta name="description" content="<?php echo business_description; ?>">
	<meta name="author" content="">
	<meta name="keywords" content="<?php echo business_keywords; ?>">

	<!-- Favicon icon-->
	<link rel="shortcut icon" type="image/png" href="<?php echo business_favicon; ?>" />

	<!-- datatable -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/users/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css" />

	<!-- country flags -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/general/countryflags/dist/flat.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/country-flags-css@1.1.2/dist/flat.min.css">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/general/css/phone-input.css?v=<?php echo filemtime(FCPATH . 'assets/general/css/phone-input.css'); ?>" />

	<!-- Swiper -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

	<!-- Core Css -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/users/css/styles.css" />
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/users/css/custom.css?v=<?php echo filemtime(FCPATH . 'assets/users/css/custom.css'); ?>" />

	<!-- Tailwind -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/general/css/tw-output.css" />

	<title><?php echo $title; ?> - <?php echo business_name; ?></title>
</head>

<body>

	<!-- Display flash message -->
	<?php echo custom_flash_message_success('status_success'); ?>
	<?php echo custom_flash_message_success('status_msg'); ?>
	<?php echo custom_flash_message_danger('status_error'); ?>
	<?php echo custom_flash_message_danger('status_msg_error'); ?>


	<!-- preloader start -->
	<div id="preloader" class="bg-light-subtle">
		<div class="preloader-wrap">
			<div class="loading-bar"></div>
		</div>
	</div>

	<div id="main-wrapper">
		<!-- Sidebar Start -->
		<aside class="left-sidebar with-vertical">
			<div>
				<!-- ---------------------------------- -->
				<!-- Start Vertical Layout Sidebar -->
				<!-- ---------------------------------- -->
				<div class="brand-logo d-flex align-items-center justify-content-between">
					<a href="index.html" class="text-nowrap logo-img">
						<img src="<?php echo business_text_logo; ?>" width="100px" class="dark-logo" alt="Logo-Dark" />
						<img src="<?php echo business_text_logo_white; ?>" width="100px" class="light-logo" alt="Logo-light" />
					</a>
					<a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
						<i class="ti ti-x"></i>
					</a>
				</div>

				<nav class="sidebar-nav scroll-sidebar" data-simplebar>
					<ul id="sidebarnav">
						<!-- ---------------------------------- -->
						<!-- Dashboard -->
						<!-- ---------------------------------- -->
						<li class="sidebar-item mt-5">
							<a class="sidebar-link" href="<?php echo base_url('dashboard'); ?>" id="get-url" aria-expanded="false">
								<span>
									<i class="ti ti-aperture"></i>
								</span>
								<span class="hide-menu">Dashboard</span>
							</a>
						</li>
						<li class="sidebar-item">
							<a class="sidebar-link" href="<?php echo base_url('user_bookings'); ?>" aria-expanded="false">
								<span>
									<i class="ti ti-briefcase"></i>
								</span>
								<span class="hide-menu">Book Bag Space</span>
							</a>
						</li>
						<li class="sidebar-item">
							<a class="sidebar-link" href="<?php echo base_url('history'); ?>" aria-expanded="false">
								<span>
									<i class="ti ti-file-text"></i>
								</span>
								<span class="hide-menu">History</span>
							</a>
						</li>
						<li class="sidebar-item">
							<a class="sidebar-link" href="<?php echo base_url('market_place'); ?>" aria-expanded="false">
								<span>
									<i class="ti ti-basket"></i>
								</span>
								<span class="hide-menu">Traveller's Market</span>
							</a>
						</li>

						<li class="sidebar-item">
							<a class="sidebar-link" href="<?php echo base_url('profile'); ?>" aria-expanded="false">
								<span>
									<i class="ti ti-user-circle"></i>
								</span>
								<span class="hide-menu">Profile</span>
							</a>
						</li>
					</ul>
				</nav>

				<div class="fixed-profile px-4 mx-4 mb-2 rounded mt-7 position-relative">
					<ul id="sidebarnav">
						<li class="sidebar-item">
							<a class="sidebar-link btn btn-danger mt-2" href="<?php echo base_url('logout'); ?>" aria-expanded="false">
								<span>
									<i class="ti ti-logout"></i>
								</span>
								<span class="hide-menu">Logout</span>
							</a>
						</li>
					</ul>
				</div>

				<!-- ---------------------------------- -->
				<!-- Start Vertical Layout Sidebar -->
				<!-- ---------------------------------- -->
			</div>
		</aside>
		<!--  Sidebar End -->
		<div class="page-wrapper">
			<!--  Header Start -->
			<header class="topbar">
				<div class="with-vertical">
					<!-- ---------------------------------- -->
					<!-- Start Vertical Layout Header -->
					<!-- ---------------------------------- -->
					<nav class="navbar navbar-expand-lg p-0">
						<ul class="navbar-nav">
							<li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
								<a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
									<i class="ti ti-menu-2"></i>
								</a>
							</li>
						</ul>

						<div class="d-block d-lg-none py-4">
							<h2 class="mb-0 fw-bolder fs-8"><?php echo $title; ?></h2>
						</div>
						<a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0" href="javascript:void(0)" data-bs-toggle="collapse"
							data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
							<i class="ti ti-dots fs-7"></i>
						</a>
						<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
							<div class="d-flex align-items-center justify-content-between">
								<ul
									class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
									<!-- ------------------------------- -->
									<!-- end notification Dropdown -->
									<!-- ------------------------------- -->

									<!-- ------------------------------- -->
									<!-- start profile Dropdown -->
									<!-- ------------------------------- -->
									<li class="nav-item dropdown">
										<a class="nav-link pe-0" href="javascript:void(0)" id="drop1" aria-expanded="false">
											<div class="d-flex align-items-center">
												<div class="user-profile-img">

														<?php
														$selfie_src = !empty($user_details->selfie) ? base_url('file/selfie/' . rawurlencode($user_details->selfie)) : user_avatar;
														?>

														<img src="<?php echo $selfie_src; ?>" class="rounded-circle" width="35" height="35" alt="Profile" />

												</div>
											</div>
										</a>
										<div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
											aria-labelledby="drop1">
											<div class="profile-dropdown position-relative" data-simplebar>
												<div class="d-flex align-items-center py-9 mx-7 border-bottom">
														<img src="<?php echo $selfie_src; ?>" class="rounded-circle" width="80" height="80" alt="Profile" />

													<div class="ms-3">
														<h5 class="mb-1 fs-4"><?php echo $user_details->firstname; ?> <?php echo $user_details->lastname; ?></h5>

														<p class="mb-1 d-flex align-items-center gap-2">
															<?php echo $user_details->email; ?>
														</p>

														<?php
														if ($user_details->is_verified == VERIFY_APPROVED) { ?>
															<span class="badge bg-success-subtle text-success">Verified</span>
														<?php } else { ?>
															<span class="badge bg-danger-subtle text-danger">Unverified</span>
														<?php } ?>
													</div>
												</div>
												<div class="d-grid py-4 px-7 pt-8">
													<a href="<?php echo base_url('logout'); ?>" class="btn btn-outline-danger">Log Out</a>
												</div>
											</div>
										</div>
									</li>
									<!-- ------------------------------- -->
									<!-- end profile Dropdown -->
									<!-- ------------------------------- -->
								</ul>
							</div>
						</div>
					</nav>
					<!-- ---------------------------------- -->
					<!-- End Vertical Layout Header -->
					<!-- ---------------------------------- -->
				</div>
			</header>
			<!--  Header End -->


			<div class="body-wrapper">
