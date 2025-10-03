let myNotify;

/**
 * -----------------------------------
 * pop
 * notify
 * displays a dark tost
 * @param title - Title on the toast
 * @param message - Message on the toast
 * @param delay - how long should the toast last in miliseconds
 * @returns `string`
 */
function simpleNotify(options) {
	switch (options.status) {
		case "success":
			toastStatus = "success";
			break;
		case "ok":
			toastStatus = "success";
			break;
		case "warning":
			toastStatus = "warning";
			break;
		case "info":
			toastStatus = "info";
			break;
		case "error":
			toastStatus = "error";
			break;
		case "bad":
			toastStatus = "error";
			break;
		case "danger":
			toastStatus = "error";
			break;
		case null:
			toastStatus = null;
			break;
		default:
			toastStatus = null;
			break;
	}

	switch (options.position) {
		case "tl":
			position = "top left";
			break;
		case "tc":
			position = "top x-center";
			break;
		case "tr":
			position = "top right";
			break;
		case "ml":
			position = "y-center left";
			break;
		case "mc":
			position = "center";
			break;
		case "mr":
			position = "y-center right";
			break;
		case "bl":
			position = "bottom left";
			break;
		case "bc":
			position = "bottom x-center";
			break;
		case "br":
			position = "bottom right";
			break;
		default:
			position = "top x-center";
			break;
	}

	speed = options.speed || 300;
	autotimeout = options.timeout || 7000;
	gap = options.gap || 20;
	distance = options.distance || 20;
	icon = options.icon == true ? true : false;
	showCloseButton = options.showCloseButton == false ? false : true;
	autoclose = options.autoClose == false ? false : true;
	showIcon = options.showIcon == false ? false : true;

	switch (options.effect) {
		case "fade":
			effect = "fade";
			break;
		case "slide":
			effect = "slide";
			break;
		default:
			effect = "slide";
			break;
	}

	let toastDefault = {
		showMethod: "slideDown",
		hideMethod: "slideUp",
		timeOut: parseInt(autotimeout),
		progressBar: true,
		closeButton: true,
		positionClass: "toastr toast-top-center",
		containerId: "toast-top-center",
	};

	switch (toastStatus) {
		case "success":
			toastr.success(
				options.message || null,
				options.title || null,
				toastDefault
			);
			break;
		case "info":
			toastr.info(options.message || null, options.title || null, toastDefault);
			break;
		case "warning":
			toastr.warning(
				options.message || null,
				options.title || null,
				toastDefault
			);
			break;
		case "error":
			toastr.error(
				options.message || null,
				options.title || null,
				toastDefault
			);
			break;
	}

	// myNotify = new Notify({
	// 	status: toastStatus,
	// 	title: options.title || null,
	// 	text: options.message || null,
	// 	effect: effect,
	// 	speed: parseInt(speed),
	// 	customClass: options.class || null,
	// 	customIcon: options.icon || null,
	// 	showIcon: showIcon,
	// 	showCloseButton: showCloseButton,
	// 	autoclose: autoclose,
	// 	autotimeout: parseInt(autotimeout),
	// 	gap: parseInt(gap),
	// 	distance: parseInt(distance),
	// 	type: 1,
	// 	position: position,
	// });

	// $(".notifications-container").css("z-index", "99999999999999");
}

function clearNotify() {
	$("body").children("div#toast-top-center").html("");
}

/**
 * -----------------------------------
 * notifySuccess
 * -----------------------------------
 * displays a tost representing success
 * @param title - Title on the toast
 * @param message - Message on the toast
 * @param delay - how long should the toast last in miliseconds
 * @param theme - Theme Color DARK OR LIGHT
 * @param position - Position using TOP, MIDDLE , BOTTOM (t , m , b) against LEFT, CENTER
 * @returns `string`
 */
function notifySuccess(
	title = null,
	message = false,
	delay = false,
	position = false,
	showIcon = true,
	showCloseButton = true,
	autoClose = true
) {
	options = {};
	title ? (options.title = title) : "";
	message ? (options.message = message) : "";
	delay ? (options.timeout = delay) : "";
	position ? (options.position = position) : "";
	showIcon == false ? (options.showIcon = showIcon) : "";
	showCloseButton == false ? (options.showCloseButton = showCloseButton) : "";
	autoClose == false ? (options.autoClose = autoClose) : "";
	options.status = "success";
	simpleNotify(options);
}

/**
 * -----------------------------------
 * notifySuccess
 * -----------------------------------
 * displays a tost representing success
 * @param title - Title on the toast
 * @param message - Message on the toast
 * @param delay - how long should the toast last in miliseconds
 * @returns `string`
 */
function notifyError(
	title = null,
	message = false,
	delay = false,
	position = false,
	showIcon = true,
	showCloseButton = true,
	autoClose = true
) {
	options = {};
	title ? (options.title = title) : "";
	message ? (options.message = message) : "";
	delay ? (options.timeout = delay) : "";
	position ? (options.position = position) : "";
	showIcon == false ? (options.showIcon = showIcon) : "";
	showCloseButton == false ? (options.showCloseButton = showCloseButton) : "";
	autoClose == false ? (options.autoClose = autoClose) : "";
	options.status = "bad";
	simpleNotify(options);
}

/**
 * -----------------------------------
 * notifyWarning
 * -----------------------------------
 * displays a tost representing warning
 * @param title - Title on the toast
 * @param message - Message on the toast
 * @param delay - how long should the toast last in miliseconds
 * @returns `string`
 */
function notifyWarning(
	title = null,
	message = false,
	delay = false,
	position = false,
	showIcon = true,
	showCloseButton = true,
	autoClose = true
) {
	options = {};
	title ? (options.title = title) : "";
	message ? (options.message = message) : "";
	delay ? (options.timeout = delay) : "";
	position ? (options.position = position) : "";
	showIcon == false ? (options.showIcon = showIcon) : "";
	showCloseButton == false ? (options.showCloseButton = showCloseButton) : "";
	autoClose == false ? (options.autoClose = autoClose) : "";
	options.status = "warning";
	simpleNotify(options);
}

/**
 * -----------------------------------
 * notifyInfo
 * -----------------------------------
 * displays a tost representing warning
 * @param title - Title on the toast
 * @param message - Message on the toast
 * @param delay - how long should the toast last in miliseconds
 * @returns `string`
 */
function notifyInfo(
	title = null,
	message = false,
	delay = false,
	position = false,
	showIcon = true,
	showCloseButton = true,
	autoClose = true
) {
	options = {};
	title ? (options.title = title) : "";
	message ? (options.message = message) : "";
	delay ? (options.timeout = delay) : "";
	position ? (options.position = position) : "";
	showIcon == false ? (options.showIcon = showIcon) : "";
	showCloseButton == false ? (options.showCloseButton = showCloseButton) : "";
	autoClose == false ? (options.autoClose = autoClose) : "";
	options.status = "info";
	simpleNotify(options);
}

/**
 * -----------------------------------
 * notify
 * -----------------------------------
 * displays a tost representing information
 * @param title - Title on the toast
 * @param message - Message on the toast
 * @param delay - how long should the toast last in miliseconds
 * @returns `string`
 */
function notify(
	title = null,
	message = false,
	delay = false,
	position = false,
	showIcon = true,
	showCloseButton = true,
	autoClose = true
) {
	options = {};
	title ? (options.title = title) : "";
	message ? (options.message = message) : "";
	delay ? (options.timeout = delay) : "";
	position ? (options.position = position) : "";
	showIcon == false ? (options.showIcon = showIcon) : "";
	showCloseButton == false ? (options.showCloseButton = showCloseButton) : "";
	autoClose == false ? (options.autoClose = autoClose) : "";
	simpleNotify(options);
}
