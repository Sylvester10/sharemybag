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

$search_js = $root . '/assets/users/js/search.js';
$search_view = $root . '/application/views/users/search_travellers.php';
$custom_css = $root . '/assets/users/css/custom.css';

assert_contains_text($search_view, 'traveller-search-panel', 'Search page should use the dark traveller search panel.');
assert_contains_text($search_view, 'traveller-search-title', 'Search page should keep a styled Search for Travellers title.');
assert_contains_text($search_view, 'Find available travellers for your route and choose who will carry your parcel.', 'Search page should include helpful search guidance.');
assert_contains_text($search_view, 'traveller-search-box', 'Destination select should sit inside the search-box treatment.');
assert_contains_text($search_view, 'traveller-search-select', 'Destination select should have a search-panel-specific class.');
assert_contains_text($search_view, 'traveller-search-submit', 'Search submit button should have a search-panel-specific class.');
assert_contains_text($search_view, 'class="btn btn-primary traveller-search-submit"', 'Search submit button should use the existing dashboard primary button pattern.');
assert_not_contains_text($search_view, 'ti ti-search', 'Search panel should not display a search icon inside the select field.');
assert_not_contains_text($search_view, 'card-header text-bg-primary', 'Search panel should not use the old default card header.');

assert_contains_text($search_js, 'buildTravellerAction', 'Traveller search should centralize action-state rendering.');
assert_contains_text($search_js, 'buildTravellerCard', 'Traveller search should render cards instead of table rows.');
assert_contains_text($search_js, 'traveller-results-grid', 'Traveller results should be wrapped in a responsive card grid.');
assert_contains_text($search_js, 'traveller-result-card', 'Traveller search should render each traveller as a card.');
assert_contains_text($search_js, 'traveller-card-visual', 'Desktop traveller cards should include a left visual block.');
assert_contains_text($search_js, 'traveller-card-content', 'Desktop traveller cards should include a middle content area.');
assert_contains_text($search_js, 'traveller-card-meta', 'Desktop traveller cards should show compact metadata rows.');
assert_contains_text($search_js, 'traveller-card-cta', 'Desktop traveller cards should keep the CTA in a right-side action area.');
assert_contains_text($search_js, 'data-traveller-href', 'Clickable traveller cards should expose the buy-space URL.');
assert_contains_text($search_js, 'escapeHtml(action.href)', 'Clickable traveller cards should safely render generated URLs.');
assert_contains_text($search_js, '$(e.target).closest("a, button").length', 'Card click handling should not hijack nested buttons or links.');
assert_contains_text($search_js, 'e.key !== "Enter" && e.key !== " "', 'Clickable traveller cards should support keyboard activation.');
assert_contains_text($search_js, 'buy-bag-space/${traveller.hash}', 'Valid traveller cards should keep the existing buy-space route.');
assert_contains_text($search_js, 'class="btn btn-primary traveller-card-action"', 'Buy Space CTA should use the existing dashboard primary button style.');
assert_not_contains_text($search_js, 'btn btn-success traveller-card-action', 'Buy Space CTA should not use a separate green button style.');
assert_contains_text($search_js, 'traveller.destination === "United Kingdom" || traveller.destination === "Canada"', 'UK and Canada verification logic should be preserved.');
assert_contains_text($search_js, 'traveller.destination === "Nigeria"', 'Nigeria profile-completion logic should be preserved.');
assert_contains_text($search_js, 'traveller.profile_completed === 0', 'Incomplete-profile checks should be preserved.');
assert_contains_text($search_js, 'data-bs-target="#verifyID"', 'Verification modal action should be preserved.');
assert_contains_text($search_js, 'data-bs-target="#goToProfile"', 'Incomplete-profile modal action should be preserved.');
assert_contains_text($search_js, 'Bag is Locked', 'Locked-bag state should be preserved.');
assert_contains_text($search_js, 'Bag is Full', 'Full-bag state should be preserved.');
assert_not_contains_text($search_js, 'console.log(response)', 'Search results should not leave debug logging in production UI code.');

assert_not_contains_text($search_js, '<table class="table text-nowrap mb-0"', 'Traveller results should no longer render a wide table.');
assert_not_contains_text($search_js, '<tbody>', 'Traveller results should no longer render table body markup.');
assert_not_contains_text($search_js, 'Swipe to view more details', 'Traveller cards should not require horizontal-swipe instructions.');

assert_contains_text($search_view, 'id="search-results"', 'Search page should retain the AJAX result mount point.');
assert_not_contains_text($search_view, '<table class="table text-nowrap mb-0 d-none"', 'Search page should not ship an unused traveller-results table shell.');
assert_not_contains_text($search_view, 'table-responsive" id="search-results"', 'Search result mount point should not imply horizontal table scrolling.');

assert_contains_text($custom_css, '.traveller-results-grid', 'Traveller card grid styles should be defined.');
assert_contains_text($custom_css, 'grid-template-columns: 1fr;', 'Desktop traveller results should render as a vertical list of wide cards.');
assert_contains_text($custom_css, '.traveller-search-panel', 'Dark traveller search panel styles should be defined.');
assert_contains_text($custom_css, '.traveller-search-box', 'Search-box wrapper styles should be defined.');
assert_contains_text($custom_css, 'max-width: 460px;', 'Search box should be narrower than the first implementation.');
assert_contains_text($custom_css, 'background: #f36b24;', 'Search button should use the exact dashboard orange brand color.');
assert_contains_text($custom_css, 'border-color: #f36b24;', 'Search button border should use the exact dashboard orange brand color.');
assert_contains_text($custom_css, 'background: #020713;', 'Search button hover should match the dashboard primary hover treatment.');
assert_not_contains_text($custom_css, '.traveller-search-box .ti', 'Search-box icon styles should be removed with the icon.');
assert_contains_text($custom_css, '.traveller-result-card', 'Traveller result card styles should be defined.');
assert_contains_text($custom_css, 'grid-template-columns: 150px minmax(0, 1fr) auto;', 'Desktop traveller cards should use horizontal list-card columns.');
assert_contains_text($custom_css, '.traveller-card-visual', 'Desktop traveller card visual styles should be defined.');
assert_contains_text($custom_css, '.traveller-card-cta', 'Desktop traveller card CTA styles should be defined.');
assert_contains_text($custom_css, 'touch-action: manipulation;', 'Clickable traveller cards should use mobile-friendly tap handling.');
assert_contains_text($custom_css, '.traveller-result-card-clickable:active', 'Clickable traveller cards should provide tap feedback.');
assert_contains_text($custom_css, '@media (max-width: 576px)', 'Traveller cards should include a mobile breakpoint.');
assert_contains_text($custom_css, 'grid-template-columns: 54px minmax(0, 1fr);', 'Mobile traveller cards should use a compact visual-and-content top row.');
assert_contains_text($custom_css, 'grid-template-areas:', 'Mobile traveller cards should use explicit grid areas.');
assert_contains_text($custom_css, '"visual content"', 'Mobile traveller cards should place the visual beside the content.');
assert_contains_text($custom_css, '"cta cta"', 'Mobile traveller cards should place the CTA below the content.');
assert_contains_text($custom_css, 'grid-area: visual;', 'Mobile visual block should be assigned to the visual grid area.');
assert_contains_text($custom_css, 'grid-area: content;', 'Mobile content block should be assigned to the content grid area.');
assert_contains_text($custom_css, 'grid-area: cta;', 'Mobile CTA block should be assigned to the CTA grid area.');
assert_contains_text($custom_css, 'width: 54px;', 'Mobile visual block should be compact like the reference card.');
assert_contains_text($custom_css, 'height: 54px;', 'Mobile visual block should be compact like the reference card.');
assert_contains_text($custom_css, 'display: none;', 'Mobile traveller cards should hide low-priority summary text.');

fwrite(STDOUT, "PASS: traveller search results use responsive clickable cards.\n");
