// Shared inline feedback for landing search and estimate fields.
(function () {
    'use strict';
    function clearField(field) {
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
        var errorId = field.id + '-error';
        var error = document.getElementById(errorId);
        if (error) error.remove();
        var described = (field.getAttribute('aria-describedby') || '').split(' ').filter(function (id) { return id && id !== errorId; });
        if (described.length) field.setAttribute('aria-describedby', described.join(' '));
        else field.removeAttribute('aria-describedby');
        var custom = field.nextElementSibling;
        if (custom && custom.classList.contains('nice-select')) {
            custom.classList.remove('is-invalid');
            custom.removeAttribute('aria-invalid');
            custom.removeAttribute('aria-describedby');
        }
    }
    window.smbFieldErrors = {
        clearField: clearField,
        clear: function (scope) { scope.querySelectorAll('select, input, button').forEach(clearField); },
        show: function (id, message) {
            var field = document.getElementById(id);
            clearField(field);
            var custom = field.nextElementSibling;
            var target = custom && custom.classList.contains('nice-select') ? custom : field;
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
            target.classList.add('is-invalid');
            target.setAttribute('aria-invalid', 'true');
            var error = document.createElement('p');
            error.id = id + '-error';
            error.className = 'smb-field-error';
            error.setAttribute('role', 'alert');
            error.textContent = message;
            field.setAttribute('aria-describedby', ((field.getAttribute('aria-describedby') || '') + ' ' + error.id).trim());
            if (target !== field) target.setAttribute('aria-describedby', error.id);
            // Composite phone controls keep feedback outside their flex row.
            var phoneGroup = field.closest('[data-smb-phone-input]');
            (phoneGroup || target).insertAdjacentElement('afterend', error);
        },
        focusFirst: function (scope) {
            var field = scope.querySelector('select.is-invalid, input.is-invalid, button.is-invalid');
            if (!field) return;
            var custom = field.nextElementSibling;
            (custom && custom.classList.contains('nice-select') ? custom : field).focus();
        }
    };
    document.addEventListener('input', function (event) {
        if (event.target.closest('#search_form, #priceCheckerForm, #traveller_form')) clearField(event.target);
    });
    jQuery(document).on('change', '#search_form select, #priceCheckerForm select, #traveller_form select, #traveller_form input', function () { clearField(this); });
}());

jQuery(function ($) {
    'use strict';

    $('#smb-policy-selector').on('change', function () {
        if (this.value) window.location.assign(this.value);
    });

    var hero = document.querySelector('.smb-hero');
    if (!hero) return;

    // Reuse the public Traveller form's existing Nice Select components.
    $('#select_destination, #pc_origin, #pc_destination, #pc_category').niceSelect('update');

    var dial = hero.querySelector('.smb-country-dial');
    var current = dial.querySelector('.smb-country-current');
    var next = dial.querySelector('.smb-country-next');
    var countries = ['United Kingdom', 'Canada', 'Nigeria'];
    var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var active = 0;
    var timer = null;
    var transition = null;

    function showCountry(index) {
        active = index;
        next.textContent = countries[active];
        dial.classList.add('is-changing');
        transition = window.setTimeout(finishTransition, 650);
    }

    function finishTransition() {
        window.clearTimeout(transition);
        current.textContent = countries[active];
        dial.classList.remove('is-changing');
        next.textContent = '';
        transition = null;
    }

    function schedule() {
        window.clearInterval(timer);
        timer = null;
        if (document.hidden || motion.matches) {
            finishTransition();
            return;
        }
        timer = window.setInterval(function () {
            showCountry((active + 1) % countries.length);
        }, 4000);
    }

    document.addEventListener('visibilitychange', schedule);
    motion.addEventListener('change', schedule);
    schedule();
});
