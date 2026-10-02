jQuery(document).ready(function ($) {
    ('use strict');

    let csrfHash = $('#csrf_hash').val();

    function getCsrfHash() {
        return csrfHash;
    }

    function updateCsrfHash(newHash) {
        if (!newHash) {
            return;
        }

        csrfHash = newHash;
        $('#csrf_hash').val(newHash);
        $('input[type="hidden"][name="q2r_secure"]').val(newHash);
    }

    if ($.fn.dataTable && $.fn.dataTable.ext) {
        $.fn.dataTable.ext.errMode = 'none';
    }

    function getDataTableAjaxErrorMessage(xhr) {
        var responseText = xhr && xhr.responseText ? xhr.responseText : '';

        if (
            xhr &&
            (xhr.status === 403 ||
                responseText.indexOf('The action you have requested is not allowed.') !== -1)
        ) {
            return 'Your session token expired. Refresh this page and try again.';
        }

        if (xhr && xhr.status === 401) {
            return 'Your admin session expired. Sign in again to continue.';
        }

        if (xhr && xhr.status >= 500) {
            return 'The server could not load this table. Please try again.';
        }

        return 'This table could not load right now. Please try again.';
    }

    function showDataTableAjaxError(selector, message) {
        var table = $(selector);
        var shell = table.closest('.admin-table-shell');
        var target = shell.length ? shell : table;
        var alertId = table.attr('id') + '_ajax_error';
        var alert = $('#' + alertId);

        if (!alert.length) {
            alert = $(
                '<div id="' +
                    alertId +
                    '" class="alert alert-danger admin-table-ajax-error" role="alert"></div>'
            );
            target.before(alert);
        }

        alert.text(message).stop(true, true).fadeIn('fast');
    }

    $(document).on('click', 'a.smb-file-preview', function (e) {
        e.preventDefault();

        var previewSrc = $(this).data('preview-src');
        var previewTitle = $(this).data('preview-title') || 'Document Preview';

        if (!previewSrc) {
            return;
        }

        $('#filePreviewModalLabel').text(previewTitle);
        $('#filePreviewModalImage').attr('src', previewSrc);
        $('#filePreviewModal').modal('show');
    });

    function setAdminVerificationLoading(element) {
        var target = $(element);
        var loadingText = target.data('loading-text') || 'Please wait...';

        if (target.data('loading')) {
            return false;
        }

        target.data('loading', true);
        target.data('original-html', target.html());
        target
            .addClass('disabled')
            .attr('aria-disabled', 'true')
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin"></i> &nbsp; ' + loadingText);

        return true;
    }

    $(document).on('click', 'a.admin-verification-action', function (e) {
        if (!setAdminVerificationLoading(this)) {
            e.preventDefault();
        }
    });

    $(document).on('submit', 'form.admin-verification-form', function () {
        if (
            $(this).hasClass('admin-form-modal__form') &&
            !this.checkValidity()
        ) {
            return;
        }

        var submitButton = $(this).find('.admin-verification-submit');

        if (submitButton.length) {
            setAdminVerificationLoading(submitButton);
        }
    });

    // Shared inline validation for modal forms using the admin form system.
    var adminModalFieldIndex = 0;
    var adminModalFieldSelector =
        'input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea';
    var adminModalDelegatedFieldSelector =
        'form.admin-form-modal__form input:not([type="hidden"]):not([type="submit"]):not([type="button"]), ' +
        'form.admin-form-modal__form select, ' +
        'form.admin-form-modal__form textarea';

    function getAdminModalValidationMessage(field) {
        var customMessage = field.getAttribute('data-validation-message');

        if (customMessage) {
            return customMessage;
        }

        if (field.validity) {
            if (field.validity.valueMissing) {
                return 'This field is required.';
            }

            if (field.validity.typeMismatch && field.type === 'email') {
                return 'Enter a valid email address.';
            }

            if (field.validity.patternMismatch) {
                return 'Enter a value in the required format.';
            }

            if (field.validity.tooShort) {
                return 'Enter at least ' + field.minLength + ' characters.';
            }

            if (field.validity.tooLong) {
                return 'Enter no more than ' + field.maxLength + ' characters.';
            }

            if (field.validity.rangeUnderflow) {
                return 'Enter a value of at least ' + field.min + '.';
            }

            if (field.validity.rangeOverflow) {
                return 'Enter a value no greater than ' + field.max + '.';
            }

            if (field.validity.stepMismatch || field.validity.badInput) {
                return 'Enter a valid value.';
            }
        }

        return field.validationMessage || 'Check this field and try again.';
    }

    function prepareAdminModalField(field) {
        var $field = $(field);
        var fieldId = $field.attr('id');

        if (!fieldId) {
            adminModalFieldIndex += 1;
            fieldId = 'admin_modal_field_' + adminModalFieldIndex;
            $field.attr('id', fieldId);
        }

        var $fieldContainer = $field.closest(
            '.form-group, [class*="col-"], .admin-form-modal__section'
        );
        var $label = $fieldContainer.find('label').first();

        if ($label.length && !$label.attr('for')) {
            $label.attr('for', fieldId);
        }

        return fieldId;
    }

    function getAdminModalErrorAnchor(field) {
        var $field = $(field);
        var $phoneGroup = $field.closest('[data-smb-phone-input]');

        if ($phoneGroup.length) {
            return $phoneGroup;
        }

        if ($field.hasClass('select2-hidden-accessible')) {
            var $select2 = $field.next('.select2-container');
            if ($select2.length) {
                return $select2;
            }
        }

        return $field;
    }

    function clearAdminModalFieldError(field) {
        var $field = $(field);
        var errorId = $field.attr('data-admin-error-id');
        var $phoneGroup = $field.closest('[data-smb-phone-input]');

        $field
            .removeClass('admin-form-modal__control--invalid')
            .removeAttr('aria-invalid');

        if ($field.hasClass('select2-hidden-accessible')) {
            $field
                .next('.select2-container')
                .find('.select2-selection')
                .removeClass('admin-form-modal__control--invalid')
                .removeAttr('aria-invalid');
        }

        if ($phoneGroup.length) {
            $phoneGroup.removeClass('admin-form-modal__field-group--invalid');
        }

        if (errorId) {
            $('#' + errorId).remove();
            $field.removeAttr('data-admin-error-id');

            var describedBy = ($field.attr('aria-describedby') || '')
                .split(/\s+/)
                .filter(function (id) {
                    return id && id !== errorId;
                })
                .join(' ');

            if (describedBy) {
                $field.attr('aria-describedby', describedBy);
            } else {
                $field.removeAttr('aria-describedby');
            }
        }
    }

    function showAdminModalFieldError(field, message) {
        var $field = $(field);
        var fieldId = prepareAdminModalField(field);
        var errorId = fieldId + '_error';
        var $anchor = getAdminModalErrorAnchor(field);
        var $phoneGroup = $field.closest('[data-smb-phone-input]');
        var describedBy = ($field.attr('aria-describedby') || '')
            .split(/\s+/)
            .filter(Boolean);

        $('#' + errorId).remove();
        $anchor.after(
            $('<span>', {
                class: 'admin-form-modal__field-error',
                id: errorId,
                text: message,
            })
        );

        if (describedBy.indexOf(errorId) === -1) {
            describedBy.push(errorId);
        }

        $field
            .addClass('admin-form-modal__control--invalid')
            .attr('aria-invalid', 'true')
            .attr('aria-describedby', describedBy.join(' '))
            .attr('data-admin-error-id', errorId);

        if ($field.hasClass('select2-hidden-accessible')) {
            $field
                .next('.select2-container')
                .find('.select2-selection')
                .addClass('admin-form-modal__control--invalid')
                .attr('aria-invalid', 'true');
        }

        if ($phoneGroup.length) {
            $phoneGroup.addClass('admin-form-modal__field-group--invalid');
        }
    }

    function validateAdminModalField(field) {
        if (field.disabled || !field.willValidate) {
            clearAdminModalFieldError(field);
            return true;
        }

        if (
            field.required &&
            (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA') &&
            typeof field.value === 'string' &&
            field.value.trim() === ''
        ) {
            showAdminModalFieldError(field, 'This field is required.');
            return false;
        }

        if (field.checkValidity()) {
            clearAdminModalFieldError(field);
            return true;
        }

        showAdminModalFieldError(field, getAdminModalValidationMessage(field));
        return false;
    }

    function clearAdminModalErrorSummary($form) {
        $form.find('.admin-form-modal__error-summary').remove();
    }

    function showAdminModalErrorSummary($form, invalidCount) {
        var $body = $form.find('.admin-form-modal__body').first();
        var message =
            invalidCount === 1
                ? 'Please correct the highlighted field.'
                : 'Please correct the ' + invalidCount + ' highlighted fields.';
        var $summary = $('<div>', {
            class: 'admin-form-modal__error-summary',
            role: 'alert',
            tabindex: '-1',
            text: message,
        });

        clearAdminModalErrorSummary($form);
        $body.prepend($summary);
        return $summary;
    }

    function focusAdminModalField(field) {
        var $field = $(field);

        if ($field.hasClass('select2-hidden-accessible')) {
            $field.select2('open');
            return;
        }

        field.focus();
    }

    function clearAdminModalValidation($form) {
        clearAdminModalErrorSummary($form);
        $form.find(adminModalFieldSelector).each(function () {
            clearAdminModalFieldError(this);
            $(this).removeData('admin-validation-touched');
        });
        $form.removeData('admin-validation-submitted');
    }

    function prepareAdminModalForm(form) {
        var $form = $(form);

        $form.attr('novalidate', 'novalidate');
        $form.find(adminModalFieldSelector).each(function () {
            prepareAdminModalField(this);
        });
    }

    function ensureOfflineBookingForm(modal) {
        var $modal = $(modal);
        var $form = $modal.find('form.admin-offline-booking-form').first();

        if ($form.length) {
            return $form;
        }

        var action = $modal.attr('data-form-action');
        var $body = $modal.find('.admin-offline-booking-body').first();
        var $footer = $modal.find('.admin-offline-booking-footer').first();

        if (!action || !$body.length || !$footer.length) {
            return $();
        }

        $form = $('<form>', {
            action: action,
            method: 'post',
            enctype: 'multipart/form-data',
            class: 'admin-form-modal__form admin-offline-booking-form',
            novalidate: 'novalidate',
        });

        $form.attr('id', 'offline_booking_form_' + ($modal.attr('id') || 'modal'));
        $form.insertBefore($body);

        $modal
            .find('.admin-form-modal__content > input[type="hidden"]')
            .appendTo($form);
        $form.append($body, $footer);

        if (!$form.find('input[name="q2r_secure"]').length && getCsrfHash()) {
            $('<input>', {
                type: 'hidden',
                name: 'q2r_secure',
                value: getCsrfHash(),
            }).prependTo($form);
        }

        return $form;
    }

    function validateAdminModalForm(form, focusFirstInvalid) {
        var $form = $(form);
        var invalidFields = [];

        prepareAdminModalForm(form);
        $form.data('admin-validation-submitted', true);
        $form.find(adminModalFieldSelector).each(function () {
            if (!validateAdminModalField(this)) {
                invalidFields.push(this);
            }
        });

        if (!invalidFields.length) {
            clearAdminModalErrorSummary($form);
            return true;
        }

        showAdminModalErrorSummary($form, invalidFields.length);

        if (focusFirstInvalid) {
            focusAdminModalField(invalidFields[0]);
        }

        return false;
    }

    $('form.admin-form-modal__form').each(function () {
        prepareAdminModalForm(this);
    });

    $(document).on('show.bs.modal', '.admin-form-modal', function () {
        var $modal = $(this);

        if (
            $modal.parents('form').length &&
            ($modal.hasClass('admin-offline-booking-modal') ||
                $modal.find('form.admin-form-modal__form').length)
        ) {
            $modal.appendTo(document.body);
        }

        if ($modal.hasClass('admin-offline-booking-modal')) {
            ensureOfflineBookingForm(this);
        }

        $modal.find('form.admin-form-modal__form').each(function () {
            prepareAdminModalForm(this);
        });
    });

    $(document).on(
        'blur',
        adminModalDelegatedFieldSelector,
        function () {
            $(this).data('admin-validation-touched', true);
            validateAdminModalField(this);
        }
    );

    $(document).on(
        'input change',
        adminModalDelegatedFieldSelector,
        function () {
            var $field = $(this);
            var $form = $field.closest('form.admin-form-modal__form');

            if (
                $field.data('admin-validation-touched') ||
                $form.data('admin-validation-submitted')
            ) {
                validateAdminModalField(this);
            }

            if (!$form.find('[aria-invalid="true"]').length) {
                clearAdminModalErrorSummary($form);
            }
        }
    );

    $(document).on('submit', 'form.admin-form-modal__form', function (event) {
        if (validateAdminModalForm(this, true)) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
    });

    $(document).on('hidden.bs.modal', '.admin-form-modal', function () {
        clearAdminModalValidation($(this).find('form.admin-form-modal__form'));
    });

    window.AdminModalValidation = {
        clear: function (form) {
            clearAdminModalValidation($(form));
        },
        clearFieldError: function (field) {
            var target = $(field)[0];
            if (target) {
                clearAdminModalFieldError(target);
            }
        },
        setFieldError: function (field, message) {
            var target = $(field)[0];
            if (target) {
                showAdminModalFieldError(target, message);
            }
        },
        validate: function (form) {
            return validateAdminModalForm($(form)[0], true);
        },
    };

    $('#filePreviewModal').on('hidden.bs.modal', function () {
        $('#filePreviewModalImage').attr('src', '');
    });

    // Dropzone Configuration
    if (Dropzone.instances.length > 0)
        Dropzone.instances.forEach((dz) => dz.destroy());
    Dropzone.options.upload_photo_form = {
        maxFilesize: 5,
        acceptedFiles: '.jpg, .jpeg, .png, .gif',
        init: function () {
            this.on('success', function () {
                if (
                    this.getQueuedFiles().length === 0 &&
                    this.getUploadingFiles().length === 0
                ) {
                    location.reload();
                }
            });
        },
    };

    // Utility functions to handle button states
    function toggleSubmitBtn(isDisabled) {
        const submitButton = $('#submit');
        submitButton.prop('disabled', isDisabled);
        submitButton.toggleClass('disabled', isDisabled);
        submitButton.html(isDisabled ? 'Please Wait...' : 'Submit');
    }

    // Quick Mail Form Submission
    $('#quick_mail_form').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.post(
            base_url + 'admin/send_quick_mail_ajax',
            formData,
            function (msg) {
                const alertType = msg == 1 ? 'success' : 'danger';
                const alertMessage =
                    msg == 1 ? 'Mail successfully sent.' : 'Email not Sent!';
                $('#q_status_msg')
                    .html(
                        `<div class="alert alert-${alertType} text-center">${alertMessage}</div>`
                    )
                    .fadeIn('fast')
                    .delay(30000)
                    .fadeOut('slow');
                if (msg == 1) $('#quick_mail_form')[0].reset();
            }
        );
    });

    //Loading icon on submit
    $(document).ready(function () {
        $('#submit_button').submit(function (e) {
            $('#send_mail_btn').attr('disabled', true);
            $('#btn_text').text('Please wait...');
            $('#loading_icon').show();
        });
    });

    //Loading icon on submit
    $(document).ready(function () {
        $('#submit_buttons').submit(function (e) {
            $('#send_mail_btns').attr('disabled', true);
            $('#btn_texts').text('Please wait...');
            $('#loading_icons').show();
        });
    });

    // Reusable DataTable Initialization Function
    // UPDATED: Added extraDataCallback to handle custom filters reliably
    function initializeDataTable(
        selector,
        ajaxUrl,
        searchLabel,
        extraDataCallback = null
    ) {
        return $(selector).DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: true,
            searching: true,
            info: true,
            scrollX: true,
            autoWidth: false,
            ordering: true,
            stateSave: false, // CHANGED: Set to false to prevent filter caching issues
            processing: true,
            serverSide: true,
            searchDelay: 500,
            pagingType: 'simple_numbers',
            dom: "<'dt_len_change'l>f<'dt_buttons'B>trip",
            language: {
                search: searchLabel,
                processing: 'Please wait a sec...',
                info: 'Showing _START_ to _END_ of _TOTAL_',
                infoFiltered: '(filtered from _MAX_ total)',
                emptyTable: 'No data to show.',
                lengthMenu: 'Show _MENU_ entries',
            },
            ajax: {
                url: ajaxUrl,
                type: 'POST',
                data: function (d) {
                    d.q2r_secure = getCsrfHash();
                    // If a callback is provided, run it to append extra data (filters)
                    if (extraDataCallback) {
                        extraDataCallback(d);
                    }
                },
                dataSrc: function (json) {
                    if (json && json.csrf_hash) {
                        updateCsrfHash(json.csrf_hash);
                    }

                    $(selector + '_ajax_error').fadeOut('fast');
                    return json && json.data ? json.data : [];
                },
                error: function (xhr) {
                    showDataTableAjaxError(
                        selector,
                        getDataTableAjaxErrorMessage(xhr)
                    );
                },
            },
            columnDefs: [{ targets: [0, 1], orderable: false }],
            buttons: [
                { extend: 'colvis', className: 'data_export_buttons' },
                { extend: 'print', className: 'data_export_buttons' },
                { extend: 'excel', className: 'data_export_buttons' },
                { extend: 'csv', className: 'data_export_buttons' },
                { extend: 'pdf', className: 'data_export_buttons' },
            ],
        });
    }

    // Initialize DataTables

    // all users
    initializeDataTable(
        '#users_table',
        base_url + 'admin_users/user_ajax',
        'Search/filter user:'
    )
        .order([9, 'desc'])
        .draw();

    /////////////////////////////////////////////////////////

    // approved users
    initializeDataTable(
        '#approved_users_table',
        base_url + 'admin_users/approved_users_ajax',
        'Search/filter user:'
    )
        .order([10, 'desc'])
        .draw();

    /////////////////////////////////////////////////////////

    // pending users
    initializeDataTable(
        '#pending_users_table',
        base_url + 'admin_users/pending_users_ajax',
        'Search/filter user:'
    )
        .order([9, 'desc'])
        .draw();

    ////////////////////////////////////////////////////////
    // upcoming travellers
    if ($.fn.DataTable.isDataTable('#upcoming_travellers_table')) {
        $('#upcoming_travellers_table').DataTable().clear().destroy();
    }

    var upcomingTravellerTable = initializeDataTable(
        '#upcoming_travellers_table',
        base_url + 'admin_travellers/upcoming_travellers_ajax',
        'Search/filter Traveller:',
        function (d) {
            d.destination = $('#destination_filter').val();
        }
    )
        .order([2, 'asc'])
        .draw();

    // Trigger reload when destination changes
    $('#destination_filter').on('change', function () {
        upcomingTravellerTable.ajax.reload();
    });

    /////////////////////////////////////////////////////////
    // approved travellers
    if ($.fn.DataTable.isDataTable('#approved_travellers_table')) {
        $('#approved_travellers_table').DataTable().clear().destroy();
    }

    var travellerTable = initializeDataTable(
        '#approved_travellers_table',
        base_url + 'admin_travellers/approved_travellers_ajax',
        'Search/filter Traveller:',
        function (d) {
            d.destination = $('#destination_filter').val();
        }
    )
        .order([2, 'desc'])
        .draw();

    $('#destination_filter').on('change', function () {
        travellerTable.ajax.reload();
    });

    if ($('#arrivals_travellers_table').length) {
        var arrivalsTravellerTable = initializeDataTable(
            '#arrivals_travellers_table',
            base_url + 'shipping/arrivals_ajax',
            'Search/filter arrivals:',
            function (d) {
                d.destination = $('#arrivals_destination_filter').val();
                d.lifecycle = $('#arrivals_lifecycle_filter').val();
            }
        )
            .order([1, 'desc'])
            .draw();

        $('#arrivals_destination_filter, #arrivals_lifecycle_filter').on('change', function () {
            arrivalsTravellerTable.ajax.reload();
        });
    }

    /////////////////////////////////////////////////////////

    initializeDataTable(
        '#pending_travellers_table',
        base_url + 'admin_travellers/pending_travellers_ajax',
        'Search/filter Traveller:'
    );

    /////////////////////////////////////////////////////////

    initializeDataTable(
        '#unapproved_travellers_table',
        base_url + 'admin_travellers/unapproved_travellers_ajax',
        'Search/filter Traveller:'
    );

    /////////////////////////////////////////////////////////

    initializeDataTable(
        '#bookings_table',
        base_url + 'admin_bookings/all_bookings_ajax',
        'Search/filter bookings:'
    )
        .order([1, 'desc'])
        .draw();

    /////////////////////////////////////////////////////////

    initializeDataTable(
        '#completed_bookings_table',
        base_url + 'admin_bookings/completed_bookings_ajax',
        'Search/filter bookings:'
    )
        .order([1, 'desc'])
        .draw();

    /////////////////////////////////////////////////////////

    initializeDataTable(
        '#canceled_bookings_table',
        base_url + 'admin_bookings/canceled_bookings_ajax',
        'Search/filter bookings:'
    )
        .order([1, 'desc'])
        .draw();

    /////////////////////////////////////////////////////////

    initializeDataTable(
        '#exchange_table',
        base_url + 'admin_exchange/all_exchange_rates',
        'Search/filter rates:'
    )
        .order([1, 'asc'])
        .draw();

    /////////////////////////////////////////////////////////
    // FINANCE (GBP)
    // ------------------------------------------------------
    if ($.fn.DataTable.isDataTable('#finances_table')) {
        $('#finances_table').DataTable().clear().destroy();
    }

    var gbpTable = initializeDataTable(
        '#finances_table',
        base_url + 'admin_finances/all_finances_ajax',
        'Search/filter Finance:',
        function (d) {
            d.month = $('#month_filter_gbp').val();
            d.year = $('#year_filter_gbp').val();
            d.route = $('#route_filter_gbp').val(); // UPDATED: route
        }
    )
        .order([])
        .draw();

    // Trigger reload on filter change
    $('#month_filter_gbp, #year_filter_gbp, #route_filter_gbp').on(
        'change',
        function () {
            gbpTable.ajax.reload(null, true);
        }
    );

    /////////////////////////////////////////////////////////
    // CAD FINANCE
    // ------------------------------------------------------
    if ($.fn.DataTable.isDataTable('#finances_cad_table')) {
        $('#finances_cad_table').DataTable().clear().destroy();
    }

    var cadTable = initializeDataTable(
        '#finances_cad_table',
        base_url + 'admin_finances/all_cad_finances_ajax',
        'Search/filter Finance:',
        function (d) {
            d.month = $('#month_filter_cad').val();
            d.year = $('#year_filter_cad').val();
            d.route = $('#route_filter_cad').val(); // UPDATED: route
        }
    )
        .order([])
        .draw();

    // Trigger reload on filter change
    $('#month_filter_cad, #year_filter_cad, #route_filter_cad').on(
        'change',
        function () {
            cadTable.ajax.reload(null, true);
        }
    );

    // Trumbowyg Text Editor
    $(document).ready(function () {
        if ($('#email_message').length) {
            $('#email_message').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['formatting'],
                    ['bold', 'italic', 'underline', 'del'],
                    [
                        'justifyLeft',
                        'justifyCenter',
                        'justifyRight',
                        'justifyFull',
                    ],
                    ['unorderedList', 'orderedList'],
                    ['link'],
                    ['removeformat'],
                    ['fullscreen'],
                ],
            });
        }

        if ($('#email_messages').length) {
            $('#email_messages').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['formatting'],
                    ['bold', 'italic', 'underline', 'del'],
                    [
                        'justifyLeft',
                        'justifyCenter',
                        'justifyRight',
                        'justifyFull',
                    ],
                    ['unorderedList', 'orderedList'],
                    ['link'],
                    ['removeformat'],
                    ['fullscreen'],
                ],
            });
        }
    });

    // -----------------------------------------------------------------
    // OFFLINE BOOKING MODAL SCRIPT
    // -----------------------------------------------------------------

    $(document).on('shown.bs.modal', '.modal', function () {
        var selectElement = $(this).find('.select2-user');
        if (selectElement.length > 0) {
            if (!selectElement.hasClass('select2-hidden-accessible')) {
                selectElement.select2({
                    placeholder: 'Search and select user...',
                    allowClear: true,
                    dropdownParent: $(this).find('.modal-content'),
                });
            }
        }

        if (typeof window.initSmbPhoneInputs === 'function') {
            window.initSmbPhoneInputs(this);
        }
    });

    function syncModalPhoneInputs(modal) {
        if (typeof window.initSmbPhoneInputs === 'function') {
            window.initSmbPhoneInputs(modal[0]);
        }
    }

    function setAutofillPhone(modal, type, userData) {
        var countryCode = userData.phone_country_code || '+44';
        var localNumber = userData.phone_local_number || userData.phone || '';

        modal.find('select[name="' + type + '_country_code"]').val(countryCode);
        modal.find('input[name="' + type + '_phone"]').val(localNumber);
        syncModalPhoneInputs(modal);
    }

    function clearAutofillFields(modal, type) {
        modal.find('input[name="' + type + '_name"]').val('');
        modal.find('input[name="' + type + '_email"]').val('');
        modal.find('input[name="' + type + '_phone"]').val('');
        modal.find('select[name="' + type + '_country_code"]').val('+44');
        modal.find('input[name="' + type + '_address"]').val('');
        modal.find('input[name="' + type + '_locality"]').val('');
        modal.find('input[name="' + type + '_postcode"]').val('');
        syncModalPhoneInputs(modal);
        modal
            .find('input[name^="' + type + '_"], select[name^="' + type + '_"]')
            .trigger('input');
    }

    $(document).on('change', '.select2-user', function () {
        var userId = $(this).val();
        var modal = $(this).closest('.modal');

        modal.data('smb-user-details', null);
        modal
            .find('.autofill-agent, .autofill-receiver')
            .prop('checked', false);
        clearAutofillFields(modal, 'agent');
        clearAutofillFields(modal, 'receiver');

        if (userId) {
            $.ajax({
                url: base_url + 'admin_travellers/get_user_details/' + userId,
                type: 'GET',
                dataType: 'json',
                beforeSend: function () {
                    console.log('Fetching user data...');
                },
                success: function (data) {
                    modal.data('smb-user-details', data);
                },
                error: function (xhr, status, error) {
                    console.error('Failed to fetch user details:', error);
                    modal.data('smb-user-details', null);
                },
            });
        }
    });

    $(document).on('change', '.autofill-agent', function () {
        var modal = $(this).closest('.modal');
        var userData = modal.data('smb-user-details');

        if ($(this).is(':checked')) {
            if (userData) {
                modal.find('input[name="agent_name"]').val(userData.fullname);
                modal.find('input[name="agent_email"]').val(userData.email);
                setAutofillPhone(modal, 'agent', userData);
                modal.find('input[name="agent_address"]').val(userData.address);
                modal.find('input[name="agent_locality"]').val(userData.city);
                modal
                    .find('input[name="agent_postcode"]')
                    .val(userData.postal_code);
                modal
                    .find('input[name^="agent_"], select[name^="agent_"]')
                    .trigger('input');
            } else {
                alert('Please select an SMB User first.');
                $(this).prop('checked', false);
            }
        } else {
            clearAutofillFields(modal, 'agent');
        }
    });

    $(document).on('change', '.autofill-receiver', function () {
        var modal = $(this).closest('.modal');
        var userData = modal.data('smb-user-details');

        if ($(this).is(':checked')) {
            if (userData) {
                modal
                    .find('input[name="receiver_name"]')
                    .val(userData.fullname);
                modal.find('input[name="receiver_email"]').val(userData.email);
                setAutofillPhone(modal, 'receiver', userData);
                modal
                    .find('input[name="receiver_address"]')
                    .val(userData.address);
                modal
                    .find('input[name="receiver_locality"]')
                    .val(userData.city);
                modal
                    .find('input[name="receiver_postcode"]')
                    .val(userData.postal_code);
                modal
                    .find('input[name^="receiver_"], select[name^="receiver_"]')
                    .trigger('input');
            } else {
                alert('Please select an SMB User first.');
                $(this).prop('checked', false);
            }
        } else {
            clearAutofillFields(modal, 'receiver');
        }
    });

    $(document).ready(function () {
        $('#populateDropAddress').change(function () {
            if ($(this).is(':checked')) {
                var currentAddress = $('input[name="address"]').val();
                $('input[name="drop_address1"]').val(currentAddress);
            } else {
                $('input[name="drop_address1"]').val('');
            }
        });
    });

    $(document).ready(function () {
        $('#populateDropAddress2').change(function () {
            if ($(this).is(':checked')) {
                var currentAddress = $('input[name="address"]').val();
                $('input[name="drop_address2"]').val(currentAddress);
            } else {
                $('input[name="drop_address2"]').val('');
            }
        });
    });

    var selectedRemoveIndex = null;

    /* ================================================================
       ON PAGE LOAD: Check sessionStorage for a pending success message
       Show it as a dismissible banner at the top of the content area,
       then clear it so it doesn't show again on subsequent reloads.
    ================================================================ */
    $(function () {
        var pendingMsg = sessionStorage.getItem('parcel_success_msg');
        if (pendingMsg) {
            sessionStorage.removeItem('parcel_success_msg');

            var $banner = $(
                '<div class="alert alert-success alert-dismissible" id="parcel_success_banner" role="alert" style="margin-bottom:16px;">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>' +
                    '<i class="las la-check-circle me-2"></i> ' +
                    $('<div>').text(pendingMsg).html() +
                    '</div>'
            );

            // Insert at the top of the main content panel, after existing flash messages
            $('.x_content').prepend($banner);

            // Auto-dismiss after 5 seconds
            setTimeout(function () {
                $banner.fadeOut(400, function () {
                    $(this).remove();
                });
            }, 5000);
        }
    });

    /* ================================================================
       OPEN ADD PARCEL MODAL
    ================================================================ */
    window.openAddParcelModal = function (bookingId) {
        bookingId = parseInt(bookingId, 10) || 0;
        selectedRemoveIndex = null;
        if (!bookingId) {
            return;
        }
        $('#add_booking_id').val(bookingId);
        $('#add_item_name').val('');
        $('#add_category').val('');
        $('#add_item_size').val('');
        $('#add_notes').val('');
        $('#add_parcel_error').addClass('d-none').text('');
        $('#add_size_label').text('Size (KG) *');
        $('#addParcelModal').modal('show');
    };

    // Update size label when category changes
    $(document).on('change', '#add_category', function () {
        var label =
            $(this).val() === 'Documents/Small Electronics' || $(this).val() === 'Laptop' || $(this).val() === 'Documents/Electronics' || $(this).val() === 'Gold'
                ? 'Quantity (PC) *'
                : 'Size (KG) *';
        $('#add_size_label').text(label);
    });

    /* ================================================================
       OPEN REMOVE PARCEL MODAL
    ================================================================ */
    window.openRemoveParcelModal = function (bookingId, itemsJson) {
        bookingId = parseInt(bookingId, 10) || 0;
        selectedRemoveIndex = null;
        if (!bookingId) {
            return;
        }
        $('#remove_booking_id').val(bookingId);
        $('#remove_notes').val('');
        $('#remove_parcel_error').addClass('d-none').text('');
        $('#confirmRemoveParcel').prop('disabled', true);

        var items = [];
        try {
            items = JSON.parse(itemsJson);
        } catch (e) {
            items = [];
        }

        var html = '';
        if (!items || !items.length) {
            html = '<p class="text-muted">No items found on this booking.</p>';
        } else {
            $.each(items, function (index, item) {
                var name = $('<div>')
                    .text(item.item_name || item.name || '')
                    .html();
                var category = $('<div>')
                    .text(item.category || '')
                    .html();
                var size = (item.size || 0) + (item.unit || 'KG');
                html +=
                    '<div class="form-check border rounded p-2 mb-2 remove-item-option" data-index="' +
                    index +
                    '">' +
                    '<input class="form-check-input" type="radio" name="remove_item_radio" id="ri_' +
                    index +
                    '" value="' +
                    index +
                    '">' +
                    '<label class="form-check-label w-100" for="ri_' +
                    index +
                    '" style="cursor:pointer;">' +
                    '<strong>' +
                    name +
                    '</strong> &mdash; ' +
                    category +
                    ' &mdash; ' +
                    size +
                    '</label>' +
                    '</div>';
            });
        }
        $('#remove_items_list').html(html);

        $(document)
            .off('change', 'input[name="remove_item_radio"]')
            .on('change', 'input[name="remove_item_radio"]', function () {
                selectedRemoveIndex = parseInt($(this).val());
                $('.remove-item-option').removeClass('is-selected');
                $(this).closest('.remove-item-option').addClass('is-selected');
                $('#confirmRemoveParcel').prop('disabled', false);
            });

        $('#removeParcelModal').modal('show');
    };

    /* ================================================================
       CONFIRM ADD PARCEL
       On success: store message in sessionStorage, reload page.
       On error: show inline error in modal, do NOT reload.
    ================================================================ */
    $(document).on('click', '#confirmAddParcel', function () {
        var bookingId = parseInt($('#add_booking_id').val(), 10) || 0;
        var itemName = $.trim($('#add_item_name').val());
        var category = $('#add_category').val();
        var itemSize = parseFloat($('#add_item_size').val());
        var notes = $.trim($('#add_notes').val());
        var errBox = $('#add_parcel_error');

        errBox.addClass('d-none').text('');

        if (!bookingId) {
            errBox
                .text('Could not detect the selected booking. Close the modal and try again.')
                .removeClass('d-none');
            return;
        }

        if (!itemName || !category || !itemSize || itemSize <= 0) {
            errBox
                .text('Please fill in all required fields with valid values.')
                .removeClass('d-none');
            return;
        }

        var $btn = $(this)
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin me-1"></i> Adding...');

        $.ajax({
            url: base_url + 'admin_bookings/add_parcel_ajax',
            type: 'POST',
            data: {
                booking_id: bookingId,
                item_name: itemName,
                category: category,
                item_size: itemSize,
                notes: notes,
                q2r_secure: getCsrfHash(),
            },
            success: function (response) {
                var res;
                try {
                    res = JSON.parse(response);
                } catch (e) {
                    res = { status: false, msg: 'Invalid server response.' };
                }

                updateCsrfHash(res.csrf_hash);

                if (res.status) {
                    // Store success message and reload
                    sessionStorage.setItem(
                        'parcel_success_msg',
                        res.msg ||
                            'Parcel added successfully. Traveller has been notified.'
                    );
                    $('#addParcelModal').modal('hide');
                    location.reload();
                } else {
                    // Stay in modal, show error
                    $btn.prop('disabled', false).html(
                        '<i class="las la-plus me-1"></i> Add Parcel'
                    );
                    errBox
                        .text(
                            res.msg || 'Something went wrong. Please try again.'
                        )
                        .removeClass('d-none');
                }
            },
            error: function (xhr) {
                var res = null;
                try {
                    res = JSON.parse(xhr.responseText);
                } catch (e) {
                    res = null;
                }

                if (res && res.csrf_hash) {
                    updateCsrfHash(res.csrf_hash);
                }

                $btn.prop('disabled', false).html(
                    '<i class="las la-plus me-1"></i> Add Parcel'
                );
                errBox
                    .text(
                        (res && res.msg) || 'Server error. Please try again.'
                    )
                    .removeClass('d-none');
            },
        });
    });

    /* ================================================================
       CONFIRM REMOVE PARCEL
       On success: store message in sessionStorage, reload page.
       On error: show inline error in modal, do NOT reload.
    ================================================================ */
    $(document).on('click', '#confirmRemoveParcel', function () {
        if (selectedRemoveIndex === null) return;

        var bookingId = parseInt($('#remove_booking_id').val(), 10) || 0;
        var notes = $.trim($('#remove_notes').val());
        var errBox = $('#remove_parcel_error');

        errBox.addClass('d-none').text('');

        if (!bookingId) {
            errBox
                .text('Could not detect the selected booking. Close the modal and try again.')
                .removeClass('d-none');
            return;
        }

        var $btn = $(this)
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin me-1"></i> Removing...');

        $.ajax({
            url: base_url + 'admin_bookings/remove_parcel_ajax',
            type: 'POST',
            data: {
                booking_id: bookingId,
                item_index: selectedRemoveIndex,
                notes: notes,
                q2r_secure: getCsrfHash(),
            },
            success: function (response) {
                var res;
                try {
                    res = JSON.parse(response);
                } catch (e) {
                    res = { status: false, msg: 'Invalid server response.' };
                }

                updateCsrfHash(res.csrf_hash);

                if (res.status) {
                    // Store success message and reload
                    sessionStorage.setItem(
                        'parcel_success_msg',
                        res.msg ||
                            'Parcel removed successfully. Traveller has been notified.'
                    );
                    $('#removeParcelModal').modal('hide');
                    location.reload();
                } else {
                    // Stay in modal, show error
                    $btn.prop('disabled', false).html(
                        '<i class="las la-minus me-1"></i> Remove Selected'
                    );
                    errBox
                        .text(
                            res.msg || 'Something went wrong. Please try again.'
                        )
                        .removeClass('d-none');
                }
            },
            error: function (xhr) {
                var res = null;
                try {
                    res = JSON.parse(xhr.responseText);
                } catch (e) {
                    res = null;
                }

                if (res && res.csrf_hash) {
                    updateCsrfHash(res.csrf_hash);
                }

                $btn.prop('disabled', false).html(
                    '<i class="las la-minus me-1"></i> Remove Selected'
                );
                errBox
                    .text(
                        (res && res.msg) || 'Server error. Please try again.'
                    )
                    .removeClass('d-none');
            },
        });
    });

    /* ================================================================
       CONTROLLED PARCEL CANCELLATION
       Super Admin only. Errors remain inside the modal; reload happens
       only after the server commits the cancellation and audit entry.
    ================================================================ */
    function setParcelInlineError(fieldSelector, errorSelector, isValid, message, showAll) {
        var $field = $(fieldSelector);
        var $error = $(errorSelector);
        var shouldShow = !isValid && (showAll || $field.data('validation-touched'));

        $field.attr('aria-invalid', isValid ? 'false' : 'true');
        $field.closest('.admin-parcel-field').toggleClass('has-error', shouldShow);
        $error.toggleClass('d-none', !shouldShow).text(shouldShow ? message : '');
    }

    function validateCancelParcel(showAll) {
        var bookingId = parseInt($('#cancel_booking_id').val(), 10) || 0;
        var reason = $.trim($('#cancellation_reason').val());
        var refundStatus = String($('#cancel_refund_status').val() || '');
        var refundReference = $.trim($('#cancel_refund_reference').val());
        var refundAmount = refundStatus === 'not_required'
            ? 0
            : parseFloat($('#cancel_refund_amount').val());
        var confirmed = $('#confirm_cancel_parcel').is(':checked');
        var validStatuses = ['refunded', 'not_required'];
        var reasonValid = reason.length >= 5 && reason.length <= 500;
        var statusValid = validStatuses.indexOf(refundStatus) !== -1;
        var amountValid = refundStatus === 'not_required' || (!isNaN(refundAmount) && refundAmount >= 0);
        var referenceValid = refundStatus !== 'refunded' || refundReference.length > 0;

        setParcelInlineError('#cancellation_reason', '#cancellation_reason_error', reasonValid, 'Enter a cancellation reason between 5 and 500 characters.', showAll);
        setParcelInlineError('#cancel_refund_status', '#cancel_refund_status_error', statusValid, 'Select a valid refund status.', showAll);
        setParcelInlineError('#cancel_refund_amount', '#cancel_refund_amount_error', amountValid, 'Enter a valid refund amount.', showAll);
        setParcelInlineError('#cancel_refund_reference', '#cancel_refund_reference_error', referenceValid, 'Enter the manual refund reference.', showAll);
        setParcelInlineError('#confirm_cancel_parcel', '#confirm_cancel_parcel_error', confirmed, 'Confirm that you want to cancel this parcel.', showAll);

        return bookingId > 0 && reasonValid && statusValid && amountValid && referenceValid && confirmed;
    }

    function syncCancelParcelSubmitState() {
        $('#confirmCancelParcel').prop('disabled', !validateCancelParcel(false));
    }

    $(document).on('click', '.open-cancel-parcel', function (event) {
        event.preventDefault();

        var $trigger = $(this);
        var bookingId = parseInt($trigger.data('booking-id'), 10) || 0;
        var reference = String($trigger.data('booking-reference') || ('#' + bookingId));
        var amount = parseFloat($trigger.data('refund-amount'));
        var currency = String($trigger.data('currency') || '').toUpperCase();

        if (!bookingId) {
            return;
        }

        $('#cancel_booking_id').val(bookingId);
        $('#cancel_parcel_reference').text(reference);
        $('#cancellation_reason').val('');
        $('#cancel_refund_status').val('');
        $('#cancel_refund_amount')
            .val(isNaN(amount) ? '0.00' : amount.toFixed(2))
            .prop('disabled', false);
        $('#cancel_refund_currency').text(currency || 'Currency');
        $('#cancel_refund_reference').val('');
        $('#cancel_refund_reference_group').hide();
        $('#confirm_cancel_parcel').prop('checked', false);
        $('#cancelParcelModal').find('input, select, textarea').removeData('validation-touched').attr('aria-invalid', 'false');
        $('#cancelParcelModal').find('.admin-parcel-field').removeClass('has-error');
        $('#cancelParcelModal').find('.admin-form-field-error').addClass('d-none').text('');
        $('#cancel_parcel_error').addClass('d-none').text('');
        $('#confirmCancelParcel').prop('disabled', true).html('<i class="las la-times"></i> Cancel Parcel');

        var $actionModal = $trigger.closest('.modal');
        if ($actionModal.length) {
            $actionModal.modal('hide');
            setTimeout(function () {
                $('#cancelParcelModal').modal('show');
            }, 200);
        } else {
            $('#cancelParcelModal').modal('show');
        }
    });

    $(document).on('change', '#cancel_refund_status', function () {
        var status = $(this).val();
        var requiresReference = status === 'refunded';
        $('#cancel_refund_reference_group').toggle(requiresReference);
        $('#cancel_refund_reference').prop('required', requiresReference);

        if (status === 'not_required') {
            $('#cancel_refund_amount').val('0.00').prop('disabled', true);
            $('#cancel_refund_reference').val('');
        } else {
            $('#cancel_refund_amount').prop('disabled', false);
        }
        syncCancelParcelSubmitState();
    });

    $(document).on('input', '#cancellation_reason, #cancel_refund_amount, #cancel_refund_reference', syncCancelParcelSubmitState);
    $(document).on('change', '#confirm_cancel_parcel', function () {
        $(this).data('validation-touched', true);
        syncCancelParcelSubmitState();
    });
    $(document).on('blur', '#cancellation_reason, #cancel_refund_amount, #cancel_refund_reference', function () {
        $(this).data('validation-touched', true);
        validateCancelParcel(false);
    });

    $(document).on('click', '#confirmCancelParcel', function () {
        var bookingId = parseInt($('#cancel_booking_id').val(), 10) || 0;
        var reason = $.trim($('#cancellation_reason').val());
        var refundStatus = $('#cancel_refund_status').val();
        var refundReference = $.trim($('#cancel_refund_reference').val());
        var refundAmount = refundStatus === 'not_required'
            ? 0
            : parseFloat($('#cancel_refund_amount').val());
        var confirmed = $('#confirm_cancel_parcel').is(':checked');
        var $error = $('#cancel_parcel_error');

        $error.addClass('d-none').text('');

        if (!bookingId) {
            $error.text('Could not detect the selected booking. Close the modal and try again.').removeClass('d-none');
            return;
        }
        if (!validateCancelParcel(true)) {
            return;
        }

        var $button = $(this)
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin"></i> Cancelling...');

        $.ajax({
            url: base_url + 'admin_bookings/cancel_parcel_ajax',
            type: 'POST',
            data: {
                booking_id: bookingId,
                cancellation_reason: reason,
                refund_status: refundStatus,
                refund_reference: refundReference,
                refund_amount: refundAmount,
                q2r_secure: getCsrfHash(),
            },
            success: function (response) {
                var result = response;
                if (typeof response !== 'object') {
                    try {
                        result = JSON.parse(response);
                    } catch (e) {
                        result = { status: false, msg: 'Invalid server response.' };
                    }
                }

                updateCsrfHash(result.csrf_hash);

                if (result.status) {
                    sessionStorage.setItem('parcel_success_msg', result.msg || 'Parcel cancelled successfully.');
                    $('#cancelParcelModal').modal('hide');
                    location.reload();
                    return;
                }

                $button.html('<i class="las la-times"></i> Cancel Parcel');
                syncCancelParcelSubmitState();
                $error.text(result.msg || 'Unable to cancel this parcel.').removeClass('d-none');
            },
            error: function (xhr) {
                var result = null;
                try {
                    result = JSON.parse(xhr.responseText);
                } catch (e) {
                    result = null;
                }

                if (result && result.csrf_hash) {
                    updateCsrfHash(result.csrf_hash);
                }

                $button.html('<i class="las la-times"></i> Cancel Parcel');
                syncCancelParcelSubmitState();
                $error.text((result && result.msg) || 'Server error. Please try again.').removeClass('d-none');
            },
        });
    });

    /* ================================================================
       CONTROLLED PARCEL MOVE
       Loads eligible travellers from the server, then submits a confirmed
       reassignment. Validation failures remain in the modal.
    ================================================================ */
    var moveParcelContextRequest = null;

    function validateMoveParcel(showAll) {
        var bookingId = parseInt($('#move_booking_id').val(), 10) || 0;
        var hasTraveller = !!$('#move_target_traveller_id').val();
        var reason = $.trim($('#move_parcel_reason').val());
        var reasonValid = reason.length >= 5 && reason.length <= 500;
        var confirmed = $('#confirm_move_parcel').is(':checked');

        setParcelInlineError('#move_target_traveller_id', '#move_target_traveller_error', hasTraveller, 'Select a destination traveller.', showAll);
        setParcelInlineError('#move_parcel_reason', '#move_parcel_reason_error', reasonValid, 'Enter a move reason between 5 and 500 characters.', showAll);
        setParcelInlineError('#confirm_move_parcel', '#confirm_move_parcel_error', confirmed, 'Confirm that you want to move this parcel.', showAll);

        return bookingId > 0 && hasTraveller && reasonValid && confirmed;
    }

    function syncMoveParcelSubmitState() {
        $('#confirmMoveParcel').prop('disabled', !validateMoveParcel(false));
    }

    function showMoveParcelModal(bookingId, reference, successUrl) {
        $('#move_booking_id').val(bookingId);
        $('#moveParcelModal').data('success-url', successUrl || '');
        $('#move_parcel_reference').text(reference || ('#' + bookingId));
        $('#move_parcel_reason').val('');
        $('#confirm_move_parcel').prop('checked', false);
        $('#moveParcelModal').find('input, select, textarea').removeData('validation-touched').attr('aria-invalid', 'false');
        $('#moveParcelModal').find('.admin-parcel-field').removeClass('has-error');
        $('#moveParcelModal').find('.admin-form-field-error').addClass('d-none').text('');
        $('#move_parcel_error').addClass('d-none').text('');
        $('#move_parcel_context').html('<i class="las la-spinner la-spin"></i> Loading eligible travellers...');
        $('#move_target_traveller_id')
            .empty()
            .append($('<option>').val('').text('Loading eligible travellers...'))
            .prop('disabled', true);
        $('#confirmMoveParcel').prop('disabled', true).html('<i class="las la-exchange-alt"></i> Move Parcel');
        $('#moveParcelModal').modal('show');

        if (moveParcelContextRequest) {
            moveParcelContextRequest.abort();
        }

        moveParcelContextRequest = $.ajax({
            url: base_url + 'admin_bookings/move_parcel_context_ajax/' + bookingId,
            type: 'POST',
            data: { q2r_secure: getCsrfHash() },
            success: function (response) {
                var result = response;
                if (typeof response !== 'object') {
                    try {
                        result = JSON.parse(response);
                    } catch (e) {
                        result = { status: false, msg: 'Invalid server response.' };
                    }
                }

                updateCsrfHash(result.csrf_hash);
                if (!result.status || !result.context) {
                    $('#move_parcel_context').html('<i class="las la-exclamation-circle"></i> Move unavailable');
                    $('#move_parcel_error').text(result.msg || 'Unable to load eligible travellers.').removeClass('d-none');
                    return;
                }

                var context = result.context;
                var travellers = context.eligible_travellers || [];
                var $select = $('#move_target_traveller_id').empty();
                $('#move_parcel_context').text(
                    'Current traveller: ' + context.current_traveller +
                    ' | Route: ' + context.route +
                    ' | Parcel: ' + parseFloat(context.parcel_size || 0).toFixed(2) + ' KG'
                );

                if (!travellers.length) {
                    $select.append($('<option>').val('').text('No eligible travellers available')).prop('disabled', true);
                    $('#move_parcel_error')
                        .text('No approved traveller on this route currently has enough available space.')
                        .removeClass('d-none');
                    return;
                }

                $select.append($('<option>').val('').text('Select destination traveller'));
                $.each(travellers, function (_, traveller) {
                    var label = traveller.fullname +
                        ' — ' + (traveller.travel_date_label || traveller.travel_date) +
                        ' — ' + parseFloat(traveller.available_space || 0).toFixed(2) + ' KG available';
                    $select.append($('<option>').val(traveller.id).text(label));
                });
                $select.prop('disabled', false);
                syncMoveParcelSubmitState();
            },
            error: function (xhr, status) {
                if (status === 'abort') {
                    return;
                }

                var result = null;
                try {
                    result = JSON.parse(xhr.responseText);
                } catch (e) {
                    result = null;
                }
                if (result && result.csrf_hash) {
                    updateCsrfHash(result.csrf_hash);
                }
                $('#move_parcel_context').html('<i class="las la-exclamation-circle"></i> Move unavailable');
                $('#move_parcel_error').text((result && result.msg) || 'Unable to load eligible travellers.').removeClass('d-none');
            },
            complete: function () {
                moveParcelContextRequest = null;
            },
        });
    }

    $(document).on('click', '.open-move-parcel', function (event) {
        event.preventDefault();
        var $trigger = $(this);
        var bookingId = parseInt($trigger.data('booking-id'), 10) || 0;
        var reference = String($trigger.data('booking-reference') || ('#' + bookingId));
        var successUrl = String($trigger.data('success-url') || '');
        if (!bookingId) {
            return;
        }

        var $actionModal = $trigger.closest('.modal');
        if ($actionModal.length) {
            $actionModal.modal('hide');
            setTimeout(function () {
                showMoveParcelModal(bookingId, reference, successUrl);
            }, 200);
        } else {
            showMoveParcelModal(bookingId, reference, successUrl);
        }
    });

    $(document).on('input', '#move_parcel_reason', syncMoveParcelSubmitState);
    $(document).on('change', '#move_target_traveller_id, #confirm_move_parcel', function () {
        $(this).data('validation-touched', true);
        syncMoveParcelSubmitState();
    });
    $(document).on('blur', '#move_parcel_reason', function () {
        $(this).data('validation-touched', true);
        validateMoveParcel(false);
    });

    $(document).on('click', '#confirmMoveParcel', function () {
        var bookingId = parseInt($('#move_booking_id').val(), 10) || 0;
        var targetTravellerId = parseInt($('#move_target_traveller_id').val(), 10) || 0;
        var reason = $.trim($('#move_parcel_reason').val());
        var confirmed = $('#confirm_move_parcel').is(':checked');
        var $error = $('#move_parcel_error');

        $error.addClass('d-none').text('');
        if (!bookingId) {
            $error.text('Could not detect the selected booking. Close the modal and try again.').removeClass('d-none');
            return;
        }
        if (!validateMoveParcel(true)) {
            return;
        }

        var $button = $(this)
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin"></i> Moving...');

        $.ajax({
            url: base_url + 'admin_bookings/move_parcel_ajax',
            type: 'POST',
            data: {
                booking_id: bookingId,
                target_traveller_id: targetTravellerId,
                move_reason: reason,
                q2r_secure: getCsrfHash(),
            },
            success: function (response) {
                var result = response;
                if (typeof response !== 'object') {
                    try {
                        result = JSON.parse(response);
                    } catch (e) {
                        result = { status: false, msg: 'Invalid server response.' };
                    }
                }

                updateCsrfHash(result.csrf_hash);
                if (result.status) {
                    sessionStorage.setItem('parcel_success_msg', result.msg || 'Parcel moved successfully.');
                    $('#moveParcelModal').modal('hide');
                    var successUrl = String($('#moveParcelModal').data('success-url') || '');
                    if (successUrl) {
                        window.location.href = successUrl;
                    } else {
                        location.reload();
                    }
                    return;
                }

                $button.html('<i class="las la-exchange-alt"></i> Move Parcel');
                syncMoveParcelSubmitState();
                $error.text(result.msg || 'Unable to move this parcel.').removeClass('d-none');
            },
            error: function (xhr) {
                var result = null;
                try {
                    result = JSON.parse(xhr.responseText);
                } catch (e) {
                    result = null;
                }
                if (result && result.csrf_hash) {
                    updateCsrfHash(result.csrf_hash);
                }
                $button.html('<i class="las la-exchange-alt"></i> Move Parcel');
                syncMoveParcelSubmitState();
                $error.text((result && result.msg) || 'Server error. Please try again.').removeClass('d-none');
            },
        });
    });

    /////////////////////////////////////////////////////////
    // SHIPPING RECORDS
    // ------------------------------------------------------
    var shippingRecordsTable = null;
    var shippingSelectedContext = null;

    function parseJsonResponse(response) {
        if (typeof response === 'object') {
            return response;
        }

        try {
            return JSON.parse(response);
        } catch (e) {
            return null;
        }
    }

    function shippingSetStep(step) {
        var mode = $('#shipping_mode').val() || 'create';
        $('[data-step-indicator]').removeClass('is-active');
        $('[data-step-panel]').addClass('d-none');
        $('[data-step-indicator="' + step + '"]').addClass('is-active');
        $('[data-step-panel="' + step + '"]').removeClass('d-none');

        if (step === 1) {
            $('#shipping_back_btn').addClass('d-none');
            $('#shipping_next_btn').removeClass('d-none');
            $('#shipping_submit_btn').addClass('d-none');
        } else {
            if (mode === 'edit') {
                $('#shipping_back_btn').addClass('d-none');
            } else {
                $('#shipping_back_btn').removeClass('d-none');
            }
            $('#shipping_next_btn').addClass('d-none');
            $('#shipping_submit_btn').removeClass('d-none');
        }
    }

    function shippingResetMessages() {
        $('#shipping_modal_error, #shipping_modal_success')
            .addClass('d-none')
            .text('');
    }

    function shippingRenderSelectedContext(context) {
        shippingSelectedContext = context || null;
        if (!context) {
            $('#shipping_selected_context').html(
                '<div class="admin-shipping-context__tracking">No booking selected yet.</div>'
            );
            return;
        }

        $('#shipping_selected_context').html(
            '<div class="admin-shipping-context__tracking"><strong>Booking Reference:</strong> ' +
                $('<div>').text(context.tracking_id || '').html() +
                '</div>' +
                '<div class="admin-shipping-context__meta">' +
                '<span><strong>User:</strong> ' + $('<div>').text(context.user || '').html() + '</span>' +
                '<span><strong>Traveller:</strong> ' + $('<div>').text(context.traveller || '').html() + '</span>' +
                '</div>'
        );
    }

    function shippingRenderStatusOptions(currentStatus, mode, providedOptions, selector) {
        var $select = $(selector || '#shipping_status');
        var creationOptions = ['Awaiting Collection', 'In Transit', 'Completed'];
        var transitionOptions = {
            'Awaiting Collection': ['In Transit', 'Completed'],
            'In Transit': ['Completed'],
            'Completed': [],
        };
        var options = mode === 'create'
            ? creationOptions
            : (providedOptions || transitionOptions[currentStatus] || []);

        $select.empty().prop('disabled', false);
        if (!options.length) {
            $select
                .append($('<option>').val(currentStatus).text(currentStatus + ' (final)'))
                .val(currentStatus)
                .prop('disabled', true);
            return;
        }

        if (mode === 'edit') {
            $select.append($('<option>').val('').text('Select the next status'));
        }

        $.each(options, function (_, status) {
            $select.append($('<option>').val(status).text(status));
        });

        if (mode === 'create') {
            $select.val(currentStatus || 'Awaiting Collection');
        }
    }

    function shippingApplyContext(context) {
        $('#shipping_booking_id').val(context.booking_id || 0);
        $('#shipping_carrier_tracking_id').val(context.carrier_tracking_id || '');
        $('#shipping_pickup_address').val(context.pickup_address || '');
        $('#shipping_dropoff_address').val(context.dropoff_address || '');
        $('#shipping_pickup_country').val(context.pickup_country || '');
        $('#shipping_courier').val(context.courier || 'DHL').trigger('change.select2');
        $('#shipping_staff_admin_id')
            .val(context.staff_admin_id || '')
            .trigger('change.select2');
        shippingRenderStatusOptions(
            context.status || 'Awaiting Collection',
            $('#shipping_mode').val() || 'create',
            context.status_next_options || null,
            '#shipping_status'
        );
        shippingRenderSelectedContext(context);
    }

    function shippingOpenModal(mode, bookingId) {
        bookingId = parseInt(bookingId, 10) || 0;
        shippingResetMessages();
        $('#shipping_mode').val(mode);
        $('#shipping_booking_id').val(bookingId);
        $('#shipping_search_query').val('');
        $('#shipping_carrier_tracking_id').val('');
        $('#shipping_tracking_note').val('');
        $('#shipping_search_results tbody').html(
            '<tr><td colspan="6" class="text-center text-muted">Search for a booking to continue.</td></tr>'
        );

        if (mode === 'edit') {
            $('#shippingModalTitle').text('Edit Shipping Record');
        } else if (bookingId) {
            $('#shippingModalTitle').text('Book Shipping');
        } else {
            $('#shippingModalTitle').text('Create Shipping Record');
        }

        if (bookingId) {
            $.ajax({
                url: base_url + 'shipping/shipping_context_ajax/' + bookingId,
                type: 'POST',
                data: { q2r_secure: getCsrfHash() },
                success: function (response) {
                    var res = parseJsonResponse(response) || {};
                    updateCsrfHash(res.csrf_hash);

                    if (!res.status || !res.context) {
                        $('#shipping_modal_error')
                            .text(res.msg || 'Unable to load the booking context.')
                            .removeClass('d-none');
                        return;
                    }

                    shippingApplyContext(res.context);
                    shippingSetStep(2);
                    $('#manageShippingModal').modal('show');
                },
                error: function (xhr) {
                    var res = parseJsonResponse(xhr.responseText) || {};
                    updateCsrfHash(res.csrf_hash);
                    $('#shipping_modal_error')
                        .text(res.msg || 'Unable to load the booking context.')
                        .removeClass('d-none');
                    $('#manageShippingModal').modal('show');
                },
            });
            return;
        }

        shippingRenderSelectedContext(null);
        shippingSetStep(1);
        $('#manageShippingModal').modal('show');
    }

    function shippingReloadViews() {
        if (shippingRecordsTable) {
            shippingRecordsTable.ajax.reload(null, false);
        } else {
            location.reload();
        }
    }

    if ($('#shipping_records_table').length) {
        shippingRecordsTable = initializeDataTable(
            '#shipping_records_table',
            base_url + 'shipping/records_ajax',
            'Search/filter shipping:'
        )
            .order([10, 'desc'])
            .draw();
    }

    $(document).on('shown.bs.modal', '#manageShippingModal, #shippingStatusModal', function () {
        var $modal = $(this);
        $modal.find('#shipping_courier, #shipping_staff_admin_id').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $modal.find('.modal-content'),
                });
            }
        });
    });

    $(document).on('click', '.open-create-shipping', function () {
        shippingOpenModal('create', $(this).data('booking-id'));
    });

    $(document).on('click', '.open-edit-shipping', function () {
        shippingOpenModal('edit', $(this).data('booking-id'));
    });

    $(document).on('click', '#shipping_search_btn', function () {
        var query = $.trim($('#shipping_search_query').val());
        if (!query) {
            $('#shipping_modal_error')
                .text('Enter a booking search term first.')
                .removeClass('d-none');
            return;
        }

        shippingResetMessages();
        $('#shipping_search_results tbody').html(
            '<tr><td colspan="6" class="text-center text-muted">Searching bookings...</td></tr>'
        );

        $.ajax({
            url: base_url + 'shipping/search_bookings_ajax',
            type: 'POST',
            data: {
                query: query,
                q2r_secure: getCsrfHash(),
            },
            success: function (response) {
                var res = parseJsonResponse(response) || {};
                updateCsrfHash(res.csrf_hash);

                var html = '';
                if (!res.status || !res.results || !res.results.length) {
                    html =
                        '<tr><td colspan="6" class="text-center text-muted">No matching bookings found.</td></tr>';
                } else {
                    $.each(res.results, function (_, row) {
                        var stateBadge = row.shipping_exists
                            ? '<span class="badge badge-info">Shipping Exists</span>'
                            : '<span class="badge badge-warning">Needs Setup</span>';
                        html +=
                            '<tr>' +
                            '<td><code>' + $('<div>').text(row.tracking_id || '').html() + '</code></td>' +
                            '<td>' + $('<div>').text(row.user || '').html() + '</td>' +
                            '<td>' + $('<div>').text(row.traveller || '').html() + '</td>' +
                            '<td>' + $('<div>').text(row.pickup_address || '').html() + '</td>' +
                            '<td>' + stateBadge + '</td>' +
                            '<td><button type="button" class="btn btn-xs btn-primary select-shipping-booking" data-booking-id="' + row.booking_id + '" data-mode="' + (row.shipping_exists ? 'edit' : 'create') + '">' + (row.shipping_exists ? 'Edit' : 'Select') + '</button></td>' +
                            '</tr>';
                    });
                }

                $('#shipping_search_results tbody').html(html);
            },
            error: function (xhr) {
                var res = parseJsonResponse(xhr.responseText) || {};
                updateCsrfHash(res.csrf_hash);
                $('#shipping_search_results tbody').html(
                    '<tr><td colspan="6" class="text-center text-danger">Search failed. Try again.</td></tr>'
                );
            },
        });
    });

    $(document).on('keypress', '#shipping_search_query', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#shipping_search_btn').trigger('click');
        }
    });

    $(document).on('click', '.select-shipping-booking', function () {
        shippingOpenModal($(this).data('mode') || $('#shipping_mode').val() || 'create', $(this).data('booking-id'));
    });

    $(document).on('click', '#shipping_next_btn', function () {
        if (!(parseInt($('#shipping_booking_id').val(), 10) || 0)) {
            $('#shipping_modal_error')
                .text('Select a booking before continuing.')
                .removeClass('d-none');
            return;
        }

        shippingSetStep(2);
    });

    $(document).on('click', '#shipping_back_btn', function () {
        shippingSetStep(1);
    });

    $(document).on('click', '#shipping_submit_btn', function () {
        var bookingId = parseInt($('#shipping_booking_id').val(), 10) || 0;
        var mode = $('#shipping_mode').val() || 'create';
        var payload = {
            booking_id: bookingId,
            carrier_tracking_id: $.trim($('#shipping_carrier_tracking_id').val()),
            pickup_address: $.trim($('#shipping_pickup_address').val()),
            dropoff_address: $.trim($('#shipping_dropoff_address').val()),
            pickup_country: $.trim($('#shipping_pickup_country').val()),
            courier: $('#shipping_courier').val(),
            staff_admin_id: $('#shipping_staff_admin_id').val(),
            status: $('#shipping_status').val(),
            tracking_note: $.trim($('#shipping_tracking_note').val()),
            q2r_secure: getCsrfHash(),
        };

        if (!bookingId) {
            $('#shipping_modal_error')
                .text('Select a booking before saving.')
                .removeClass('d-none');
            return;
        }
        if (!payload.carrier_tracking_id) {
            $('#shipping_modal_error')
                .text('Enter the carrier tracking ID before saving.')
                .removeClass('d-none');
            return;
        }
        if (!payload.status) {
            $('#shipping_modal_error')
                .text('Select the next shipping status before saving.')
                .removeClass('d-none');
            return;
        }

        var url =
            mode === 'edit'
                ? base_url + 'shipping/edit_shipping_ajax/' + bookingId
                : base_url + 'shipping/create_shipping_ajax';

        var $btn = $(this)
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin"></i> Saving...');

        shippingResetMessages();

        $.ajax({
            url: url,
            type: 'POST',
            data: payload,
            success: function (response) {
                var res = parseJsonResponse(response) || {};
                updateCsrfHash(res.csrf_hash);
                $btn.prop('disabled', false).html('<i class="las la-save"></i> Save Shipping');

                if (!res.status) {
                    $('#shipping_modal_error')
                        .text(res.msg || 'Unable to save shipping details.')
                        .removeClass('d-none');
                    return;
                }

                $('#shipping_modal_success')
                    .text(res.msg || 'Shipping saved successfully.')
                    .removeClass('d-none');

                setTimeout(function () {
                    $('#manageShippingModal').modal('hide');
                    shippingReloadViews();
                }, 400);
            },
            error: function (xhr) {
                var res = parseJsonResponse(xhr.responseText) || {};
                updateCsrfHash(res.csrf_hash);
                $btn.prop('disabled', false).html('<i class="las la-save"></i> Save Shipping');
                $('#shipping_modal_error')
                    .text(res.msg || 'Unable to save shipping details.')
                    .removeClass('d-none');
            },
        });
    });

    $(document).on('click', '.open-status-shipping', function () {
        var bookingId = parseInt($(this).data('booking-id'), 10) || 0;
        $('#shipping_status_booking_id').val(bookingId);
        $('#shipping_status_heading').val('');
        $('#shipping_status_body').val('');
        $('#shipping_status_update').empty().append('<option value="">Loading...</option>').prop('disabled', true);
        $('#shipping_status_error').addClass('d-none').text('');
        $('#shipping_status_submit_btn').prop('disabled', true);
        $('#shippingStatusModal').modal('show');

        $.ajax({
            url: base_url + 'shipping/shipping_context_ajax/' + bookingId,
            type: 'POST',
            data: { q2r_secure: getCsrfHash() },
            success: function (response) {
                var res = parseJsonResponse(response) || {};
                updateCsrfHash(res.csrf_hash);
                if (!res.status || !res.context) {
                    $('#shipping_status_error').text(res.msg || 'Unable to load the current status.').removeClass('d-none');
                    return;
                }

                shippingRenderStatusOptions(
                    res.context.status,
                    'edit',
                    res.context.status_next_options || [],
                    '#shipping_status_update'
                );
                if (!(res.context.status_next_options || []).length) {
                    $('#shipping_status_error').text('This shipment is completed and has no further status options.').removeClass('d-none');
                    $('#shipping_status_submit_btn').prop('disabled', true);
                } else {
                    $('#shipping_status_submit_btn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                var res = parseJsonResponse(xhr.responseText) || {};
                updateCsrfHash(res.csrf_hash);
                $('#shipping_status_error').text(res.msg || 'Unable to load the current status.').removeClass('d-none');
            },
        });
    });

    $(document).on('click', '#shipping_status_submit_btn', function () {
        var bookingId = parseInt($('#shipping_status_booking_id').val(), 10) || 0;
        var payload = {
            status: $('#shipping_status_update').val(),
            heading: $.trim($('#shipping_status_heading').val()),
            body: $.trim($('#shipping_status_body').val()),
            q2r_secure: getCsrfHash(),
        };

        if (!bookingId) {
            $('#shipping_status_error')
                .text('Invalid booking selected.')
                .removeClass('d-none');
            return;
        }

        var $btn = $(this)
            .prop('disabled', true)
            .html('<i class="las la-spinner la-spin"></i> Saving...');

        $.ajax({
            url: base_url + 'shipping/update_status_ajax/' + bookingId,
            type: 'POST',
            data: payload,
            success: function (response) {
                var res = parseJsonResponse(response) || {};
                updateCsrfHash(res.csrf_hash);
                $btn.prop('disabled', false).html('<i class="las la-sync"></i> Add Update');

                if (!res.status) {
                    $('#shipping_status_error')
                        .text(res.msg || 'Unable to add the shipping update.')
                        .removeClass('d-none');
                    return;
                }

                $('#shippingStatusModal').modal('hide');
                shippingReloadViews();
            },
            error: function (xhr) {
                var res = parseJsonResponse(xhr.responseText) || {};
                updateCsrfHash(res.csrf_hash);
                $btn.prop('disabled', false).html('<i class="las la-sync"></i> Add Update');
                $('#shipping_status_error')
                    .text(res.msg || 'Unable to add the shipping update.')
                    .removeClass('d-none');
            },
        });
    });









































});
