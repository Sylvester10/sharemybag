function autoLoadPageHelpers() {
	//preloader
	$(window).ready(function () {
		$("#preloader").delay(100).fadeOut("fade");
	});

	// monitor image changes on page
	$("input.visible_image_input:not(.img-change-monitor-init)").each(function () {
		monitorImageChange($(this).attr("id"), $(this).attr("holder"));
	});

	// monitor image changes on page
	$("input.visible_image_input2:not(.img-change-monitor-init)").each(function () {
		monitorImageChange($(this).attr("id"), $(this).attr("holder"));
	});

	$("#selfie-paragraph").each(function () {
		$(this).off("click");
		$(this).click(function () {
			toggleCamera();
			$("#capture-video").modal("show");
		});
	});

	$(".take-selfie").each(function () {
		$(this).off("click");
		let input = $(this)[0].hasAttribute("target-input") ? $(this).attr("target-input") : false;
		let img = $(this)[0].hasAttribute("target-img") ? $(this).attr("target-img") : false;

		if (!input || !img) {
			console.log("Can't find target image or target input");
			return;
		}
		$(this).click(function () {
			openCamera(input, img);
		});
	});

	//Copy text to clip board
	function copyToClipboard(text, message = false, title = null, duration = 1000) {
		if (!message) {
			message = "Text Copied!";
		}
		navigator.clipboard
			.writeText(text)

			.then(() => {
				notifySuccess(title, message, duration);
			})
			.catch((error) => {
				notifyError("Unable to copy text to clipboard:", error);
			});
	}

	$(".copy-referral").click(function () {
		copyToClipboard($(this).children(".r-link").text(), "Referral Link Copied", null, 500);
	});

	document.addEventListener("DOMContentLoaded", function () {
		const categorySelect = document.querySelector('select[name="category"]');
		const weightSelect = document.querySelector('select[name="size"]');
		const maxSpace = weightSelect
			? parseInt(weightSelect.getAttribute("data-max-space")) || 15
			: 15;
	});
	

	// sender/agent
	document.addEventListener("DOMContentLoaded", function () {
		// Get radio buttons
		const withMeRadio = document.getElementById("parcelWithMe");
		const withSomeoneRadio = document.getElementById("parcelWithSomeone");

		// Agent form fields
		const nameField = document.getElementById("agentName");
		const emailField = document.getElementById("agentEmail");
		const phoneField = document.getElementById("agentPhone");
		const addressField = document.getElementById("agentAddress");
		const cityField = document.getElementById("agentCity");
		const postcodeField = document.getElementById("agentPostcode");

		// 👇 Add these: DOM elements to show live text updates
		const agentValue = document.getElementById("agentNameValue");
		const agentaddressValue = document.getElementById("agentAddressValue");

		// 👇 Get user details from hidden data attributes in your HTML
		const userDiv = document.getElementById("user-details");
		if (!userDiv) return;
		const userDetails = {
			name: userDiv?.dataset?.name || "",
			email: userDiv?.dataset?.email || "",
			phone: userDiv?.dataset?.phone || "",
			address: userDiv?.dataset?.address || "",
			city: userDiv?.dataset?.city || "",
			postcode: userDiv?.dataset?.postcode || "",
		};

		// 👇 Function to fill form fields AND update visible display
		function fillFieldsWithUserData() {
			nameField.value = userDetails.name;
			emailField.value = userDetails.email;
			phoneField.value = userDetails.phone;
			addressField.value = userDetails.address;
			cityField.value = userDetails.city;
			postcodeField.value = userDetails.postcode;

			// 👇 ALSO update display text manually
			if (agentValue) agentValue.textContent = userDetails.name;
			if (agentaddressValue) agentaddressValue.textContent = userDetails.address;
		}

		// 👇 Function to clear form fields AND display text
		function clearFields() {
			nameField.value = "";
			emailField.value = "";
			phoneField.value = "";
			addressField.value = "";
			cityField.value = "";
			postcodeField.value = "";

			// 👇 ALSO clear display text
			if (agentValue) agentValue.textContent = "";
			if (agentaddressValue) agentaddressValue.textContent = "";
		}

		// 👇 Listen to changes in radio buttons
		withMeRadio.addEventListener("change", function () {
			if (withMeRadio.checked) {
				fillFieldsWithUserData();
			}
		});

		withSomeoneRadio.addEventListener("change", function () {
			if (withSomeoneRadio.checked) {
				clearFields();
			}
		});

		// 👇 Optional: fill or clear on page load
		if (withMeRadio.checked) {
			fillFieldsWithUserData();
		} else if (withSomeoneRadio.checked) {
			clearFields();
		}

		// 👇 Also live update visible text when typing manually
		if (nameField && agentValue) {
			nameField.addEventListener("input", (event) => {
				agentValue.textContent = event.target.value;
			});
		}

		if (addressField && agentaddressValue) {
			addressField.addEventListener("input", (event) => {
				agentaddressValue.textContent = event.target.value;
			});
		}
	});

	// receiver
	document.addEventListener("DOMContentLoaded", function () {
		// Get radio buttons
		const withMeRadio = document.getElementById("receiverIsMe");
		const withSomeoneRadio = document.getElementById("receiverIsSomeoneElse");

		// Agent form fields
		const nameField = document.getElementById("receiverName");
		const emailField = document.getElementById("receiverEmail");
		const phoneField = document.getElementById("receiverPhone");
		const addressField = document.getElementById("receiverAddress");
		const cityField = document.getElementById("receiverCity");
		const postcodeField = document.getElementById("receiverPostcode");

		// 👇 Add these: DOM elements to show live text updates
		const agentValue = document.getElementById("receiverNameValue");
		const agentaddressValue = document.getElementById("receiverAddressValue");

		// 👇 Get user details from hidden data attributes in your HTML
		const userDiv = document.getElementById("user-details");
		if (!userDiv) return;
		const userDetails = {
			name: userDiv.dataset.name || "",
			email: userDiv.dataset.email || "",
			phone: userDiv.dataset.phone || "",
			address: userDiv.dataset.address || "",
			city: userDiv.dataset.city || "",
			postcode: userDiv.dataset.postcode || "",
		};

		// 👇 Function to fill form fields AND update visible display
		function fillFieldsWithUserData() {
			nameField.value = userDetails.name;
			emailField.value = userDetails.email;
			phoneField.value = userDetails.phone;
			addressField.value = userDetails.address;
			cityField.value = userDetails.city;
			postcodeField.value = userDetails.postcode;

			// 👇 ALSO update display text manually
			if (agentValue) agentValue.textContent = userDetails.name;
			if (agentaddressValue) agentaddressValue.textContent = userDetails.address;
		}

		// 👇 Function to clear form fields AND display text
		function clearFields() {
			nameField.value = "";
			emailField.value = "";
			phoneField.value = "";
			addressField.value = "";
			cityField.value = "";
			postcodeField.value = "";

			// 👇 ALSO clear display text
			if (agentValue) agentValue.textContent = "";
			if (agentaddressValue) agentaddressValue.textContent = "";
		}

		// 👇 Listen to changes in radio buttons
		withMeRadio.addEventListener("change", function () {
			if (withMeRadio.checked) {
				fillFieldsWithUserData();
			}
		});

		withSomeoneRadio.addEventListener("change", function () {
			if (withSomeoneRadio.checked) {
				clearFields();
			}
		});

		// 👇 Optional: fill or clear on page load
		if (withMeRadio.checked) {
			fillFieldsWithUserData();
		} else if (withSomeoneRadio.checked) {
			clearFields();
		}

		// 👇 Also live update visible text when typing manually
		if (nameField && agentValue) {
			nameField.addEventListener("input", (event) => {
				agentValue.textContent = event.target.value;
			});
		}

		if (addressField && agentaddressValue) {
			addressField.addEventListener("input", (event) => {
				agentaddressValue.textContent = event.target.value;
			});
		}
	});
	
	
}
