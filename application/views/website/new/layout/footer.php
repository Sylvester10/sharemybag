<!-- Shared public footer -->
<footer class="smb-footer">
    <div class="smb-footer-grid">
        <div class="smb-footer-brand">
            <a href="<?= base_url(); ?>"><img src="<?= business_text_logo_white; ?>" alt="ShareMyBag home" width="170"></a>
            <p>RC: 1736583</p>
        </div>
        <div>
            <h2>Company</h2>
            <ul>
                <li><a href="<?= base_url('terms-of-use'); ?>">Terms of Use</a></li>
                <li><a href="<?= base_url('terms-and-conditions'); ?>">Terms &amp; Conditions</a></li>
                <li><a href="<?= base_url('waiver'); ?>">Liability Waiver</a></li>
                <li><a href="<?= base_url('prohibited-items'); ?>">Prohibited Items</a></li>
                <li><a href="<?= base_url('privacy-policy'); ?>">Privacy Policy</a></li>
                <li><a href="<?= base_url('cookies'); ?>">Cookie Policy</a></li>
            </ul>
        </div>
        <div>
            <h2>Contact</h2>
            <address><?= html_escape(business_address); ?></address>
            <ul>
                <li><a href="tel:<?= html_escape(business_phone_number); ?>"><?= html_escape(business_phone_number); ?></a></li>
                <li><a href="mailto:<?= html_escape(business_web_mail); ?>"><?= html_escape(business_web_mail); ?></a></li>
            </ul>
        </div>
        <div>
            <h2>Socials</h2>
            <ul>
                <li><a href="<?= business_facebook; ?>"><i class="lab la-facebook-f" aria-hidden="true"></i> Facebook</a></li>
                <li><a href="<?= business_instagram; ?>"><i class="lab la-instagram" aria-hidden="true"></i> Instagram</a></li>
                <li><span class="smb-footer-unavailable"><i class="lab la-twitter" aria-hidden="true"></i> Twitter</span></li>
            </ul>
        </div>
    </div>
    <div class="smb-footer-bottom">
        <p>© <?= date('Y'); ?> <?= business; ?>. All Rights Reserved.</p>
        <a href="#top">Back to top <i class="las la-arrow-up" aria-hidden="true"></i></a>
    </div>
</footer>

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

<!-- general scripts -->
<script src="<?php echo base_url(); ?>assets/general/js/my_functions.js"></script>
<script src="<?php echo base_url(); ?>assets/general/js/phone_input.js?v=<?php echo filemtime(FCPATH . 'assets/general/js/phone_input.js'); ?>"></script>
<script src="<?= base_url('assets/website/js/home.js'); ?>?v=<?= filemtime(FCPATH . 'assets/website/js/home.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/website/js/track.js"></script>
<script src="<?php echo base_url(); ?>assets/website/js/custom.js?v=<?php echo filemtime(FCPATH . 'assets/website/js/custom.js'); ?>"></script>

<script src="<?= base_url('assets/website/js/landing-refresh.js'); ?>?v=<?= filemtime(FCPATH . 'assets/website/js/landing-refresh.js'); ?>"></script>
<script src="<?= base_url('assets/website/js/traveller-refresh.js'); ?>?v=<?= filemtime(FCPATH . 'assets/website/js/traveller-refresh.js'); ?>"></script>

<!-- pass base_url to js -->
<script type="text/javascript">
    var base_url = "<?php echo base_url(); ?>";
    window.appCsrf = {
        name: "<?php echo $this->security->get_csrf_token_name(); ?>",
        hash: "<?php echo $this->security->get_csrf_hash(); ?>"
    };
</script>

</body>

</html>
