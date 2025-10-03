jQuery(document).ready(function ($) {
	("use strict");

	//preloader
	$(window).ready(function () {
		$("#preloader").delay(100).fadeOut("fade");
	});

	//prohibited items search
	let searchInput = document.getElementById("searchProhibited");
	if (searchInput) {
		searchInput.addEventListener("keyup", function (e) {
			let searchValue = e.target.value.trim().toLowerCase();
			let faqs = document.querySelectorAll("#accordionFaq .card");

			// Show all FAQs if search is empty
			if (searchValue === "") {
				faqs.forEach((faq) => faq.classList.remove("d-none"));
				return;
			}

			// Loop through each FAQ card
			faqs.forEach((faq) => {
				let question = faq
					.querySelector(".card-header button")
					.textContent.trim()
					.toLowerCase();
				let answer = faq
					.querySelector(".card-body")
					.textContent.trim()
					.toLowerCase();

				if (question.includes(searchValue) || answer.includes(searchValue)) {
					faq.classList.remove("d-none");
				} else {
					faq.classList.add("d-none");
				}
			});
		});
	}
	
	
	// Show country flags on nice select
	$("#country_code").niceSelect();

	$("#country_code")
		.next(".nice-select")
		.find("li")
		.each(function () {
		var flag =
			$(this).data("flag") ||
			$(this).attr("data-flag") ||
			$("#country_code option").eq($(this).index()).data("flag");
		if (flag) {
			$(this).prepend('<span class="cf ' + flag + '"></span> ');
		}
	});
	
	$("#country_code2").niceSelect();

	$("#country_code2")
		.next(".nice-select")
		.find("li")
		.each(function () {
		var flag =
			$(this).data("flag") ||
			$(this).attr("data-flag") ||
			$("#country_code option").eq($(this).index()).data("flag");
		if (flag) {
			$(this).prepend('<span class="cf ' + flag + '"></span> ');
		}
	});


	// Only digits in the phone field
	var phoneInput = document.querySelector('input[name="phone"]');
	if (phoneInput) {
		phoneInput.addEventListener("input", function (e) {
		this.value = this.value.replace(/\D/g, "").slice(0, 10);
		});
	}

	var altPhoneInput = document.querySelector('input[name="alt_phone"]');
	if (altPhoneInput) {
		altPhoneInput.addEventListener("input", function (e) {
		this.value = this.value.replace(/\D/g, "").slice(0, 10);
		});
	}
	

	
});




