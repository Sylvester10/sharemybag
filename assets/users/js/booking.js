jQuery(document).ready(function ($) {
	// ("use strict");

	// Define variables for total price and total kg
	let totalPrice = 0;
	let totalKg = 0;
	let totalSpecialCharge = 0; // Initialize a variable to store the total special charge

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
		// Check the available space
		fetch(`${base_url}user_bookings/get_traveling_available_space/${$("form#booking_form[key]").attr("key")}`)
			.then((response) => {
				return response.text();
			})
			.then((aSpace) => {
				let calculations = calculateBooking();
				let size = document.getElementById("select2").value;
				let currentBagSpace = parseFloat(aSpace) - calculations.selectedSpace;

				$("#availableSpace").text(`${currentBagSpace}KG`);

				if (size > currentBagSpace) {
					// Display error message
					toastr.error(`${size}KG exceeds available bag space(${currentBagSpace}KG)`, `Not Enough Bag Space`, {
						progressBar: true,
						timeOut: 5000,
					});
					return false;
				}

				return parseFloat(currentBagSpace);
			})
			.then((aSpace) => {
				if (aSpace === false) {
					return false;
				}
				// Error message function
				function showError(error, element) {
					if (element.is("select:hidden")) {
						element.next(".nice-select").after(error);
					} else {
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

				// Error messages for add button
				if (category.trim() == "" || !parseFloat(size) || itemName.trim() == "") {
					if (category.trim() == "") {
						showError('<span for="select1" class="text_danger">Select a category</span>', $("#select1"));
					} else {
						$("span[for='select1']").remove();
					}

					if (itemName.trim() == "") {
						showError('<span for="item-name" class="text_danger">Provide item name</span>', $("#item-name"));
					} else {
						$("span[for='#item-name']").remove();
					}

					if (!parseInt(size)) {
						showError('<span for="select2" class="text_danger">Select item size</span>', $("#select2"));
					} else {
						$("span[for='select2']").remove();
					}
					return;
				} else {
					$("#item-options").find("span.text_danger").remove();
				}

				// Calculate the kg based on the selected size
				let kg = parseFloat(size);

				// Define special charges for the two categories
				const specialCharges = {
					"Fish/Medicine": 10, // Special charge for this category
					"Documents/Electronics": 15, // Special charge for this category
				};

				// Determine the special charge based on the category
				let specialCharge = 0;
				if (specialCharges[category]) {
					specialCharge = specialCharges[category]; // Get the special charge value
				}

				// Create a new item element with the selected category, item name, size, price, special charge, and delete icon
				const newItem = document.createElement("div");
				let currencySymbol = $("#holdThisInfo").attr("symbol");
				newItem.classList.add("item");
				newItem.classList.add("select_item");
				newItem.setAttribute("category", category);
				newItem.setAttribute("itemName", itemName);
				newItem.setAttribute("size", kg);
				newItem.setAttribute("price", kg * price); // Add special charge to price
				newItem.setAttribute("unitPrice", price);
				newItem.innerHTML = `
                    <span class="category">${category}</span>
                    <span class="name">${itemName}</span>
                    <span class="size">${size}KG</span>
                    <span class="price">${currencySymbol}${parseFloat((kg * price).toFixed(2))} </span>
                    <span class="delete" title="Delete item"><i class="ti ti-trash fs-4 text-danger"></i></span>
                `;

				// Add the new item element to the list
				document.getElementById("item-list").appendChild(newItem);

				// Add an event listener to the delete icon of the new item element
				newItem.querySelector(".delete").addEventListener("click", function () {
					// Remove the new item element from the list
					newItem.remove();

					// Subtract the price, kg, and special charge of the deleted item from the total
					totalPrice -= price;
					totalKg -= kg;
					totalSpecialCharge -= specialCharge; // Subtract the special charge from the total

					// Update the total price, total kg, and total special charge
					document.getElementById("total-price").textContent = totalPrice;
					document.getElementById("total-kg").textContent = totalKg;
					document.getElementById("total-kgs").textContent = totalKg;
					updateitems();
					updateBooking();
					document.getElementById("special-charge-value").textContent = getSpecialCharge().toFixed(2);
				});

				// Add the price, kg, and special charge of the new item to the total
				totalPrice += price + specialCharge; // Add the special charge to the total price
				totalKg += kg;
				totalSpecialCharge += specialCharge; // Add the special charge to the total

				// Update the total price, total kg, and total special charge elements
				document.getElementById("total-price").textContent = totalPrice;
				document.getElementById("total-kg").textContent = totalKg;
				document.getElementById("total-kgs").textContent = totalKg;

				// Clear the select and text fields
				document.getElementById("select1").selectedIndex = 0;
				document.getElementById("item-name").value = "";
				document.getElementById("select2").selectedIndex = 0;

				$("span[for='select1']").remove();
				$("span[for='item-name']").remove();
				$("span[for='select2']").remove();
				$("#item-options").find("span.error").remove();

				$("#availableSpace").text(`${parseFloat(aSpace) - size}KG`);
				updateitems();
				updateBooking();
				document.getElementById("special-charge-value").textContent = getSpecialCharge().toFixed(2); // Update special charge value
			})
			.catch((error) => console.error("Error:", error));
		return;
	});

	// Update booking on insurance select change
	$("#insuranceBox").change(function () {
		updateBooking();
	});

	//For text to display while typing
	const receiverInput = document.getElementById("receiverName");
	const agentInput = document.getElementById("agentName");
	const receiveraddressInput = document.getElementById("receiveraddress");
	const agentaddressInput = document.getElementById("agentAddress");

	const receiverValue = document.getElementById("receiverNameValue");
	const agentValue = document.getElementById("agentNameValue");
	const receiverAddressValue = document.getElementById("receiverAddressValue");
	const agentaddressValue = document.getElementById("agentAddressValue");

	if (
		receiverInput &&
		agentInput &&
		receiveraddressInput &&
		agentaddressInput
	) {
		receiverInput.addEventListener("input", (event) => {
			receiverValue.textContent = event.target.value;
		});

		agentInput.addEventListener("input", (event) => {
			agentValue.textContent = event.target.value;
			console.log("Agent Name:", event.target.value); // Debugging line
		});

		receiveraddressInput.addEventListener("input", (event) => {
			receiverAddressValue.textContent = event.target.value;
		});

		agentaddressInput.addEventListener("input", (event) => {
			agentaddressValue.textContent = event.target.value;
		});
	}

	$("#capture-video").on("hidden.bs.modal", function () {
		// toggleCamera();
		closeCamera();
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
	snapIcon
		? snapIcon.addEventListener("click", function () {
				let input = $(`#${$("#capture-video").attr("target-input")}`)[0];
				let image = $(`#${$("#capture-video").attr("target-img")}`)[0];

				captureImage(input, image);
		  })
		: "";

	// Event listener for when the retake icon is clicked
	const retakeIcon = document.getElementById("retake-icon");
	retakeIcon ? retakeIcon.addEventListener("click", retakePicture) : "";

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
						$(this).parents("div.nice-select").siblings("span.error").css("display", "none");
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

	$(".reset_img_input").click(function () {
		inputRef = $(this).siblings("img").attr("id");
		targetInput = $('input[holder = "' + inputRef + '"]');
		targetInput.val("");
		changeEvent = new Event("change");
		targetInput[0].dispatchEvent(changeEvent);
	});

	autoLoadPageHelpers();
});

function openCamera(input, img) {
	const videoContainer = document.getElementById("video-container");
	const videoPreview = document.getElementById("video-preview");
	const imagePreview = document.getElementById("image-preview");

	let captureModal = $("#capture-video");
	let input_count = $(`#${input}`).length;
	let img_count = $(`#${img}`).length;

	if (!input_count || !img_count) {
		console.log("No input or image element");
		return;
	} else {
		captureModal.attr("target-img", img);
		captureModal.attr("target-input", input);
	}

	// Only open the camera if it is not currently open
	if (!videoPreview.srcObject) {
		navigator.mediaDevices
			.getUserMedia({ video: true })
			.then((stream) => {
				videoPreview.srcObject = stream;
				videoContainer.style.display = "block";
				videoPreview.style.display = "block";
				imagePreview.style.display = "none";
				captureModal.modal("show");
			})
			.catch((error) => {
				console.log("Error accessing webcam:", error);
			});
	}
}

// Function to close camera
function closeCamera() {
	const videoContainer = document.getElementById("video-container");
	const videoPreview = document.getElementById("video-preview");
	const imagePreview = document.getElementById("image-preview");

	let captureModal = $("#capture-video");

	// Check if the camera is open by checking if srcObject has a stream
	if (videoPreview.srcObject) {
		// Stop video stream
		const stream = videoPreview.srcObject;
		const tracks = stream.getTracks();
		tracks.forEach((track) => track.stop());

		videoPreview.srcObject = null; // Clear the video source to release the camera
		videoContainer.style.display = "none"; // Optionally hide video container
		videoPreview.style.display = "none";
		imagePreview.style.display = "block";
	}

	captureModal.removeAttr("target-img");
	captureModal.removeAttr("target-input");
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
function captureImage(input = null, image = null) {
	const videoPreview = document.getElementById("video-preview");
	const imagePreview = image ?? document.getElementById("selfie_holder");
	const imagePreview2 = document.getElementById("image-preview");
	const imagePreview3 = image ?? document.getElementById("selfie_holder2");
	const inputEl = input ?? document.getElementById("image-input");
	const inputEl2 = input ?? document.getElementById("image-input2");

	// Create a canvas element
	const canvas = document.createElement("canvas");
	canvas.width = videoPreview.videoWidth;
	canvas.height = videoPreview.videoHeight;
	const context = canvas.getContext("2d");
	context.drawImage(videoPreview, 0, 0, canvas.width, canvas.height);

	// Convert the canvas image to a data URL
	const imageDataURL = canvas.toDataURL();
	inputEl.value = imageDataURL;
	inputEl2.value = imageDataURL;

	inputEl.classList.add("input-image-blob");
	inputEl2.classList.add("input-image-blob");

	// Display the captured image in the preview box
	imagePreview.src = imageDataURL;
	imagePreview2.src = imageDataURL;
	imagePreview3.src = imageDataURL;

	// Hide the video preview
	videoPreview.style.display = "none";
	imagePreview2.style.display = "block";
	imagePreview3.style.display = "block";
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

// Monitor Image Changes on and input and image element
function monitorImageChange(inputId, imageId) {
	var input = document.getElementById(inputId);
	var image = document.getElementById(imageId);
	var previousSrc = image.src;

	input.addEventListener("change", function () {
		if (input.files && input.files[0]) {
			var file = input.files[0];
			var reader = new FileReader();

			reader.onload = function (e) {
				if (isImageFile(file)) {
					image.setAttribute("data-previous-src", previousSrc);
					image.classList.add("img-changed");
					image.src = e.target.result;
				} else {
					// Not an image file, do something (e.g., display an error message)
					console.log("Selected file is not an image.");
				}
			};

			reader.readAsDataURL(file);
		} else {
			image.src = previousSrc;
			image.classList.remove("img-changed");
		}
		input.classList.add("img-change-monitor-init");
	});
}

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
	$("#items_input").trigger("change");
	return items;
}

$("#items_input").change(function () {
	let val = $(this).val().trim();
	let category = $('select[name="category"]');
	let size = $('select[name="size"]');
	let item = $('input[name="item"]');

	if (val !== "") {
		category.removeClass("required");
		size.removeClass("required");
		item.removeClass("required");
	} else {
		category.addClass("required");
		size.addClass("required");
		item.addClass("required");
	}
});

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

	firstSelectedPrice =
		currency == "pounds" ? 50000 / parseFloat(onePound) : 50000;
	secondSelectedPrice =
		currency == "pounds" ? 100000 / parseFloat(onePound) : 100000;
	insuranceOne = currency == "pounds" ? 1500 / parseFloat(onePound) : 1500;
	insuranceTwo = currency == "pounds" ? 3000 / parseFloat(onePound) : 3000;

	// Get the selected insurance value from the select element
	let selectedInsurance = parseFloat(
		$("#insuranceBox option:selected").data("insurance")
	);

	// Use the selected insurance value directly
	insurance = selectedInsurance ? selectedInsurance : 0;

	let currentAvailableSpace = initialAvailableSpace - selectedSpace;
	let subTotal = serviceCharge + selectedPrice;
	// let vat = (7.5 / 100) * subTotal;
	let totalAmount = subTotal + insurance + getSpecialCharge();
	let calculatedValues = {
		initialAvailableSpace: initialAvailableSpace,
		selectedSpace: parseFloat(selectedSpace.toFixed(2)),
		selectedPrice: parseFloat(selectedPrice.toFixed(2)),
		totalAmount: parseFloat(totalAmount.toFixed(2)),
		subTotal: parseFloat(subTotal.toFixed(2)),
		// vat: parseFloat(vat.toFixed(2)),
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
	$("#total-kgs").html(`${calculateBooking().selectedSpace}`);
	$("#total-price").html(`${calculateBooking().totalAmount.toLocaleString()}`);
	$("#sub-total").html(`${calculateBooking().subTotal.toLocaleString()}`);
	// $("#vat-price").html(`${calculateBooking().vat.toLocaleString()}`);
	$("#insurance-value").html(
		`${calculateBooking().insurance.toLocaleString()}`
	);
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

// get special charge
function getSpecialCharge() {
	let specialCharge = 0;
	let specialCharges = {
		"Fish/Medicine": 10, // Special charge for this category
		"Documents/Electronics": 15, // Special charge for this category
	};
	let items = $("#items_input").val();
	if (items) {
		items = JSON.parse(items);
		let categories = items.map((item) => item.category);
		if (categories.includes("Fish/Medicine")) {
			specialCharge += 10;
		}
		if (categories.includes("Documents/Electronics")) {
			specialCharge += 15;
		}
	}
	return specialCharge;
}

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

function isImageFile(file) {
	return file.type.startsWith("image/");
}

