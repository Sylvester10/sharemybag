jQuery(document).ready(function ($) {
    'use strict';

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

    // Keep hiddenVerificationCodeInput in sync so the form-submit handler in
    // home.js (which serializes the form) sends the combined OTP value.
    function syncVerificationCode() {
        if (!hiddenVerificationCodeInput) {
            return;
        }
        let combined = '';
        otpInputs.forEach((input) => {
            combined += input.value;
        });
        hiddenVerificationCodeInput.value = combined;
    }

    otpInputs.forEach((input) => {
        input.addEventListener('input', syncVerificationCode);
    });
});
