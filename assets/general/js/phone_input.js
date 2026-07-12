(function () {
	'use strict';

	function updatePhoneFlag(select) {
		var wrapper = select.closest('[data-smb-phone-input]');
		if (!wrapper) return;

		var flag = wrapper.querySelector('[data-smb-phone-flag]');
		if (!flag) return;

		var selected = select.options[select.selectedIndex];
		var flagClass = selected ? selected.getAttribute('data-flag') : '';
		flag.className = flagClass || 'cf-16 cf-gb';
	}

	function initPhoneInputs(root) {
		var scope = root || document;
		var selects = scope.querySelectorAll('[data-smb-phone-country]');

		selects.forEach(function (select) {
			if (select.dataset.smbPhoneBound === '1') {
				updatePhoneFlag(select);
				return;
			}

			select.dataset.smbPhoneBound = '1';
			select.addEventListener('change', function () {
				updatePhoneFlag(select);
			});
			updatePhoneFlag(select);
		});
	}

	window.initSmbPhoneInputs = initPhoneInputs;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initPhoneInputs(document);
		});
	} else {
		initPhoneInputs(document);
	}
})();
