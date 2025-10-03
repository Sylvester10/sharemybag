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
                                                <li class="fw-500">${entry.heading}</li>
                                                <li class="text-muted">${entry.description}</li>
                                            </ul>
                                            <div class="row">
                                                <div class="col-6">
                                                    <ul class="list-unstyled">
                                                        <li class="fw-500">Date:</li>
                                                        <li class="text-muted">${entry.date_added}</li>
                                                    </ul>
                                                </div>
                                                <div class="col-6">
                                                    <ul class="list-unstyled">
                                                        <li class="fw-500">Delivery Status:</li>
                                                        <li class="text-muted">${entry.delivery_status} <span class="text-success text-3 ms-1">${entry.icon_text}</span></li>
                                                    </ul> 
                                                </div>
                                            </div>
                                            <hr>`;
						});
						$("#trackingHistory").html(trackingHistory);
					} else {
						var noResultsHtml = `<p class="text-center">Sorry, no parcel matches the tracking number. Please check the number and try again.</p>`;
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
