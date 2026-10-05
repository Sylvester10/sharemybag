(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('[data-user-details-form]');
        if (form) {
            var clearButton = form.querySelector('[data-clear-phone]');
            var flag = form.querySelector('[name="clear_phone"]');
            var phone = form.querySelector('[name="number"]');
            var country = form.querySelector('[name="country_code"]');
            var initialPhone = phone.value;
            var initialCountry = country.value;
            var initialRequired = phone.required;
            var notice = form.querySelector('[data-phone-reset-notice]');
            clearButton.addEventListener('click', function () {
                if (flag.value === '1') {
                    flag.value = '0';
                    phone.value = initialPhone;
                    country.value = initialCountry;
                    phone.readOnly = false;
                    phone.required = initialRequired;
                    country.required = initialRequired;
                    clearButton.textContent = 'Clear phone number';
                    notice.hidden = true;
                } else {
                    flag.value = '1';
                    phone.value = '';
                    phone.readOnly = true;
                    phone.required = false;
                    country.required = false;
                    clearButton.textContent = 'Undo';
                    notice.hidden = false;
                }
            });
            form.addEventListener('submit', function (event) {
                if (flag.value === '1' && !window.confirm('Clear this phone number? The user will need to verify a new number.')) {
                    event.preventDefault();
                    return;
                }
                form.querySelector('[type="submit"]').disabled = true;
                form.querySelector('#btn_text').textContent = 'Please wait…';
                form.querySelector('#loading_icon').style.display = '';
            });
            // Discard an unsaved reset when the modal is dismissed.
            jQuery(form.closest('.modal')).on('hidden.bs.modal', function () {
                if (flag.value === '1') { clearButton.click(); }
            });
        }

        var modal = document.querySelector('[data-user-activity-modal]');
        if (!modal) { return; }
        var body = modal.querySelector('[data-activity-body]');
        var type = 'details';
        var request;
        function load(page) {
            if (request) { request.abort(); }
            body.setAttribute('aria-busy', 'true');
            body.innerHTML = '<p class="text-center"><i class="las la-spinner la-spin" aria-hidden="true"></i> Loading…</p>';
            request = jQuery.ajax({
                url: modal.dataset.historyUrl, data: {type: type, page: page}, dataType: 'html',
                success: function (html) { body.innerHTML = html; },
                error: function (_, status) {
                    if (status !== 'abort') {
                        body.innerHTML = '<p class="text-danger">Activity could not be loaded.</p><button type="button" class="btn btn-default btn-sm" data-activity-page="' + page + '">Retry</button>';
                    }
                },
                complete: function () { body.setAttribute('aria-busy', 'false'); }
            });
        }
        jQuery(modal).on('shown.bs.modal', function () { load(1); });
        jQuery(modal).on('hidden.bs.modal', function () { if (request) { request.abort(); } });
        modal.addEventListener('click', function (event) {
            var tab = event.target.closest('[data-activity-type]');
            var pager = event.target.closest('[data-activity-page]');
            if (tab) {
                event.preventDefault();
                type = tab.dataset.activityType;
                modal.querySelectorAll('[data-activity-type]').forEach(function (item) {
                    item.parentElement.classList.toggle('active', item === tab);
                    item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
                });
                load(1);
            } else if (pager) { load(Number(pager.dataset.activityPage)); }
        });
    });
}());
