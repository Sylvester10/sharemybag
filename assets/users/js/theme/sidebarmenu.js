document.addEventListener("DOMContentLoaded", function () {
	// Get data-layout attribute
	var layoutType = document.documentElement.getAttribute("data-layout");

	if (layoutType === "vertical" || layoutType === "horizontal") {
		function setActiveLink() {
			var currentUrl = window.location.href;
			var sidebarLinks = document.querySelectorAll("#sidebarnav a");

			// Remove active class from all links first
			sidebarLinks.forEach(function (link) {
				link.classList.remove("active");
				link.closest("li")?.classList.remove("selected");
			});

			// Set active class to the current link
			sidebarLinks.forEach(function (link) {
				if (link.href === currentUrl) {
					link.classList.add("active");
					link.closest("li")?.classList.add("selected");
				}
			});
		}

		// Run on page load
		setActiveLink();

		// Add click event listener to each sidebar link
		document.querySelectorAll("#sidebarnav a").forEach(function (link) {
			link.addEventListener("click", function (e) {
				// Prevent default action
				e.preventDefault();

				// Update URL
				window.location.href = this.href;

				// Set active class on click
				setActiveLink();
			});
		});
	}
});
