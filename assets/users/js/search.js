jQuery(document).ready(function ($) {
  ("use strict");

  //Search
  // 	$("#search_form").submit(function (e) {
  // 		e.preventDefault();
  // 		$("#search-spinner").removeClass("d-none");
  // 		$("#search-results").html("");
  // 		let val = $("#select_destination").val();
  // 		let url = $(this).attr("action");

  // 		if (val.trim() == "") {
  // 			return;
  // 		}

  // 		let form_data = $(this).serialize();
  // 		$.ajax({
  // 			url: url,
  // 			type: "POST",
  // 			data: form_data,
  // 			contentType: "application/x-www-form-urlencoded",
  // 			success: function (response) {
  // 				response = JSON.parse(response);
  // 				console.log(response);
  // 				if (response.status) {
  // 					let html_response = `<table class="table text-nowrap mb-0" id="section-1">
  //                                 <thead>
  //                                     <tr>
  //                                         <th scope="col"> <i class="ti ti-calendar fs-5"></i> Date</th>
  //                                         <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
  //                                         <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
  //                                         <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Airport </th>
  //                                         <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
  //                                         <th scope="col"> </th>
  //                                     </tr>
  //                                 </thead>
  //                                 <tbody>`;
  // 					response.travellers.forEach((traveller) => {
  // 						html_response += `<tr>
  //                                 <td> ${traveller.travel_date} </td>
  //                                 <td> ${traveller.days_remaining} </td>
  //                                 <td> ${traveller.current_state} </td>
  //                                 <td> ${traveller.departure_state} </td>
  //                                 <td> ${traveller.available_space} KG </td>
  //                                 <td>
  //                                     <a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                         Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                     </a>
  //                                 </td>
  //                               </tr>`;
  // 					});
  // 					html_response += `</tbody></table>`;
  // 					setTimeout(function () {
  // 						$("#search-results").html(html_response);
  // 						$("#search-spinner").addClass("d-none");
  // 					}, 2000);
  // 				} else {
  // 					// Handle no results
  // 					var noResultsHtml = `<div class="card mb-0">
  //                                 <div class="card-body">
  //                                     <div class="text-center">
  //                                         <video max-width="250" autoplay loop muted>
  //                                             <source src="${base_url}assets/users/images/illustrations/empty-note.webm" type="video/webm">
  //                                             Your browser does not support the video tag.
  //                                         </video>
  //                                     </div>
  //                                     <h4 class="card-title mb-3 mt-3 text-center">No Traveller Available</h4>
  //                                 </div>
  //                               </div>`;
  // 					setTimeout(function () {
  // 						$("#search-results").html(noResultsHtml);
  // 						$("#search-spinner").addClass("d-none");
  // 					}, 2000);
  // 				}
  // 			},
  // 			error: function (error) {
  // 				console.log(`This is the error ${error}`);
  // 			},
  // 		});
  // 	});

  // Search
  // 	$("#search_form").submit(function (e) {
  // 		e.preventDefault(); // Prevent default form submission
  // 		$("#search-spinner").removeClass("d-none"); // Show spinner
  // 		$("#search-results").html(""); // Clear previous search results
  // 		let val = $("#select_destination").val(); // Get the selected destination
  // 		let url = $(this).attr("action"); // Form action URL

  // 		// If the input is empty, return early
  // 		if (val.trim() == "") {
  // 			return;
  // 		}

  // 		let form_data = $(this).serialize(); // Serialize form data for AJAX
  // 		$.ajax({
  // 			url: url,
  // 			type: "POST",
  // 			data: form_data,
  // 			contentType: "application/x-www-form-urlencoded",
  // 			success: function (response) {
  // 				response = JSON.parse(response); // Parse the JSON response
  // 				console.log(response); // Log for debugging
  // 				if (response.status) {
  // 					let html_response = `
  // 					<div style="margin-top: 15px; margin-left: 15px; font-size: 13px;">
  //                         <p>Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
  //                     </div>

  // 					<table class="table text-nowrap mb-0" id="section-1">

  //                                 <thead>
  //                                     <tr>
  //                                         <th scope="col"> <i class="ti ti-calendar fs-5"></i>Travel Date</th>
  //                                         <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
  //                                         <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
  //                                         <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Departure Airport </th>
  //                                         <th scope="col"> <i class="ti ti-plane-arrival fs-5"></i> Arrival Airport </th>
  //                                         <th scope="col"> <i class="ti ti-map-pin fs-5"></i> Final Destination </th>
  //                                         <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
  //                                         <th scope="col"> </th>
  //                                     </tr>
  //                                 </thead>
  //                                 <tbody>`;

  // 					// Iterate over each traveller and generate table rows
  // 					response.travellers.forEach((traveller) => {
  // 						let buttonHtml = ""; // Store button HTML dynamically

  // 						// Check for "United Kingdom" destination
  // 						if (traveller.destination === "United Kingdom") {
  // 							// If `is_verified` is 0 or 1, show the modal-trigger button
  // 							if (traveller.is_verified === 0 || traveller.is_verified === 1) {
  // 								buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verifyID">
  //                                             Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                           </button>`;
  // 							} else {
  // 								// Otherwise, show the link button
  // 								buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                             Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                           </a>`;
  // 							}
  // 						} else {
  // 							// For all other destinations, show the link button
  // 							buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                         Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                       </a>`;
  // 						}

  // 						// Append the row with dynamically generated content
  // 						html_response += `<tr>
  //                                          <td> ${traveller.travel_date} </td>
  //                                         <td> ${traveller.days_remaining} </td>
  //                                         <td> ${traveller.current_state} </td>
  //                                         <td> ${traveller.departure_state} </td>
  //                                         <td> ${traveller.arrival_airport} </td>
  //                                         <td> ${traveller.arrival_state} </td>
  //                                         <td> ${traveller.available_space} KG </td>
  //                                         <td>
  //                                             ${buttonHtml}
  //                                         </td>
  //                                       </tr>`;
  // 					});

  // 					html_response += `</tbody></table>`; // Close the table
  // 					setTimeout(function () {
  // 						$("#search-results").html(html_response); // Display search results
  // 						$("#search-spinner").addClass("d-none"); // Hide spinner
  // 					}, 2000);
  // 				} else {
  // 					// Handle no travellers found
  // 					var noResultsHtml = `<div class="card mb-0">
  //                                 <div class="card-body">
  //                                     <div class="text-center">
  //                                         <video max-width="250" autoplay loop muted>
  //                                             <source src="${base_url}assets/users/images/illustrations/empty-note.webm" type="video/webm">
  //                                             Your browser does not support the video tag.
  //                                         </video>
  //                                     </div>
  //                                     <h4 class="card-title mb-3 mt-3 text-center">No Traveller Available</h4>
  //                                 </div>
  //                               </div>`;
  // 					setTimeout(function () {
  // 						$("#search-results").html(noResultsHtml); // Display "no results" card
  // 						$("#search-spinner").addClass("d-none"); // Hide spinner
  // 					}, 2000);
  // 				}
  // 			},
  // 			error: function (error) {
  // 				// Handle AJAX error
  // 				console.log(`This is the error ${error}`);
  // 			},
  // 		});
  // 	});

  //  Search
  //  $("#search_form").submit(function (e) {
  //     e.preventDefault(); // Prevent default form submission
  //     $("#search-spinner").removeClass("d-none"); // Show spinner
  //     $("#search-results").html(""); // Clear previous search results
  //     let val = $("#select_destination").val(); // Get the selected destination
  //     let url = $(this).attr("action"); // Form action URL

  //     // If the input is empty, return early
  //     if (val.trim() == "") {
  //         return;
  //     }

  //     let form_data = $(this).serialize(); // Serialize form data for AJAX
  //     $.ajax({
  //         url: url,
  //         type: "POST",
  //         data: form_data,
  //         contentType: "application/x-www-form-urlencoded",
  //         success: function (response) {
  //             response = JSON.parse(response); // Parse the JSON response
  //             console.log(response); // Log for debugging
  //             let html_response = ''; // Declare html_response once here

  //             if (response.status) {
  //                 // Check for "Nigeria" destination
  //                 if (val === "Nigeria") {
  //                     html_response += `
  //                     <div style="margin-top: 15px; margin-left: 15px; font-size: 13px;">
  //                         <p>Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
  //                     </div>

  //                     <table class="table text-nowrap mb-0" id="section-1">
  //                         <thead>
  //                             <tr>
  //                                 <th scope="col"> <i class="ti ti-calendar fs-5"></i>Travel Date</th>
  //                                 <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
  //                                 <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
  //                                 <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Departure Airport </th>
  //                                 <th scope="col"> <i class="ti ti-plane-arrival fs-5"></i> Arrival Airport </th>
  //                                 <th scope="col"> <i class="ti ti-map-pin fs-5"></i> Final Destination </th>
  //                                 <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
  //                                 <th scope="col"> </th>
  //                             </tr>
  //                         </thead>
  //                         <tbody>`;
  //                 } else {
  //                     html_response += `
  //                     <div style="margin-top: 15px; margin-left: 15px; font-size: 13px;">
  //                         <p>Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
  //                     </div>

  //                     <table class="table text-nowrap mb-0" id="section-1">
  //                         <thead>
  //                             <tr>
  //                                 <th scope="col"> <i class="ti ti-calendar fs-5"></i>Travel Date</th>
  //                                 <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
  //                                 <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
  //                                 <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Departure Airport </th>
  //                                 <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
  //                                 <th scope="col"> </th>
  //                             </tr>
  //                         </thead>
  //                         <tbody>`;
  //                 }

  //                 // Iterate over each traveller and generate table rows
  //                 response.travellers.forEach((traveller) => {
  //                     let buttonHtml = ""; // Store button HTML dynamically

  //                     // Check for "United Kingdom" destination
  //                     if (traveller.destination === "United Kingdom") {
  //                         // If `is_verified` is 0 or 1, show the modal-trigger button
  //                         if (traveller.is_verified === 0 || traveller.is_verified === 1) {
  //                             buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verifyID">
  //                                           Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                         </button>`;
  //                         } else {
  //                             // Otherwise, show the link button
  //                             buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                           Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                         </a>`;
  //                         }
  //                     } else {
  //                         // For all other destinations, show the link button
  //                         buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                     Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                   </a>`;
  //                     }

  //                     // Append the row with dynamically generated content based on destination
  //                     if (traveller.destination === "Nigeria") {
  //                         html_response += `<tr>
  //                                             <td> ${traveller.travel_date} </td>
  //                                             <td> ${traveller.days_remaining} </td>
  //                                             <td> ${traveller.current_state} </td>
  //                                             <td> ${traveller.departure_state} </td>
  //                                             <td> ${traveller.arrival_airport} </td>
  //                                             <td> ${traveller.arrival_state} </td>
  //                                             <td> ${traveller.available_space} KG </td>
  //                                             <td>${buttonHtml}</td>
  //                                           </tr>`;
  //                     } else {
  //                         html_response += `<tr>
  //                                             <td> ${traveller.travel_date} </td>
  //                                             <td> ${traveller.days_remaining} </td>
  //                                             <td> ${traveller.current_state} </td>
  //                                             <td> ${traveller.departure_state} </td>
  //                                             <td> ${traveller.available_space} KG </td>
  //                                             <td>${buttonHtml}</td>
  //                                           </tr>`;
  //                     }
  //                 });

  //                 html_response += `</tbody></table>`; // Close the table
  //                 setTimeout(function () {
  //                     $("#search-results").html(html_response); // Display search results
  //                     $("#search-spinner").addClass("d-none"); // Hide spinner
  //                 }, 2000);
  //             } else {
  //                 // Handle no travellers found
  //                 var noResultsHtml = `<div class="card mb-0">
  //                                 <div class="card-body">
  //                                     <div class="text-center">
  //                                         <video max-width="250" autoplay loop muted>
  //                                             <source src="${base_url}assets/users/images/illustrations/empty-note.webm" type="video/webm">
  //                                             Your browser does not support the video tag.
  //                                         </video>
  //                                     </div>
  //                                     <h4 class="card-title mb-3 mt-3 text-center">No Traveller Available</h4>
  //                                 </div>
  //                               </div>`;
  //                 setTimeout(function () {
  //                     $("#search-results").html(noResultsHtml); // Display "no results" card
  //                     $("#search-spinner").addClass("d-none"); // Hide spinner
  //                 }, 2000);
  //             }
  //         },
  //         error: function (error) {
  //             // Handle AJAX error
  //             console.log(`This is the error ${error}`);
  //         },
  //     });
  // });

  //  Search
  // 	$("#search_form").submit(function (e) {
  // 		e.preventDefault(); // Prevent default form submission
  // 		$("#search-spinner").removeClass("d-none"); // Show spinner
  // 		$("#search-results").html(""); // Clear previous search results
  // 		let val = $("#select_destination").val(); // Get the selected destination
  // 		let url = $(this).attr("action"); // Form action URL

  // 		// If the input is empty, return early
  // 		if (val.trim() == "") {
  // 			return;
  // 		}

  // 		let form_data = $(this).serialize(); // Serialize form data for AJAX
  // 		$.ajax({
  // 			url: url,
  // 			type: "POST",
  // 			data: form_data,
  // 			contentType: "application/x-www-form-urlencoded",
  // 			success: function (response) {
  // 				response = JSON.parse(response); // Parse the JSON response
  // 				console.log(response); // Log for debugging
  // 				let html_response = ""; // Declare html_response once here

  // 				if (response.status) {
  // 					html_response += `
  //                     <div style="margin-top: 15px; margin-left: 15px; font-size: 13px;">
  //                         <p>Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
  //                     </div>

  //                     <table class="table text-nowrap mb-0" id="section-1">
  //                         <thead>
  //                             <tr>
  //                                 <th scope="col"> <i class="ti ti-calendar fs-5"></i>Travel Date</th>
  //                                 <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
  //                                 <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
  //                                 <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Departure Airport </th>
  //                                 <th scope="col"> <i class="ti ti-plane-arrival fs-5"></i> Arrival Airport </th>
  //                                 <th scope="col"> <i class="ti ti-map-pin fs-5"></i> Final Destination </th>
  //                                 <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
  //                                 <th scope="col"> </th>
  //                             </tr>
  //                         </thead>
  //                         <tbody>`;

  // 					// Iterate over each traveller and generate table rows
  // 				// 	response.travellers.forEach((traveller) => {
  // 				// 		let buttonHtml = ""; // Store button HTML dynamically

  // 				// 		// Check for "United Kingdom" destination
  // 				// 		if (traveller.destination === "United Kingdom") {
  // 				// 			// If `is_verified` is 0 or 1, show the modal-trigger button
  // 				// 			if (traveller.is_verified === 0 || traveller.is_verified === 1) {
  // 				// 				buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verifyID">
  //     //                                       Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //     //                                     </button>`;
  // 				// 			} else {
  // 				// 				// Otherwise, show the link button
  // 				// 				buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //     //                                       Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //     //                                     </a>`;
  // 				// 			}
  // 				// 		} else {
  // 				// 			// For all other destinations, show the link button
  // 				// 			buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //     //                                 Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //     //                               </a>`;
  // 				// 		}

  // 				// 		html_response += `<tr>
  //     //                                         <td> ${traveller.travel_date} </td>
  //     //                                         <td> ${traveller.days_remaining} </td>
  //     //                                         <td> ${traveller.current_state} </td>
  //     //                                         <td> ${traveller.departure_state} </td>
  //     //                                         <td> ${traveller.arrival_airport} </td>
  //     //                                         <td> ${traveller.arrival_state} </td>
  //     //                                         <td> ${traveller.available_space} KG </td>
  //     //                                         <td>${buttonHtml}</td>
  //     //                                       </tr>`;
  // 				// 	});

  // 				    // Iterate over each traveller and generate table rows
  // 					response.travellers.forEach((traveller) => {
  // 						let buttonHtml = ""; // Store button HTML dynamically

  // 						// Check for "United Kingdom" destination
  // 						if (traveller.destination === "United Kingdom") {
  // 							// If `is_verified` is 0 or 1, show the modal-trigger button
  // 							if (traveller.is_verified === 0 || traveller.is_verified === 1) {
  // 								buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verifyID">
  //                                           Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                         </button>`;
  // 							} else {
  // 								// Otherwise, show the link button
  // 								buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                           Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                         </a>`;
  // 							}
  // 						} else if (traveller.destination === "Nigeria") {
  // 							// If `profile_completed` is 0 or 1, show the modal-trigger button
  // 							if (traveller.profile_completed === 0) {
  // 								buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goToProfile">
  // 								Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  // 								</button>`;
  // 							} else {
  // 								// Otherwise, show the link button
  // 								buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  // 								Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  // 								</a>`;
  // 							}
  // 						} else {
  // 							// For all other destinations, show the link button
  // 							buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.id}" class="btn btn-primary">
  //                                     Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
  //                                   </a>`;
  // 						}

  // 						html_response += `<tr>
  //                                             <td> ${traveller.travel_date} </td>
  //                                             <td> ${traveller.days_remaining} </td>
  //                                             <td> ${traveller.current_state} </td>
  //                                             <td> ${traveller.departure_state} </td>
  //                                             <td> ${traveller.arrival_airport} </td>
  //                                             <td> ${traveller.arrival_state} </td>
  //                                             <td> ${traveller.available_space} KG </td>
  //                                             <td>${buttonHtml}</td>
  //                                           </tr>`;
  // 					});

  // 					html_response += `</tbody></table>`; // Close the table
  // 					setTimeout(function () {
  // 						$("#search-results").html(html_response); // Display search results
  // 						$("#search-spinner").addClass("d-none"); // Hide spinner
  // 					}, 2000);
  // 				} else {
  // 					// Handle no travellers found
  // 					var noResultsHtml = `<div class="card mb-0">
  //                                 <div class="card-body">
  //                                     <div class="text-center">
  //                                         <video max-width="250" autoplay loop muted>
  //                                             <source src="${base_url}assets/users/images/illustrations/empty-note.webm" type="video/webm">
  //                                             Your browser does not support the video tag.
  //                                         </video>
  //                                     </div>
  //                                     <h4 class="card-title mb-3 mt-3 text-center">No Traveller Available</h4>
  //                                 </div>
  //                               </div>`;
  // 					setTimeout(function () {
  // 						$("#search-results").html(noResultsHtml); // Display "no results" card
  // 						$("#search-spinner").addClass("d-none"); // Hide spinner
  // 					}, 2000);
  // 				}
  // 			},
  // 			error: function (error) {
  // 				// Handle AJAX error
  // 				console.log(`This is the error ${error}`);
  // 			},
  // 		});
  // 	});

  // Search
  $("#search_form").submit(function (e) {
    e.preventDefault(); // Prevent default form submission
    $("#search-spinner").removeClass("d-none"); // Show spinner
    $("#search-results").html(""); // Clear previous search results
    let val = $("#select_destination").val(); // Get the selected destination
    let url = $(this).attr("action"); // Form action URL

    // If the input is empty, return early
    if (val.trim() == "") {
      return;
    }

    let form_data = $(this).serialize(); // Serialize form data for AJAX
    $.ajax({
      url: url,
      type: "POST",
      data: form_data,
      contentType: "application/x-www-form-urlencoded",
      success: function (response) {
        response = JSON.parse(response); // Parse the JSON response
        console.log(response); // Log for debugging
        let html_response = ""; // Declare html_response once here

        if (response.status) {
          html_response += `
                    <div style="margin-top: 15px; margin-left: 15px; font-size: 13px;">
                        <p>Swipe to view more details <i class="ti ti-arrow-right text-primary"></i></p>
                    </div>

                    <table class="table text-nowrap mb-0" id="section-1">
                        <thead>
                            <tr>
                                <th scope="col"> <i class="ti ti-calendar fs-5"></i>Travel Date</th>
                                <th scope="col"> <i class="ti ti-clock fs-5"></i> Time Left</th>
                                <th scope="col"> <i class="ti ti-map fs-5"></i> Current Location</th>
                                <th scope="col"> <i class="ti ti-plane-departure fs-5"></i> Departure Airport </th>
                                <th scope="col"> <i class="ti ti-plane-arrival fs-5"></i> Arrival Airport </th>
                                <th scope="col"> <i class="ti ti-map-pin fs-5"></i> Final Destination </th>
                                <th scope="col"> <i class="ti ti-weight fs-5"></i> Available Space</th>
                                <th scope="col"> </th>
                            </tr>
                        </thead>
                        <tbody>`;

          // Iterate over each traveller and generate table rows
          response.travellers.forEach((traveller) => {
            let buttonHtml = ""; // Store button HTML dynamically

            // Check for "United Kingdom" destination
            if (traveller.destination === "United Kingdom") {
              if (traveller.is_verified === 0 || traveller.is_verified === 1) {
                buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verifyID">
										Buy Space <i class='ti ti-arrow-up-right-circle'></i>
									</button>`;
              } else {
                if (traveller.available_space == 0) {
                  buttonHtml = `<button class="btn btn-danger">
										Bag is Full <i class='ti ti-luggage-off'></i> 
									</button>`;
                } else {
                  buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.hash}" class="btn btn-success">
										Buy Space <i class='ti ti-luggage'></i>
									</a>`;
                }
              }
            } else if (traveller.destination === "Nigeria") {
              if (traveller.profile_completed === 0) {
                buttonHtml = `<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goToProfile">
										Buy Space <i class='ti ti-arrow-up-right-circle fs-5'></i>
									</button>`;
              } else {
                if (traveller.available_space == 0) {
                  buttonHtml = `<button class="btn btn-danger">
										Bag is Full <i class='ti ti-luggage-off'></i> 
									</button>`;
                } else {
                  buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.hash}" class="btn btn-success">
										Buy Space <i class='ti ti-luggage'></i>
									</a>`;
                }
              }
            } else {
              buttonHtml = `<a href="${base_url}buy-bag-space/${traveller.hash}" class="btn btn-success">
										Buy Spaces <i class='ti ti-luggage'></i>
									</a>`;
            }

            html_response += `<tr>
                                            <td> ${traveller.travel_date} </td>
                                            <td> ${traveller.days_remaining} </td>
                                            <td> ${traveller.current_state} </td>
                                            <td> ${traveller.departure_state} </td>
                                            <td> ${traveller.arrival_airport} </td>
                                            <td> ${traveller.arrival_state} </td>
                                            <td> ${traveller.available_space} KG </td>
                                            <td>${buttonHtml}</td>
                                          </tr>`;
          });

          html_response += `</tbody></table>`; // Close the table
          setTimeout(function () {
            $("#search-results").html(html_response); // Display search results
            $("#search-spinner").addClass("d-none"); // Hide spinner
          }, 2000);
        } else {
          // Handle no travellers found
          var noResultsHtml = `<div class="card mb-0">
                                <div class="card-body">
                                    <div class="text-center">
                                        <video max-width="250" autoplay loop muted>
                                            <source src="${base_url}assets/users/images/illustrations/empty-note.webm" type="video/webm">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                    <h4 class="card-title mb-3 mt-3 text-center">No Traveller Available</h4>
                                </div>
                              </div>`;
          setTimeout(function () {
            $("#search-results").html(noResultsHtml); // Display "no results" card
            $("#search-spinner").addClass("d-none"); // Hide spinner
          }, 2000);
        }
      },
      error: function (error) {
        // Handle AJAX error
        console.log(`This is the error ${error}`);
      },
    });
  });
});
