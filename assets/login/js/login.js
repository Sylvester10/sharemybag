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

  // Existing auto-move and paste logic for OTP fields...
  const otpInputs = document.querySelectorAll(
    ".otp-input-container .otp-input"
  );
  const hiddenVerificationCodeInput =
    document.getElementById("verificationCode");

  otpInputs.forEach((input, index) => {
    input.addEventListener("input", () => {
      if (input.value.length === 1 && index < otpInputs.length - 1) {
        otpInputs[index + 1].focus();
      }
    });
    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && index > 0 && input.value === "") {
        otpInputs[index - 1].focus();
      }
    });
  });

  otpInputs[0].addEventListener("paste", (e) => {
    e.preventDefault();
    const pastedData = (e.clipboardData || window.clipboardData).getData(
      "text"
    );
    const otp = pastedData.replace(/\D/g, "").slice(0, otpInputs.length);

    otp.split("").forEach((char, index) => {
      if (otpInputs[index]) {
        otpInputs[index].value = char;
      }
    });

    if (otp.length === otpInputs.length) {
      otpInputs[otp.length - 1].focus();
    } else {
      otpInputs[otp.length].focus();
    }
  });

  //Sign up
  $("#signup_form").submit(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none");
    var form_data = $(this).serialize();
    var redirect_url = base_url + "verify-email";
    disableSubmitBtn();
    $.ajax({
      url: base_url + "registration/signup",
      type: "POST",
      data: form_data,
      dataType: "json",
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
              .fadeIn("fast");
            $("#signup_form")[0].reset(); //reset form fields
            $("#search-spinner").addClass("d-none");
            setTimeout(function () {
              $(location).attr("href", redirect_url);
            }, 4000);
            enableSubmitBtn();
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
            $("#search-spinner").addClass("d-none");
            enableSubmitBtn();
          }
        }, 3000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  // verify email
  $("#verify_email_form").submit(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none");

    // 1. Combine the OTP values here
    let combinedCode = "";
    otpInputs.forEach((input) => {
      combinedCode += input.value;
    });

    // 2. Add the combined code to the form data
    var form_data = $(this).serialize();
    form_data += "&verification_code=" + combinedCode;

    var redirect_url = base_url + "signin";
    disableSubmitBtn();

    $.ajax({
      url: base_url + "registration/verify_email_ajax",
      type: "POST",
      data: form_data,
      dataType: "json",
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
              .fadeIn("fast");
            $("#verify_email_form")[0].reset(); //reset form fields
            $("#search-spinner").addClass("d-none");
            setTimeout(function () {
              $(location).attr("href", redirect_url);
            }, 4000);
            enableSubmitBtn();
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
            $("#search-spinner").addClass("d-none");
            enableSubmitBtn();
          }
        }, 2000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  // Resend Verification email
  $("#resend_verification_email").click(function () {
    $("#search-spinners").removeClass("d-none"); // Show spinner

    $.ajax({
      url: base_url + "registration/resend_verification_email_ajax",
      type: "POST",
      dataType: "json",
      success: function (res) {
        // Delay handling for the success message
        setTimeout(function () {
          if (res.status) {
            $("#status_msg")
              .html(
                '<div class="alert alert-success text-center" style="color: #000">' +
                  res.msg +
                  "</div>"
              )
              .fadeIn("fast")
              .delay(3000) // Show the success message for 5 seconds
              .fadeOut("slow"); // Then fade it out
          } else {
            $("#status_msg")
              .html(
                '<div class="alert alert-danger text-center" style="color: #000">' +
                  res.msg +
                  "</div>"
              )
              .fadeIn("fast")
              .delay(3000)
              .fadeOut("slow");
          }
          $("#search-spinners").addClass("d-none"); // Hide spinner
        }, 2000); // 2000 milliseconds (2 seconds) delay
      },
      error: function () {
        $("#status_msg")
          .html(
            '<div class="alert alert-danger text-center" style="color: #000">' +
              "An error occurred. Please try again." +
              "</div>"
          )
          .fadeIn("fast")
          .delay(5000)
          .fadeOut("slow");
        $("#search-spinners").addClass("d-none"); // Hide spinner
      },
    });
  });

  //Recover Password
  $("#recover_password_form").submit(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none");
    var form_data = $(this).serialize();
    var redirect_url = base_url + "signin";
    disableSubmitBtn();
    $.ajax({
      url: base_url + "recover_password/password_recovery_ajax",
      type: "POST",
      data: form_data,
      dataType: "json",
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
              .fadeIn("fast");
            $("#recover_password_form")[0].reset(); //reset form fields
            $("#search-spinner").addClass("d-none");
            setTimeout(function () {
              $(location).attr("href", redirect_url);
            }, 5000);
            enableSubmitBtn();
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
            $("#search-spinner").addClass("d-none");
            enableSubmitBtn();
          }
        }, 2000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  //Change Password
  $("#change_pass_form").submit(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none");
    var form_data = $(this).serialize();
    var redirect_url = base_url + "signin";
    disableSubmitBtn();
    $.ajax({
      url: base_url + "recover_password/change_password_ajax",
      type: "POST",
      data: form_data,
      dataType: "json",
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
              .fadeIn("fast");
            $("#change_pass_form")[0].reset(); //reset form fields
            $("#search-spinner").addClass("d-none");
            setTimeout(function () {
              $(location).attr("href", redirect_url);
            }, 5000);
            enableSubmitBtn();
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
            $("#search-spinner").addClass("d-none");
            enableSubmitBtn();
          }
        }, 2000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });

  //User login
  $("#user_login_form").submit(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none");
    var form_data = $(this).serialize();
    var redirect_url = $("#requested_page").val();
    disableSubmitBtn();
    $.ajax({
      url: base_url + "user_login/login_ajax",
      type: "POST",
      data: form_data,
      dataType: "json",
      success: function (res) {
        // Delay success message for 5 seconds
        setTimeout(function () {
          if (res.status) {
            $("#user_login_form")[0].reset(); //reset form fields
            $("#search-spinner").addClass("d-none");
            setTimeout(function () {
              $(location).attr("href", redirect_url);
            }, 900);
            enableSubmitBtn();
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
            $("#search-spinner").addClass("d-none");
            enableSubmitBtn();
          }
        }, 3000); // 5000 milliseconds (5 seconds) delay
      },
    });
  });
});
