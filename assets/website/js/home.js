jQuery(document).ready(function ($) {
    ('use strict');

    var csrfTokenName = $('#homepage_csrf_name').val() || null;
    var csrfHashInput = $('#homepage_csrf_hash');

    // function appendCsrf(data) {
    //     if (!csrfTokenName || !csrfHashInput.length) {
    //         return data;
    //     }

    //     if (typeof data === 'string') {
    //         return data + '&' + encodeURIComponent(csrfTokenName) + '=' + encodeURIComponent(csrfHashInput.val());
    //     }

    //     data[csrfTokenName] = csrfHashInput.val();
    //     return data;
    // }

    function appendCsrf(data) {
        if (!csrfTokenName || !csrfHashInput.length) {
            return data;
        }

        if (data instanceof FormData) {
            data.append(csrfTokenName, csrfHashInput.val());
            return data;
        }

        if (typeof data === 'string') {
            return (
                data +
                '&' +
                encodeURIComponent(csrfTokenName) +
                '=' +
                encodeURIComponent(csrfHashInput.val())
            );
        }

        data[csrfTokenName] = csrfHashInput.val();
        return data;
    }

    function updateCsrf(newHash) {
        if (!newHash || !csrfHashInput.length) {
            return;
        }

        csrfHashInput.val(newHash);
        $('#search_form')
            .find('input[name="' + csrfTokenName + '"]')
            .val(newHash);
    }

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

    function resetTravellerFormUi() {
        var travellerForm = $('#traveller_form');
        if (!travellerForm.length) {
            return;
        }

        travellerForm[0].reset();
        travellerForm.find('select').each(function () {
            $(this).niceSelect('update');
        });
        travellerForm.find('.location-flag, .destination-flag').addClass('d-none');
        $('#status_msg').html('').hide();
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
            $('#search-spinner').addClass('d-none');
            return;
        }

        let form_data = appendCsrf($(this).serialize());
        disableSubmitBtn();

        $.ajax({
            url: url,
            type: 'POST',
            data: form_data,
            contentType: 'application/x-www-form-urlencoded',
            success: function (response) {
                response = JSON.parse(response);
                updateCsrf(response.csrf_hash);
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

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

                    $('body').addClass('search-active');
                    $('#search-results').html(html_response);
                } else {
                    // Handle case when response.status is false (e.g., no results found)
                    var noResultsHtml = `
                      <section id="section-1" class="bg-white rounded shadow-md">
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

                    $('body').addClass('search-active');
                    $('#search-results').html(noResultsHtml);
                }
            },
            error: function (error) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#search-results').html(
                    '<div class="alert alert-danger text-center mt-3 shadow-md">An error occurred while searching. Please try again.</div>'
                );
                $('body').addClass('search-active');
            },
        });
    });

    // Traveller form
    $('#traveller_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');

        var form_data = new FormData(this);
        form_data = appendCsrf(form_data);

        disableSubmitBtn();

        $.ajax({
            url: base_url + 'home/add_traveller_ajax',
            type: 'POST',
            data: form_data,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            success: function (res) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                updateCsrf(res.csrf_hash);

                if (res.status) {
                    resetTravellerFormUi();
                    $('#travellerSuccessModal').modal('show');
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
                }
            },
            error: function (xhr) {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">' +
                            (xhr.status === 403
                                ? 'The form request was blocked. Please refresh the page and try again.'
                                : 'Server error. Please try again.') +
                            '</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    $(document).on('click', '.traveller-scroll-trigger', function (e) {
        var targetId = $(this).attr('href');
        if (!targetId || targetId.charAt(0) !== '#') {
            return;
        }

        var target = $(targetId);
        if (!target.length) {
            return;
        }

        e.preventDefault();
        $('html, body').animate(
            {
                scrollTop: target.offset().top - 80,
            },
            700
        );
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
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#signup_form')[0].reset();
                    setTimeout(function () {
                        $(location).attr('href', redirect_url);
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
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
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#verify_email_form')[0].reset();
                    setTimeout(function () {
                        $(location).attr('href', redirect_url);
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
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
                $('#search-spinners').addClass('d-none');

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(3000)
                        .fadeOut('slow');
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinners').addClass('d-none');
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
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
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#recover_password_form')[0].reset();
                    setTimeout(function () {
                        $(location).attr('href', redirect_url);
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
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
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-success text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast');
                    $('#change_pass_form')[0].reset();
                    setTimeout(function () {
                        $(location).attr('href', redirect_url);
                    }, 1500);
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
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
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();

                if (res.status) {
                    $('#user_login_form')[0].reset();
                    window.location.href = redirect_url; // Instant redirect
                } else {
                    $('#status_msg')
                        .html(
                            '<div class="alert alert-danger text-center" style="color: #000">' +
                                res.msg +
                                '</div>'
                        )
                        .fadeIn('fast')
                        .delay(4000)
                        .fadeOut('slow');
                }
            },
            error: function () {
                $('#search-spinner').addClass('d-none');
                enableSubmitBtn();
                $('#status_msg')
                    .html(
                        '<div class="alert alert-danger text-center">Server error. Please try again.</div>'
                    )
                    .fadeIn('fast')
                    .delay(4000)
                    .fadeOut('slow');
            },
        });
    });

    //Date Picker
    $('#travelDate').daterangepicker(
        {
            singleDatePicker: true,
            minDate: moment(),
            autoUpdateInput: false,
            autoApply: true,
        },
        function (chosen_date) {
            $('#travelDate').val(chosen_date.format('YYYY-MM-DD'));
        }
    );

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
