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
					return;
				}
			};

			reader.readAsDataURL(file);
		} else {
			image.src = previousSrc;
			image.classList.remove("img-changed");
		}
	});
}

function isImageFile(file) {
	return file.type.startsWith("image/");
}

function getGlobalCsrfName() {
	return window.appCsrf && window.appCsrf.name ? window.appCsrf.name : null;
}

function getGlobalCsrfHash() {
	return window.appCsrf && window.appCsrf.hash ? window.appCsrf.hash : null;
}

function updateGlobalCsrfHash(newHash) {
	if (!newHash) {
		return;
	}

	if (!window.appCsrf) {
		window.appCsrf = {};
	}

	window.appCsrf.hash = newHash;

	let csrfName = getGlobalCsrfName();
	if (!csrfName) {
		return;
	}

	$('input[type="hidden"][name="' + csrfName + '"]').val(newHash);
}

function appendGlobalCsrfToFormData(formData) {
	let csrfName = getGlobalCsrfName();
	let csrfHash = getGlobalCsrfHash();

	if (!csrfName || !csrfHash || !(formData instanceof FormData) || formData.has(csrfName)) {
		return formData;
	}

	formData.append(csrfName, csrfHash);
	return formData;
}

function ensureFormHasCsrfInput(form) {
	let csrfName = getGlobalCsrfName();
	let csrfHash = getGlobalCsrfHash();

	if (!csrfName || !csrfHash || !form) {
		return;
	}

	let existingInput = form.querySelector('input[type="hidden"][name="' + csrfName + '"]');
	if (!existingInput) {
		existingInput = document.createElement("input");
		existingInput.type = "hidden";
		existingInput.name = csrfName;
		form.appendChild(existingInput);
	}

	existingInput.value = csrfHash;
}

function getAjaxErrorMessage(xhr, fallbackMessage) {
	let fallback = fallbackMessage || "Something went wrong. Please try again.";
	let title = "Error";
	let message = fallback;

	try {
		let responseJson = xhr.responseJSON
			? xhr.responseJSON
			: xhr.responseText
			? JSON.parse(xhr.responseText)
			: null;

		if (responseJson && responseJson.msg) {
			message = responseJson.msg;
		}

		if (responseJson && responseJson.title) {
			title = responseJson.title;
		}

		if (responseJson && responseJson.csrf_hash) {
			updateGlobalCsrfHash(responseJson.csrf_hash);
		}
	} catch (e) {}

	if (
		xhr &&
		typeof xhr.responseText === "string" &&
		xhr.responseText.indexOf("The action you have requested is not allowed.") !== -1
	) {
		title = "Session Expired";
		message = "This form expired. Refresh the page and try again.";
	}

	return { title, message };
}

function ensureWizardStepErrorStyles() {
	if (document.getElementById("wizard-step-error-styles")) {
		return;
	}

	let style = document.createElement("style");
	style.id = "wizard-step-error-styles";
	style.textContent = `
		.wizard > .steps .wizard-step-error a,
		.wizard > .steps .wizard-step-error a:hover,
		.wizard > .steps .wizard-step-error a:active {
			background: #d20913 !important;
			color: #fff !important;
		}

		.wizard > .steps .wizard-step-error .number {
			color: #ffffff !important;
		}
	`;
	document.head.appendChild(style);
}

function getWizardStepItems(form) {
	let wizardRoot = $(form).closest(".wizard");
	if (!wizardRoot.length) {
		wizardRoot = $(form);
	}

	return wizardRoot.find("> .steps ul li");
}

function clearWizardStepErrors(form) {
	getWizardStepItems(form).removeClass("wizard-step-error");
}

function applyWizardStepErrors(form, steps) {
	if (!Array.isArray(steps) || !steps.length) {
		return;
	}

	ensureWizardStepErrorStyles();
	clearWizardStepErrors(form);

	let items = getWizardStepItems(form);
	steps.forEach(function (stepIndex) {
		let index = parseInt(stepIndex, 10);
		if (!Number.isNaN(index) && index >= 0) {
			items.eq(index).addClass("wizard-step-error");
		}
	});
}

//universal form ajax
function submitFormAjax(form) {
	let action = form.hasAttribute("action")
		? form.getAttribute("action")
		: false;
	let redirect = form.hasAttribute("redirect")
		? form.getAttribute("redirect")
		: false;
	let reset = form.hasAttribute("reset") ? true : false;

	if (action) {
		// If action
		let form_data = new FormData(form);
		form_data = appendGlobalCsrfToFormData(form_data);
		// Get raw image input
		$(".input-image-blob").each(function () {
			let blob = dataURItoBlob($(this).val()); // Updated ID here
			let ext = getFileExtensionFromMimeType(blob.type);
			form_data.append($(this).attr("name"), blob, "image." + ext);
		});

		showFormLoader();

		// Sending http request
		$.ajax({
			url: action, // Replace with your server endpoint
			type: "POST",
			data: form_data,
			dataType: "json",
			processData: false, // Required for form_data to work without URL-encoding data
			contentType: false, // Prevent jQuery from setting content-type header
			success: function (res) {
				if (res && res.csrf_hash) {
					updateGlobalCsrfHash(res.csrf_hash);
				}

				if (res.status) {
					clearWizardStepErrors(form);

					// Display success message
					toastr.success(res.msg, res?.title ?? "Success", {
						progressBar: true,
						timeOut: res?.msg_timeout ?? 5000,
					});

					// Reset form fields
					if (reset) {
						form.reset();
					}

					// Redirect
					if (redirect) {
						setTimeout(function () {
							$(location).attr("href", redirect);
						}, res?.msg_timeout ?? 4000);
						return;
					}

					// Redirect server
					if (res?.redirect) {
						setTimeout(function () {
							$(location).attr("href", res?.redirect);
						}, res?.msg_timeout ?? 4000);
						return;
					}

					setTimeout(() => {
						hideFormLoader();
					}, 700);
				} else {
					if (res && res.error_steps) {
						applyWizardStepErrors(form, res.error_steps);
					}
					setTimeout(() => {
						hideFormLoader();
					}, 700);
					// Display error message
					toastr.error(res.msg, res?.title ?? "Error", {
						progressBar: true,
						timeOut: res?.msg_timeout ?? 5000,
					});
				}
			},
			error: function (xhr, status, error) {
				setTimeout(() => {
					hideFormLoader();
				}, 700);
				let ajaxError = getAjaxErrorMessage(xhr);
				let responseJson = xhr && xhr.responseJSON ? xhr.responseJSON : null;
				if (responseJson && responseJson.error_steps) {
					applyWizardStepErrors(form, responseJson.error_steps);
				}

				// Handle error response
				toastr.error(ajaxError.message, ajaxError.title, {
					progressBar: true,
					timeOut: 5000,
				});
			},
		});
	}

	return;
}

// Inline-status form ajax: renders into #status_msg, toggles #search-spinner / #submit,
// keeps CSRF token fresh on every response, and surfaces meaningful error messages
// (including CI's "form expired" case) via getAjaxErrorMessage().
//
// opts:
//   url             string  required if form has no action attribute
//   statusEl        selector/jQuery  default: form's #status_msg, else page-level #status_msg
//   spinnerEl       selector/jQuery  default: form's #search-spinner, else page-level
//   submitEl        selector/jQuery  default: form's #submit, else page-level
//   resetOnSuccess  bool             reset the form on res.status === true
//   redirect        string|function(res)  destination on success; falls back to res.redirect
//   redirectDelay   number ms        delay before redirect (default 1500)
//   extraData       object           extra key/value pairs appended to FormData
//   successTimeout  number ms        how long the success alert stays before fading (default 3000)
//   errorTimeout    number ms        how long the error alert stays before fading (default 4000)
function submitInlineAjax(form, opts) {
	opts = opts || {};
	if (!form) {
		return;
	}

	let $form = $(form);
	let url = opts.url || form.getAttribute("action");
	if (!url) {
		console.warn("submitInlineAjax: missing url and form has no action attribute");
		return;
	}

	let statusEl = opts.statusEl ? $(opts.statusEl) : $form.find("#status_msg");
	if (!statusEl.length) {
		statusEl = $("#status_msg");
	}

	let spinnerEl = opts.spinnerEl ? $(opts.spinnerEl) : $form.find("#search-spinner");
	if (!spinnerEl.length) {
		spinnerEl = $("#search-spinner");
	}

	let submitEl = opts.submitEl ? $(opts.submitEl) : $form.find("#submit");
	if (!submitEl.length) {
		submitEl = $("#submit");
	}

	let resetOnSuccess = !!opts.resetOnSuccess;
	let redirect = opts.redirect;
	let redirectDelay = typeof opts.redirectDelay === "number" ? opts.redirectDelay : 1500;
	let extraData = opts.extraData && typeof opts.extraData === "object" ? opts.extraData : null;
	let successTimeout = typeof opts.successTimeout === "number" ? opts.successTimeout : 3000;
	let errorTimeout = typeof opts.errorTimeout === "number" ? opts.errorTimeout : 4000;

	let formData = new FormData(form);
	formData = appendGlobalCsrfToFormData(formData);
	if (extraData) {
		Object.keys(extraData).forEach(function (key) {
			formData.append(key, extraData[key]);
		});
	}

	function renderAlert(type, msg) {
		if (!statusEl.length) {
			return;
		}
		let cls = type === "success" ? "alert-success" : "alert-danger";
		statusEl
			.stop(true, true)
			.html(
				'<div class="alert ' + cls + ' text-center" style="color: #000">' +
					msg +
					"</div>"
			)
			.fadeIn("fast");
	}

	function fadeOutAlert(delay) {
		if (!statusEl.length) {
			return;
		}
		statusEl.delay(delay).fadeOut("slow");
	}

	function showLoading() {
		spinnerEl.removeClass("d-none");
		submitEl.addClass("disabled").attr("disabled", true);
	}

	function hideLoading() {
		spinnerEl.addClass("d-none");
		submitEl.removeClass("disabled").attr("disabled", false);
	}

	function resolveRedirect(res) {
		if (res && typeof res.redirect === "string" && res.redirect.length) {
			return res.redirect;
		}
		if (typeof redirect === "function") {
			return redirect(res);
		}
		if (typeof redirect === "string" && redirect.length) {
			return redirect;
		}
		return null;
	}

	showLoading();

	$.ajax({
		url: url,
		type: "POST",
		data: formData,
		dataType: "json",
		processData: false,
		contentType: false,
		success: function (res) {
			if (res && res.csrf_hash) {
				updateGlobalCsrfHash(res.csrf_hash);
			}

			if (res && res.status) {
				renderAlert("success", res.msg || "Success");

				if (resetOnSuccess) {
					try {
						form.reset();
					} catch (e) {}
				}

				let target = resolveRedirect(res);
				if (target) {
					// Keep button disabled during the redirect window
					setTimeout(function () {
						window.location.href = target;
					}, redirectDelay);
					return;
				}

				hideLoading();
				fadeOutAlert(successTimeout);
			} else {
				hideLoading();
				renderAlert("danger", (res && res.msg) || "Request failed.");
				fadeOutAlert(errorTimeout);
			}
		},
		error: function (xhr) {
			// Refresh CSRF first so a follow-up retry isn't dead on arrival
			let responseJson = null;
			try {
				responseJson = xhr && xhr.responseJSON
					? xhr.responseJSON
					: xhr && xhr.responseText
					? JSON.parse(xhr.responseText)
					: null;
			} catch (e) {}
			if (responseJson && responseJson.csrf_hash) {
				updateGlobalCsrfHash(responseJson.csrf_hash);
			}

			hideLoading();

			let fallback = "Something went wrong. Please try again.";
			if (xhr && xhr.status === 0) {
				fallback = "Couldn't reach the server. Check your connection and try again.";
			} else if (xhr && xhr.status >= 500) {
				fallback = "The server hit a problem processing this request.";
			}

			let ajaxError = getAjaxErrorMessage(xhr, fallback);
			renderAlert("danger", ajaxError.message);
			fadeOutAlert(errorTimeout);
		},
	});
}

// show form loader
function showFormLoader() {
	let loader = document.getElementById("transparent-loader");

	if (loader) {
		loader.style.display = "grid";
	}
}

// hide form loader
function hideFormLoader() {
	let loader = document.getElementById("transparent-loader");

	if (loader) {
		loader.style.display = "none";
	}
}

$(".form-wizard-ajax").each(function () {
	let advanced_form = $(this).show();
	ensureFormHasCsrfInput(this);

	advanced_form.on("change input", "input, select, textarea", function () {
		clearWizardStepErrors(advanced_form);
	});

	advanced_form
		.steps({
			headerTag: "h3",
			bodyTag: "fieldset",
			transitionEffect: "slideLeft",
			onStepChanging: function (event, currentIndex, newIndex) {
				// Allways allow previous action even if the current form is not valid!
				if (currentIndex > newIndex) {
					return true;
				}

				// === START: CUSTOM VALIDATION FOR BOOKING FORM ===
				// First, check if the item input field exists on this specific form.
				// This ensures this logic ONLY runs on the booking page.
				if ($("#items_input").length > 0) {
					// If we are on the first step (About Your Item) and moving forward...
					if (currentIndex === 0) {
						// Check if any items have been added by checking the hidden input.
						if ($("#items_input").val().trim() === "") {
							// If no items are added, show an error and stop the user from proceeding.
							toastr.error("You must click the 'Add' button to add at least one item.", "No Items Added", {
								progressBar: true,
								timeOut: 4000,
							});
							return false; // This prevents the wizard from moving to the next step.
						}
					}
				}
				// === END: CUSTOM VALIDATION FOR BOOKING FORM ===

				// Needed in some cases if the user went back (clean up)
				if (currentIndex < newIndex) {
					// To remove error styles
					advanced_form.find(".body:eq(" + newIndex + ") label.error").remove();
					advanced_form
						.find(".body:eq(" + newIndex + ") .error")
						.removeClass("error");
				}
				advanced_form.validate().settings.ignore = ":disabled,:hidden";
				return advanced_form.valid();
			},
			onStepChanged: function (event, currentIndex, priorIndex) {
				autoLoadPageHelpers();
			},
// 			onFinishing: function (event, currentIndex) {
// 				advanced_form.validate().settings.ignore = ":disabled";
// 				return advanced_form.valid();
// 			},
            onFinishing: function (event, currentIndex) {
				// === START: FINAL CHECK FOR BOOKING FORM ITEMS ===
				// First, check if the item input field exists on this specific form.
				// This ensures this logic ONLY runs on the booking page.
				if ($("#items_input").length > 0) {
					// Check if the hidden items_input field is empty.
					if ($("#items_input").val().trim() === "") {
						// If it's empty, show an error and stop the form submission.
						toastr.error("You cannot proceed with an empty item list.", "No Items Added", {
							progressBar: true,
							timeOut: 4000,
						});
						return false; // This prevents the form from being submitted.
					}
				}
				// === END: FINAL CHECK FOR BOOKING FORM ITEMS ===

				// Original validation logic runs if the check above passes.
				advanced_form.validate().settings.ignore = ":disabled";
				return advanced_form.valid();
			},
			onFinished: function (event, currentIndex) {
				submitFormAjax(event.target);
			},
			onInit: function (event, currentIndex) {
				autoLoadPageHelpers();
			},
		})
		.validate({
			errorPlacement: function errorPlacement(error, element) {
				if (
					advanced_form.hasClass("booking-wizard-form") ||
					advanced_form.hasClass("kyc-wizard-form")
				) {
					return;
				}
				element.before(error);
			},
			highlight: function (element, errorClass) {
				$(element).addClass(errorClass);
				if (advanced_form.hasClass("booking-wizard-form")) {
					$(element).next(".nice-select").addClass(errorClass);
				}
			},
			unhighlight: function (element, errorClass) {
				$(element).removeClass(errorClass);
				if (advanced_form.hasClass("booking-wizard-form")) {
					$(element).next(".nice-select").removeClass(errorClass);
				}
			},
			rules: {
				confirm: {
					equalTo: "#password-2",
				},
			},
		});
});

// form validation
$(".form-ajax").each(function () {
	ensureFormHasCsrfInput(this);
	$(this).submit(function (e) {
		e.preventDefault();
		submitFormAjax(e.target);
	});
});

// password toggle
const toggleButtons = document.querySelectorAll('.toggle-password');

toggleButtons.forEach(button => {
    button.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
});
