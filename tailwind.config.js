/** @type {import('tailwindcss').Config} */
module.exports = {
	prefix: "tw-",
	darkMode: ["selector", '[data-bs-theme="dark"]'],
	content: [
		// "./application/views/**/*.{html,php}",
		"./**/*.html", // Scans all HTML files in the project
		"./**/*.php", // Scans all php files in the project
	],
	theme: {
		extend: {},
	},
	plugins: [],
};
