jQuery(document).ready(function ($) {
  ("use strict");

  $("#submit-me").click(function (e) {
    e.preventDefault();
    $("#search-spinner").removeClass("d-none");
    $("#tracking-detail").modal("show"); // Correct modal ID

    let val = $("#parcel-input").val();
    let url = $("#tracking_form").attr("action");

    if (val.trim() == "") {
      var noResultsHtml = `<p class="text-center">Please enter a tracking number.</p>`;
      $("#trackingHistory").html(noResultsHtml);
      $("#search-spinner").addClass("d-none");
      return;
    }

    let form_data = $("#tracking_form").serialize();

    $.ajax({
      url: url,
      type: "POST",
      data: form_data,
      contentType: "application/x-www-form-urlencoded",
      success: function (response) {
        try {
          response = JSON.parse(response);

          if (response.status) {
            $("#m-trackingNumber").html(response.data[0].tracking_id);

            let trackingHistory = "";
            response.data.forEach((entry) => {
              trackingHistory += `<ul class="list-unstyled">
                                                <li class="fw-bolder text-black">${entry.heading}</li>
                                                <li class="card-subtitle">${entry.description}</li>
                                            </ul>
                                            <div class="row">
                                                <div class="col-6">
                                                    <ul class="list-unstyled">
                                                        <li class="fw-bolder text-black">Date:</li>
                                                        <li class="card-subtitle">${entry.date_added}</li>
                                                    </ul>
                                                </div>
                                                <div class="col-6">
                                                    <ul class="list-unstyled">
                                                        <li class="fw-bolder text-black">Delivery Status:</li>
                                                        <li class="card-subtitle">${entry.delivery_status} <span class="text-success text-3 ms-1">${entry.icon_text}</span></li>
                                                    </ul> 
                                                </div>
                                            </div>
                                            <hr>`;
            });
            $("#trackingHistory").html(trackingHistory);
          } else {
            var noResultsHtml = `<p class="text-center">We'll update this page, once the traveller receives your parcel.</p>`;
            $("#trackingHistory").html(noResultsHtml);
          }
        } catch (error) {
          console.log("Parsing error: ", error);
        }
        $("#search-spinner").addClass("d-none");
      },
      error: function (error) {
        console.log("This is the error", error);
      },
    });
  });

  $("#tracking_form").submit(function (e) {
    e.preventDefault();
  });
});
