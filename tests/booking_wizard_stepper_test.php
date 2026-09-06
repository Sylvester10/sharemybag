<?php

$root = dirname(__DIR__);

function assert_contains_text($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, "FAIL: {$message}\nMissing: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

function assert_not_contains_text($file, $needle, $message)
{
    $contents = file_get_contents($file);
    if (strpos($contents, $needle) !== false) {
        fwrite(STDERR, "FAIL: {$message}\nUnexpected: {$needle}\nFile: {$file}\n");
        exit(1);
    }
}

$booking_view = $root . '/application/views/users/book_space.php';
$booking_js = $root . '/assets/users/js/booking.js';
$custom_css = $root . '/assets/users/css/custom.css';
$footer_view = $root . '/application/views/users/layout/footer.php';
$ajax_helpers = $root . '/assets/general/js/my_functions.js';

assert_contains_text($booking_view, 'form-wizard-ajax booking-wizard-form', 'Booking form should have a booking-specific wizard class.');
assert_contains_text($booking_view, 'class="fs-6 fw-bolder text-black text-center available_space"', 'Booking summary available space should expose the class used by booking calculations.');
assert_contains_text($booking_view, 'space="<?= $traveller_details->available_space ?>"', 'Booking summary available space should expose the initial space value.');
assert_contains_text($booking_view, 'booking-payment-options', 'Payment choices should use the booking payment-card wrapper.');
assert_contains_text($booking_view, 'booking-payment-input', 'Payment radios should keep form inputs with payment-specific class.');
assert_contains_text($booking_view, 'booking-payment-card', 'Payment logos should be rendered as selectable cards.');
assert_contains_text($booking_view, 'booking-choice-options', 'Agent and receiver radio choices should use booking choice cards.');
assert_contains_text($booking_view, 'booking-choice-input', 'Agent and receiver radios should keep hidden inputs with choice-specific class.');
assert_contains_text($booking_view, 'booking-choice-card', 'Agent and receiver options should render as selectable cards.');

assert_contains_text($booking_js, 'initBookingStepper', 'Booking JS should initialize the booking stepper helper.');
assert_contains_text($booking_js, 'booking-current-step-title', 'Booking JS should inject a clean mobile current-step title block.');
assert_contains_text($booking_js, ".replace(/current step:\\s*/i, '')", 'Mobile step title should strip jQuery Steps helper copy.');
assert_not_contains_text($booking_js, 'Current step:', 'Mobile step title should not hard-code extra helper copy.');
assert_contains_text($booking_js, 'MutationObserver', 'Booking step title should stay in sync with jQuery Steps class changes.');
assert_contains_text($booking_js, 'markBookingStepTransition', 'Booking step changes should trigger a short transition state.');
assert_contains_text($booking_js, 'booking-step-transitioning', 'Booking wizard should expose an animation class while changing steps.');
assert_contains_text($booking_js, 'showMobileItemAddedFeedback', 'Booking JS should show mobile feedback when an item is added.');
assert_contains_text($booking_js, "window.matchMedia('(max-width: 991px)')", 'Add-item feedback should be mobile-only.');
assert_contains_text($booking_js, '}, 4600)', 'Mobile add-item feedback should stay visible long enough to notice.');
assert_contains_text($booking_js, "document.getElementById('receiverAddress')", 'Receiver address live-summary binding should use the correct field id.');
assert_contains_text($booking_js, 'booking-step-past', 'Booking stepper should maintain deterministic past-step classes.');
assert_contains_text($booking_js, 'booking-step-future', 'Booking stepper should maintain deterministic future-step classes.');

assert_contains_text($custom_css, '.booking-wizard-form.wizard > .steps > ul', 'Booking stepper should be scoped to the booking wizard.');
assert_contains_text($custom_css, 'background: #f36b24;', 'Booking stepper should use the ShareMyBag orange for active/completed steps.');
assert_contains_text($custom_css, 'background: #020713;', 'Booking wizard should continue using the ShareMyBag dark color where applicable.');
assert_contains_text($custom_css, '.booking-current-step-title', 'Mobile current-step title styles should be defined.');
assert_contains_text($custom_css, '.booking-wizard-form .form-control.error', 'Booking validation errors should use field-border styling.');
assert_contains_text($custom_css, 'background-image: url("data:image/svg+xml', 'Booking validation errors should show an inline info icon.');
assert_contains_text($custom_css, 'background-position: right 14px center !important;', 'Booking validation input icons should sit on the far right.');
assert_contains_text($custom_css, 'background-color: #ffffff !important;', 'Booking validation errors should not use a red input background.');
assert_contains_text($custom_css, '.booking-wizard-form label.error', 'Booking validation text labels should be hidden.');
assert_contains_text($custom_css, '@keyframes booking-step-pulse', 'Booking stepper should animate active-step movement.');
assert_contains_text($custom_css, '.booking-payment-input:checked + .booking-payment-card', 'Payment selection should be shown with card styling.');
assert_contains_text($custom_css, '.booking-choice-input:checked + .booking-choice-card', 'Agent and receiver selections should be shown with card styling.');
assert_contains_text($custom_css, 'opacity: 0;', 'Payment radio circle should be visually hidden.');
assert_contains_text($custom_css, 'border-color: #f36b24;', 'Selected payment card should use the ShareMyBag orange border.');
assert_contains_text($custom_css, '@media (max-width: 576px)', 'Booking stepper should include a mobile breakpoint.');
assert_contains_text($custom_css, 'font-size: 0;', 'Mobile booking stepper should hide step labels and keep icons visible.');
assert_contains_text($custom_css, '.booking-mobile-added-feedback', 'Mobile add-item feedback styles should be defined.');
assert_contains_text($custom_css, '@keyframes booking-summary-pulse', 'View Summary button should pulse after adding an item on mobile.');
assert_contains_text($custom_css, 'booking-summary-drawer-open', 'Mobile summary drawer should have open-state styles.');

assert_contains_text($footer_view, "typeof _waEmbed === 'function'", 'WhatsApp widget should be guarded when the external script is blocked.');
assert_contains_text($footer_view, "typeof Swiper !== 'function'", 'Swiper initialization should be guarded when the CDN script is blocked.');
assert_contains_text($footer_view, 'booking-summary-drawer-open', 'Mobile summary button should toggle the summary drawer state.');
assert_contains_text($footer_view, 'aria-expanded', 'Mobile summary button should expose its expanded state.');

assert_contains_text($ajax_helpers, 'advanced_form.hasClass("booking-wizard-form")', 'Booking wizard validation should suppress text labels without affecting other forms.');
assert_contains_text($ajax_helpers, "[name='receiver_name']", 'Booking wizard validation should identify the Receiver Details step by its field rather than a fragile step number.');
assert_contains_text($ajax_helpers, 'bookingAgentAndReceiverMatch', 'Booking wizard should compare agent and receiver details before leaving Receiver Details.');
assert_contains_text($ajax_helpers, 'Enter different details for the agent and receiver.', 'Duplicate agent and receiver details should show the established booking error immediately.');
assert_not_contains_text($ajax_helpers, 'age-2', 'Booking wizard should not inherit sample age-step skip logic.');

fwrite(STDOUT, "PASS: booking wizard has responsive stepper styling hooks.\n");
