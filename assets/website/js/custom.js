jQuery(document).ready(function ($) {
    ('use strict');

    // Preloader
    $(window).ready(function () {
        $('#preloader').delay(100).fadeOut('fade');
    });

    // Prohibited items search
    let searchInput = document.getElementById('searchProhibited');
    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            let searchValue = e.target.value.trim().toLowerCase();
            let faqs = document.querySelectorAll('#accordionFaq .card');

            if (searchValue === '') {
                faqs.forEach((faq) => faq.classList.remove('d-none'));
                return;
            }

            faqs.forEach((faq) => {
                let question = faq
                    .querySelector('.card-header button')
                    .textContent.trim()
                    .toLowerCase();
                let answer = faq
                    .querySelector('.card-body')
                    .textContent.trim()
                    .toLowerCase();

                if (
                    question.includes(searchValue) ||
                    answer.includes(searchValue)
                ) {
                    faq.classList.remove('d-none');
                } else {
                    faq.classList.add('d-none');
                }
            });
        });
    }

    function decoratePhoneCountrySelect(selector) {
        var select = $(selector);
        var niceSelect = select.next('.nice-select');

        if (!niceSelect.length) {
            return;
        }

        select.closest('[data-smb-phone-input]').addClass('smb-phone-input--nice-select');

        niceSelect.find('li').each(function () {
            var option = select.find('option').eq($(this).index());
            var flag = option.data('flag');
            var code = option.val() || option.text().trim();
            var label = code;

            if (flag) {
                label =
                    '<span class="smb-phone-select-flag cf ' +
                        flag +
                        '"></span> ' +
                    code;
            }

            $(this).html(label);
        });

        var selectedOption = select.find('option:selected');
        var selectedFlag = selectedOption.data('flag');
        var selectedCode = selectedOption.val() || selectedOption.text().trim();
        var current = niceSelect.find('.current');

        if (!current.length) {
            return;
        }

        if (!selectedFlag) {
            current.text(selectedCode);
            return;
        }

        current.html(
            '<span class="smb-phone-select-flag cf ' +
                selectedFlag +
                '"></span> ' +
                selectedCode
        );
    }

    // DRY: Show one country flag on nice select for ALL country code dropdowns
    const countrySelectors = [
        '#country_code',
        '#country_code2',
        '#country_code3',
    ];

    countrySelectors.forEach(function (selector) {
        if ($(selector).length) {
            $(selector).niceSelect();
            decoratePhoneCountrySelect(selector);

            $(selector).on('change', function () {
                decoratePhoneCountrySelect(selector);
            });
        }
    });

    // Only digits in the phone field
    var phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            this.value = this.value.replace(/\D/g, '');
        });
    }

    var altPhoneInput = document.querySelector('input[name="alt_phone"]');
    if (altPhoneInput) {
        altPhoneInput.addEventListener('input', function (e) {
            this.value = this.value.replace(/\D/g, '');
        });
    }
});
