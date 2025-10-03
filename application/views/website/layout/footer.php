<!-- Footer Area -->

<footer class="footer-area">
    <div class="container">
        <div class="footer-up">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 wow fadeInUp animated" data-wow-delay="100ms">
                    <div class="logo logoss mt-30">
                        <img src="<?php echo business_logo_text; ?>" alt="ShareMyBag" width="150" />
                    </div>
                    <div class="contact-info">
                        <p><b>RC: 1736583</b></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 wow fadeInUp animated" data-wow-delay="200ms">
                    <h6>Company</h6>
                    <ul>
                        <li>
                            <a href="<?php echo base_url('terms-of-use'); ?>">Terms of Use</a>
                            <a href="<?php echo base_url('terms-and-conditions'); ?>">Terms &amp; Conditions</a>
                            <a href="<?php echo base_url('prohibited-items'); ?>">Prohibited Items</a>
                            <a href="<?php echo base_url('privacy-policy'); ?>">Privacy Policy</a>
                            <a href="<?php echo base_url('cookies'); ?>">Cookie Policy</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 col-12 wow fadeInUp animated" data-wow-delay="300ms">
                    <h6>Contact</h6>
                    <ul>
                        <li>
                            <p> <?= business_address ?></p>
                            <p><?= business_phone_number ?></p>
                            <p> <?= business_web_mail ?></p>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 col-12 wow fadeInUp animated" data-wow-delay="400ms">
                    <h6>Socials</h6>
                    <ul>
                        <li>
                            <a href="<?= business_facebook ?>"><i class="lab la-facebook-f"></i> Facebook</a>
                            <a href="<?= business_instagram ?>"><i class="lab la-instagram"></i> Instagram</a>
                            <a href="<?= business_twitter ?>"><i class="lab la-twitter"></i> Twitter</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Footer Bottom Area  -->

<div class="footer-bottom">
    <div class="container copyright">
        <div class="copyright">
            <div class="col-xl-12 col-lg-12 col-12">
                <p class="copyright-line">© 2025 <?php echo business; ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>

<!-- Scroll Top Area -->
<a href="#top" class="go-top"><i class="las la-angle-up"></i></a>



<!-- Cookies -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cookieMessage = document.getElementById('cookie-message');
        const acceptCookiesButton = document.getElementById('accept-cookies');

        if (acceptCookiesButton) { // Check if the button exists
            acceptCookiesButton.addEventListener('click', function() {
                // Set a cookie to track user's consent
                document.cookie = 'cookieConsent=true; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/';

                // Hide the cookie message
                if (cookieMessage) {
                    cookieMessage.style.display = 'none';
                }
            });
        }

        // Check if the user has already given consent
        if (document.cookie.indexOf('cookieConsent=true') !== -1 && cookieMessage) {
            cookieMessage.style.display = 'none';
        }
    });
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

<!-- jquery -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery-1.12.4.min.js"></script>
<!-- Popper JS -->
<script src="<?php echo base_url(); ?>assets/website/js/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="<?php echo base_url(); ?>assets/website/js/bootstrap.min.js"></script>
<!-- Wow JS -->
<script src="<?php echo base_url(); ?>assets/website/js/wow.min.js"></script>
<!-- Way Points JS -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery.waypoints.min.js"></script>
<!-- Counter Up JS -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery.counterup.min.js"></script>
<!-- Owl Carousel JS -->
<script src="<?php echo base_url(); ?>assets/website/js/owl.carousel.min.js"></script>
<!-- Slick JS -->
<script src="<?php echo base_url(); ?>assets/website/js/slick.js"></script>
<!-- Magnific Popup JS -->
<script src="<?php echo base_url(); ?>assets/website/js/magnific-popup.min.js"></script>
<!-- Nice Select  -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery.nice-select.min.js"></script>
<!-- Sticky JS -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery.sticky.js"></script>
<!-- Appear JS -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery.appear.min.js"></script>
<!-- Odometer JS -->
<script src="<?php echo base_url(); ?>assets/website/js/odometer.min.js"></script>
<!-- Progress Bar JS -->
<script src="<?php echo base_url(); ?>assets/website/js/jquery.barfiller.js"></script>
<!-- Main JS -->
<script src="<?php echo base_url(); ?>assets/website/js/main.js"></script>
<!-- Date Picker -->
<script src="<?php echo base_url(); ?>assets/website/vendor/daterangepicker/moment.min.js"></script>
<script src="<?php echo base_url(); ?>assets/website/vendor/daterangepicker/daterangepicker.js"></script>

<!-- schema -->
<?php if (isset($schema)): ?>
    <script type="application/ld+json">
        <?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
<?php endif; ?>

<!-- general scripts -->
<script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
<script src="<?php echo base_url(); ?>assets/website/js/traveller.js"></script>
<script src="<?php echo base_url(); ?>assets/website/js/home.js"></script>
<script src="<?php echo base_url(); ?>assets/website/js/track.js"></script>
<script src="<?php echo base_url(); ?>assets/website/js/custom.js"></script>

<!-- pass base_url to js -->
<script type="text/javascript">
    var base_url = "<?php echo base_url(); ?>";
</script>

</body>

</html>