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
	<meta name="robots" content="noindex, follow">
	<link rel="canonical" href="<?= current_url(); ?>">

	<!-- Open Graph Tags -->
	<meta property="og:title" content="<?php echo $title; ?>" />
	<meta property="og:description" content="<?php echo business_description; ?>" />
	<meta property="og:image" content="<?php echo base_url(); ?>assets/website/img/home.png" />
	<meta property="og:url" content="<?php echo current_url(); ?>" />
	<meta property="og:type" content="website" />

	<!-- Twitter Card Tags -->
	<meta name="twitter:card" content="summary" />
	<meta name="twitter:title" content="<?php echo $title; ?>" />
	<meta name="twitter:description" content="<?php echo business_description; ?>" />
	<meta name="twitter:image" content="<?php echo base_url(); ?>assets/website/img/home.png" />
	<meta name="twitter:url" content="<?php echo current_url(); ?>" />

	<meta name="mswebdialog-title" content="<?php echo $title; ?>" />
	<meta name="mswebdialog-logo" content="<?php echo business_logo; ?>" />
	<meta name="mswebdialog-header-color" content="#FFF" />
	<meta name="mswebdialog-newwindowurl" content="*" />

	<!-- Favicon icon-->
	<link rel="shortcut icon" type="image/png" href="<?php echo business_favicon; ?>" />

	<!-- datatable -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/users/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css" />

	<!-- country flags -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/general/countryflags/dist/flat.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/country-flags-css@1.1.2/dist/flat.min.css">

	<!-- Core Css -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/users/css/styles.css" />
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/users/css/custom.css" />

	<!-- Tailwind -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/general/css/tw-output.css" />

	<title><?php echo $title; ?> - <?php echo business_name; ?></title>
</head>

<body>
	<!-- <div class="toast toast-onload align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="toast-body hstack align-items-start gap-6">
			<i class="ti ti-alert-circle fs-6"></i>
			<div>
				<h5 class="text-white fs-3 mb-1">Welcome back, <?php echo $user_details->firstname; ?>!</h5>
			</div>
			<button type="button" class="btn-close btn-close-white fs-2 m-0 ms-auto shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
		</div>
	</div> -->

	<!-- Display flash message -->
	<?php echo custom_flash_message_success('status_success'); ?>
	<?php echo custom_flash_message_danger('status_error'); ?>


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

						<!-- <li class="sidebar-item">
							<a
								class="sidebar-link has-arrow"
								href="javascript:void(0)"
								aria-expanded="false">
								<span class="d-flex">
									<i class="ti ti-basket"></i>
								</span>
								<span class="hide-menu">Market Place</span>
							</a>
							<ul aria-expanded="false" class="collapse first-level">
								<li class="sidebar-item">
									<a href="eco-shop.html" class="sidebar-link">
										<div
											class="round-16 d-flex align-items-center justify-content-center">
											<i class="ti ti-circle"></i>
										</div>
										<span class="hide-menu">Shop</span>
									</a>
								</li>
								<li class="sidebar-item">
									<a href="eco-shop-detail.html" class="sidebar-link">
										<div
											class="round-16 d-flex align-items-center justify-content-center">
											<i class="ti ti-circle"></i>
										</div>
										<span class="hide-menu">Details</span>
									</a>
								</li>
								<li class="sidebar-item">
									<a href="eco-product-list.html" class="sidebar-link">
										<div
											class="round-16 d-flex align-items-center justify-content-center">
											<i class="ti ti-circle"></i>
										</div>
										<span class="hide-menu">List</span>
									</a>
								</li>
								<li class="sidebar-item">
									<a href="eco-checkout.html" class="sidebar-link">
										<div
											class="round-16 d-flex align-items-center justify-content-center">
											<i class="ti ti-circle"></i>
										</div>
										<span class="hide-menu">Checkout</span>
									</a>
								</li>
								<li class="sidebar-item">
									<a href="eco-add-product.html" class="sidebar-link">
										<div
											class="round-16 d-flex align-items-center justify-content-center">
											<i class="ti ti-circle"></i>
										</div>
										<span class="hide-menu">Add Product</span>
									</a>
								</li>
								<li class="sidebar-item">
									<a href="eco-edit-product.html" class="sidebar-link">
										<div
											class="round-16 d-flex align-items-center justify-content-center">
											<i class="ti ti-circle"></i>
										</div>
										<span class="hide-menu">Edit Product</span>
									</a>
								</li>
							</ul>
						</li> -->

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

				<div class="fixed-profile px-4 py-9 mx-4 mb-2 bg-primary-subtle rounded position-relative cta">
					<div class="sidebar-footer-text position-relative z-1">
						<h4 class="fw-bolder fs-5">Want to </h4>
						<h4 class="fw-bolder fs-5">earn money?</h4>
						<a href="<?php echo base_url('travellers'); ?>" target="_blank" class="btn btn-primary mt-2">Become a Traveller</a>
					</div>
					<img src="<?php echo base_url(); ?>assets/users/images/backgrounds/sidebar-buynow.png" alt="" class="buynow-img img-fluid position-absolute end-0 bottom-0">
				</div>

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
									<!-- start dark mode -->
									<!-- ------------------------------- -->

									<!-- <li class="nav-item nav-icon-hover-bg rounded-circle">
										<a class="nav-link moon dark-layout" href="javascript:void(0)">
											<i class="ti ti-moon moon"></i>
										</a>
										<a class="nav-link sun light-layout" href="javascript:void(0)">
											<i class="ti ti-sun sun"></i>
										</a>
									</li> -->

									<!-- ------------------------------- -->
									<!-- end dark mode -->
									<!-- ------------------------------- -->

									<!-- ------------------------------- -->
									<!-- start notification Dropdown -->
									<!-- ------------------------------- -->

									<!-- <li class="nav-item nav-icon-hover-bg rounded-circle dropdown">
										<a class="nav-link position-relative" href="javascript:void(0)" id="drop2" aria-expanded="false">
											<i class="ti ti-bell-ringing"></i>
											<div class="notification bg-primary rounded-circle"></div>
										</a>
										<div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
											aria-labelledby="drop2">
											<div
												class="d-flex align-items-center justify-content-between py-3 px-7">
												<h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
												<span class="badge text-bg-primary rounded-2 px-3 py-1 lh-sm">5 new</span>
											</div>
											<div class="message-body" data-simplebar>
												<a href="javascript:void(0)" class="py-6 px-7 d-flex align-items-center dropdown-item">
													<span class="me-3">
														<img src="<?php echo base_url(); ?>assets/users/images/profile/user-2.jpg" alt="user" class="rounded-circle" width="48"
															height="48" />
													</span>
													<div class="w-100">
														<h6 class="mb-0 fs-4 lh-base">Roman Joined the Team!</h6>
														<span class="fs-3 d-block text-body-secondary">Congratulate him</span>
													</div>
												</a>
												<a href="javascript:void(0)"
													class="py-6 px-7 d-flex align-items-center dropdown-item">
													<span class="me-3">
														<img src="<?php echo base_url(); ?>assets/users/images/profile/user-3.jpg" alt="user" class="rounded-circle" width="48"
															height="48" />
													</span>
													<div class="w-100">
														<h6 class="mb-0 fs-4 lh-base">New message</h6>
														<span class="fs-3 d-block text-body-secondary">Salma sent you new message</span>
													</div>
												</a>
												<a href="javascript:void(0)" class="py-6 px-7 d-flex align-items-center dropdown-item">
													<span class="me-3">
														<img src="<?php echo base_url(); ?>assets/users/images/profile/user-4.jpg" alt="user" class="rounded-circle" width="48"
															height="48" />
													</span>
													<div class="w-100">
														<h6 class="mb-0 fs-4 lh-base">Bianca sent payment</h6>
														<span class="fs-3 d-block text-body-secondary">Check your earnings</span>
													</div>
												</a>
												<a href="javascript:void(0)" class="py-6 px-7 d-flex align-items-center dropdown-item">
													<span class="me-3">
														<img src="<?php echo base_url(); ?>assets/users/images/profile/user-5.jpg" alt="user" class="rounded-circle" width="48"
															height="48" />
													</span>
													<div class="w-100">
														<h6 class="mb-0 fs-4 lh-base">Jolly completed tasks</h6>
														<span class="fs-3 d-block text-body-secondary">Assign her new tasks</span>
													</div>
												</a>
												<a href="javascript:void(0)"
													class="py-6 px-7 d-flex align-items-center dropdown-item">
													<span class="me-3">
														<img src="<?php echo base_url(); ?>assets/users/images/profile/user-7.jpg" alt="user" class="rounded-circle" width="48"
															height="48" />
													</span>
													<div class="w-100">
														<h6 class="mb-0 fs-4 lh-base">Roman Joined the Team!</h6>
														<span class="fs-3 d-block text-body-secondary">Congratulate him</span>
													</div>
												</a>
											</div>
											<div class="py-6 px-7 mb-1">
												<button class="btn btn-outline-primary w-100">
													See All Notifications
												</button>
											</div>
										</div>
									</li> -->

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
													if ($user_details->selfie != NULL) { ?>

														<img src="<?php echo base_url('assets/selfie/' . $user_details->selfie); ?>" class="rounded-circle" width="35" height="35" alt="Profile" />

													<?php } else { ?>

														<img src="<?php echo user_avatar; ?>" class="rounded-circle" width="35" height="35" alt="Profile" />

													<?php } ?>

												</div>
											</div>
										</a>
										<div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
											aria-labelledby="drop1">
											<div class="profile-dropdown position-relative" data-simplebar>
												<div class="d-flex align-items-center py-9 mx-7 border-bottom">
													<?php
													if ($user_details->selfie != NULL) { ?>

														<img src="<?php echo base_url('assets/selfie/' . $user_details->selfie); ?>" class="rounded-circle" width="80" height="80" alt="Profile" />

													<?php } else { ?>

														<img src="<?php echo user_avatar; ?>" class="rounded-circle" width="80" height="80" alt="Profile" />

													<?php } ?>

													<div class="ms-3">
														<h5 class="mb-1 fs-4"><?php echo $user_details->firstname; ?> <?php echo $user_details->lastname; ?></h5>

														<p class="mb-1 d-flex align-items-center gap-2">
															<?php echo $user_details->email; ?>
														</p>

														<?php
														if ($user_details->is_verified == 2) { ?>
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