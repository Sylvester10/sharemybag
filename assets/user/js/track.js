jQuery(document).ready(function ($) {
	("use strict");

	$("#submit-me").click(function (e) {
		e.preventDefault();
		$("#search-spinner").removeClass("d-none");
		$("#modal-dialog").removeClass("d-none");
		let val = $("#parcel-input").val();
		let url = $("#tracking_form").attr("action");

		if (val.trim() == "") {
			var noResultsHtml = ``;
			$("#trackingHistory").html(noResultsHtml);
			$("#search-spinner").addClass("d-none");
			$("#modal-dialog").removeClass("d-none");
			$("#modal-dialog").children(".small-dialog-header.sub").addClass("d-none");
			return;
		}
		$("#modal-dialog").children(".small-dialog-header.sub").removeClass("d-none");

		let form_data = $("#tracking_form").serialize();
		$.ajax({
			url: url,
			type: "POST",
			data: form_data,
			contentType: "application/x-www-form-urlencoded",
			success: function (response) {
				response = JSON.parse(response);
				console.log(response);
				if (response.status) {
					$("#m-trackingNumber").html(response.data[0].tracking_id);
					var trackingHistory = "";

					for (var i = 0; i < response.data.length; i++) {
						// Loop over each data entry
						trackingHistory += `<h6>${response.data[i].icon_text} ${response.data[i].heading}</h6>
											<p>${response.data[i].description}</p>
											<p class="date">${response.data[i].date_added}</p>`;
					}

					$("#trackingHistory").html(trackingHistory);
					$("#search-spinner").addClass("d-none");
				} else {
					// Handle case when response.status is falsy (e.g., no results found)
					var noResultsHtml = `<div class="small-dialog-header">
											<h3>Tracking Information</h3>
										</div>
										<div class="table_wrapper">
											<div class="cart-list menu-gallery parcel">
												<div class="parcel-box">
													<div class="content">
														<p class="text-center">Sorry, no parcel matches the tracking number. Please check the number and try again.</p>
													</div>
												</div>
											</div>
										</div>`;

					$("#modal-dialog").html(noResultsHtml);
					$("#search-spinner").addClass("d-none");
				}
			},

			error: function (error) {
				console.log(`This is the error ${error}`);
			},
		});
	});
	//Search
	$("#tracking_form").submit(function (e) {
		e.preventDefault();
	});
});
