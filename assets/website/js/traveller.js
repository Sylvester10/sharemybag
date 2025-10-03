jQuery(document).ready(function ($) {
  ("use strict");

  /*=========== Disable Button ===========*/
  function disableSubmitBtn() {
    var submitButton = $("#submit");
    submitButton.addClass("disabled");
    submitButton.attr("disabled", true); // Disables the button
  }

  /*=========== Enable Button ===========*/
  function enableSubmitBtn() {
    var submitButton = $("#submit");
    submitButton.removeClass("disabled");
    submitButton.attr("disabled", false); // Enables the button
  }

  // Traveller form
  $("#traveller_form").submit(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none"); // Updated to match the spinner ID in the HTML
    var form_data = new FormData(this);
    disableSubmitBtn();
    $.ajax({
      url: base_url + "home/add_traveller_ajax",
      type: "POST",
      data: form_data,
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $("#status_msg")
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  "</div>"
              )
              .fadeIn("fast")
              .delay(5000)
              .fadeOut("slow");
            $("#traveller_form")[0].reset(); // Reset form fields
          } else {
            $("#status_msg")
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  "</div>"
              )
              .fadeIn("fast")
              .delay(5000)
              .fadeOut("slow");
          }
          $("#search-spinner").addClass("d-none"); // Hide the spinner after response
          enableSubmitBtn();
        }, 3000); // 3000 milliseconds (3 seconds) delay
      },
    });
  });

  //Wait List Form
  // $("#waitlist_form").submit(function (e) {
  // 	e.preventDefault();
  // 	$("#loading-spinner").removeClass("d-none");
  // 	var form_data = $(this).serialize();

  // 	$.ajax({
  // 		url: base_url + "home/waitlist_ajax",
  // 		type: "POST",
  // 		data: form_data,
  // 		success: function (msg) {
  // 			// Delay success message for 5 seconds
  // 			setTimeout(function () {
  // 				if (msg == 1) {
  // 					$("#status_msg")
  // 						.html(
  // 							'<div class="alert alert-success text-center" style="color: #000;"> Thank you! One of our agents will contact you shortly.</div>'
  // 						)
  // 						.fadeIn("fast")
  // 						.delay(5000)
  // 						.fadeOut("slow");
  // 					$("#loading-spinner").addClass("d-none");
  // 					$("#waitlist_form")[0].reset(); //reset form fields
  // 				} else {
  // 					$("#status_msg")
  // 						.html(
  // 							'<div class="alert alert-danger text-center" style="color: #000;">' +
  // 								msg +
  // 								"</div>"
  // 						)
  // 						.fadeIn("fast")
  // 						.delay(5000)
  // 						.fadeOut("slow");
  // 					$("#loading-spinner").addClass("d-none");
  // 				}
  // 			}, 2000); // 5000 milliseconds (5 seconds) delay
  // 		},
  // 	});
  // });

  //Date Picker
  $("#travelDate").daterangepicker(
    {
      singleDatePicker: true,
      minDate: moment(),
      autoUpdateInput: false,
      autoApply: true,
    },
    function (chosen_date) {
      $("#travelDate").val(chosen_date.format("YYYY-MM-DD"));
    }
  );
});
