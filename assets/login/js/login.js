jQuery(document).ready(function ($) {
    'use strict';

    /*=========== OTP Input Logic ===========*/
    document.querySelectorAll('.otp-input-container').forEach((group) => {
        const otpInputs = Array.from(group.querySelectorAll('.otp-input'));
        const hiddenInput = document.getElementById(group.dataset.otpTarget);

        function syncGroup() {
            if (hiddenInput) {
                hiddenInput.value = otpInputs.map((input) => input.value).join('');
            }
        }

        function submitWhenComplete() {
            if (group.dataset.otpAutoSubmit === 'true' && otpInputs.every((input) => /^[0-9]$/.test(input.value))) {
                group.dispatchEvent(new CustomEvent('otp-complete', { bubbles: true }));
            }
        }

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(-1);
                group.classList.remove('is-invalid');
                group.setAttribute('aria-invalid', 'false');
                syncGroup();
				group.parentElement.querySelectorAll('.auth-inline-error').forEach((error) => error.remove());

                if (input.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
				submitWhenComplete();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !input.value && index > 0) {
                    otpInputs[index - 1].focus();
                } else if (event.key === 'ArrowLeft' && index > 0) {
                    event.preventDefault();
                    otpInputs[index - 1].focus();
                } else if (event.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    event.preventDefault();
                    otpInputs[index + 1].focus();
                }
            });
        });

        group.addEventListener('paste', (event) => {
            event.preventDefault();
            const clipboard = event.clipboardData || window.clipboardData;
            const otp = clipboard.getData('text').replace(/\D/g, '').slice(0, otpInputs.length);

            otpInputs.forEach((input, index) => {
                input.value = otp[index] || '';
            });
            syncGroup();
			group.parentElement.querySelectorAll('.auth-inline-error').forEach((error) => error.remove());
            group.classList.remove('is-invalid');
            group.setAttribute('aria-invalid', 'false');

            const focusIndex = Math.min(otp.length, otpInputs.length - 1);
            if (otpInputs[focusIndex]) {
                otpInputs[focusIndex].focus();
            }
			submitWhenComplete();
        });

        const form = group.closest('form');
        if (form) {
            form.addEventListener('submit', syncGroup);
        }
    });
});
