jQuery(document).ready(function ($) {
	("use strict");

	Dropzone.options.upload_photo_form = {
		maxFilesize: 5,
		acceptedFiles: ".jpg, .jpeg, .png, .gif",
		init: function () {
			this.on("success", function () {
				if (
					this.getQueuedFiles().length == 0 &&
					this.getUploadingFiles().length == 0
				) {
					location.reload(); //reload page after upload success
				}
			});
		},
	};

	function disableSubmitBtn() {
		var submitButton = $("#submit");
		submitButton.html("Please Wait...");
		submitButton.attr("disabled", true);
		submitButton.addClass("disabled");
	}

	function enableSubmitBtn() {
		var submitButton = $("#submit");
		submitButton.html("Submit");
		submitButton.removeClass("disabled");
		submitButton.attr("disabled", false);
	}

	//Quick Mail
	$("#quick_mail_form").submit(function (e) {
		e.preventDefault();
		var form_data = $("#quick_mail_form").serialize();
		$.ajax({
			url: base_url + "admin/send_quick_mail_ajax",
			type: "POST",
			data: form_data,
			success: function (msg) {
				if (msg == 1) {
					$("#q_status_msg")
						.html(
							'<div class="alert alert-success text-center">Mail successfully sent.</div>'
						)
						.fadeIn("fast")
						.delay(30000)
						.fadeOut("slow");
					$("#quick_mail_form")[0].reset(); //reset form fields
					var email_message = $("#email_message");
					email_message.val("");
					email_message.siblings('[class="trumbowyg-editor"]').html("");
				} else {
					$("#q_status_msg")
						.html(
							'<div class="alert alert-danger text-center"> Email not Sent!</div>'
						)
						.fadeIn("fast")
						.delay(30000)
						.fadeOut("slow");
				}
			},
		});
	});

	//print and export buttons for DataTables
	var ExportButtons = [
		{
			extend: "colvis", //column visibility
			className: "data_export_buttons",
		},
		{
			extend: "print",
			className: "data_export_buttons",
			exportOptions: {
				columns: ":visible",
			},
		},
		{
			extend: "excel",
			className: "data_export_buttons",
			exportOptions: {
				columns: ":visible",
			},
		},
		{
			extend: "csv",
			className: "data_export_buttons",
			exportOptions: {
				columns: ":visible",
			},
		},
		{
			extend: "pdf",
			className: "data_export_buttons",
			exportOptions: {
				columns: ":visible",
			},
		},
	];

	//Approved Travellers
	var csrf_hash = $("#csrf_hash").val();
	$("#approved_travellers_table").DataTable({
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
		order: [], //Initial no order.
		language: {
			search: "Search/filter Traveller: ",
			processing: "Please wait a sec...",
			info: "Showing _START_ to _END_ of _TOTAL_ Travellers",
			infoFiltered: "(filtered from _MAX_ total Traveller)",
			emptyTable: "No traveller to show.",
			lengthMenu: "Show _MENU_ Traveller",
		},
		ajax: {
			url: base_url + "travellers/approved_travellers_ajax",
			dataType: "json",
			type: "POST",
			data: { q2r_secure: csrf_hash }, //cross site request forgery protection
		},
		columnDefs: [{ targets: [0, 1], orderable: false }],
		buttons: ExportButtons,
	});

	//Pending Travellers
	var csrf_hash = $("#csrf_hash").val();
	$("#pending_travellers_table").DataTable({
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
		order: [], //Initial no order.
		language: {
			search: "Search/filter Traveller: ",
			processing: "Please wait a sec...",
			info: "Showing _START_ to _END_ of _TOTAL_ Travellers",
			infoFiltered: "(filtered from _MAX_ total Traveller)",
			emptyTable: "No traveller to show.",
			lengthMenu: "Show _MENU_ Traveller",
		},
		ajax: {
			url: base_url + "travellers/pending_travellers_ajax",
			dataType: "json",
			type: "POST",
			data: { q2r_secure: csrf_hash }, //cross site request forgery protection
		},
		columnDefs: [{ targets: [0, 1], orderable: false }],
		buttons: ExportButtons,
	});

	//Unapproved Travellers
	var csrf_hash = $("#csrf_hash").val();
	$("#unapproved_travellers_table").DataTable({
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
		order: [], //Initial no order.
		language: {
			search: "Search/filter Traveller: ",
			processing: "Please wait a sec...",
			info: "Showing _START_ to _END_ of _TOTAL_ Travellers",
			infoFiltered: "(filtered from _MAX_ total Traveller)",
			emptyTable: "No traveller to show.",
			lengthMenu: "Show _MENU_ Traveller",
		},
		ajax: {
			url: base_url + "travellers/unapproved_travellers_ajax",
			dataType: "json",
			type: "POST",
			data: { q2r_secure: csrf_hash }, //cross site request forgery protection
		},
		columnDefs: [{ targets: [0, 1], orderable: false }],
		buttons: ExportButtons,
	});

	//Bookings
	var csrf_hash = $("#csrf_hash").val();
	var $bookings_table = $("#bookings_table").DataTable({
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
		order: [], //Initial no order.
		language: {
			search: "Search/filter bookings: ",
			processing: "Please wait a sec...",
			info: "Showing _START_ to _END_ of _TOTAL_ bookings",
			infoFiltered: "(filtered from _MAX_ total bookings)",
			emptyTable: "No booking to show.",
			lengthMenu: "Show _MENU_ bookings",
		},
		ajax: {
			url: base_url + "bookings/all_bookings_ajax",
			dataType: "json",
			type: "POST",
			data: { q2r_secure: csrf_hash }, //cross site request forgery protection
		},
		columnDefs: [{ targets: [0, 1], orderable: false }],
		buttons: ExportButtons,
	});

	//Finances
	var csrf_hash = $("#csrf_hash").val();
	var $finances_table = $("#finances_table").DataTable({
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
		order: [], //Initial no order.
		language: {
			search: "Search/filter Finance: ",
			processing: "Please wait a sec...",
			info: "Showing _START_ to _END_ of _TOTAL_ Finance",
			infoFiltered: "(filtered from _MAX_ total Finance)",
			emptyTable: "No finance to show.",
			lengthMenu: "Show _MENU_ Finance",
		},
		ajax: {
			url: base_url + "finances/all_finances_ajax",
			dataType: "json",
			type: "POST",
			data: { q2r_secure: csrf_hash }, //cross site request forgery protection
		},
		columnDefs: [{ targets: [0, 1], orderable: false }],
		buttons: ExportButtons,
	});

	//Exchange Rate
	var csrf_hash = $("#csrf_hash").val();
	$("#exchange_table").DataTable({
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
		order: [], //Initial no order.
		language: {
			search: "Search/filter rates: ",
			processing: "Please wait a sec...",
			info: "Showing _START_ to _END_ of _TOTAL_ rates",
			infoFiltered: "(filtered from _MAX_ total rates)",
			emptyTable: "No rates to show.",
			lengthMenu: "Show _MENU_ rates",
		},
		ajax: {
			url: base_url + "exchange/all_exchange_rates",
			dataType: "json",
			type: "POST",
			data: { q2r_secure: csrf_hash }, //cross site request forgery protection
		},
		columnDefs: [{ targets: [0, 1], orderable: false }],
		buttons: ExportButtons,
	});

	//Text Editor
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
		changeActiveDropdownIcon: true,
		imageWidthModalEdit: true,
	});

	//Loading icon on submit
	$(document).ready(function () {
		$("#submit_button").submit(function (e) {
			$("#send_mail_btn").attr("disabled", true); // Disable the button
			$("#btn_text").text("Please wait..."); // Change the button text
			$("#loading_icon").show(); // Show the loading icon
		});
	});

	//Display State when nationality is Nigeria
	var locationSelect = document.getElementById("current_location");
	var stateGroup = document.getElementById("state");
	var dropAddress1 = document.getElementById("dropaddress1");
	var dropDate1 = document.getElementById("dropdate1");
	var departureState = document.getElementById("departurestate");

	// Function to toggle fields based on the selected country and state
	function toggleFields() {
		// If the selected country is Nigeria
		if (locationSelect.value === "Nigeria") {
			stateGroup.style.display = "block";
			dropAddress1.style.display = "block";
			dropDate1.style.display = "block";
			departureState.style.display = "block";

			var selectedDepartureState = departureState.value;
			var selectedCurrentState = document.querySelector(
				'#state select[name="current_state"]'
			).value;
		} else {
			// If the selected country is not Nigeria, hide all relevant fields
			stateGroup.style.display = "none";
			dropAddress1.style.display = "none";
			dropDate1.style.display = "none";
			departureState.style.display = "none";
			dropAddress2.style.display = "none";
			dropDate2.style.display = "none";
		}
	}

	// Function to handle the change event of the departure state select element
	function handleDepartureStateChange() {
		var selectedDepartureState = departureState.value;
		var selectedCurrentState = document.querySelector(
			'#state select[name="current_state"]'
		).value;

		// If the selected departure state is different from the current state
		if (selectedDepartureState !== selectedCurrentState) {
			dropAddress2.style.display = "block";
			dropDate2.style.display = "block";
		} else {
			dropAddress2.style.display = "none";
			dropDate2.style.display = "none";
		}
	}

	locationSelect.addEventListener("change", toggleFields);
	departureState.addEventListener("change", handleDepartureStateChange);

	// Call the toggleFields function on page load
	window.addEventListener("DOMContentLoaded", function () {
		if (locationSelect.value === "Nigeria") {
			toggleFields();
		}
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

	// Display the 2nd drop address and 2nd drop date if the departure state matches the current state
	$(document).ready(function () {
		// Check the values on page load
		checkFields();

		// Check the values whenever either field changes
		$('select[name="current_state"], select[name="departure_state"]').change(
			function () {
				checkFields();
			}
		);

		function checkFields() {
			var currentState = $('select[name="current_state"]').val();
			var departureState = $('select[name="departure_state"]').val();

			if (currentState !== departureState) {
				$("#dropaddress2, #dropdate2").show();
			} else {
				$("#dropaddress2, #dropdate2").hide();
			}
		}
	});
}); //jQuery(document).ready(function)
