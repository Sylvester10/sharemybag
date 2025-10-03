<!----Footer--->
<!-- -------------------------------------------------------------- -->
<!-- footer -->
<!-- -------------------------------------------------------------- -->
<footer class="footer py-3 bg-white border-top text-center">
	<div class="mb-2">
		<?php echo date("Y"); ?> © <?php echo business_name; ?>. All Rights Reserved.
	</div>
	<div>
		Powered by <a href="<?php echo software_vendor_site; ?>" target="_blank"><?php echo software_vendor; ?></a>
	</div>
</footer>
<!-- -------------------------------------------------------------- -->
<!-- End footer -->
<!-- -------------------------------------------------------------- -->
<!----Footer End--->
</div>

</div>

</div>
<div class="dark-transparent sidebartoggler"></div>

<!-- form loader -->
<div id="transparent-loader">
	<iconify-icon icon="eos-icons:loading"></iconify-icon>
</div>

<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		const summaryButton = document.getElementById("sign-in");
		const summaryDialog = document.getElementById("sign-in-dialogs");

		if (summaryButton && summaryDialog) {
			summaryButton.addEventListener("click", function() {
				// Toggle visibility on mobile
				if (window.innerWidth < 992) {
					// Check if it's mobile view
					summaryDialog.classList.toggle("d-none");
					summaryDialog.scrollIntoView({
						behavior: "smooth"
					});
				}
			});
		}
	});
</script>


<!-- Jquery -->
<script src="<?php echo base_url(); ?>assets/general/js/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.2.0/jquery.magnific-popup.min.js" integrity="sha512-fCRpXk4VumjVNtE0j+OyOqzPxF1eZwacU3kN3SsznRPWHgMTSSo7INc8aY03KQDBWztuMo5KjKzCFXI/a5rVYQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="<?php echo base_url(); ?>assets/users/js/vendor.min.js"></script>
<!-- Import Js Files -->
<script src="<?php echo base_url(); ?>assets/users/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url(); ?>assets/users/libs/simplebar/dist/simplebar.min.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/theme/app.init.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/theme/theme.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/theme/app.min.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/theme/sidebarmenu.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/plugins/toastr-init.js"></script>
<script src="<?php echo base_url(); ?>assets/users/libs/jquery-steps/build/jquery.steps.min.js"></script>
<script src="<?php echo base_url(); ?>assets/users/libs/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/forms/form-wizard.js"></script>
<script src="<?php echo base_url(); ?>assets/users/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/datatable/datatable-basic.init.js"></script>

<!-- Mapbox -->
<script id="search-js" defer src="https://api.mapbox.com/search-js/v1.0.0-beta.25/web.js"></script>
<script>
	const script = document.getElementById('search-js');
	// Wait for the Mapbox Search JS script to load before using it
	script.onload = function() {
		mapboxsearch.config.accessToken = 'pk.eyJ1IjoicGV0ZTIxMiIsImEiOiJjbHp1N20zc3cyNnhlMmlxdzFjbXVnZ3B4In0.JzZPZQyk-DmLtwczUuqJUA';

		mapboxsearch.autofill({
			options: {
				country: 'gb'
			}
		});

		// Fix: Ensure name attribute is set correctly before submitting
		$('#address-input').on('change', function() {
			$(this).attr('name', 'address'); // Set the correct name attribute
		});
	};
</script>


<!-- Whatsapp -->
<script async src='https://d2mpatx37cqexb.cloudfront.net/delightchat-whatsapp-widget/embeds/embed.min.js'></script>
<script>
	var wa_btnSetting = {
		"btnColor": "#16BE45",
		"ctaText": "Need Help? Chat with us",
		"cornerRadius": "50",
		"marginBottom": 50,
		"marginLeft": 20,
		"marginRight": 20,
		"btnPosition": "right",
		"whatsAppNumber": "2348149265396",
		"zIndex": 999999,
		"btnColorScheme": "light"
	};
	window.onload = () => {
		_waEmbed(wa_btnSetting);
	};
</script>


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

<!-- solar icons -->
<script src="<?php echo base_url(); ?>assets/users/libs/cdn.jsdelivr.net/iconify-icon.min.js"></script>
<!-- <script src="<?php echo base_url(); ?>assets/users/libs/apexcharts/dist/apexcharts.min.js"></script> -->
<script src="<?php echo base_url(); ?>assets/users/js/dashboards/dashboard.js"></script>

<!-- iconify -->
<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

<script src="https://js.paystack.co/v1/inline.js"></script>

<!-- custom js -->
<script src="<?php echo base_url(); ?>assets/users/js/users_custom.js"></script>
<script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/search.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/track.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/booking.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/notify.js"></script>

<script type="text/javascript">
	//pass base_url to js
	const base_url = "<?php echo base_url(); ?>";
</script>
</body>

</html>