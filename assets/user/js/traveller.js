jQuery(document).ready(function ($) {
	("use strict");

	/*=========== Display Loading Button ===========*/
	function displayLoadingBtn() {
		var submitButton = $("#submit");
		var spinnerButton = $("#booking-spinner");
		submitButton.html("Share my Bag");
		submitButton.attr("disabled", false);
		submitButton.removeClass("disabled");
		spinnerButton.attr("disabled", true);
		spinnerButton.addClass("d-none");
	}
	
	/*=========== Hide Loading Button ===========*/
	function hideLoadingBtn() {
		var submitButton = $("#submit");
		var spinnerButton = $("#booking-spinner");
		submitButton.html("Share my Bag");
		submitButton.attr("disabled", true);
		submitButton.addClass("disabled");
		spinnerButton.attr("disabled", false);
		spinnerButton.removeClass("d-none");
	}

	//Traveller Form
	$("#traveller_form").submit(function (e) {
		e.preventDefault();
		var form_data = $(this).serialize();
		hideLoadingBtn();

		$.ajax({
			url: base_url + "home/add_traveller_ajax",
			type: "POST",
			data: form_data,
			success: function (msg) {
				if (msg == 1) {
					$("#status_msg")
						.html(
							'<div class="alert alert-success text-center" style="color: #000;"> Thank you! One of our agents will contact you shortly.</div>'
						)
						.fadeIn("fast")
						.delay(15000)
						.fadeOut("slow");
					displayLoadingBtn();
					$("#traveller_form")[0].reset(); //reset form fields
				} else {
					$("#status_msg")
						.html(
							'<div class="alert alert-danger text-center" style="color: #000;">' +
								msg +
								"</div>"
						)
						.fadeIn("fast")
						.delay(7000)
						.fadeOut("slow");
					displayLoadingBtn();
				}
			},
		});
	});

	//Date Picker
	var currentDate = new Date();
	$("#datepicker").datepicker({
		clearBtn: true,
		format: "yyyy-mm-dd",
		todayHighlight: true,
		startDate: currentDate,
	});
});
