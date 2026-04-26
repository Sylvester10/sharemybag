jQuery(document).ready(function ($) {
    ('use strict');

    /*=========== Disable Button ===========*/
    function disableSubmitBtn() {
        var submitButton = $('#submit');
        submitButton.addClass('disabled');
        submitButton.attr('disabled', true);
    }

    /*=========== Enable Button ===========*/
    function enableSubmitBtn() {
        var submitButton = $('#submit');
        submitButton.removeClass('disabled');
        submitButton.attr('disabled', false);
    }

    /*=========== OTP Input Logic ===========*/
    const otpInputs = document.querySelectorAll(
        '.otp-input-container .otp-input'
    );
    const hiddenVerificationCodeInput =
        document.getElementById('verificationCode');

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (input.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && index > 0 && input.value === '') {
                otpInputs[index - 1].focus();
            }
        });
    });

    if (otpInputs.length > 0) {
        otpInputs[0].addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = (
                e.clipboardData || window.clipboardData
            ).getData('text');
            const otp = pastedData
                .replace(/\D/g, '')
                .slice(0, otpInputs.length);

            otp.split('').forEach((char, index) => {
                if (otpInputs[index]) {
                    otpInputs[index].value = char;
                }
            });

            if (otp.length === otpInputs.length) {
                otpInputs[otp.length - 1].focus();
            } else {
                otpInputs[otp.length].focus();
            }
        });
    }

    /*=========== Sign Up ===========*/
    $('#signup_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');
        var form_data = $(this).serialize();
        var redirect_url = base_url + 'verify-email';
        disableSubmitBtn();

        $.ajax({
            url: base_url + 'registration/signup',
            type: 'POST',
            data: form_data,
            dataType: 'json',
            success: function (res) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#signup_form')[0].reset();

                    // Brief pause so user can read the success message
                    setTimeout(function () {
                        window.location.href = redirect_url;
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    /*=========== Verify Email ===========*/
    $('#verify_email_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');

        let combinedCode = '';
        otpInputs.forEach((input) => {
            combinedCode += input.value;
        });

        var form_data =
            $(this).serialize() + '&verification_code=' + combinedCode;
        var redirect_url = base_url + 'signin';
        disableSubmitBtn();

        $.ajax({
            url: base_url + 'registration/verify_email_ajax',
            type: 'POST',
            data: form_data,
            dataType: 'json',
            success: function (res) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#verify_email_form')[0].reset();

                    setTimeout(function () {
                        window.location.href = redirect_url;
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    /*=========== Resend Verification Email ===========*/
    $('#resend_verification_email').click(function () {
        $('#search-spinners').removeClass('d-none');

        $.ajax({
            url: base_url + 'registration/resend_verification_email_ajax',
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                $('#search-spinners').addClass('d-none');

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(3000)
                        .fadeOut('slow');
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinners').addClass('d-none');
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    /*=========== Recover Password ===========*/
    $('#recover_password_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');
        var form_data = $(this).serialize();
        var redirect_url = base_url + 'signin';
        disableSubmitBtn();

        $.ajax({
            url: base_url + 'recover_password/password_recovery_ajax',
            type: 'POST',
            data: form_data,
            dataType: 'json',
            success: function (res) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#recover_password_form')[0].reset();

                    setTimeout(function () {
                        window.location.href = redirect_url;
                    }, 2000);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    /*=========== Change Password ===========*/
    $('#change_pass_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');
        var form_data = $(this).serialize();
        var redirect_url = base_url + 'signin';
        disableSubmitBtn();

        $.ajax({
            url: base_url + 'recover_password/change_password_ajax',
            type: 'POST',
            data: form_data,
            dataType: 'json',
            success: function (res) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#change_pass_form')[0].reset();

                    setTimeout(function () {
                        window.location.href = redirect_url;
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    /*=========== User Login ===========*/
    $('#user_login_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');
        var form_data = $(this).serialize();
        var redirect_url = $('#requested_page').val() || base_url + 'dashboard';
        disableSubmitBtn();

        $.ajax({
            url: base_url + 'user_login/login_ajax',
            type: 'POST',
            data: form_data,
            dataType: 'json',
            success: function (res) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#user_login_form')[0].reset();
                    // Immediate redirect on successful login
                    window.location.href = redirect_url;
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });
});
