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

$searchJs = $root . '/assets/users/js/search.js';
$searchView = $root . '/application/views/users/search_travellers.php';
$customCss = $root . '/assets/users/css/custom.css';
$controller = $root . '/application/controllers/User_bookings.php';
$model = $root . '/application/models/Traveller_read_model.php';

assert_contains_text($searchView, 'traveller-search-panel', 'Search page should retain its existing dark search panel.');
assert_contains_text($searchView, 'Search Travellers</h4>', 'Search page should keep the approved existing heading.');
assert_contains_text($searchView, 'name="location" id="select_location"', 'Search should collect the parcel origin.');
assert_contains_text($searchView, 'name="destination" id="select_destination"', 'Search should collect the parcel destination.');
assert_contains_text($searchView, 'Where is your parcel?', 'Origin field should use the requested wording.');
assert_contains_text($searchView, 'Where is your parcel going?', 'Destination field should use the requested wording.');
assert_contains_text($searchView, 'traveller-search-submit', 'Search should retain the existing primary action.');

assert_contains_text($searchJs, 'updateRouteOptionAvailability', 'Route selects should synchronize their available countries.');
$compactSearchJs = preg_replace('/\s+/', '', file_get_contents($searchJs));
if (strpos($compactSearchJs, ".prop('disabled',shouldExclude).prop('hidden',shouldExclude)") === false) {
    throw new RuntimeException('The selected country should be disabled and hidden in the other select.');
}
assert_contains_text($searchJs, 'location === destination', 'Client validation should reject identical route endpoints.');
assert_contains_text($searchJs, "dataType: 'json'", 'Search should request structured JSON directly.');
assert_not_contains_text($searchJs, 'setTimeout(function ()', 'Search should not impose an artificial loading delay.');
assert_contains_text($searchJs, 'buildTravellerAction', 'Traveller action-state rendering should remain centralized.');
assert_contains_text($searchJs, 'buildTravellerCard', 'Traveller results should remain responsive cards.');
assert_contains_text($searchJs, 'traveller-result-card-clickable', 'Available traveller cards should remain clickable.');
assert_contains_text($searchJs, 'Bag is Locked', 'Locked-bag state should remain supported.');
assert_contains_text($searchJs, 'Bag is Full', 'Full-bag state should remain supported.');

assert_not_contains_text($searchView, 'traveller-result-card-skeleton', 'Search should no longer render skeleton cards.');
assert_contains_text($searchView, 'Searching for travellers…', 'Loading copy should match the approved design.');
assert_contains_text($searchView, 'traveller-book-page', 'Search should use the locally animated book.');
assert_not_contains_text($searchView, 'spinner-border', 'Search loading should not use a rotating spinner.');
assert_not_contains_text($customCss, '.traveller-skeleton-block', 'Unused skeleton styles should be removed.');
assert_contains_text($customCss, '@keyframes traveller-book-turn', 'Book pages should animate locally without Lottie.');
assert_contains_text($customCss, '@media (prefers-reduced-motion: reduce)', 'Animations should respect reduced-motion preferences.');
assert_contains_text($customCss, '.traveller-search-fields', 'The two route inputs should have a responsive layout.');
assert_contains_text($customCss, 'grid-template-columns: repeat(2, minmax(0, 1fr));', 'Route fields should sit side by side on larger screens.');

assert_contains_text($controller, "post('location', true)", 'Backend search should accept a filtered origin.');
assert_contains_text($controller, "post('destination', true)", 'Backend search should accept a filtered destination.');
assert_contains_text($controller, '$location === $destination', 'Backend validation should reject identical route endpoints.');
assert_contains_text($controller, 'get_travellers_by_route($location, $destination)', 'Controller should query the complete route.');
assert_contains_text($model, 'function get_travellers_by_route($location, $destination)', 'Traveller model should expose a route search.');
assert_contains_text($model, "where('location', \$location)", 'Route search should filter traveller origin.');
assert_contains_text($model, "where('destination', \$destination)", 'Route search should filter traveller destination.');

fwrite(STDOUT, "PASS: complete-route search, existing result actions and accessible local page-flipping loader.\n");
