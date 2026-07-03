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
            processing: false,
            serverSide: true,
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

                    return json && json.data ? json.data : [];
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
        .order([1, 'desc'])
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
        .order([1, 'desc'])
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
    });

    function clearAutofillFields(modal, type) {
        modal.find('input[name="' + type + '_name"]').val('');
        modal.find('input[name="' + type + '_email"]').val('');
        modal.find('input[name="' + type + '_phone"]').val('');
        modal.find('input[name="' + type + '_address"]').val('');
        modal.find('input[name="' + type + '_locality"]').val('');
        modal.find('input[name="' + type + '_postcode"]').val('');
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
                modal.find('input[name="agent_phone"]').val(userData.phone);
                modal.find('input[name="agent_address"]').val(userData.address);
                modal.find('input[name="agent_locality"]').val(userData.city);
                modal
                    .find('input[name="agent_postcode"]')
                    .val(userData.postal_code);
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
                modal.find('input[name="receiver_phone"]').val(userData.phone);
                modal
                    .find('input[name="receiver_address"]')
                    .val(userData.address);
                modal
                    .find('input[name="receiver_locality"]')
                    .val(userData.city);
                modal
                    .find('input[name="receiver_postcode"]')
                    .val(userData.postal_code);
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
                var currentArea = $('input[name="area"]').val();
                $('input[name="drop_address1"]').val(currentAddress);
                $('input[name="drop_area1"]').val(currentArea);
            } else {
                $('input[name="drop_address1"]').val('');
                $('input[name="drop_area1"]').val('');
            }
        });
    });

    $(document).ready(function () {
        $('#populateDropAddress2').change(function () {
            if ($(this).is(':checked')) {
                var currentAddress = $('input[name="address"]').val();
                var currentArea = $('input[name="area"]').val();
                $('input[name="drop_address2"]').val(currentAddress);
                $('input[name="drop_area2"]').val(currentArea);
            } else {
                $('input[name="drop_address2"]').val('');
                $('input[name="drop_area2"]').val('');
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
            '<div class="admin-shipping-context__tracking"><strong>Tracking ID:</strong> ' +
                $('<div>').text(context.tracking_id || '').html() +
                '</div>' +
                '<div class="admin-shipping-context__meta">' +
                '<span><strong>User:</strong> ' + $('<div>').text(context.user || '').html() + '</span>' +
                '<span><strong>Traveller:</strong> ' + $('<div>').text(context.traveller || '').html() + '</span>' +
                '</div>'
        );
    }

    function shippingApplyContext(context) {
        $('#shipping_booking_id').val(context.booking_id || 0);
        $('#shipping_pickup_address').val(context.pickup_address || '');
        $('#shipping_dropoff_address').val(context.dropoff_address || '');
        $('#shipping_pickup_country').val(context.pickup_country || '');
        $('#shipping_courier').val(context.courier || 'DHL').trigger('change.select2');
        $('#shipping_staff_admin_id')
            .val(context.staff_admin_id || '')
            .trigger('change.select2');
        $('#shipping_status').val(context.status || 'In Transit');
        shippingRenderSelectedContext(context);
    }

    function shippingOpenModal(mode, bookingId) {
        bookingId = parseInt(bookingId, 10) || 0;
        shippingResetMessages();
        $('#shipping_mode').val(mode);
        $('#shipping_booking_id').val(bookingId);
        $('#shipping_search_query').val('');
        $('#shipping_tracking_note').val('');
        $('#shipping_search_results tbody').html(
            '<tr><td colspan="6" class="text-center text-muted">Search for a booking to continue.</td></tr>'
        );

        if (mode === 'edit') {
            $('#shippingModalTitle').text('Edit Shipping Record');
            $('#shippingModalSubtitle').text('Update the active shipping details for this booking and optionally append a note to the update history.');
        } else {
            $('#shippingModalTitle').text('Create Shipping Record');
            $('#shippingModalSubtitle').text('Find the booking first, then confirm the shipping details that will show in the admin shipping table.');
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
        $('#shipping_status_update').val('In Transit');
        $('#shipping_status_error').addClass('d-none').text('');
        $('#shippingStatusModal').modal('show');
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
