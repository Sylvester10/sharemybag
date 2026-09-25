(function ($) {
    'use strict';

    var $requestButton = $('#requestPhoneVerification');
    var $verifyForm = $('#verifyPhoneNumberForm');
    if (!$requestButton.length || !$verifyForm.length) {
        return;
    }

    var modalElement = document.getElementById('phoneVerificationModal');
    var modal = modalElement && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function renderStatus($target, type, message) {
        var $alert = $('<div>', {
            class: 'alert py-2 mb-0 ' + (type === 'success' ? 'alert-success' : 'alert-danger'),
            text: message,
        });
        $target.empty().append($alert);
    }

    $requestButton.on('click', function () {
        var $spinner = $('#phoneVerificationRequestSpinner');
        var formData = new FormData();
        formData.append('country_code', $('#profileCountryCode').val() || '');
        formData.append('number', $('#profilePhoneNumber').val() || '');
        appendGlobalCsrfToFormData(formData);

        $requestButton.prop('disabled', true);
        $spinner.removeClass('d-none');
        $('#phoneVerificationStatus').empty();

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
                    renderStatus($('#phoneVerificationStatus'), 'error', res.msg || 'We could not send a code.');
                    return;
                }

                $('#phoneVerificationChallengeToken').val(res.challenge_token || '');
                $('#phoneVerificationInstructions').text(
                    'Enter the code sent through ' + (res.delivery_channel || 'your selected channel') + '.'
                );
                $('#phoneVerificationCode').val('');
                if (modal) {
                    modal.show();
                    modalElement.addEventListener('shown.bs.modal', function focusCode() {
                        $('#phoneVerificationCode').trigger('focus');
                        modalElement.removeEventListener('shown.bs.modal', focusCode);
                    });
                }
            },
            error: function (xhr) {
                var error = getAjaxErrorMessage(xhr, 'We could not send a code. Please try again.');
                renderStatus($('#phoneVerificationStatus'), 'error', error.message);
            },
            complete: function () {
                $requestButton.prop('disabled', false);
                $spinner.addClass('d-none');
            },
        });
    });

    $verifyForm.on('submit', function (event) {
        event.preventDefault();
        var $submit = $('#verifyPhoneNumberButton');
        var $spinner = $('#phoneVerificationCodeSpinner');
        var formData = new FormData(this);
        appendGlobalCsrfToFormData(formData);
        $submit.prop('disabled', true);
        $spinner.removeClass('d-none');

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
                    renderStatus($('#phoneVerificationModalStatus'), 'error', res.msg || 'This code could not be verified.');
                    return;
                }

                if (!$('#phoneVerificationState .badge').length) {
                    $requestButton.before('<span class="badge text-bg-success px-3 py-2"><i class="ti ti-circle-check me-1" aria-hidden="true"></i> Verified</span>');
                }
                $requestButton.html('Verify a New Number<span class="spinner-border spinner-border-sm ms-1 d-none" id="phoneVerificationRequestSpinner" aria-hidden="true"></span>');
                $('#phoneVerificationState small').first().text('This number can be used for passwordless sign-in.');
                renderStatus($('#phoneVerificationStatus'), 'success', res.msg || 'Your phone number is now verified.');
                $('#phoneVerificationModalStatus').empty();
                if (modal) {
                    modal.hide();
                }
            },
            error: function (xhr) {
                var error = getAjaxErrorMessage(xhr, 'This code could not be verified.');
                renderStatus($('#phoneVerificationModalStatus'), 'error', error.message);
            },
            complete: function () {
                $submit.prop('disabled', false);
                $spinner.addClass('d-none');
            },
        });
    });
})(jQuery);
