jQuery(document).ready(function ($) {
  ("use strict");

  const csrf_hash = $("#csrf_hash").val();

  // Dropzone Configuration
  Dropzone.options.upload_photo_form = {
    maxFilesize: 5,
    acceptedFiles: ".jpg, .jpeg, .png, .gif",
    init: function () {
      this.on("success", function () {
        if (
          this.getQueuedFiles().length === 0 &&
          this.getUploadingFiles().length === 0
        ) {
          location.reload(); // reload page after upload success
        }
      });
    },
  };

  // Utility functions to handle button states
  function toggleSubmitBtn(isDisabled) {
    const submitButton = $("#submit");
    submitButton.prop("disabled", isDisabled);
    submitButton.toggleClass("disabled", isDisabled);
    submitButton.html(isDisabled ? "Please Wait..." : "Submit");
  }

  // Quick Mail Form Submission
  $("#quick_mail_form").on("submit", function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.post(base_url + "admin/send_quick_mail_ajax", formData, function (msg) {
      const alertType = msg == 1 ? "success" : "danger";
      const alertMessage =
        msg == 1 ? "Mail successfully sent." : "Email not Sent!";
      $("#q_status_msg")
        .html(
          `<div class="alert alert-${alertType} text-center">${alertMessage}</div>`
        )
        .fadeIn("fast")
        .delay(30000)
        .fadeOut("slow");
      if (msg == 1) $("#quick_mail_form")[0].reset();
    });
  });

  //Loading icon on submit
  $(document).ready(function () {
    $("#submit_button").submit(function (e) {
      $("#send_mail_btn").attr("disabled", true); // Disable the button
      $("#btn_text").text("Please wait..."); // Change the button text
      $("#loading_icon").show(); // Show the loading icon
    });
  });

  //Loading icon on submit
  $(document).ready(function () {
    $("#submit_buttons").submit(function (e) {
      $("#send_mail_btns").attr("disabled", true); // Disable the button
      $("#btn_texts").text("Please wait..."); // Change the button text
      $("#loading_icons").show(); // Show the loading icon
    });
  });

  // Reusable DataTable Initialization Function
  function initializeDataTable(selector, ajaxUrl, searchLabel) {
    return $(selector).DataTable({
      paging: true,
      pageLength: 10,
      lengthChange: true,
      searching: true,
      info: true,
      scrollX: true,
      autoWidth: false,
      ordering: true,
      stateSave: true,
      processing: false,
      serverSide: true,
      pagingType: "simple_numbers",
      dom: "<'dt_len_change'l>f<'dt_buttons'B>trip",
      language: {
        search: searchLabel,
        processing: "Please wait a sec...",
        info: "Showing _START_ to _END_ of _TOTAL_",
        infoFiltered: "(filtered from _MAX_ total)",
        emptyTable: "No data to show.",
        lengthMenu: "Show _MENU_ entries",
      },
      ajax: {
        url: ajaxUrl,
        type: "POST",
        data: { q2r_secure: csrf_hash },
      },
      columnDefs: [{ targets: [0, 1], orderable: false }],
      buttons: [
        { extend: "colvis", className: "data_export_buttons" },
        { extend: "print", className: "data_export_buttons" },
        { extend: "excel", className: "data_export_buttons" },
        { extend: "csv", className: "data_export_buttons" },
        { extend: "pdf", className: "data_export_buttons" },
      ],
    });
  }

  // Initialize DataTables
  initializeDataTable(
    "#users_table",
    base_url + "admin_users/user_ajax",
    "Search/filter user:"
  )
    .order([9, "desc"])
    .draw(); // 9 is the index of 'Date Registered' column

  // Initialize DataTables
  initializeDataTable(
    "#approved_users_table",
    base_url + "admin_users/approved_users_ajax",
    "Search/filter user:"
  )
    .order([9, "desc"])
    .draw(); // 9 is the index of 'Date Registered' column

  // Initialize DataTables
  initializeDataTable(
    "#pending_users_table",
    base_url + "admin_users/pending_users_ajax",
    "Search/filter user:"
  )
    .order([9, "desc"])
    .draw(); // 9 is the index of 'Date Registered' column

  initializeDataTable(
    "#upcoming_travellers_table",
    base_url + "admin_travellers/upcoming_travellers_ajax",
    "Search/filter Traveller:"
  )
    .order([2, "asc"])
    .draw();

  initializeDataTable(
    "#approved_travellers_table",
    base_url + "admin_travellers/approved_travellers_ajax",
    "Search/filter Traveller:"
  )
    .order([2, "desc"])
    .draw();

  initializeDataTable(
    "#pending_travellers_table",
    base_url + "admin_travellers/pending_travellers_ajax",
    "Search/filter Traveller:"
  );

  initializeDataTable(
    "#unapproved_travellers_table",
    base_url + "admin_travellers/unapproved_travellers_ajax",
    "Search/filter Traveller:"
  );

  initializeDataTable(
    "#bookings_table",
    base_url + "admin_bookings/all_bookings_ajax",
    "Search/filter bookings:"
  )
    .order([1, "desc"])
    .draw();

  initializeDataTable(
    "#completed_bookings_table",
    base_url + "admin_bookings/completed_bookings_ajax",
    "Search/filter bookings:"
  )
    .order([1, "desc"])
    .draw();

  initializeDataTable(
    "#canceled_bookings_table",
    base_url + "admin_bookings/canceled_bookings_ajax",
    "Search/filter bookings:"
  )
    .order([1, "desc"])
    .draw();

  initializeDataTable(
    "#exchange_table",
    base_url + "admin_exchange/all_exchange_rates",
    "Search/filter rates:"
  );

  if ($.fn.DataTable.isDataTable("#finances_table")) {
    $("#finances_table").DataTable().clear().destroy();
  }

  var table = initializeDataTable(
    "#finances_table",
    base_url + "admin_finances/all_finances_ajax",
    "Search/filter Finance:"
  )
    .order([1, "desc"])
    .draw();

  // Add filter to AJAX
  $.fn.dataTable.ext.errMode = "none";
  table.on("preXhr.dt", function (e, settings, data) {
    data.month = $("#month_filter").val();
    data.year = $("#year_filter").val();
  });

  // Trigger reload on filter change
  $("#month_filter, #year_filter").on("change", function () {
    table.ajax.reload();
  });

  // Trumbowyg Text Editor
  $("#email_message").trumbowyg({
    btns: [
      ["viewHTML"],
      ["formatting"],
      ["bold", "italic", "underline", "del"],
      ["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
      ["unorderedList", "orderedList"],
      ["link"],
      ["removeformat"],
      ["fullscreen"],
    ],
  });

  // Trumbowyg Text Editor
  $("#email_messages").trumbowyg({
    btns: [
      ["viewHTML"],
      ["formatting"],
      ["bold", "italic", "underline", "del"],
      ["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
      ["unorderedList", "orderedList"],
      ["link"],
      ["removeformat"],
      ["fullscreen"],
    ],
  });

  // Update the drop off address field with the data on the current address field
  $(document).ready(function () {
    $("#populateDropAddress").change(function () {
      if ($(this).is(":checked")) {
        var currentAddress = $('input[name="address"]').val();
        $('input[name="drop_address1"]').val(currentAddress);
      } else {
        $('input[name="drop_address1"]').val("");
      }
    });
  });

  $(document).ready(function () {
    $("#populateDropAddress2").change(function () {
      if ($(this).is(":checked")) {
        var currentAddress = $('input[name="address"]').val();
        $('input[name="drop_address2"]').val(currentAddress);
      } else {
        $('input[name="drop_address2"]').val("");
      }
    });
  });
});
