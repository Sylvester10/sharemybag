jQuery(document).ready(function ($) {
	"use strict";

	//Admin login
	$("#admin_login_form").submit(function (e) {
		e.preventDefault();
		submitInlineAjax(this, {
			url: base_url + "admin_login/login_ajax",
			redirect: function () {
				return $("#requested_page").val() || base_url + "admin";
			},
			redirectDelay: 1500,
			errorTimeout: 10000,
		});
	});
});
