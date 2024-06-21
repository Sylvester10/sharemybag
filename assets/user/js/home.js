jQuery(document).ready(function ($) {
	("use strict");

	//Search
	$("#search_form").submit(function (e) {
		e.preventDefault();
		$("#search-spinner").removeClass("d-none");
		$("#search-results").html("");
		let val = $("#select_destination").val();
		let url = $(this).attr("action");

		if (val.trim() == "") {
			return;
		}

		let form_data = $(this).serialize();
		$.ajax({
			url: url,
			type: "POST",
			data: form_data,
			contentType: "application/x-www-form-urlencoded",
			success: function (response) {
				response = JSON.parse(response);
				console.log(response);
				if (response.status) {
					var html_response = `<section id="section-1">
					<h5>Next Traveller</h5>
					<div class="table_wrapper">
						<div class="cart-list menu-gallery parcel">
							<div class="parcel-box">
								<div class="search_result">
									<h4><i class="fa-solid fa-calendar"></i><br>Date</h4>
									<p>${response.travel_date}</p>
								</div>
								<div class="search_result">
									<h4><i class="fa-solid fa-clock"></i><br>Time left</h4>
									<p>${response.days_remaining}</p>
								</div>
								<div class="search_result">
									<h4><i class="fa-solid fa-map"></i><br>Current Location</h4>
									<p>${response.current_state} </p>
								</div>
								<div class="search_result">
									<h4><i class="fa-solid fa-plane-departure"></i><br>Departure State</h4>
									<p>${response.departure_state}</p>
								</div>
								<div class="search_result">
									<h4><i class="fa-solid fa-weight-hanging"></i><br>Available space</h4>
									<p>${response.available_space} kg</p>
								</div>
							</div>
							<div class="search_result">
								<h4></h4>
								<a href="${base_url}booking/${response.id}" class="btn_1 gradient" type="submit">Buy Space</a>
							</div>
						</div>
					</div>
					</section>`;
					setTimeout(function () {
						$("#search-results").html(html_response);
						$("#search-spinner").addClass("d-none");
					}, 4000);
				} else {
					// Handle case when response.status is false (e.g., no results found)
					var noResultsHtml = `<section id="section-1">
					<h5>No traveler Available</h5>
					<br>
					<a href="${base_url}home/join_waitlist" class="btn_1 gradient">Join Wait List</a>
					</section>`;
					setTimeout(function () {
						$("#search-results").html(noResultsHtml);
						$("#search-spinner").addClass("d-none");
					}, 2000);
				}
			},
			error: function (error) {
				console.log(`This is the error ${error}`);
			},
		});
	});
});
