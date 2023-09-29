jQuery(document).ready(function ($) {
	("use strict");

	/*=========== Enable Button ===========*/
	function enableSubmitBtn() {
		var submitButton = $("#submit");
		submitButton.html("I have made payments");
		submitButton.attr("disabled", false);
		submitButton.removeClass("disabled");
	}

	/*=========== Disable Button ===========*/
	function disableSubmitBtn() {
		var submitButton = $("#submit");
		submitButton.html("Booking...");
		submitButton.attr("disabled", true);
		submitButton.addClass("disabled");
	}

	/*=========== Enable Prev Button ===========*/
	function enablePrevSubmitBtn() {
		var submitButton = $("#prev");
		submitButton.html("Prev");
		submitButton.attr("disabled", false);
		submitButton.removeClass("disabled");
	}

	/*=========== Disable Prev Button ===========*/
	function disablePrevSubmitBtn() {
		var submitButton = $("#prev");
		submitButton.html("Prev");
		submitButton.attr("disabled", true);
		submitButton.addClass("disabled");
	}

	/*=========== Enable Loading Button ===========*/
	function enableLoadingBtn() {
		var submitButton = $("#booking-spinner");
		submitButton.attr("disabled", false);
		submitButton.removeClass("d-none");
	}

	/*=========== Disable Loading Button ===========*/
	function disableLoadingBtn() {
		var submitButton = $("#booking-spinner");
		submitButton.attr("disabled", true);
		submitButton.addClass("d-none");
	}

	// Function to capture the image from the webcam
	let isCameraOpen = false;

	// Function to open/close the camera
	function toggleCamera() {
		const selfieParagraph = document.getElementById("selfie-paragraph");
		const videoContainer = document.getElementById("video-container");
		const actionButtons = document.getElementById("action-buttons");
		const videoPreview = document.getElementById("video-preview");
		const imagePreview = document.getElementById("image-preview");
		videoPreview.style.display = "block";
		imagePreview.style.display = "none";

		if (!isCameraOpen) {
			// Access the user's webcam
			navigator.mediaDevices
				.getUserMedia({ video: true })
				.then((stream) => {
					videoPreview.srcObject = stream;
				})
				.catch((error) => {
					console.log("Error accessing webcam:", error);
				});

			selfieParagraph.textContent = "Close camera";
			videoContainer.style.display = "block";
			imagePreview.style.display = "none";
			isCameraOpen = true;
		} else {
			// Stop video stream
			const stream = videoPreview.srcObject;
			const tracks = stream.getTracks();
			tracks.forEach((track) => track.stop());

			selfieParagraph.textContent = "Click here to take a selfie!";
			videoContainer.style.display = "block";
			imagePreview.style.display = "block";
			isCameraOpen = false;
		}
	}

	// Function to capture the image from the webcam
	function captureImage() {
		const videoPreview = document.getElementById("video-preview");
		const imagePreview = document.getElementById("selfie_holder");
		const imagePreview2 = document.getElementById("image-preview");

		// Create a canvas element
		const canvas = document.createElement("canvas");
		canvas.width = videoPreview.videoWidth;
		canvas.height = videoPreview.videoHeight;
		const context = canvas.getContext("2d");
		context.drawImage(videoPreview, 0, 0, canvas.width, canvas.height);

		// Convert the canvas image to a data URL
		const imageDataURL = canvas.toDataURL();
		$("#image-input").val(imageDataURL);

		// Display the captured image in the preview box
		imagePreview.src = imageDataURL;
		imagePreview2.src = imageDataURL;

		// Hide the video preview
		videoPreview.style.display = "none";
		imagePreview2.style.display = "block";
	}

	// Function to retake the picture
	function retakePicture() {
		const videoPreview = document.getElementById("video-preview");
		const imagePreview = document.getElementById("image-preview");

		// Show the video preview
		videoPreview.style.display = "block";

		// Hide the image preview
		imagePreview.style.display = "none";
	}

	$("#capture-video").on("hidden.bs.modal", function () {
		toggleCamera();
	});

	// Event listener for when the paragraph is clicked to open/close the camera
	const selfieParagraph = document.getElementById("selfie-paragraph");
	selfieParagraph
		? selfieParagraph.addEventListener("click", function () {
				toggleCamera();
				$("#capture-video").modal("show");
		  })
		: "";

	// Event listener for when the snap icon is clicked to capture the image
	const snapIcon = document.getElementById("snap-icon");
	snapIcon ? snapIcon.addEventListener("click", captureImage) : "";

	// Event listener for when the retake icon is clicked
	const retakeIcon = document.getElementById("retake-icon");
	retakeIcon ? retakeIcon.addEventListener("click", retakePicture) : "";

	// Function to convert Data URI to Blob
	function dataURItoBlob(dataURI) {
		// Split the Data URI into metadata and data parts
		var parts = dataURI.split(",");
		var metadata = parts[0]; // e.g., "data:image/png;base64"
		var data = parts[1]; // e.g., "iVBORw0KGg...."

		// Extract the mime type from the metadata
		var mimeType = metadata.split(";")[0].split(":")[1];

		// Convert base64-encoded data to a Blob object
		var byteCharacters = atob(data);
		var byteArrays = [];
		for (var i = 0; i < byteCharacters.length; i++) {
			byteArrays.push(byteCharacters.charCodeAt(i));
		}
		var byteArray = new Uint8Array(byteArrays);
		var blob = new Blob([byteArray], { type: mimeType });

		return blob;
	}

	// Get extension form raw image data
	function getFileExtensionFromMimeType(mimeType) {
		const mimeTypesMap = {
			"image/jpeg": "jpg",
			"image/png": "png",
			"image/gif": "gif",
			"image/bmp": "bmp",
			"image/webp": "webp",
			"image/svg+xml": "svg",
			"application/pdf": "pdf",
			"application/msword": "doc",
			"application/vnd.openxmlformats-officedocument.wordprocessingml.document":
				"docx",
			"application/vnd.ms-excel": "xls",
			"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet": "xlsx",
			"application/vnd.ms-powerpoint": "ppt",
			"application/vnd.openxmlformats-officedocument.presentationml.presentation":
				"pptx",
			"text/plain": "txt",
			"text/html": "html",
			"text/css": "css",
			"application/json": "json",
			"application/xml": "xml",
			// Add more MIME types and their corresponding extensions as needed
		};

		// Extract MIME type without any parameters
		const mimeTypeWithoutParams = mimeType.split(";")[0].trim();

		// Search for matching MIME type in the map
		for (const type in mimeTypesMap) {
			if (mimeTypesMap.hasOwnProperty(type) && type === mimeTypeWithoutParams) {
				return mimeTypesMap[type];
			}
		}

		// If no matching extension is found, return null or handle the case as needed
		return null;
	}

	//Booking space form
	$("#booking_space").submit(function (e) {
		e.preventDefault(); // Prevent normal form submission

		// Create FormData object to store form data
		var formData = new FormData(this);
		var blob = dataURItoBlob($("#image-input").val()); // Updated ID here
		var ext = getFileExtensionFromMimeType(blob.type);
		formData.append("selfie", blob, "image." + ext);
		var redirect_url = base_url + "success";
		disableSubmitBtn();
		disablePrevSubmitBtn();
		enableLoadingBtn();

		$.ajax({
			url: base_url + "home/add_booking_ajax", // Replace with your processing script URL
			type: "POST",
			data: formData,
			dataType: "json",
			processData: false, // Prevent jQuery from processing the data
			contentType: false, // Prevent jQuery from setting content type

			success: function (res) {
				if (res.status) {
					setTimeout(function () {
						$(location).attr("href", redirect_url);
					}, 5000);
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
					enableSubmitBtn();
					enablePrevSubmitBtn();
					disableLoadingBtn();
				}
			},
		});
	});

	//Edit Booking space form
	$("#edit_booking_space").submit(function (e) {
		e.preventDefault(); // Prevent normal form submission

		// Create FormData object to store form data
		var formData = new FormData(this);
		var redirect_url = base_url + "bookings";
		disableSubmitBtn();
		disablePrevSubmitBtn();
		enableLoadingBtn();
		url = $(this).attr("action");

		$.ajax({
			url: url, // Replace with your processing script URL
			type: "POST",
			data: formData,
			dataType: "json",
			processData: false, // Prevent jQuery from processing the data
			contentType: false, // Prevent jQuery from setting content type

			success: function (res) {
				if (res.status) {
					setTimeout(function () {
						$(location).attr("href", redirect_url);
					}, 5000);
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
					enableSubmitBtn();
					enablePrevSubmitBtn();
					disableLoadingBtn();
				}
			},
		});
	});

	// Define variables for total price and total kg
	let totalPrice = 0;
	let totalKg = 0;

	//Update the add in the item field
	function updateitems() {
		var items = [];

		$(".select_item").each(function (index, element) {
			let item = {
				category: $(this).attr("category"),
				item_name: $(this).attr("itemName"),
				size: $(this).attr("size"),
				price: $(this).attr("price"),
				unit_price: $(this).attr("unitPrice"),
			};
			items.push(item);
		});
		items.length === 0
			? $("#items_input").val("")
			: $("#items_input").val(JSON.stringify(items));
		return items;
	}

	//Calculate selected items
	function calculateBooking() {
	let initialAvailableSpace = parseFloat($(".available_space").attr("space"));
	let serviceCharge = parseFloat($(".service_charge").attr("charge"));
	let selectedSpace = 0;
	let selectedPrice = 0;
	let insurance = 0;
	let currency = $("#holdThisInfo").attr("currency");
	let onePound = $("#holdThisInfo").attr("one_pound");

	$(".select_item").each(function (index, element) {
		selectedSpace += parseFloat($(this).attr("size"));
		selectedPrice += parseFloat($(this).attr("price"));
	});

	firstSelectedPrice = currency == "pounds" ? 50000 / parseFloat(onePound) : 50000;
	secondSelectedPrice = currency == "pounds" ? 100000 / parseFloat(onePound) : 100000;
	insuranceOne = currency == "pounds" ? 1500 / parseFloat(onePound) : 1500;
	insuranceTwo = currency == "pounds" ? 3000 / parseFloat(onePound) : 3000;

	// Get the selected insurance value from the select element
	let selectedInsurance = parseFloat($("#insuranceBox option:selected").data("insurance"));

	// Use the selected insurance value directly
	insurance = selectedInsurance ? selectedInsurance : 0;

	let currentAvailableSpace = initialAvailableSpace - selectedSpace;
	let subTotal = serviceCharge + selectedPrice;
	let vat = (7.5 / 100) * subTotal;
	let totalAmount = subTotal + vat + insurance;
	let calculatedValues = {
		initialAvailableSpace: initialAvailableSpace,
		selectedSpace: parseFloat(selectedSpace.toFixed(2)),
		selectedPrice: parseFloat(selectedPrice.toFixed(2)),
		totalAmount: parseFloat(totalAmount.toFixed(2)),
		subTotal: parseFloat(subTotal.toFixed(2)),
		vat: parseFloat(vat.toFixed(2)),
		currentAvailableSpace: currentAvailableSpace,
		serviceCharge: parseFloat(serviceCharge.toFixed(2)),
		insurance: parseFloat(insurance.toFixed(2)),
		currency: currency,
		onePound: onePound,
	};
	$("#price_calculations").val(JSON.stringify(calculatedValues));
	return calculatedValues;
	}

	// Update prices in summary
	function updateBooking() {
	$(".available_space").html(`${calculateBooking().currentAvailableSpace}KG`);
	$("#total-kg").html(`${calculateBooking().selectedSpace}`);
	$("#total-price").html(`${calculateBooking().totalAmount.toLocaleString()}`);
	$("#sub-total").html(`${calculateBooking().subTotal.toLocaleString()}`);
	$("#vat-price").html(`${calculateBooking().vat.toLocaleString()}`);
	$("#insurance-value").html(`${calculateBooking().insurance.toLocaleString()}`);
	totalAmountNaira =
		calculateBooking().currency == "naira"
		? calculateBooking().totalAmount
		: calculateBooking().totalAmount * calculateBooking().onePound;
	totalAmountPounds =
		calculateBooking().currency == "pounds"
		? calculateBooking().totalAmount
		: calculateBooking().totalAmount / calculateBooking().onePound;

	$("#totalAmountNaira").html(
		$("#holdThisInfo").attr("naira_sign") +
		totalAmountNaira.toFixed(2).toLocaleString()
	);

	$("#totalAmountPounds").html(
		$("#holdThisInfo").attr("pound_sign") +
		totalAmountPounds.toFixed(2).toLocaleString()
	);
	}

	updateBooking();
	updateitems();

	$("#item-list")
		.children(".select_item")
		.each(function () {
			activeItem = $(this);
			activeItem.find(".delete").click(function () {
				activeItem.remove();
				updateitems();
				updateBooking();
			});
		});

	// Add an event listener to the "add-item" button
	$("button#add-me").click(function () {
		//error message
		function showError(error, element) {
			if (element.is("select:hidden")) {
				//error.insertAfter(element.next(".nice-select"));
				element.next(".nice-select").after(error);
			} else {
				//error.insertAfter(element);
				element.after(error);
			}
		}

		// Get the selected category, item name, and size
		const category = document.getElementById("select1").value;
		const price = $(`option[value = "${category}"]`).attr("data-price");
		const itemName = document.getElementById("item-name").value;
		const size = document.getElementById("select2").value;

		$(".error_msg_item").html("");

		if (parseFloat(size) > calculateBooking().currentAvailableSpace) {
			$(".error_msg_item").html(`
			<div class="col-lg-12 text-danger">
				Selected space (${parseFloat(size)}kg) cannot exceed available space (${
				calculateBooking().currentAvailableSpace
			}kg)
			</div>`);
			return;
		}

		//Error message for add button
		if (category.trim() == "" || !parseFloat(size) || itemName.trim() == "") {
			if (category.trim() == "") {
				showError(
					'<span for="select1" class="error">Select a category</span>',
					$("#select1")
				);
			} else {
				$("span[for='select1']").remove();
			}

			if (itemName.trim() == "") {
				showError(
					'<span for="item-name" class="error">Provide item name</span>',
					$("#item-name")
				);
			} else {
				$("span[for='#item-name']").remove();
			}

			if (!parseInt(size)) {
				showError(
					'<span for="select2" class="error">Select item size</span>',
					$("#select2")
				);
			} else {
				$("span[for='select2']").remove();
			}
			return;
		} else {
			$("#item-options").find("span.error").remove();
		}

		// Calculate the kg based on the selected size
		let kg = parseFloat(size);

		// Create a new item element with the selected category, item name, size, price, and delete icon
		const newItem = document.createElement("div");
		var currencySymbol = $("#holdThisInfo").attr("symbol");
		newItem.classList.add("item");
		newItem.classList.add("select_item");
		newItem.setAttribute("category", category);
		newItem.setAttribute("itemName", itemName);
		newItem.setAttribute("size", kg);
		newItem.setAttribute("price", kg * price);
		newItem.setAttribute("unitPrice", price);
		newItem.innerHTML = `
            <span class="category">${category}</span>
            <span class="name">${itemName}</span>
            <span class="size">${size}KG</span>
            <span class="price">${currencySymbol}${parseFloat(
			(kg * price).toFixed(2)
		).toLocaleString()}</span>
            <div class="delete" title="Delete item"><i class="fa-solid fa-trash"></i></div>
        `;

		// Add the new item element to the list
		document.getElementById("item-list").appendChild(newItem);

		// Add an event listener to the delete icon of the new item element
		newItem.querySelector(".delete").addEventListener("click", function () {
			// Remove the new item element from the list
			newItem.remove();

			// Subtract the price and kg of the deleted item from the total price and total kg
			totalPrice -= price;
			totalKg -= kg;

			// Update the total price and total kg elements
			document.getElementById("total-price").textContent = totalPrice;
			document.getElementById("total-kg").textContent = totalKg;
			updateitems();
			updateBooking();
		});

		// Add the price and kg of the new item to the total price and total kg
		totalPrice += price;
		totalKg += kg;

		// Update the total price and total kg elements
		document.getElementById("total-price").textContent = totalPrice;
		document.getElementById("total-kg").textContent = totalKg;

		// Clear the select and text fields
		document.getElementById("select1").selectedIndex = 0;
		document.getElementById("item-name").value = "";
		document.getElementById("select2").selectedIndex = 0;
		$("select").niceSelect("update");
		$("span[for='select1']").remove();
		$("span[for='item-name']").remove();
		$("span[for='select2']").remove();
		console.log(updateitems());
		updateBooking();

		$("#item-options").find("span.error").remove();
	});

	// Update booking on insurance select change
	$("#insuranceBox").change(function () {
	updateBooking();
	});

	//For text to display while typing
	const senderInput = document.getElementById("sender-name");
	const recieverInput = document.getElementById("reciever-name");
	const senderaddressInput = document.getElementById("sender-address");
	const recieveraddressInput = document.getElementById("reciever-address");

	const senderValue = document.getElementById("sendername-value");
	const recieverValue = document.getElementById("recievername-value");
	const senderaddressValue = document.getElementById("senderaddress-value");
	const recieveraddressValue = document.getElementById("recieveraddress-value");

	senderInput.addEventListener("input", (event) => {
		senderValue.textContent = event.target.value;
	});

	recieverInput.addEventListener("input", (event) => {
		recieverValue.textContent = event.target.value;
	});

	senderaddressInput.addEventListener("input", (event) => {
		senderaddressValue.textContent = event.target.value;
	});

	recieveraddressInput.addEventListener("input", (event) => {
		recieveraddressValue.textContent = event.target.value;
	});

	//Image Preview
	function ImagePreview(input) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function (e) {
				$("#image_preview").attr("src", e.target.result);
			};
			reader.readAsDataURL(input.files[0]);
		}
	}

	$("#the_image").change(function () {
		ImagePreview(this);
		$("#image_preview_area").css("display", "block");
	});

	$("#remove_image").click(function () {
		$("#the_image").val("");
		$("#image_preview_area").css("display", "none");
	});

	//Image preview on update: toggle current and new image
	$("#the_image_on_update").change(function () {
		ImagePreview(this);
		$("#image_preview_area").css("display", "block");
		$("#current_image_area").css("display", "none");
		$("#change_image_text").css("display", "none");
	});

	$("#remove_image").click(function () {
		$("#the_image_on_update").val("");
		$("#image_preview_area").css("display", "none");
		$("#current_image_area").css("display", "block");
		$("#change_image_text").css("display", "block");
	});

	//Display account details based on account selected
	$(document).ready(function () {
		function handleSelectedAccChange() {
			var selectedAcc = $("#payment_acc_select");

			// Get the selected value
			var selectedValue = selectedAcc.val();

			var ubaAcc = $("#uba_account");
			var metroAcc = $("#metro_account");

			// Show the corresponding account column based on the selected value
			if (selectedValue === "United Bank of Africa") {
				ubaAcc.removeClass("d-none");
				metroAcc.addClass("d-none");
			} else if (selectedValue === "Metro Bank PLC") {
				metroAcc.removeClass("d-none");
				ubaAcc.addClass("d-none");
			} else if (selectedValue === "") {
				metroAcc.addClass("d-none");
				ubaAcc.addClass("d-none");
			}
		}

		$(document).ready(function () {
			// Call the function on load
			handleSelectedAccChange();

			var selectedAcc = $("#payment_acc_select");

			// Bind the function to the change event
			selectedAcc.on("change", handleSelectedAccChange);
		});
	});

	$("input.form-control").change(function () {
		$(this).siblings("span.error").css("display", "none");
	});

	$("select").change(function () {
		$(this).siblings("span.error").css("display", "none");
	});

	function activateSelect() {
		$("div.nice-select").each(function () {
			if ($(this).attr("activate-select") != "yes") {
				$(this).attr("activate-select", "yes");
				$($(this))
					.find("ul.list")
					.children(".option")
					.click(function () {
						selectedVal = $(this).attr("data-value").trim();
						console.log(selectedVal);
						$(this).parents("div.nice-select").siblings("select").val(selectedVal);
						$(this)
							.parents("div.nice-select")
							.siblings("span.error")
							.css("display", "none");
					});
			}
		});
	}

	activateSelect();

	$("#bottom-wizard")
		.find("button.forward")
		.click(function () {
			activateSelect();
		});

	$("#bottom-wizard")
		.find("button.backward")
		.click(function () {
			activateSelect();
		});

	$("input.visible_image_input").each(function () {
		monitorImageChange($(this).attr("id"), $(this).attr("holder"));
	});

	$(".reset_img_input").click(function () {
		inputRef = $(this).siblings("img").attr("id");
		targetInput = $('input[holder = "' + inputRef + '"]');
		targetInput.val("");
		changeEvent = new Event("change");
		targetInput[0].dispatchEvent(changeEvent);
	});
});
