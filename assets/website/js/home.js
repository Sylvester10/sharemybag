jQuery(document).ready(function ($) {
  ('use strict');

  /*=========== Disable Button ===========*/
  function disableSubmitBtn() {
    var submitButton = $('#submit');
    submitButton.addClass('disabled');
    submitButton.attr('disabled', true); // Disables the button
  }

  /*=========== Enable Button ===========*/
  function enableSubmitBtn() {
    var submitButton = $('#submit');
    submitButton.removeClass('disabled');
    submitButton.attr('disabled', false); // Enables the button
  }

  // Close search results when clicking the close button (using event delegation)
  $(document).on('click', '.search-back-drop', function () {
    $('body').removeClass('search-active');
    $('#search-results').html(''); // Clear search results
  });

  //Search
  $('#search_form').submit(function (e) {
    e.preventDefault();
    $('#search-spinner').removeClass('d-none');
    $('#search-results').html('');
    let val = $('#select_destination').val();
    let url = $(this).attr('action');

    if (val.trim() == '') {
      return;
    }

    let form_data = $(this).serialize();
    $.ajax({
      url: url,
      type: 'POST',
      data: form_data,
      contentType: 'application/x-www-form-urlencoded',
      success: function (response) {
        response = JSON.parse(response);
        console.log(response);

        if (response.status) {
          let availableSpaceText =
            parseFloat(response.available_space) > 0
              ? `${response.available_space} kg`
              : `<span class="text-danger fw-bold">Bag Full</span>`;

          let html_response = `
					<section id="section-1" class="bg-white rounded shadow-md">

						<span class="search-back-drop"></span>

						<div class="prohibited_items bg-white rounded shadow-md mt-3 p-4">

							<div class="prohibited-box">
								<div class="prohibited_icon wow fadeInUp animated" data-wow-delay=".2s">
									<img src="${base_url}assets/website/icons/calendar.png">
									<h4>Date</h4>
									<p>${response.travel_date}</p>
								</div>
								<div class="prohibited_icon wow fadeInUp animated" data-wow-delay=".4s">
									<img src="${base_url}assets/website/icons/location.png">
									<h4>Current Location</h4>
									<p>${response.current_state}</p>
								</div>
								<div class="prohibited_icon wow fadeInUp animated" data-wow-delay=".6s">
									<img src="${base_url}assets/website/icons/destination.png">
									<h4>Final Destination</h4>
									<p>${response.arrival_state}</p>
								</div>
								<div class="prohibited_icon wow fadeInUp animated" data-wow-delay=".8s">
									<img src="${base_url}assets/website/icons/weight.png">
									<h4>Available space</h4>
									<p>${availableSpaceText}</p>
								</div>
							</div>

							<h6>
								<a href="${base_url}registration" class="login-btn primary wow fadeInUp animated" data-wow-delay=".15s" type="submit">
									Sign up to see all available travellers
								</a>
							</h6>

						</div>

					</section>`;

          setTimeout(function () {
            $('body').addClass('search-active');
            $('#search-results').html(html_response);
            $('#search-spinner').addClass('d-none');
          }, 3000);
        } else {
          // Handle case when response.status is false (e.g., no results found)
          var noResultsHtml = `<section id="section-1" class="bg-white rounded shadow-md">

										<span class="search-back-drop"></span>

										<div class="prohibited_items bg-white rounded shadow-md mt-3 p-4 text-center">
											<div class="prohibited_icon wow fadeInUp animated" data-wow-delay=".8s">
												<img src="${base_url}assets/website/icons/no-bag.png">
												<h5>No Traveller currently available</h5>
											</div>

                      <h6>
                        <a href="${base_url}registration" class="login-btn primary wow fadeInUp animated" data-wow-delay=".15s" type="submit">
                          Sign up to join the wait list
                        </a>
                      </h6>
										</div>

									</section>`;
          setTimeout(function () {
            $('body').addClass('search-active');
            $('#search-results').html(noResultsHtml);
            $('#search-spinner').addClass('d-none');
          }, 2000);
        }
      },
      error: function (error) {
        console.log(`This is the error ${error}`);
      },
    });
  });

  //Sign up
  $('#signup_form').submit(function (e) {
    e.preventDefault();
    $('#search-spinner').removeClass('d-none');
    var form_data = $(this).serialize();
    var redirect_url = base_url + 'verify-email';
    disableSubmitBtn();
    $.ajax({
      url: base_url + 'registration/signup',
      type: 'POST',
      data: form_data,
      dataType: 'json',
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $('#status_msg')
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast');
            $('#signup_form')[0].reset(); //reset form fields
            $('#search-spinner').addClass('d-none');
            setTimeout(function () {
              $(location).attr('href', redirect_url);
            }, 4000);
            enableSubmitBtn();
          } else {
            $('#status_msg')
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(5000)
              .fadeOut('slow');
            $('#search-spinner').addClass('d-none');
            enableSubmitBtn();
          }
        }, 3000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  //Verify email
  $('#verify_email_form').submit(function (e) {
    e.preventDefault();
    $('#search-spinner').removeClass('d-none');
    var form_data = $(this).serialize();
    var redirect_url = base_url + 'signin';
    disableSubmitBtn();
    $.ajax({
      url: base_url + 'registration/verify_email_ajax',
      type: 'POST',
      data: form_data,
      dataType: 'json',
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $('#status_msg')
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast');
            $('#verify_email_form')[0].reset(); //reset form fields
            $('#search-spinner').addClass('d-none');
            setTimeout(function () {
              $(location).attr('href', redirect_url);
            }, 4000);
            enableSubmitBtn();
          } else {
            $('#status_msg')
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(5000)
              .fadeOut('slow');
            $('#search-spinner').addClass('d-none');
            enableSubmitBtn();
          }
        }, 2000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  // Resend Verification email
  $('#resend_verification_email').click(function () {
    $('#search-spinners').removeClass('d-none'); // Show spinner

    $.ajax({
      url: base_url + 'registration/resend_verification_email_ajax',
      type: 'POST',
      dataType: 'json',
      success: function (res) {
        // Delay handling for the success message
        setTimeout(function () {
          if (res.status) {
            $('#status_msg')
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(3000) // Show the success message for 5 seconds
              .fadeOut('slow'); // Then fade it out
          } else {
            $('#status_msg')
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(3000)
              .fadeOut('slow');
          }
          $('#search-spinners').addClass('d-none'); // Hide spinner
        }, 2000); // 2000 milliseconds (2 seconds) delay
      },
      error: function () {
        $('#status_msg')
          .html(
            '<div class="alert alert-danger text-center" style="color: #000">' +
              'An error occurred. Please try again.' +
              '</div>'
          )
          .fadeIn('fast')
          .delay(5000)
          .fadeOut('slow');
        $('#search-spinners').addClass('d-none'); // Hide spinner
      },
    });
  });

  //Recover Password
  $('#recover_password_form').submit(function (e) {
    e.preventDefault();
    $('#search-spinner').removeClass('d-none');
    var form_data = $(this).serialize();
    var redirect_url = base_url + 'signin';
    disableSubmitBtn();
    $.ajax({
      url: base_url + 'recover_password/password_recovery_ajax',
      type: 'POST',
      data: form_data,
      dataType: 'json',
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $('#status_msg')
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast');
            $('#recover_password_form')[0].reset(); //reset form fields
            $('#search-spinner').addClass('d-none');
            setTimeout(function () {
              $(location).attr('href', redirect_url);
            }, 5000);
            enableSubmitBtn();
          } else {
            $('#status_msg')
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(5000)
              .fadeOut('slow');
            $('#search-spinner').addClass('d-none');
            enableSubmitBtn();
          }
        }, 2000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  //Change Password
  $('#change_pass_form').submit(function (e) {
    e.preventDefault();
    $('#search-spinner').removeClass('d-none');
    var form_data = $(this).serialize();
    var redirect_url = base_url + 'signin';
    disableSubmitBtn();
    $.ajax({
      url: base_url + 'recover_password/change_password_ajax',
      type: 'POST',
      data: form_data,
      dataType: 'json',
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $('#status_msg')
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast');
            $('#change_pass_form')[0].reset(); //reset form fields
            $('#search-spinner').addClass('d-none');
            setTimeout(function () {
              $(location).attr('href', redirect_url);
            }, 5000);
            enableSubmitBtn();
          } else {
            $('#status_msg')
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(5000)
              .fadeOut('slow');
            $('#search-spinner').addClass('d-none');
            enableSubmitBtn();
          }
        }, 2000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  //User login
  $('#user_login_form').submit(function (e) {
    e.preventDefault();
    $('#search-spinner').removeClass('d-none');
    var form_data = $(this).serialize();
    var redirect_url = $('#requested_page').val();
    disableSubmitBtn();
    $.ajax({
      url: base_url + 'user_login/login_ajax',
      type: 'POST',
      data: form_data,
      dataType: 'json',
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $('#user_login_form')[0].reset(); //reset form fields
            $('#search-spinner').addClass('d-none');
            setTimeout(function () {
              $(location).attr('href', redirect_url);
            }, 900);
            enableSubmitBtn();
          } else {
            $('#status_msg')
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  '</div>'
              )
              .fadeIn('fast')
              .delay(5000)
              .fadeOut('slow');
            $('#search-spinner').addClass('d-none');
            enableSubmitBtn();
          }
        }, 3000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  // Login - specific
  // $(document).ready(function () {
  //   // --- Send OTP ---
  //   $("#send_otp_btn").on("click", function (e) {
  //     e.preventDefault();

  //     var phone = $("#phone_number").val();
  //     var country_code = $("#country_code").val();

  //     if (!phone) {
  //       $("#status_msg")
  //         .html(
  //           '<div class="alert alert-danger text-center" style="color: #000">Please enter your phone number.</div>'
  //         )
  //         .fadeIn("fast")
  //         .delay(5000)
  //         .fadeOut("slow");
  //       return;
  //     }

  //     $("#send_otp_spinner").removeClass("d-none");
  //     $("#send_otp_btn").addClass("d-none");
  //     $("#status_msg").html("");

  //     var postData = {
  //       phone: phone,
  //       country_code: country_code,
  //     };
  //     postData[csrf_token_name] = csrf_token_hash;

  //     $.ajax({
  //       url: base_url + "user_login/send_otp",
  //       type: "POST",
  //       data: postData,
  //       dataType: "json",
  //       success: function (res) {
  //         update_csrf(res.csrf_hash); // Update CSRF token if you're sending it back

  //         if (res.status) {
  //           $("#status_msg")
  //             .html(
  //               '<div class="alert alert-success text-center" style="color: #000">Verification code sent!</div>'
  //             )
  //             .fadeIn("fast")
  //             .delay(5000)
  //             .fadeOut("slow");
  //           $("#send_otp_wrapper")
  //             .html('<span class="text-success">Verification code sent!</span>')
  //             .fadeIn("fast")
  //             .delay(5000)
  //             .fadeOut("slow");
  //           $("#otp1").focus(); // Focus on the first OTP box
  //         } else {
  //           $("#status_msg")
  //             .html(
  //               '<div class="alert alert-danger text-center" style="color: #000">' +
  //                 (res.msg || "Failed to send code.") +
  //                 "</div>"
  //             )
  //             .fadeIn("fast")
  //             .delay(5000)
  //             .fadeOut("slow");
  //           $("#send_otp_spinner").addClass("d-none");
  //           $("#send_otp_btn").removeClass("d-none");
  //         }
  //       },
  //       error: function (xhr, status, error) {
  //         $("#status_msg").html(
  //           '<div class="alert alert-danger text-center" style="color: #000">An error occurred. Please try again.</div>'
  //         );
  //         $("#send_otp_spinner").addClass("d-none");
  //         $("#send_otp_btn").removeClass("d-none");
  //       },
  //     });
  //   });

  //   // --- Verify OTP (Form Submission) ---
  //   $("#verify_otp_form").on("submit", function (e) {
  //     e.preventDefault();

  //     // Combine OTP fields into the hidden input
  //     var otp_code =
  //       $("#otp1").val() +
  //       $("#otp2").val() +
  //       $("#otp3").val() +
  //       $("#otp4").val();
  //     $("#full_otp_code").val(otp_code);

  //     if (otp_code.length !== 4) {
  //       $("#status_msg").html(
  //         '<div class="alert alert-danger text-center" style="color: #000">Please enter the 4-digit code.</div>'
  //       );
  //       return;
  //     }

  //     $("#login_spinner").removeClass("d-none");
  //     $("#submit_otp_btn").prop("disabled", true);
  //     $("#status_msg").html("");

  //     var postData = {
  //       otp: otp_code,
  //     };
  //     postData[csrf_token_name] = csrf_token_hash;

  //     $.ajax({
  //       url: $(this).attr("action"), // Get action from form
  //       type: "POST",
  //       data: postData,
  //       dataType: "json",
  //       success: function (res) {
  //         update_csrf(res.csrf_hash); // Update CSRF

  //         if (res.status) {
  //           $("#status_msg").html(
  //             '<div class="alert alert-success text-center" style="color: #000">Login Successful! Redirecting...</div>'
  //           );
  //           // Redirect to dashboard (or requested page)
  //           window.location.href = base_url + "dashboard";
  //         } else {
  //           $("#status_msg").html(
  //             '<div class="alert alert-danger text-center">' +
  //               (res.msg || "Invalid or expired OTP.") +
  //               "</div>"
  //           );
  //           $("#login_spinner").addClass("d-none");
  //           $("#submit_otp_btn").prop("disabled", false);
  //         }
  //       },
  //       error: function (xhr, status, error) {
  //         $("#status_msg").html(
  //           '<div class="alert alert-danger text-center" style="color: #000">An error occurred. Please try again.</div>'
  //         );
  //         $("#login_spinner").addClass("d-none");
  //         $("#submit_otp_btn").prop("disabled", false);
  //       },
  //     });
  //   });

  //   // --- OTP Input Auto-Focus ---
  //   $(".otp-input").on("keyup", function (e) {
  //     var $this = $(this);
  //     if ($this.val().length === $this.attr("maxlength")) {
  //       var $next = $this.next(".otp-input");
  //       if ($next.length) {
  //         $next.focus();
  //       }
  //     }
  //     // Handle Backspace
  //     if (e.key === "Backspace") {
  //       var $prev = $this.prev(".otp-input");
  //       if ($prev.length) {
  //         $prev.focus();
  //       }
  //     }
  //   });
  // });
});
