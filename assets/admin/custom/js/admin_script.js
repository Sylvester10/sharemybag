jQuery(document).ready(function ($) {
  ('use strict');

  const csrf_hash = $('#csrf_hash').val();

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

    $.post(base_url + 'admin/send_quick_mail_ajax', formData, function (msg) {
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
    });
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
          d.q2r_secure = csrf_hash;
          // If a callback is provided, run it to append extra data (filters)
          if (extraDataCallback) {
            extraDataCallback(d);
          }
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
          ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
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
          ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
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
    modal.find('.autofill-agent, .autofill-receiver').prop('checked', false);
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
        modal.find('input[name="agent_postcode"]').val(userData.postal_code);
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
        modal.find('input[name="receiver_name"]').val(userData.fullname);
        modal.find('input[name="receiver_email"]').val(userData.email);
        modal.find('input[name="receiver_phone"]').val(userData.phone);
        modal.find('input[name="receiver_address"]').val(userData.address);
        modal.find('input[name="receiver_locality"]').val(userData.city);
        modal.find('input[name="receiver_postcode"]').val(userData.postal_code);
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
});
