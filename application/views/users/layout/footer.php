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
            function closeSummaryDrawer() {
                summaryDialog.classList.remove("booking-summary-drawer-open");
                document.body.classList.remove("booking-summary-open");
                summaryButton.setAttribute("aria-expanded", "false");
            }

            summaryDialog.classList.add("booking-summary-drawer");
            summaryButton.setAttribute("aria-expanded", "false");

            summaryDialog.addEventListener("transitionend", function(event) {
                if (
                    event.propertyName === "transform" &&
                    !summaryDialog.classList.contains("booking-summary-drawer-open") &&
                    window.innerWidth < 992
                ) {
                    summaryDialog.classList.add("d-none");
                }
            });

            summaryButton.addEventListener("click", function() {
                if (window.innerWidth < 992) {
                    if (summaryDialog.classList.contains("booking-summary-drawer-open")) {
                        closeSummaryDrawer();
                        return;
                    }

                    summaryDialog.classList.remove("d-none");
                    window.requestAnimationFrame(function() {
                        summaryDialog.classList.add("booking-summary-drawer-open");
                        document.body.classList.add("booking-summary-open");
                        summaryButton.setAttribute("aria-expanded", "true");
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
        mapboxsearch.config.accessToken = '<?= getenv("MAPBOX_TOKEN") ?>';

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
        "ctaText": "Need Help?",
        "cornerRadius": "50",
        "marginBottom": 60,
        "marginLeft": 20,
        "marginRight": 10,
        "btnPosition": "right",
        "whatsAppNumber": "2348149265396",
        "zIndex": 999999,
        "btnColorScheme": "light"
    };
    window.addEventListener('load', function() {
        if (typeof _waEmbed === 'function') {
            _waEmbed(wa_btnSetting);
        }
    });
</script>

<!-- swiper -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let swiper;

        function initSwiper() {
            if (typeof Swiper !== 'function') {
                return;
            }

            if (window.innerWidth < 576) {
                if (!swiper) {
                    swiper = new Swiper('.mySwiper', {
                        slidesPerView: 'auto',
                        spaceBetween: 16,
                        grabCursor: true,
                        pagination: {
                            el: '.swiper-pagination',
                            clickable: true,
                        },
                    });
                }
            } else {
                if (swiper) {
                    swiper.destroy(true, true);
                    swiper = undefined;
                }
            }
        }

        initSwiper();
        window.addEventListener('resize', initSwiper);
    });
</script>

<!-- Lottie -->
<script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.14/dist/dotlottie-wc.js" type="module"></script>

<!-- solar icons -->
<script src="<?php echo base_url(); ?>assets/users/libs/cdn.jsdelivr.net/iconify-icon.min.js"></script>
<!-- <script src="<?php echo base_url(); ?>assets/users/libs/apexcharts/dist/apexcharts.min.js"></script> -->
<script src="<?php echo base_url(); ?>assets/users/js/dashboards/dashboard.js"></script>

<!-- Swiper's JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- iconify -->
<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

<script src="https://js.paystack.co/v1/inline.js"></script>

<!-- custom js -->
<script src="<?php echo base_url(); ?>assets/users/js/users_custom.js?v=<?php echo filemtime(FCPATH . 'assets/users/js/users_custom.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/general/js/my_functions.js?v=<?php echo filemtime(FCPATH . 'assets/general/js/my_functions.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/general/js/phone_input.js?v=<?php echo filemtime(FCPATH . 'assets/general/js/phone_input.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/users/js/search.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/track.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/pricing_payout.js"></script>
<script src="<?php echo base_url(); ?>assets/users/js/booking.js?v=<?php echo filemtime(FCPATH . 'assets/users/js/booking.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/users/js/notify.js"></script>

<script type="text/javascript">
    //pass base_url to js
    const base_url = "<?php echo base_url(); ?>";
    window.appCsrf = {
        name: "<?php echo $this->security->get_csrf_token_name(); ?>",
        hash: "<?php echo $this->security->get_csrf_hash(); ?>"
    };
</script>
</body>

</html>
