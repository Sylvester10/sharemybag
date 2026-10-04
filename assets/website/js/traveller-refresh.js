// Presentation and inline feedback for the updated public Traveller form.
(function ($) {
    'use strict';
    var safetyDeck = document.querySelector('.smb-safety-deck');
    if (safetyDeck) {
        var cards = Array.from(safetyDeck.querySelectorAll('.smb-safety-card'));
        var activeCard = 0;
        var desktopCards = window.matchMedia('(min-width: 768px)');
        function activateCard(index, focus) {
            activeCard = (index + cards.length) % cards.length;
            cards.forEach(function (card, i) {
                var active = i === activeCard;
                card.classList.toggle('is-active', active);
                var trigger = card.querySelector('.smb-safety-trigger');
                var panel = card.querySelector('.smb-safety-body');
                trigger.setAttribute('aria-expanded', String(active));
                panel.setAttribute('aria-hidden', String(!active));
                panel.inert = !active;
                if (active && focus) trigger.focus();
            });
        }
        cards.forEach(function (card, index) {
            var trigger = card.querySelector('.smb-safety-trigger');
            trigger.addEventListener('click', function () { activateCard(index, false); });
            trigger.addEventListener('keydown', function (event) {
                var next = index;
                if (event.key === 'ArrowRight') next += desktopCards.matches ? -1 : 1;
                else if (event.key === 'ArrowLeft') next += desktopCards.matches ? 1 : -1;
                else if (event.key === 'ArrowDown') next++;
                else if (event.key === 'ArrowUp') next--;
                else if (event.key === 'Home') next = desktopCards.matches ? cards.length - 1 : 0;
                else if (event.key === 'End') next = desktopCards.matches ? 0 : cards.length - 1;
                else return;
                event.preventDefault();
                activateCard(next, true);
            });
        });
        var touchStart = null;
        safetyDeck.addEventListener('pointerdown', function (event) {
            if (event.pointerType === 'touch') touchStart = {x: event.clientX, y: event.clientY};
        });
        safetyDeck.addEventListener('pointerup', function (event) {
            if (!touchStart || event.pointerType !== 'touch') return;
            var dx = event.clientX - touchStart.x;
            var dy = event.clientY - touchStart.y;
            touchStart = null;
            if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                var direction = dx < 0 ? 1 : -1;
                activateCard(activeCard + (desktopCards.matches ? -direction : direction), false);
            }
        });
        safetyDeck.addEventListener('pointercancel', function () { touchStart = null; });
        activateCard(0, false);
        safetyDeck.classList.add('is-enhanced');
    }

    var video = document.getElementById('smb-traveller-video');
    var playButton = document.getElementById('smb-traveller-play');
    if (video && playButton) {
        var hero = video.closest('.smb-traveller-video-hero');
        var videoError = document.getElementById('smb-traveller-video-error');
        function stopVideo() {
            hero.classList.remove('is-playing');
            video.controls = false;
        }
        function failVideo() {
            stopVideo();
            videoError.classList.remove('d-none');
        }
        playButton.addEventListener('click', function () {
            videoError.classList.add('d-none');
            video.controls = true;
            var request = video.play();
            if (request && request.catch) request.catch(failVideo);
        });
        video.addEventListener('play', function () {
            hero.classList.add('is-playing');
            video.focus();
        });
        video.setAttribute('tabindex', '0');
        video.addEventListener('ended', function () {
            stopVideo();
            playButton.focus();
        });
        video.addEventListener('error', failVideo);
    }

    var form = document.getElementById('traveller_form');
    if (!form) return;

    var itinerary = document.getElementById('traveller_itinerary_photo');
    var preview = document.getElementById('smb-itinerary-preview');
    var removeItinerary = document.getElementById('smb-itinerary-remove');
    var previewUrl = null;
    function releasePreview() {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    }
    function showPreview() {
        if (!itinerary || !preview) return;
        releasePreview();
        preview.replaceChildren();
        var file = itinerary.files && itinerary.files[0];
        if (removeItinerary) removeItinerary.classList.toggle('d-none', !file);
        var caption = document.createElement('span');
        if (!file) {
            var emptyIcon = document.createElement('i');
            emptyIcon.className = 'las la-file-upload';
            emptyIcon.setAttribute('aria-hidden', 'true');
            caption.textContent = 'Your itinerary preview will appear here.';
            preview.append(emptyIcon, caption);
            return;
        }
        caption.textContent = file.name;
        if (/^image\/(jpeg|png)$/.test(file.type)) {
            var thumbnail = document.createElement('img');
            thumbnail.alt = 'Selected itinerary preview';
            previewUrl = URL.createObjectURL(file);
            thumbnail.src = previewUrl;
            preview.append(thumbnail, caption);
        } else {
            var icon = document.createElement('i');
            icon.className = file.type === 'application/pdf' || /\.pdf$/i.test(file.name)
                ? 'las la-file-pdf' : 'las la-file';
            icon.setAttribute('aria-hidden', 'true');
            preview.append(icon, caption);
        }
    }
    if (itinerary && preview) {
        itinerary.addEventListener('change', showPreview);
        form.addEventListener('reset', function () { window.setTimeout(showPreview, 0); });
        window.addEventListener('pagehide', releasePreview);
    }

    if (removeItinerary) removeItinerary.addEventListener('click', function () {
        itinerary.value = '';
        $(itinerary).trigger('change');
        showPreview();
        itinerary.focus();
    });

    var originSelect = form.elements.location;
    var destinationSelect = form.elements.destination;
    var destinationOptions = Array.from(destinationSelect.options).map(function (option) {
        return {value: option.value, text: option.text, flag: option.getAttribute('data-flag') || ''};
    });
    function decorateCountry(select) {
        var custom = $(select).next('.nice-select');
        if (!custom.length) return;
        function label(target, option) {
            target.empty();
            var flag = option && option.getAttribute('data-flag');
            if (flag) $('<span aria-hidden="true"></span>').addClass('smb-country-option-flag cf cf-16 ' + flag).appendTo(target);
            $('<span></span>').text(option ? option.text : '').appendTo(target);
        }
        custom.find('.option').each(function (index) { label($(this), select.options[index]); });
        label(custom.find('.current'), select.options[select.selectedIndex]);
    }
    function updateDestinations() {
        var selected = destinationSelect.value;
        destinationSelect.replaceChildren();
        destinationOptions.forEach(function (option) {
            if (!option.value || option.value !== originSelect.value) {
                var countryOption = new Option(option.text, option.value);
                countryOption.setAttribute('data-flag', option.flag);
                destinationSelect.add(countryOption);
            }
        });
        destinationSelect.value = selected !== originSelect.value && destinationOptions.some(function (option) { return option.value === selected; }) ? selected : '';
        $(destinationSelect).niceSelect('update');
        window.smbFieldErrors.clearField(destinationSelect);
        decorateCountry(destinationSelect);
        decorateCountry(originSelect);
    }
    $(originSelect).on('change', updateDestinations);
    $(destinationSelect).on('change', function () { decorateCountry(destinationSelect); });
    $(function () { updateDestinations(); });
    form.addEventListener('reset', function () { window.setTimeout(updateDestinations, 0); });

    window.smbValidateTraveller = function () {
        var errors = window.smbFieldErrors;
        errors.clear(form);
        var valid = true;
        Array.from(form.elements).forEach(function (field) {
            if (!field.id || field.type === 'hidden' || field.disabled || !field.willValidate) return;
            if (!field.checkValidity()) {
                errors.show(field.id, field.validationMessage || 'Please complete this field.');
                valid = false;
            }
        });
        var dateValue = document.getElementById('traveller_date_value');
        if (dateValue && !dateValue.value) {
            errors.show('travelDate', 'Please select your travel date.');
            valid = false;
        }
        var origin = form.elements.location.value;
        var destination = form.elements.destination.value;
        if (origin && destination && (origin === destination ||
            (origin === 'Canada' && destination === 'United Kingdom') ||
            (origin === 'United Kingdom' && destination === 'Canada'))) {
            errors.show('traveller_destination', origin === destination
                ? 'Choose a different destination.' : 'This route is not available right now.');
            valid = false;
        }
        if (form.elements.c_captcha_code.value &&
            form.elements.c_captcha_code.value.trim() !== form.elements.captcha_code.value.trim()) {
            errors.show('traveller_c_captcha_code', 'Please enter the captcha code shown.');
            valid = false;
        }
        if (!valid) errors.focusFirst(form);
        return valid;
    };
    window.smbTravellerServerErrors = function (messages) {
        var shown = false;
        Object.keys(messages || {}).forEach(function (name) {
            var field = name === 'travel_date' ? document.getElementById('travelDate') : form.elements.namedItem(name);
            if (field && field.id && messages[name]) {
                window.smbFieldErrors.show(field.id, messages[name]);
                shown = true;
            }
        });
        if (shown) window.smbFieldErrors.focusFirst(form);
        return shown;
    };
}(jQuery));
