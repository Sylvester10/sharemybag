// Restore the existing form controls. Checkout identity, prices and tokens remain server-owned.
function restoreBookingFormFields(form, snapshot) {
    const editableFields = [
        'category', 'item', 'size', 'parcel_status', 'receiver_status',
        'agent_name', 'agent_email', 'agent_country_code', 'agent_phone',
        'agent_address', 'agent_locality', 'agent_postcode',
        'receiver_name', 'receiver_email', 'receiver_country_code', 'receiver_phone',
        'receiver_address', 'receiver_locality', 'receiver_postcode',
        'insurance', 'payment_method',
    ];
    editableFields.forEach(function (name) {
        if (!Object.prototype.hasOwnProperty.call(snapshot, name)) return;
        const value = String(snapshot[name] == null ? '' : snapshot[name]);
        form.querySelectorAll('[name="' + name + '"]').forEach(function (field) {
            if (field.type === 'radio' || field.type === 'checkbox') {
                field.checked = field.value === value;
            } else {
                field.value = value;
            }
        });
    });
    let parcels = snapshot.items || [];
    if (typeof parcels === 'string') {
        try { parcels = JSON.parse(parcels); } catch (error) { parcels = []; }
    }
    return {
        items: Array.isArray(parcels) ? parcels : [],
        step: Math.min(4, Math.max(0, parseInt(snapshot._booking_step, 10) || 0)),
    };
}

function createBookingParcelNode(item, currencySymbol) {
    const parcel = document.createElement('div');
    const size = Number(item.size) || 0;
    const unitPrice = Number(item.unit_price) || 0;
    const price = unitPrice * size;
    const unit = item.unit || 'KG';
    parcel.className = 'item select_item';
    Object.entries({category: item.category, itemName: item.item_name, size: size,
        price: price, unitPrice: unitPrice, unit: unit}).forEach(function (entry) {
        parcel.setAttribute(entry[0], String(entry[1] == null ? '' : entry[1]));
    });
    const values = {category: item.category, name: item.item_name,
        size: size + unit, price: currencySymbol + price.toFixed(2)};
    Object.keys(values).forEach(function (name) {
        const span = document.createElement('span');
        span.className = name;
        span.textContent = String(values[name] == null ? '' : values[name]);
        parcel.appendChild(span);
    });
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'delete btn btn-link p-0';
    remove.title = 'Delete item';
    remove.setAttribute('aria-label', 'Remove ' + String(item.item_name || 'parcel'));
    const icon = document.createElement('i');
    icon.className = 'ti ti-trash fs-4 text-danger';
    icon.setAttribute('aria-hidden', 'true');
    remove.appendChild(icon);
    parcel.appendChild(remove);
    return parcel;
}

jQuery(document).ready(function ($) {
    // ("use strict");

    // Define variables for total price and total kg
    let totalPrice = 0;
    let totalKg = 0;
    let totalSpecialCharge = 0; // Initialize a variable to store the total special charge

    function getBookingStepTitle(stepLink) {
        let titleClone = stepLink.clone();
        titleClone.find('.number, i, .current-info, .audible').remove();
        return titleClone
            .text()
            .replace(/current step:\s*/i, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function updateBookingStepTitle(form) {
        let currentStep = form.children('.steps').find('li.current a').first();
        let titleTarget = form.children('.booking-current-step-title');

        if (!currentStep.length || !titleTarget.length) {
            return;
        }

        titleTarget.text(getBookingStepTitle(currentStep));
    }

    function syncBookingStepperState(form) {
        let currentItem = form.children('.steps').find('li.current').first();
        let currentIndex = currentItem.index();

        if (currentIndex < 0) {
            return;
        }

        form.children('.steps')
            .find('li')
            .each(function (index) {
                $(this)
                    .toggleClass('booking-step-past', index < currentIndex)
                    .toggleClass('booking-step-future', index > currentIndex);
            });
    }

    function markBookingStepTransition(form) {
        clearTimeout(form.data('booking-step-transition-timeout'));
        form.addClass('booking-step-transitioning');
        form.data(
            'booking-step-transition-timeout',
            setTimeout(function () {
                form.removeClass('booking-step-transitioning');
            }, 420)
        );
    }

    function initBookingStepper(attempt) {
        let form = $('#booking_form.booking-wizard-form');
        let steps = form.children('.steps');
        let currentAttempt = attempt || 0;

        if (!form.length) {
            return;
        }

        if (!steps.length) {
            if (currentAttempt < 10) {
                setTimeout(function () {
                    initBookingStepper(currentAttempt + 1);
                }, 50);
            }
            return;
        }

        syncBookingStepperState(form);

        if (!form.children('.booking-current-step-title').length) {
            steps.after(
                '<div class="booking-current-step-title" aria-live="polite"></div>'
            );
        }

        updateBookingStepTitle(form);

        form.off('click.bookingStepper').on(
            'click.bookingStepper',
            '.steps a, .actions a',
            function () {
                setTimeout(function () {
                    markBookingStepTransition(form);
                    syncBookingStepperState(form);
                    updateBookingStepTitle(form);
                }, 80);
            }
        );

        if (window.MutationObserver && !form.data('booking-stepper-observer')) {
            let observer = new MutationObserver(function () {
                markBookingStepTransition(form);
                syncBookingStepperState(form);
                updateBookingStepTitle(form);
            });

            steps.find('li').each(function () {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            });

            form.data('booking-stepper-observer', observer);
        }
    }

    function syncKycStepperState(form) {
        let currentItem = form.children('.steps').find('li.current').first();
        let currentIndex = currentItem.index();

        if (currentIndex < 0) {
            return;
        }

        form.children('.steps')
            .find('li')
            .each(function (index) {
                $(this)
                    .toggleClass('kyc-step-past', index < currentIndex)
                    .toggleClass('kyc-step-future', index > currentIndex);
            });
    }

    function syncKycActionLabels(form) {
        let currentIndex = form
            .children('.steps')
            .find('li.current')
            .first()
            .index();
        let nextAction = form.children('.actions').find('a[href="#next"]');
        let finishAction = form.children('.actions').find('a[href="#finish"]');

        if (nextAction.length) {
            nextAction.text(currentIndex === 0 ? 'Continue' : 'Next');
        }

        if (finishAction.length) {
            finishAction.text('Submit');
        }
    }

    function initKycStepper(attempt) {
        let form = $('.kyc-wizard-form');
        let steps = form.children('.steps');
        let currentAttempt = attempt || 0;

        if (!form.length) {
            return;
        }

        if (!steps.length) {
            if (currentAttempt < 10) {
                setTimeout(function () {
                    initKycStepper(currentAttempt + 1);
                }, 50);
            }
            return;
        }

        syncKycStepperState(form);
        syncKycActionLabels(form);

        form.off('click.kycStepper').on(
            'click.kycStepper',
            '.steps a, .actions a',
            function () {
                setTimeout(function () {
                    syncKycStepperState(form);
                    syncKycActionLabels(form);
                }, 80);
            }
        );

        if (window.MutationObserver && !form.data('kyc-stepper-observer')) {
            let observer = new MutationObserver(function () {
                syncKycStepperState(form);
                syncKycActionLabels(form);
            });

            steps.find('li').each(function () {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            });

            form.data('kyc-stepper-observer', observer);
        }
    }

    function showMobileItemAddedFeedback(itemName) {
        if (!window.matchMedia('(max-width: 991px)').matches) {
            return;
        }

        let feedback = $('#booking-mobile-added-feedback');
        let summaryButton = $('#sign-in');

        if (!feedback.length) {
            feedback = $(
                '<div id="booking-mobile-added-feedback" class="booking-mobile-added-feedback" aria-live="polite"></div>'
            );
            $('body').append(feedback);
        }

        feedback.html(
            `<i class="ti ti-check"></i> ${itemName ? itemName : 'Item'} added. Tap View Summary to review.`
        );
        feedback.removeClass('is-visible');
        summaryButton.removeClass('booking-summary-pulse');

        window.requestAnimationFrame(function () {
            feedback.addClass('is-visible');
            summaryButton.addClass('booking-summary-pulse');
        });

        clearTimeout(feedback.data('hide-timeout'));
        feedback.data(
            'hide-timeout',
            setTimeout(function () {
                feedback.removeClass('is-visible');
                summaryButton.removeClass('booking-summary-pulse');
            }, 4600)
        );
    }

    function updateCategoryAdvisory() {
        const selectedCategory = $('#select1').val();
        const advisory = $('#category-advisory');

        if (!advisory.length) {
            return;
        }

        if (selectedCategory === 'Fish/Meat') {
            advisory.text(
                'Fish and meat travel at your own risk. ShareMyBag will not be responsible if the item goes bad during the traveller’s journey.'
            );
        } else if (selectedCategory === 'Medication') {
            advisory.text(
                'Important Notice: All medications must be procured by ShareMyBag. Please be advised that any medication purchased independently will be opened and subjected to testing.'
            );
        } else {
            advisory.text('');
        }
    }

    function getSelectedPaymentMethod() {
        return $('input[name="payment_method"]:checked').val() || 'paystack';
    }

    function syncPaymentMethod() {
        $('#payment_method').val(getSelectedPaymentMethod());
    }

    function isPremiumSmallCategory(category) {
        return (
            category === 'Documents/Small Electronics' ||
            category === 'Documents/Electronics' ||
            category === 'Gold'
        );
    }

    function isPremiumLaptopCategory(category) {
        return category === 'Laptop';
    }

    function isSpecialCategory(category) {
        return (
            category === 'Fish/Medicine' ||
            category === 'Fish/Meat' ||
            category === 'Medication'
        );
    }

    function isPieceCategory(category) {
        return (
            isPremiumSmallCategory(category) ||
            isPremiumLaptopCategory(category)
        );
    }

    function calculateTravellerCommissionForSummary(selectedSpace) {
        let normalPayout =
            parseFloat($('#holdThisInfo').attr('normal_payout')) || 0;
        let specialPayout =
            parseFloat($('#holdThisInfo').attr('special_payout')) ||
            normalPayout;
        let premiumSmallPayout =
            parseFloat($('#holdThisInfo').attr('premium_small_payout')) ||
            normalPayout;
        let premiumLaptopPayout =
            parseFloat($('#holdThisInfo').attr('premium_laptop_payout')) ||
            premiumSmallPayout;
        let items = [];

        $('.select_item').each(function () {
            items.push({
                category: $(this).attr('category'),
                size: parseFloat($(this).attr('size')) || 0,
            });
        });

        return window.ShareMyBagPricing.calculateConfiguredTravellerCommission(
            items,
            {
                normal: normalPayout,
                special: specialPayout,
                premiumSmall: premiumSmallPayout,
                premiumLaptop: premiumLaptopPayout,
            }
        );
    }

    // --- NEW: Function to update weight unit in the dropdown ---
    function updateWeightUnit(unit) {
        // Update the label text
        $('#weight-label').text(`Weight (${unit}) *`);

        // Update the options text in the dropdown
        $('#select2 option').each(function (index) {
            if (index > 0) {
                let value = $(this).val();
                // Remove old unit and append new unit
                let text = $(this).text();
                text = text.replace(/KG|Piece/g, '');
                $(this).text(`${value}${unit}`);
            }
        });

        // Store the current unit in the select element
        $('#select2').attr('data-unit', unit);

        // Update the unit in the summary panel
        $('#total-unit').text(unit);
        updateWeightRoundingNotice();
    }

    function updateWeightRoundingNotice(animate = true) {
        const showNotice =
            $('#select2').val() !== '' &&
            $('#select2').attr('data-unit') === 'KG';
        const notice = $('#weight-rounding-notice');

        if (notice.data('expanded') === showNotice) {
            return;
        }

        notice.data('expanded', showNotice).stop(true, true);
        notice.attr('aria-hidden', showNotice ? 'false' : 'true');

        const reduceMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;
        const duration = animate && !reduceMotion ? 240 : 0;

        if (showNotice) {
            notice.removeClass('d-none');
            if (duration) {
                notice.hide().slideDown(duration);
            } else {
                notice.show();
            }
        } else if (duration) {
            notice.slideUp(duration, function () {
                notice.addClass('d-none').css('display', '');
            });
        } else {
            notice.addClass('d-none').css('display', '');
        }
    }

    $('#select2').on('change', function () {
        updateWeightRoundingNotice();
    });

    // --- NEW: Event listener for category change ---
    $('#select1').change(function () {
        let selectedCategory = $(this).val();
        if (isPieceCategory(selectedCategory)) {
            updateWeightUnit('PC');
        } else {
            updateWeightUnit('KG');
        }
        updateCategoryAdvisory();
    });
    // --- END NEW LOGIC ---

    const bookingForm = document.getElementById('booking_form');
    const restoreData = document.getElementById('booking-restore-data');
    let restoredBooking = null;
    if (bookingForm && restoreData) {
        try {
            restoredBooking = restoreBookingFormFields(bookingForm, JSON.parse(restoreData.textContent));
            const list = document.getElementById('item-list');
            restoredBooking.items.forEach(function (item) {
                const option = Array.from(document.getElementById('select1').options)
                    .find(function (candidate) { return candidate.value === item.category; });
                const rate = option ? Number(option.getAttribute('data-price')) : Number(item.unit_price);
                list.appendChild(createBookingParcelNode(Object.assign({}, item, {unit_price: rate}),
                    $('#holdThisInfo').attr('symbol') || ''));
            });
            $('#select1').trigger('change');
            if (typeof window.initSmbPhoneInputs === 'function') window.initSmbPhoneInputs(bookingForm);
            ['agentName', 'agentAddress', 'receiverName', 'receiverAddress'].forEach(function (id) {
                const input = document.getElementById(id);
                const target = document.getElementById(id + 'Value');
                if (input && target) target.textContent = input.value;
            });
        } catch (error) {
            toastr.error('We could not restore this booking. Reload the page and try again.', 'Booking Error');
        }
    }

    // NOTE: Calling updateBooking() once immediately to fix initial currency display
    initBookingStepper();
    initKycStepper();
    syncPaymentMethod();
    updateitems();
    updateBooking();
    updateCategoryAdvisory();
    updateWeightRoundingNotice(false);

    $('#item-list').on('click.bookingParcel', '.delete', function () {
        $(this).closest('.select_item').remove();
        $('#booking_form [name="booking_quote_token"]').val('');
        updateitems();
        updateBooking();
    });

    if (restoredBooking && $.fn.steps) {
        for (let index = 0; index < restoredBooking.step; index++) {
            const prior = $(bookingForm).steps('getCurrentIndex');
            $(bookingForm).steps('next');
            if ($(bookingForm).steps('getCurrentIndex') === prior) break;
        }
        syncBookingStepperState($(bookingForm));
        updateBookingStepTitle($(bookingForm));
    }
    $(bookingForm).on('input.bookingQuote change.bookingQuote', 'input:not([type="hidden"]), select, textarea', function () {
        $(bookingForm).find('[name="booking_quote_token"]').val('');
        $(bookingForm).removeData('checkout-quote');
    });

    window.applyBookingCheckoutQuote = function (form, response) {
        const pricing = response.pricing;
        if (!pricing || !Array.isArray(pricing.items) || !response.quote_token) return false;
        const currencySymbol = pricing.currency === 'GBP' ? '£' : pricing.currency === 'CAD' ? 'C$' : '₦';
        $(form).find('[name="currency"]').val(pricing.currency);
        $('#holdThisInfo').attr('currency', pricing.currency).attr('symbol', currencySymbol);
        $('#holdThisInfo').attr(pricing.currency === 'CAD' ? 'dollar_sign' : 'pound_sign', currencySymbol);
        $('#parcel-guarantee-notice').attr('data-currency-symbol', currencySymbol);
        $('#insuranceBox option[data-protection-label]').each(function () {
            const option = $(this);
            option.text(option.attr('data-protection-label') + ' ' + currencySymbol + Number(option.val()).toFixed(2));
        });
        const list = document.getElementById('item-list');
        list.replaceChildren();
        pricing.items.forEach(function (item) {
            list.appendChild(createBookingParcelNode(item, $('#holdThisInfo').attr('symbol') || ''));
        });
        $('.service_charge').attr('charge', Number(pricing.service_charge) || 0);
        const insuranceOption = $('#insuranceBox option:selected');
        insuranceOption.attr('data-insurance', Number(pricing.insurance) || 0)
            .data('insurance', Number(pricing.insurance) || 0);
        if (Number(pricing.exchange_rate) > 0) {
            $('#holdThisInfo').attr(pricing.currency === 'CAD' ? 'one_dollar' : 'one_pound', pricing.exchange_rate);
            $(form).find('[name="rate"]').val(pricing.exchange_rate);
        }
        updateitems();
        $(form).find('[name="booking_quote_token"]').val(response.quote_token);
        $(form).data('checkout-quote', pricing);
        updateBooking();
        updateParcelGuaranteeNotice();
        if ($.fn.steps) {
            for (let index = $(form).steps('getCurrentIndex'); index < 4; index++) {
                const prior = $(form).steps('getCurrentIndex');
                $(form).steps('next');
                if ($(form).steps('getCurrentIndex') === prior) break;
            }
        }
        // Confirmation remains an explicit second click on the existing Finish control.
        $(form).find('.actions a[href="#finish"]').trigger('focus');
        return true;
    };

    function scheduleBookingRecoveryExpiry(button, timestamp) {
        window.clearTimeout(button.data('booking-recovery-timeout'));
        const deadline = Number(timestamp) * 1000;
        if (!Number.isFinite(deadline) || deadline <= 0) return;
        button.attr('data-resume-until', timestamp);
        const expire = function () {
            button.addClass('d-none').prop('disabled', true);
            const container = button.closest('[data-booking-recovery]');
            container.find('[data-booking-recovery-status]').text('');
            if (button.attr('data-booking-mode') === 'check' || button.attr('data-expiry-checking') === 'true') {
                container.closest('td').find('.badge').first()
                    .removeClass('bg-danger-subtle text-danger text_danger')
                    .addClass('bg-warning-subtle text-warning').text('Checking payment');
            } else {
                container.closest('td').find('.badge').first()
                    .removeClass('bg-warning-subtle text-warning text-danger')
                    .addClass('bg-danger-subtle text_danger').text('Canceled');
            }
        };
        if (deadline <= Date.now()) expire();
        else button.data('booking-recovery-timeout', window.setTimeout(expire, Math.min(900001, deadline - Date.now() + 1)));
    }
    function initializeBookingRecoveryButtons(buttons) {
        buttons.each(function () {
            const button = $(this);
            if (button.data('booking-recovery-initialized')) return;
            button.data('booking-recovery-initialized', true).prop('disabled', false);
            scheduleBookingRecoveryExpiry(button, button.attr('data-resume-until'));
        });
    }
    initializeBookingRecoveryButtons($('[data-booking-resume]'));
    if ($.fn.dataTable && $.fn.dataTable.isDataTable('#default_order')) {
        // DataTables detaches other pages. Their controls and deadlines must start now too.
        initializeBookingRecoveryButtons($('#default_order').DataTable().rows().nodes().to$().find('[data-booking-resume]'));
    }
    $('#default_order').on('draw.dt.bookingRecovery', function () {
        initializeBookingRecoveryButtons($(this).find('[data-booking-resume]'));
    });
    $(document).on('click.bookingResume', '[data-booking-resume]', function () {
        const button = $(this);
        if (button.prop('disabled')) return;
        const container = button.closest('[data-booking-recovery]');
        const body = new FormData();
        body.append('booking_hash', button.attr('data-booking-hash'));
        body.append('mode', button.attr('data-booking-mode') === 'check' ? 'check' : 'resume');
        body.append('booking_checkout_token', container.attr('data-checkout-token'));
        appendGlobalCsrfToFormData(body);
        button.prop('disabled', true).attr('aria-busy', 'true');
        button.find('[data-booking-resume-spinner]').removeClass('d-none');
        $.ajax({
            url: base_url + 'user_bookings/resume_ajax',
            type: 'POST', data: body, dataType: 'json', processData: false, contentType: false,
            success: function (response) {
                updateGlobalCsrfHash(response.csrf_hash);
                if (response.status && response.redirect) {
                    window.location.assign(response.redirect);
                    return;
                }
                const message = response.msg || 'We could not resume this booking. Please try again.';
                container.find('[data-booking-recovery-status]').text(message);
                toastr.error(message, response.title || 'Booking unavailable', {progressBar: true, timeOut: 6000});
                const badge = container.closest('td').find('.badge').first();
                if (response.review_required === true || response.review === true) {
                    window.clearTimeout(button.data('booking-recovery-timeout'));
                    button.addClass('d-none');
                    badge.removeClass('bg-danger-subtle text-danger text_danger').addClass('bg-warning-subtle text-warning').text('Payment needs review');
                    return;
                }
                if (response.checking_payment === true) {
                    button.removeClass('d-none').attr('data-booking-mode', 'check');
                    button.find('[data-booking-resume-label]').text('Check payment');
                    badge.removeClass('bg-danger-subtle text-danger text_danger').addClass('bg-warning-subtle text-warning').text('Checking payment');
                    scheduleBookingRecoveryExpiry(button, response.resume_until || button.attr('data-resume-until'));
                    return;
                }
                if (response.recovery_expired === true) {
                    button.attr('data-booking-mode', 'resume');
                    scheduleBookingRecoveryExpiry(button, response.resume_until || 1);
                    return;
                }
                if (response.resume_available === true) {
                    button.removeClass('d-none').attr('data-booking-mode', 'resume');
                    button.find('[data-booking-resume-label]').text('Resume booking');
                    badge.removeClass('bg-danger-subtle text-danger text_danger').addClass('bg-warning-subtle text-warning').text('Not completed');
                    scheduleBookingRecoveryExpiry(button, response.resume_until);
                    return;
                }
                if (response.resume_available === false && response.checking_payment !== true) button.addClass('d-none');
            },
            error: function (xhr) {
                const error = getAjaxErrorMessage(xhr, 'We could not check this booking. Please try again.');
                container.find('[data-booking-recovery-status]').text(error.message);
                toastr.error(error.message, error.title, {progressBar: true, timeOut: 6000});
            },
            complete: function () {
                button.prop('disabled', false).attr('aria-busy', 'false');
                button.find('[data-booking-resume-spinner]').addClass('d-none');
            },
        });
    });

    // Add an event listener to the "add-item" button
    $('button#add-me').click(function () {
        // Check the available space
        fetch(
            `${base_url}user_bookings/get_traveling_available_space/${$(
                'form#booking_form[key]'
            ).attr('key')}`
        )
            .then((response) => {
                return response.text();
            })
            .then((aSpace) => {
                let calculations = calculateBooking();
                let size = document.getElementById('select2').value;
                let currentBagSpace =
                    parseFloat(aSpace) - calculations.selectedSpace;

                $('#availableSpace').text(`${currentBagSpace}KG`);

                if (size > currentBagSpace) {
                    // Display error message
                    toastr.error(
                        `${size}KG exceeds available bag space(${currentBagSpace}KG)`,
                        `Not Enough Bag Space`,
                        {
                            progressBar: true,
                            timeOut: 5000,
                        }
                    );
                    return false;
                }

                return parseFloat(currentBagSpace);
            })
            .then((aSpace) => {
                if (aSpace === false) {
                    return false;
                }
                // Error message function
                function showError(error, element) {
                    element.addClass('error');
                    if (element.is('select:hidden')) {
                        element.next('.nice-select').addClass('error');
                    }
                }

                // Get the selected category, item name, and size
                const category = document.getElementById('select1').value;
                const price = $(`option[value = "${category}"]`).attr(
                    'data-price'
                );
                const itemName = document.getElementById('item-name').value;
                const size = document.getElementById('select2').value;

                // --- NEW: Determine unit for the new item ---
                const unit = $('#select2').attr('data-unit'); // Get the current unit (KG or Piece)

                $('.error_msg_item').html('');

                if (
                    parseFloat(size) > calculateBooking().currentAvailableSpace
                ) {
                    $('.error_msg_item').html(`
                    <div class="col-lg-12 text-danger">
                        Selected space (${parseFloat(
                            size
                        )}kg) cannot exceed available space (${
                            calculateBooking().currentAvailableSpace
                        }kg)
                    </div>`);
                    return;
                }

                // Error messages for add button
                if (
                    category.trim() == '' ||
                    !parseFloat(size) ||
                    itemName.trim() == ''
                ) {
                    if (category.trim() == '') {
                        showError(
                            '<span for="select1" class="text_danger">Select a category</span>',
                            $('#select1')
                        );
                    } else {
                        $("span[for='select1']").remove();
                        $('#select1')
                            .removeClass('error')
                            .next('.nice-select')
                            .removeClass('error');
                    }

                    if (itemName.trim() == '') {
                        showError(
                            '<span for="item-name" class="text_danger">Provide item name</span>',
                            $('#item-name')
                        );
                    } else {
                        $("span[for='#item-name']").remove();
                        $('#item-name').removeClass('error');
                    }

                    if (!parseInt(size)) {
                        showError(
                            '<span for="select2" class="text_danger">Select item size</span>',
                            $('#select2')
                        );
                    } else {
                        $("span[for='select2']").remove();
                        $('#select2')
                            .removeClass('error')
                            .next('.nice-select')
                            .removeClass('error');
                    }
                    return;
                } else {
                    $('#item-options').find('span.text_danger').remove();
                    $('#select1, #item-name, #select2').removeClass('error');
                    $('#select1, #select2')
                        .next('.nice-select')
                        .removeClass('error');
                }

                // Calculate the kg based on the selected size
                let kg = parseFloat(size);

                // Define special charges for the two categories
                let specialCharge = isSpecialCategory(category) ? 10 : 0;

                // Create a new item element with the selected category, item name, size, price, special charge, and delete icon
                let currencySymbol = $('#holdThisInfo').attr('symbol');
                const newItem = createBookingParcelNode({category: category,
                    item_name: itemName, size: kg, unit_price: price, unit: unit}, currencySymbol);

                // Add the new item element to the list
                document.getElementById('item-list').appendChild(newItem);
                showMobileItemAddedFeedback(itemName);

                // Clear the select and text fields
                document.getElementById('select1').selectedIndex = 0;
                document.getElementById('item-name').value = '';
                document.getElementById('select2').selectedIndex = 0;

                // Reset the unit display for the next item input in the form
                updateWeightUnit('KG');

                $("span[for='select1']").remove();
                $("span[for='item-name']").remove();
                $("span[for='select2']").remove();
                $('#item-options').find('span.error').remove();

                $('#availableSpace').text(`${parseFloat(aSpace) - size}KG`);
                updateitems();
                updateBooking();
                document.getElementById('special-charge-value').textContent =
                    getSpecialCharge().toFixed(2); // Update special charge value
            })
            .catch(() => {
                toastr.error(
                    'We could not confirm the available bag space. Please try again.',
                    'Booking Error',
                    {
                        progressBar: true,
                        timeOut: 5000,
                    }
                );
            });
        return;
    });

    function updateParcelGuaranteeNotice() {
        let guaranteeSelect = $('#insuranceBox');
        let notice = $('#parcel-guarantee-notice');
        let protectionNameTarget = $('#parcel-protection-name');
        let coverageTarget = $('#parcel-guarantee-coverage');
        let priceTarget = $('#parcel-guarantee-price');

        if (
            !guaranteeSelect.length ||
            !notice.length ||
            !protectionNameTarget.length ||
            !coverageTarget.length ||
            !priceTarget.length
        ) {
            return;
        }

        let selectedOption = guaranteeSelect.find('option:selected');
        let coverage = selectedOption.attr('data-coverage');
        let price = selectedOption.attr('data-insurance');
        let protectionName = selectedOption.attr('data-protection-label');

        if (!coverage || !price || !protectionName) {
            protectionNameTarget.text('');
            coverageTarget.text('');
            priceTarget.text('');
            notice.addClass('d-none');
            return;
        }

        let currencySymbol = notice.attr('data-currency-symbol') || '';
        protectionNameTarget.text(protectionName);
        coverageTarget.text(currencySymbol + coverage);
        priceTarget.text(currencySymbol + price);
        notice.removeClass('d-none');
    }

    // Update the booking total and the applicable guarantee notice together.
    $('#insuranceBox').change(function () {
        updateParcelGuaranteeNotice();
        updateBooking();
    });
    updateParcelGuaranteeNotice();

    $('input[name="payment_method"]').change(function () {
        syncPaymentMethod();
        updateBooking();
    });

    //For text to display while typing
    const receiverInput = document.getElementById('receiverName');
    const agentInput = document.getElementById('agentName');
    const receiverAddressInput = document.getElementById('receiverAddress');
    const agentaddressInput = document.getElementById('agentAddress');

    const receiverValue = document.getElementById('receiverNameValue');
    const agentValue = document.getElementById('agentNameValue');
    const receiverAddressValue = document.getElementById(
        'receiverAddressValue'
    );
    const agentaddressValue = document.getElementById('agentAddressValue');

    if (
        receiverInput &&
        agentInput &&
        receiverAddressInput &&
        agentaddressInput
    ) {
        receiverInput.addEventListener('input', (event) => {
            receiverValue.textContent = event.target.value;
        });

        agentInput.addEventListener('input', (event) => {
            agentValue.textContent = event.target.value;
        });

        receiverAddressInput.addEventListener('input', (event) => {
            receiverAddressValue.textContent = event.target.value;
        });

        agentaddressInput.addEventListener('input', (event) => {
            agentaddressValue.textContent = event.target.value;
        });
    }

    $('#capture-video').on('hidden.bs.modal', function () {
        // toggleCamera();
        closeCamera();
    });

    // Event listener for when the paragraph is clicked to open/close the camera
    const selfieParagraph = document.getElementById('selfie-paragraph');
    selfieParagraph
        ? selfieParagraph.addEventListener('click', function () {
              toggleCamera();
              $('#capture-video').modal('show');
          })
        : '';

    // Event listener for when the snap icon is clicked to capture the image
    const snapIcon = document.getElementById('snap-icon');
    snapIcon
        ? snapIcon.addEventListener('click', function () {
              let input = $(`#${$('#capture-video').attr('target-input')}`)[0];
              let image = $(`#${$('#capture-video').attr('target-img')}`)[0];

              captureImage(input, image);
              $('#capture-video').modal('hide');
          })
        : '';

    // Event listener for when the retake icon is clicked
    const retakeIcon = document.getElementById('retake-icon');
    retakeIcon ? retakeIcon.addEventListener('click', retakePicture) : '';

    //Image Preview
    function ImagePreview(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#image_preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#the_image').change(function () {
        ImagePreview(this);
        $('#image_preview_area').css('display', 'block');
    });

    $('#remove_image').click(function () {
        $('#the_image').val('');
        $('#image_preview_area').css('display', 'none');
    });

    //Image preview on update: toggle current and new image
    $('#the_image_on_update').change(function () {
        ImagePreview(this);
        $('#image_preview_area').css('display', 'block');
        $('#current_image_area').css('display', 'none');
        $('#change_image_text').css('display', 'none');
    });

    $('#remove_image').click(function () {
        $('#the_image_on_update').val('');
        $('#image_preview_area').css('display', 'none');
        $('#current_image_area').css('display', 'block');
        $('#change_image_text').css('display', 'block');
    });

    //Display account details based on account selected
    $(document).ready(function () {
        function handleSelectedAccChange() {
            var selectedAcc = $('#payment_acc_select');

            // Get the selected value
            var selectedValue = selectedAcc.val();

            var ubaAcc = $('#uba_account');
            var metroAcc = $('#metro_account');

            // Show the corresponding account column based on the selected value
            if (selectedValue === 'United Bank of Africa') {
                ubaAcc.removeClass('d-none');
                metroAcc.addClass('d-none');
            } else if (selectedValue === 'Metro Bank PLC') {
                metroAcc.removeClass('d-none');
                ubaAcc.addClass('d-none');
            } else if (selectedValue === '') {
                metroAcc.addClass('d-none');
                ubaAcc.addClass('d-none');
            }
        }

        $(document).ready(function () {
            // Call the function on load
            handleSelectedAccChange();

            var selectedAcc = $('#payment_acc_select');

            // Bind the function to the change event
            selectedAcc.on('change', handleSelectedAccChange);
        });
    });

    $('input.form-control').change(function () {
        $(this).siblings('span.error').css('display', 'none');
        $(this).removeClass('error');
    });

    $('select').change(function () {
        $(this).siblings('span.error').css('display', 'none');
        $(this).removeClass('error').next('.nice-select').removeClass('error');
    });

    function activateSelect() {
        $('div.nice-select').each(function () {
            if ($(this).attr('activate-select') != 'yes') {
                $(this).attr('activate-select', 'yes');
                $($(this))
                    .find('ul.list')
                    .children('.option')
                    .click(function () {
                        selectedVal = $(this).attr('data-value').trim();
                        $(this)
                            .parents('div.nice-select')
                            .siblings('select')
                            .val(selectedVal);
                        $(this)
                            .parents('div.nice-select')
                            .siblings('span.error')
                            .css('display', 'none');
                    });
            }
        });
    }

    activateSelect();

    $('#bottom-wizard')
        .find('button.forward')
        .click(function () {
            activateSelect();
        });

    $('#bottom-wizard')
        .find('button.backward')
        .click(function () {
            activateSelect();
        });

    $('.reset_img_input').click(function () {
        const image = $(this).siblings('img');
        const inputRef = image.attr('id');
        const targetInput = $('input[holder="' + inputRef + '"]');

        if (!targetInput.length) {
            return;
        }

        targetInput.val('').removeClass('input-image-blob error');

        const inputEl = targetInput[0];
        // Camera captures use a data URL field, including type="text" with hidden.
        if (inputEl.type !== 'file') {
            const originalSrc = image.data('original-src') || image.attr('src');
            image.attr('src', originalSrc).removeClass('img-changed');
            $('.take-selfie[target-input="' + inputEl.id + '"]')
                .removeClass('kyc-selfie-retake')
                .html('<i class="ti ti-camera"></i><span>Take Selfie</span>');
            return;
        }

        const changeEvent = new Event('change');
        inputEl.dispatchEvent(changeEvent);
    });

    autoLoadPageHelpers();
});

function openCamera(input, img) {
    const videoContainer = document.getElementById('video-container');
    const videoPreview = document.getElementById('video-preview');
    const imagePreview = document.getElementById('image-preview');

    let captureModal = $('#capture-video');
    let input_count = $(`#${input}`).length;
    let img_count = $(`#${img}`).length;

    if (!input_count || !img_count) {
        return;
    } else {
        captureModal.attr('target-img', img);
        captureModal.attr('target-input', input);
    }

    // Only open the camera if it is not currently open
    if (!videoPreview.srcObject) {
        navigator.mediaDevices
            .getUserMedia({ video: true })
            .then((stream) => {
                videoPreview.srcObject = stream;
                videoContainer.style.display = 'block';
                videoPreview.style.display = 'block';
                imagePreview.style.display = 'none';
                captureModal.modal('show');
            })
            .catch(() => {});
    }
}

// Function to close camera
function closeCamera() {
    const videoContainer = document.getElementById('video-container');
    const videoPreview = document.getElementById('video-preview');
    const imagePreview = document.getElementById('image-preview');

    let captureModal = $('#capture-video');

    // Check if the camera is open by checking if srcObject has a stream
    if (videoPreview.srcObject) {
        // Stop video stream
        const stream = videoPreview.srcObject;
        const tracks = stream.getTracks();
        tracks.forEach((track) => track.stop());

        videoPreview.srcObject = null; // Clear the video source to release the camera
        videoContainer.style.display = 'none'; // Optionally hide video container
        videoPreview.style.display = 'none';
        imagePreview.style.display = 'block';
    }

    captureModal.removeAttr('target-img');
    captureModal.removeAttr('target-input');
}

// Function to capture the image from the webcam
let isCameraOpen = false;

// Function to open/close the camera
function toggleCamera() {
    const selfieParagraph = document.getElementById('selfie-paragraph');
    const videoContainer = document.getElementById('video-container');
    const actionButtons = document.getElementById('action-buttons');
    const videoPreview = document.getElementById('video-preview');
    const imagePreview = document.getElementById('image-preview');
    videoPreview.style.display = 'block';
    imagePreview.style.display = 'none';

    if (!isCameraOpen) {
        // Access the user's webcam
        navigator.mediaDevices
            .getUserMedia({ video: true })
            .then((stream) => {
                videoPreview.srcObject = stream;
            })
            .catch(() => {});

        selfieParagraph.textContent = 'Close camera';
        videoContainer.style.display = 'block';
        imagePreview.style.display = 'none';
        isCameraOpen = true;
    } else {
        // Stop video stream
        const stream = videoPreview.srcObject;
        const tracks = stream.getTracks();
        tracks.forEach((track) => track.stop());

        selfieParagraph.textContent = 'Click here to take a selfie!';
        videoContainer.style.display = 'block';
        imagePreview.style.display = 'block';
        isCameraOpen = false;
    }
}

// Function to capture the image from the webcam
function captureImage(input = null, image = null) {
    const videoPreview = document.getElementById('video-preview');
    const modalPreview = document.getElementById('image-preview');
    const imagePreview = image ?? document.getElementById('selfie_holder');
    const inputEl = input ?? document.getElementById('image-input');

    if (
        !videoPreview ||
        !imagePreview ||
        !inputEl ||
        !videoPreview.videoWidth
    ) {
        return;
    }

    // Create a canvas element
    const canvas = document.createElement('canvas');
    canvas.width = videoPreview.videoWidth;
    canvas.height = videoPreview.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(videoPreview, 0, 0, canvas.width, canvas.height);

    // Convert the canvas image to a data URL
    const imageDataURL = canvas.toDataURL();
    inputEl.value = imageDataURL;

    inputEl.classList.add('input-image-blob');

    // Display the captured image in the preview box
    if (!$(imagePreview).data('original-src')) {
        $(imagePreview).data('original-src', imagePreview.src);
    }
    imagePreview.src = imageDataURL;
    imagePreview.classList.add('img-changed');

    if (modalPreview) {
        modalPreview.src = imageDataURL;
        modalPreview.style.display = 'block';
    }

    $('.take-selfie[target-input="' + inputEl.id + '"]')
        .addClass('kyc-selfie-retake')
        .html('<i class="ti ti-camera-rotate"></i><span>Retake Selfie</span>');

    // Hide the video preview
    videoPreview.style.display = 'none';
}

// Function to retake the picture
function retakePicture() {
    const captureModal = $('#capture-video');
    const videoPreview = document.getElementById('video-preview');
    const imagePreview = document.getElementById('image-preview');

    if (!videoPreview || !navigator.mediaDevices) {
        return;
    }

    if (videoPreview.srcObject) {
        videoPreview.srcObject.getTracks().forEach((track) => track.stop());
        videoPreview.srcObject = null;
    }

    navigator.mediaDevices
        .getUserMedia({ video: true })
        .then((stream) => {
            videoPreview.srcObject = stream;
            videoPreview.style.display = 'block';
            if (imagePreview) {
                imagePreview.style.display = 'none';
            }
            captureModal.modal('show');
        })
        .catch(() => {});
}

// Monitor Image Changes on and input and image element
function monitorImageChange(inputId, imageId) {
    var input = document.getElementById(inputId);
    var image = document.getElementById(imageId);
    var previousSrc = image.src;

    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var reader = new FileReader();

            reader.onload = function (e) {
                if (isImageFile(file)) {
                    image.setAttribute('data-previous-src', previousSrc);
                    image.classList.add('img-changed');
                    image.src = e.target.result;
                } else {
                    // Not an image file, do something (e.g., display an error message)
                    return;
                }
            };

            reader.readAsDataURL(file);
        } else {
            image.src = previousSrc;
            image.classList.remove('img-changed');
        }
        input.classList.add('img-change-monitor-init');
    });
}

//Update the add in the item field
function updateitems() {
    var items = [];

    $('.select_item').each(function (index, element) {
        let item = {
            category: $(this).attr('category'),
            item_name: $(this).attr('itemName'),
            size: $(this).attr('size'),
            price: $(this).attr('price'),
            unit_price: $(this).attr('unitPrice'),
            unit: $(this).attr('unit'), // Capture unit from item element
        };
        items.push(item);
    });
    items.length === 0
        ? $('#items_input').val('')
        : $('#items_input').val(JSON.stringify(items));
    $('#items_input').trigger('change');
    return items;
}

function getSelectedPaymentMethod() {
    return $('input[name="payment_method"]:checked').val() || 'paystack';
}

function syncPaymentMethod() {
    $('#payment_method').val(getSelectedPaymentMethod());
}

function isPremiumSmallCategory(category) {
    return (
        category === 'Documents/Small Electronics' ||
        category === 'Documents/Electronics' ||
        category === 'Gold'
    );
}

function isPremiumLaptopCategory(category) {
    return category === 'Laptop';
}

function isSpecialCategory(category) {
    return (
        category === 'Fish/Medicine' ||
        category === 'Fish/Meat' ||
        category === 'Medication'
    );
}

function calculateTravellerCommissionForSummary(selectedSpace) {
    let normalPayout =
        parseFloat($('#holdThisInfo').attr('normal_payout')) || 0;
    let specialPayout =
        parseFloat($('#holdThisInfo').attr('special_payout')) || normalPayout;
    let premiumSmallPayout =
        parseFloat($('#holdThisInfo').attr('premium_small_payout')) ||
        normalPayout;
    let premiumLaptopPayout =
        parseFloat($('#holdThisInfo').attr('premium_laptop_payout')) ||
        premiumSmallPayout;
    let items = [];

    $('.select_item').each(function () {
        items.push({
            category: $(this).attr('category'),
            size: parseFloat($(this).attr('size')) || 0,
        });
    });

    return window.ShareMyBagPricing.calculateConfiguredTravellerCommission(
        items,
        {
            normal: normalPayout,
            special: specialPayout,
            premiumSmall: premiumSmallPayout,
            premiumLaptop: premiumLaptopPayout,
        }
    );
}

$('#items_input').change(function () {
    let val = $(this).val().trim();
    let category = $('select[name="category"]');
    let size = $('select[name="size"]');
    let item = $('input[name="item"]');

    if (val !== '') {
        category.removeClass('required');
        size.removeClass('required');
        item.removeClass('required');
    } else {
        category.addClass('required');
        size.addClass('required');
        item.addClass('required');
    }
});

//Calculate selected items
function calculateBooking() {
    let initialAvailableSpace = parseFloat($('.available_space').attr('space'));
    let serviceCharge = parseFloat($('.service_charge').attr('charge'));
    let selectedSpace = 0;
    let selectedPrice = 0;
    let insurance = 0;
    let paymentMethod =
        $('input[name="payment_method"]:checked').val() || 'paystack';

    // --- UPDATED CURRENCY ATTRIBUTES ---
    let currency = $('#holdThisInfo').attr('currency');
    let onePound = $('#holdThisInfo').attr('one_pound');
    let oneDollar = $('#holdThisInfo').attr('one_dollar');
    // --- END UPDATED CURRENCY ATTRIBUTES ---

    $('.select_item').each(function (index, element) {
        selectedSpace += parseFloat($(this).attr('size'));
        selectedPrice += parseFloat($(this).attr('price'));
    });

    // The logic for firstSelectedPrice and secondSelectedPrice seems deprecated
    // or incomplete and is generally avoided in production code unless necessary.
    // We'll keep the original structure but note the currency difference.
    firstSelectedPrice =
        currency == 'GBP'
            ? 50000 / parseFloat(onePound)
            : currency == 'CAD'
              ? 50000 / parseFloat(oneDollar)
              : 50000;

    secondSelectedPrice =
        currency == 'GBP'
            ? 100000 / parseFloat(onePound)
            : currency == 'CAD'
              ? 100000 / parseFloat(oneDollar)
              : 100000;

    insuranceOne =
        currency == 'GBP'
            ? 1500 / parseFloat(onePound)
            : currency == 'CAD'
              ? 1500 / parseFloat(oneDollar)
              : 1500;

    insuranceTwo =
        currency == 'GBP'
            ? 3000 / parseFloat(onePound)
            : currency == 'CAD'
              ? 3000 / parseFloat(oneDollar)
              : 3000;

    // Get the selected insurance value from the select element
    let selectedInsurance = parseFloat(
        $('#insuranceBox option:selected').data('insurance')
    );

    // Use the selected insurance value directly
    insurance = selectedInsurance ? selectedInsurance : 0;

    let currentAvailableSpace = initialAvailableSpace - selectedSpace;
    let specialCharge = getSpecialCharge();
    let subTotal = serviceCharge + selectedPrice;
    let baseTotal = subTotal + insurance + specialCharge;
    let travellerCommission =
        calculateTravellerCommissionForSummary(selectedSpace);
    let platformCommission = Math.max(
        0,
        baseTotal - travellerCommission - serviceCharge - insurance
    );
    let vatBase = platformCommission + serviceCharge;
    let vatRate = Number($('#holdThisInfo').attr('vat_rate')) || 0;
    let vat = paymentMethod === 'paystack' ? vatRate * vatBase : 0;
    let totalAmount = baseTotal + vat;
    let calculatedValues = {
        initialAvailableSpace: initialAvailableSpace,
        selectedSpace: parseFloat(selectedSpace.toFixed(2)),
        selectedPrice: parseFloat(selectedPrice.toFixed(2)),
        totalAmount: parseFloat(totalAmount.toFixed(2)),
        baseTotal: parseFloat(baseTotal.toFixed(2)),
        subTotal: parseFloat(subTotal.toFixed(2)),
        vat: parseFloat(vat.toFixed(2)),
        vatBase: parseFloat(vatBase.toFixed(2)),
        platformCommission: parseFloat(platformCommission.toFixed(2)),
        travellerCommission: parseFloat(travellerCommission.toFixed(2)),
        currentAvailableSpace: currentAvailableSpace,
        serviceCharge: parseFloat(serviceCharge.toFixed(2)),
        insurance: parseFloat(insurance.toFixed(2)),
        specialCharge: parseFloat(specialCharge.toFixed(2)),
        paymentMethod: paymentMethod,
        currency: currency,
        onePound: onePound,
        oneDollar: oneDollar, // Added oneDollar
    };
    const quoted = $('#booking_form').data('checkout-quote');
    if (quoted && $('#booking_form [name="booking_quote_token"]').val()) {
        Object.assign(calculatedValues, {
            selectedSpace: Number(quoted.selected_space),
            selectedPrice: quoted.items.reduce(function (sum, item) { return sum + Number(item.unit_price) * Number(item.size); }, 0),
            subTotal: Number(quoted.sub_total),
            serviceCharge: Number(quoted.service_charge),
            insurance: Number(quoted.insurance),
            vat: Number(quoted.vat),
            totalAmount: Number(quoted.total_amount),
            baseTotal: Number(quoted.total_amount) - Number(quoted.vat),
        });
    }
    $('#price_calculations').val(JSON.stringify(calculatedValues));
    return calculatedValues;
}

// Update prices in summary
function updateBooking() {
    $('.available_space').html(`${calculateBooking().currentAvailableSpace}KG`);
    $('#total-kg').html(`${calculateBooking().selectedSpace}`);
    $('#total-kgs').html(`${calculateBooking().selectedSpace}`);

    // --- NEW: Update the summary unit based on the last added item's category/unit state ---
    let unit = $('#select2').attr('data-unit');
    $('#total-unit').text(unit);

    // Update unit for items already in the list
    $('#item-list .select_item').each(function () {
        let itemCategory = $(this).attr('category');
        let itemUnit = $(this).attr('unit'); // Use the unit attribute stored when item was added
        $(this)
            .find('.size')
            .text($(this).attr('size') + itemUnit);
    });
    // --- END NEW: Update unit logic ---

    // --- UPDATED CURRENCY DISPLAY LOGIC ---
    let currency = calculateBooking().currency;
    let totalAmount = calculateBooking().totalAmount;
    let subTotal = calculateBooking().subTotal;
    let insurance = calculateBooking().insurance;
    let vat = calculateBooking().vat;
    let specialCharge = getSpecialCharge();

    let displaySymbol = $('#holdThisInfo').attr('symbol');

    $('#total-price').html(
        `${displaySymbol}${totalAmount.toFixed(2).toLocaleString()}`
    );
    $('#sub-total').html(
        `${displaySymbol}${subTotal.toFixed(2).toLocaleString()}`
    );
    $('#insurance-value').html(
        `${displaySymbol}${insurance.toFixed(2).toLocaleString()}`
    );
    $('#vat-value').html(`${displaySymbol}${vat.toFixed(2).toLocaleString()}`);
    $('#special-charge-value').html(
        `${displaySymbol}${specialCharge.toFixed(2)}`
    );
    $('.vat_row').toggle(vat > 0);

    // Explicitly update Service Charge with symbol (Fixes missing symbol on load/update)
    let serviceChargeValue = parseFloat($('.service_charge').attr('charge'));
    $('.service_charge span span').html(
        `${displaySymbol}${serviceChargeValue.toFixed(2)}`
    );

    // Determine the correct total amount to show in the large summary area
    let largeTotalDisplay = '';
    if (currency === 'CAD') {
        // Show CAD amount with $ symbol
        largeTotalDisplay =
            $('#holdThisInfo').attr('dollar_sign') +
            totalAmount.toFixed(2).toLocaleString();
    } else {
        // Show GBP amount with £ symbol
        largeTotalDisplay =
            $('#holdThisInfo').attr('pound_sign') +
            totalAmount.toFixed(2).toLocaleString();
    }

    // Set the value for the large display on the summary page
    if (getSelectedPaymentMethod() === 'paystack') {
        const quote = $('#booking_form').data('checkout-quote');
        const rate = Number($('#holdThisInfo').attr(currency === 'CAD' ? 'one_dollar' : 'one_pound'));
        const payable = quote && $('#booking_form [name="booking_quote_token"]').val()
            ? Number(quote.charge_minor) / 100 : totalAmount * rate;
        if (Number.isFinite(payable) && payable > 0) {
            largeTotalDisplay += ' (₦' + payable.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
        }
    }
    $('#totalAmountDisplay').text(largeTotalDisplay);

    // --- END UPDATED CURRENCY DISPLAY LOGIC ---
}

// get special charge
function getSpecialCharge() {
    let specialCharge = 0;
    // Note: This reflects the customer-facing special charge displayed in the summary
    let items = $('#items_input').val();
    if (items) {
        items = JSON.parse(items);
        let categories = items.map((item) => item.category);
        if (categories.some((category) => isSpecialCategory(category))) {
            specialCharge += 10; // Extra £10 displayed to user
        }
    }
    return specialCharge;
}

// function getSpecialCharge() {
//   let specialCharge = 0;
//   let specialCharges = {
//     "Fish/Medicine": 10, // Special charge for this category
//     // "Documents/Electronics": 15, // Special charge for this category
//   };
//   let items = $("#items_input").val();
//   if (items) {
//     items = JSON.parse(items);
//     let categories = items.map((item) => item.category);
//     if (categories.includes("Fish/Medicine")) {
//       specialCharge += 10;
//     }
//     // if (categories.includes("Documents/Electronics")) {
//     //   specialCharge += 15;
//     // }
//   }
//   return specialCharge;
// }

// Function to convert Data URI to Blob
function dataURItoBlob(dataURI) {
    // Split the Data URI into metadata and data parts
    var parts = dataURI.split(',');
    var metadata = parts[0]; // e.g., "data:image/png;base64"
    var data = parts[1]; // e.g., "iVBORw0KGg...."

    // Extract the mime type from the metadata
    var mimeType = metadata.split(';')[0].split(':')[1];

    // Convert base64-encoded data to a Blob object
    var byteCharacters = atob(data);
    var byteArrays = [];
    for (var i = 0; i < byteCharacters.length; i++) {
        byteArrays.push(byteCharacters.charCodeAt(i));
    }
    var byteArray = new Uint8Array(byteArrays);
    var blob = new Blob([byteArray], { type: mimeType });

    return blob;
}

// Get extension form raw image data
function getFileExtensionFromMimeType(mimeType) {
    const mimeTypesMap = {
        'image/jpeg': 'jpg',
        'image/png': 'png',
        'image/gif': 'gif',
        'image/bmp': 'bmp',
        'image/webp': 'webp',
        'image/svg+xml': 'svg',
        'application/pdf': 'pdf',
        'application/msword': 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            'docx',
        'application/vnd.ms-excel': 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            'xlsx',
        'application/vnd.ms-powerpoint': 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation':
            'pptx',
        'text/plain': 'txt',
        'text/html': 'html',
        'text/css': 'css',
        'application/json': 'json',
        'application/xml': 'xml',
        // Add more MIME types and their corresponding extensions as needed
    };

    // Extract MIME type without any parameters
    const mimeTypeWithoutParams = mimeType.split(';')[0].trim();

    // Search for matching MIME type in the map
    for (const type in mimeTypesMap) {
        if (
            mimeTypesMap.hasOwnProperty(type) &&
            type === mimeTypeWithoutParams
        ) {
            return mimeTypesMap[type];
        }
    }

    // If no matching extension is found, return null or handle the case as needed
    return null;
}

function isImageFile(file) {
    return file.type.startsWith('image/');
}
