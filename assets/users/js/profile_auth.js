(function ($) {
    'use strict';

    var $requestButton = $('#requestPhoneVerification');
    var $verifyForm = $('#verifyPhoneNumberForm');
    if (!$verifyForm.length) {
        return;
    }

    var $phoneField = $('#profilePhoneSignIn');
    var $phoneInput = $('#profilePhoneNumber');
    var $methodModal = $('#profileSignInCodesModal');
    var $methodSwitches = $methodModal.find('.profile-code-method-switch');
    var savedMethod = $methodModal.attr('data-method') || 'email';
    var phoneVerified = $methodModal.attr('data-verified') === '1';
    var savingMethod = false;
    var verifyingCode = false;
    var requestingCode = false;
    var verificationModalOpen = false;

    function updatePhoneAction() {
        var hasNumber = /[0-9]/.test($phoneInput.val() || '');
        $phoneField.toggleClass('has-number', hasNumber);
        $requestButton.prop('disabled', !hasNumber || requestingCode || verifyingCode);
    }
    $phoneInput.on('input change', updatePhoneAction);
    updatePhoneAction();

    function renderMethod(method) {
        $methodSwitches.each(function () {
            var selected = $(this).attr('data-method') === method;
            this.checked = selected;
            this.disabled = savingMethod || ($(this).attr('data-method') !== 'email' && !phoneVerified);
            $(this).closest('.profile-code-method').toggleClass('is-selected', selected)
                .toggleClass('is-unavailable', $(this).attr('data-method') !== 'email' && !phoneVerified);
        });
    }
    renderMethod(savedMethod);
    if (window.location.hash === '#pills-security' && window.bootstrap) {
        window.bootstrap.Tab.getOrCreateInstance(document.getElementById('pills-security-tab')).show();
    }
    $methodSwitches.on('change', function () {
        var selectedMethod = $(this).attr('data-method');
        if (!this.checked || savingMethod) { renderMethod(savedMethod); return; }
        if (selectedMethod !== 'email' && !phoneVerified) { renderMethod(savedMethod); return; }
        savingMethod = true;
        renderMethod(selectedMethod);
        var $status = $('#profileCodeMethodStatus');
        $status.text('Saving…');
        var formData = new FormData();
        formData.append('enabled', selectedMethod === 'email' ? '0' : '1');
        formData.append('phone_otp_channel', selectedMethod === 'email' ? 'sms' : selectedMethod);
        appendGlobalCsrfToFormData(formData);
        $.ajax({
            url: base_url + 'profile/set_phone_signin_ajax',
            type: 'POST', data: formData, dataType: 'json', processData: false, contentType: false,
            success: function (res) {
                updateGlobalCsrfHash(res.csrf_hash);
                if (!res.status) { toastr.error(res.msg || 'Could not save your sign-in method.'); return; }
                savedMethod = res.enabled ? res.phone_otp_channel : 'email';
                $methodModal.attr('data-method', savedMethod);
                toastr.success('Sign-in method updated.');
            },
            error: function (xhr) {
                toastr.error(getAjaxErrorMessage(xhr, 'Could not save your sign-in method. Please try again.').message);
            },
            complete: function () {
                savingMethod = false;
                renderMethod(savedMethod);
                $status.empty();
            },
        });
    });
    $methodModal.on('hidden.bs.modal', function () { renderMethod(savedMethod); });
    $('#profileVerifyPhoneLink').on('click', function () {
        var el = $methodModal[0];
        el.addEventListener('hidden.bs.modal', function focusPhone() {
            el.removeEventListener('hidden.bs.modal', focusPhone);
            window.bootstrap.Tab.getOrCreateInstance(document.getElementById('pills-account-tab')).show();
            $phoneInput.trigger('focus');
        });
        window.bootstrap.Modal.getOrCreateInstance(el).hide();
    });

    var modalElement = document.getElementById('phoneVerificationModal');
    var modal = modalElement && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function releasePhoneInput() {
        if (!phoneVerified && !requestingCode && !verifyingCode && !verificationModalOpen) {
            $phoneInput.prop('readOnly', false);
            $('#profileCountryCode').prop('disabled', false);
            $('#phoneVerificationChallengeToken').val('');
            resetCode();
        }
    }
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            verificationModalOpen = false;
            releasePhoneInput();
        });
    }
    $('#profilePasswordModal').on('hidden.bs.modal', function () {
        $(this).find('input[type="password"]').val('');
    });

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

        requestingCode = true;
        $phoneInput.prop('readOnly', true);
        $('#profileCountryCode').prop('disabled', true);
        $requestButton.prop('disabled', true).addClass('is-loading').attr('aria-busy', 'true');
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
                requestingCode = false;
                updatePhoneAction();
                releasePhoneInput();
                $requestButton.removeClass('is-loading').removeAttr('aria-busy');
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
                if (res.local_number) $phoneInput.val(res.local_number);
                if (res.country_code) $('#profileCountryCode').val(res.country_code).trigger('change');
                $phoneInput.prop('readOnly', true);
                $('#profileCountryCode').prop('disabled', true);
                phoneVerified = true;
                $methodModal.attr('data-verified', '1');
                $('#profileCodeMethodsVerifyNotice').addClass('d-none');
                renderMethod(savedMethod);
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
                releasePhoneInput();
                updatePhoneAction();
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
