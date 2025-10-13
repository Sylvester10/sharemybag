jQuery(document).ready(function ($) {
	"use strict";

	/*=========== Disable Button ===========*/
	function disableSubmitBtn() {
		var submitButton = $("#submit");
		submitButton.html("Validating...");
		submitButton.attr("disabled", true);
		submitButton.addClass("disabled");
	}

	/*=========== Enable Button ===========*/
	function enableSubmitBtn() {
		var submitButton = $("#submit");
		submitButton.html("LOGIN");
		submitButton.removeClass("disabled");
		submitButton.attr("disabled", false);
	}

	//Admin login
	$("#admin_login_form").submit(function (e) {
		e.preventDefault();
		var form_data = $(this).serialize();
		var redirect_url = $("#requested_page").val();
		disableSubmitBtn();
		$.ajax({
			url: base_url + "admin_login/login_ajax",
			type: "POST",
			data: form_data,
			dataType: "json",
			success: function (res) {
				// Delay success message for 5 seconds
				setTimeout(function () {
					if (res.status) {
						$("#status_msg")
							.html(
								'<div class="alert alert-success text-center" style="color: #fff">' +
									res.msg +
									"</div>"
							)
							.fadeIn("fast");
						enableSubmitBtn();
						setTimeout(function () {
							$(location).attr("href", redirect_url);
						}, 3000);
					} else {
						$("#status_msg")
							.html(
								'<div class="alert alert-danger text-center" style="color: #fff">' +
									res.msg +
									"</div>"
							)
							.fadeIn("fast")
							.delay(10000)
							.fadeOut("slow");
						enableSubmitBtn();
					}
				}, 3000); // 5000 milliseconds (5 seconds) delay
			},
		});
	});
});
