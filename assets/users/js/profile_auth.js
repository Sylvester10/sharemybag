(function ($) {
    'use strict';

    var $requestButton = $('#requestPhoneVerification');
    var $verifyForm = $('#verifyPhoneNumberForm');
    if (!$verifyForm.length) {
        return;
    }

    var $phoneSignIn = $('#profilePhoneSignIn');
    var $phoneSignInToggle = $('#phoneSignInToggle');

    $phoneSignInToggle.on('change', function () {
        var enabled = this.checked;
        $phoneSignIn.toggleClass('is-active', enabled);

        if ($phoneSignInToggle.attr('data-verified') !== '1') {
            if (enabled) $('#profilePhoneNumber').trigger('focus');
            return;
        }

        $phoneSignInToggle.prop('disabled', true);
        var formData = new FormData();
        formData.append('enabled', enabled ? '1' : '0');
        appendGlobalCsrfToFormData(formData);
        $.ajax({
            url: base_url + 'profile/set_phone_signin_ajax',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (res) {
                updateGlobalCsrfHash(res.csrf_hash);
                if (!res.status) {
                    $phoneSignInToggle.prop('checked', !enabled);
                    $phoneSignIn.toggleClass('is-active', !enabled);
                    toastr.error(res.msg || 'Could not update phone sign-in.');
                    return;
                }
                toastr.success(res.msg);
            },
            error: function (xhr) {
                var error = getAjaxErrorMessage(xhr, 'Could not update phone sign-in. Please try again.');
                $phoneSignInToggle.prop('checked', !enabled);
                $phoneSignIn.toggleClass('is-active', !enabled);
                toastr.error(error.message);
            },
            complete: function () {
                $phoneSignInToggle.prop('disabled', false);
            },
        });
    });

    var modalElement = document.getElementById('phoneVerificationModal');
    var modal = modalElement && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalElement) : null;
    var verificationModalOpen = false;
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            verificationModalOpen = false;
            $phoneSignInToggle.prop('disabled', false);
        });
    }
    var verifyingCode = false;

    function resetCode() {
        var $group = $verifyForm.find('.otp-input-container');
        $group.removeClass('is-invalid').attr('aria-invalid', 'false');
        $group.find('.otp-input').val('');
        $('#phoneVerificationCode').val('');
        $('#phoneVerificationModalStatus').empty();
    }

    function showVerificationError(message) {
        $('#phoneVerificationModalStatus').empty();
        toastr.error(message, 'Phone Verification');
    }

    $requestButton.on('click', function () {
        var $spinner = $('#phoneVerificationRequestSpinner');
        var formData = new FormData();
        formData.append('country_code', $('#profileCountryCode').val() || '');
        formData.append('number', $('#profilePhoneNumber').val() || '');
        appendGlobalCsrfToFormData(formData);

        $requestButton.prop('disabled', true).addClass('is-loading').attr('aria-busy', 'true');
        $phoneSignInToggle.prop('disabled', true);
        $spinner.removeClass('d-none');

        $.ajax({
            url: base_url + 'profile/request_phone_verification_ajax',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (res) {
                updateGlobalCsrfHash(res.csrf_hash);
                if (!res.status) {
                    showVerificationError(res.msg || 'We could not send a code.');
                    return;
                }

                $('#phoneVerificationChallengeToken').val(res.challenge_token || '');
                resetCode();
                if (modal) {
                    verificationModalOpen = true;
                    modal.show();
                    modalElement.addEventListener('shown.bs.modal', function focusCode() {
                        $verifyForm.find('.otp-input').first().trigger('focus');
                        modalElement.removeEventListener('shown.bs.modal', focusCode);
                    });
                }
            },
            error: function (xhr) {
                var error = getAjaxErrorMessage(xhr, 'We could not send a code. Please try again.');
                showVerificationError(error.message);
            },
            complete: function () {
                $requestButton.prop('disabled', false).removeClass('is-loading').removeAttr('aria-busy');
                if (!verificationModalOpen) $phoneSignInToggle.prop('disabled', false);
                $spinner.addClass('d-none');
            },
        });
    });

    $verifyForm.on('submit', function (event) {
        event.preventDefault();
        if (verifyingCode) return;
        var $group = $verifyForm.find('.otp-input-container');
        if ($('#phoneVerificationCode').val().length !== 6) {
            $group.addClass('is-invalid').attr('aria-invalid', 'true');
            showVerificationError('Enter the complete verification code.');
            $group.find('.otp-input').filter(function () { return !this.value; }).first().trigger('focus');
            return;
        }
        var $status = $('#phoneVerificationModalStatus');
        var formData = new FormData(this);
        appendGlobalCsrfToFormData(formData);
        verifyingCode = true;
        $group.attr('aria-busy', 'true');
        $status.empty().append(
            $('<div>', { class: 'text-center text-muted', role: 'status' })
                .append($('<span>', { class: 'spinner-border spinner-border-sm me-2', 'aria-hidden': 'true' }))
                .append(document.createTextNode('Verifying code…'))
        );

        $.ajax({
            url: this.action,
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (res) {
                updateGlobalCsrfHash(res.csrf_hash);
                if (!res.status) {
                    $group.addClass('is-invalid').attr('aria-invalid', 'true');
                    showVerificationError(res.msg || 'This code could not be verified.');
                    return;
                }

                $requestButton.replaceWith(
                    '<span class="input-group-text text-bg-success profile-phone-verified" id="phoneVerificationState" aria-label="Phone number verified" title="Phone number verified">' +
                        '<i class="ti ti-circle-check text-white" aria-hidden="true"></i>' +
                    '</span>'
                );
                $('#profilePhoneNumber').prop('readOnly', true);
                $('#profileCountryCode').prop('disabled', true);
                $phoneSignInToggle.attr('data-verified', '1').prop('checked', true);
                $phoneSignIn.addClass('is-active');
                if ($('[name="address"]').val().trim() && $('[name="state"]').val().trim() && $('[name="post_code"]').val().trim()) {
                    $('#profileSubmitAction').addClass('d-none');
                    $('#profileSupportNotice').removeClass('d-none');
                }
                resetCode();
                if (modal) {
                    modal.hide();
                }
                toastr.success(res.msg || 'Your phone number is now verified.', 'Phone Verified');
            },
            error: function (xhr) {
                var error = getAjaxErrorMessage(xhr, 'This code could not be verified.');
                showVerificationError(error.message);
            },
            complete: function () {
                verifyingCode = false;
                $group.removeAttr('aria-busy');
            },
        });
    });

    $verifyForm.on('otp-complete', '.otp-input-container', function () {
        if (!verifyingCode) $verifyForm.trigger('submit');
    });

    $verifyForm.on('input', '.otp-input', function () {
        if (!verifyingCode) $('#phoneVerificationModalStatus').empty();
    });
})(jQuery);
