<!-- JQuery -->
<script src="<?php echo base_url('assets/user/js/jquery-3.3.1.min.js'); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

<!-- COMMON SCRIPTS -->
<script src="assets/user/js/common_scripts.min.js"></script>
<script src="assets/user/js/common_func.js"></script>
<script src="assets/user/js/validate.js"></script>
<script type="text/javascript">
    jQuery.event.special.touchstart = {
        setup: function (_, ns, handle) {
            this.addEventListener("touchstart", handle, {
                passive: !ns.includes("noPreventDefault")
            });
        }
    };
</script>

<!-- SPECIFIC SCRIPTS -->
<script src="assets/user/js/my_functions.js"></script>
<script src="assets/user/js/wizard/wizard_scripts.js"></script>
<script src="assets/user/js/wizard/wizard_func.js"></script>
<script src="assets/user/js/sticky_sidebar.min.js"></script>
<script src="assets/user/js/sticky-kit.min.js"></script>
<script src="assets/user/js/specific_detail.js"></script>
<script src="assets/user/js/vegas.min.js"></script>
<script src="assets/user/js/booking.js"></script>
<script src="assets/user/js/traveller.js"></script>
<script src="assets/user/js/home.js"></script>
<script src="assets/user/js/track.js"></script>

<!-- Bootstrap -->
<script src="assets/user/libraries/datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="assets/user/libraries/datepicker/dist/locales/bootstrap-datepicker.de.min.js"></script>

<!-- Video header -->
<script src="assets/user/js/modernizr.min.js"></script>

<!-- Whatsapp -->
<script async src='https://d2mpatx37cqexb.cloudfront.net/delightchat-whatsapp-widget/embeds/embed.min.js'></script>
<script>
    var wa_btnSetting = { "btnColor": "#16BE45", "ctaText": "Need Help? Chat with us", "cornerRadius": "50", "marginBottom": 90, "marginLeft": 20, "marginRight": 20, "btnPosition": "right", "whatsAppNumber": "2348149265396", "zIndex": 999999, "btnColorScheme": "light" };
    window.onload = () => {
        _waEmbed(wa_btnSetting);
    };
</script>

<!-- Cookies -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cookieMessage = document.getElementById('cookie-message');
        const acceptCookiesButton = document.getElementById('accept-cookies');

        acceptCookiesButton.addEventListener('click', function () {
            // Set a cookie to track user's consent
            document.cookie = 'cookieConsent=true; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/';

            // Hide the cookie message
            cookieMessage.style.display = 'none';
        });

        // Check if the user has already given consent
        if (document.cookie.indexOf('cookieConsent=true') !== -1) {
            cookieMessage.style.display = 'none';
        }
    });
</script>

<!-- Tidio 
<script src="//code.tidio.co/mrrutxmgzrut7wshxtev98yw9qwzmncn.js" async></script> -->

<script type="text/javascript">
    //pass base_url to js
    var base_url = "<?php echo base_url(); ?>";
</script>

</body>

</html>