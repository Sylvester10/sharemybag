# Dashboard and finance visual review

## Release calculation clarification

Gross Profit retains the previous card calculation: stored total payment minus stored traveller payout and stored VAT, separately for GBP and CAD. Parcel commission in the existing finance table remains unchanged. Finance card subtotals and dashboard booking activity use booking creation dates; their labels now state this explicitly rather than implying payment-confirmation timestamps. Regression checks include nonzero and legacy NULL VAT and a booking confirmed after its creation day. No financial records or database schema are changed by this clarification.

## Follow-up review: in-place periods and consistency

The latest user instructions supersede the earlier light-blue finance-card reference: finance now uses the exact shared dashboard stat-card component and blue background, and all admin stat cards use 9px corners. Finance summaries remain all-time; cards therefore precede the table's Month/Year/Route filters.

Both chart ranges are preloaded in the same role-filtered response. Native buttons update the existing ApexCharts instance with a 450ms transition, respect reduced-motion preference, and change neither location nor history. Browser verification switched 7 → 30 → 7 with the same chart DOM ID and URL, and confirmed 30/7 accessible daily rows respectively. The legend, recent rows, and View links all compute to 13px. All user/traveler/booking notification counts use warning yellow (#ffc107), with dark text.

Recent queries now apply a rolling seven-day cutoff and reject future timestamps before ordering/limiting each source. The regression fixture includes a 260-day-old registration and proves it is excluded from every recent list. Zero-count attention items are removed so the icon-based Nothing to show state can appear. Both empty states were rendered and inspected in the browser.

Current follow-up captures: `evidence/verification/dashboard-refresh/revision-dashboard.png` and `revision-finance.png`, using disposable test data. Earlier screenshot comparisons below document the superseded styling. Model/view and JavaScript regressions pass; no database schema changes or production writes are involved.

final result: passed

Scope: the changed production components, rendered through the real CodeIgniter view loader with disposable test data. This is component verification, not an authenticated full-page or production deployment claim.

## Visual sources

- Dashboard: `/Users/mac/.codex/generated_images/01a02a2e-950b-7463-a3dd-5b0787c72c45/exec-ebb2ff99-1d7a-47b0-9e37-3cfba4461292.png` (1385 × 1136).
- Finance: `/Users/mac/.codex/generated_images/01a02a2e-950b-7463-a3dd-5b0787c72c45/exec-f2296b0a-9050-4ce4-b95c-26d576efdb90.png` (1663 × 946).
- Implementation: `evidence/verification/dashboard-refresh/smb-dashboard-final.png`, `smb-finance-cards-final.png`, `smb-sidebar-badge.png`, `smb-dashboard-mobile.png` in that directory.
- Browser viewport: 1280 × 720 desktop; 390 × 844 mobile. Desktop screenshots are 1× pixels; dashboard capture includes the full component page. Finance capture is viewport-sized. No device frame.
- Focused, paired comparisons: `chart-comparison.png`, `activity-comparison.png`, `finance-comparison.png` in the same evidence directory. Reference and implementation content crops are normalized to equal 800-pixel widths and placed together; this compares component geometry, not browser chrome or omitted sidebar context.

## Approved differences from the images

- Preserve existing application font families, sizes and weights instead of copying generated typography.
- Smooth curved lines replace the reference's straight segments. Show the full daily series, including zero days, within rolling 7/30-calendar-day periods ending today.
- No finance cards on the dashboard. Keep existing finance Month/Year/Route filters; omit the Booking Breakdown heading and All-time badge.
- Keep the agreed Traveller Payouts label. Finance calculations remain unchanged.
- Record counts, dates and list length are data-driven; test fixtures are not live customer activity.

## Comparison iterations

1. P1: HTML/CSS bars, missing explicit activity actions, unmatched finance-card style. Replaced with bundled ApexCharts smooth lines; added list filters, details/time/action columns and explicit View links; added blue-bordered finance cards.
2. P2: last chart date label omitted by automatic datetime ticks. Changed to equally spaced daily categories with explicit first/last labels; 30-day browser capture shows 7 October through 5 November 2026, and 7-day capture shows 30 October through 5 November.
3. P1: legacy global white stat-title rule reduced contrast on finance cards. Added a scoped title-color override; computed title color is rgb(64,92,124).
4. P2: finance cards too tall compared with the reference. Tightened padding and amount line-height, preserving existing family, size and weight. Final cards measure 137.55 pixels high.
5. Final desktop: chart/Needs Attention heights both 319.80 pixels; no horizontal overflow. Finance cards have equal heights, blue borders, light blue backgrounds, rounded corners, today amounts, and readable headings.

## Required fidelity surfaces

- Typography: application Figtree/Be Vietnam Pro styles retained; ApexCharts reads computed application typography. No new text font family, size or weight definitions. Existing bold headings and icon-size utilities reused.
- Layout: equal-height desktop panels; responsive full-width mobile stacking; list columns and filter controls follow the reference. Finance filters remain intact.
- Colors/tokens: reference teal/blue/purple series, muted panel headers, blue finance borders and light blue finance backgrounds. Parent signup badge uses teal.
- Assets: existing Line Awesome icons and bundled ApexCharts; no custom chart artwork or new raster dependencies. Test preview assets are served from the existing asset tree.
- Content: no unwanted subtitles, View All button, finance dashboard cards, All-time badge or Booking Breakdown heading. Activity rows show explicit actions and relative times.

## Verification

- Users filter displayed only user events in the browser; automated checks cover switching back to All.
- Parent Users badge visible while child menu display is none.
- Mobile chart retained first/last dates and did not overflow; mobile finance cards stacked at equal widths.
- Browser console: no errors in the final component page.
- PHP view/model tests, JavaScript chart/filter regression, and syntax/whitespace checks passed.
- Actual local database dashboard queries for all three roles and GBP/CAD summaries passed in a READ ONLY transaction.

No remaining P0/P1/P2 findings within the reviewed component scope. Full authenticated page composition and production deployment remain outside this proof boundary.

---

## Earlier review (preserved)

# Search and booking-history implementation review

Date: 3 November 2026. Scope: approved search loader, mobile history/details and posting instructions, followed by the user's route/quantity cards, accordion and inset-modal refinements. Production deployment was not performed.

## Mobile search pointer fix and final action order

The latest check reproduced the user's unclickable mobile search field: the filter had zero height, its label floated, and `elementsFromPoint()` returned `.dataTables_scrollBody` above the enabled input. Clicking left focus on BODY. Earlier programmatic-fill checks proved filtering but missed this pointer obstruction. Scoped mobile filter layout now contains the floated label and resets its float/margin, keeping the scroll body below the input. After the fix, actual clicks focused the input at 390px and 320px; left/centre/right input hit tests returned the input itself, the filter height was 40px, and typed Canada filtering returned six of fourteen synthetic bookings.

The accordion heading and all three note texts compute to 12px on mobile. Card order is country route, kg/price, date/status, then Resume booking or Check payment as the final action. The incomplete badge is Not completed in PHP and AJAX rendering. Cancellation toasts keep title plus message because that matches the existing shared success/error notification format. Current evidence is `history-mobile-search-action-checks.json` and `history-mobile-final-action.png` under `evidence/history-search-implementation/`; older screenshots with the longer badge or recovery above the date are superseded.

## Earlier requested refinements

The user's latest instructions supersede the reference mockup's reference-first cards and fullscreen dialog. The notes now use a Bootstrap accordion headed “There is no refund or transfer of service to another traveler”, with a circled information icon, the app's black background and white text. The remaining three original notes use the original list styling and icons; the policy is not duplicated. Opening and closing use a 400ms eased height transition, disabled for reduced-motion preferences. Mobile cards lead with country routes (UK → Nigeria, Nigeria → UK, Nigeria → Canada, Canada → Nigeria), with a luggage icon beside purchased kg and price on the right. Below any recovery action, the footer places the date left and the single status pill right. The desktop status retains its previous position. All statuses retain the existing semantic colours and `fs-2` size. Mobile cards are consistently white. The existing desktop table and single DataTables pagination remain.

Country metadata is fetched in one query for IDs from the owner's booking list, selecting only id/location/destination and retaining archived trips. An isolated SQLite test exercises the real model/query builder, duplicate/missing IDs, empty lists, query count, archived routes and exclusion of contact fields. View tests cover country direction, UK abbreviation, legacy country fallback, withholding unavailable traveller contacts and avoiding airport labels when country metadata is unavailable. Browser checks at 390px found Canada and United Kingdom searches returning six and eight of fourteen synthetic bookings respectively; 320px cards had no horizontal overflow and completed details retained their status/invoice. New captures and measurements use the `history-country-` filenames under `evidence/history-search-implementation/`; they supersede the earlier city/airport card previews.

The relocated single status was also checked with a 20-second page-two fixture: its card and already-open modal changed from Payment not completed to Canceled, Resume disappeared, no invoice was added, and Canceled search found both matching records. The standard 15-minute preview was regenerated afterwards. Page two retained its enabled recovery action; desktop retained six columns, 12px text and status before the action. Captures: `history-country-cards-mobile.png`, `history-note-list-mobile.png`, and browser measurements `history-country-checks.json`. Final changes are self-reviewed; browser proof uses synthetic rows, and live authenticated data remains unverified.

## Country flags and modal ordering

The latest refinement adds the matching flag before each card country and copies that same route into the modal heading. Flags reuse the existing local GB/NG/CA PNG assets; scoped CSS overrides the older header CDN's flag-image declarations. Browser-computed URLs resolve to localhost and the flag dimensions are 16px. The modal body now reads Traveller Details, Agent Details, Item Details, then Payment Details. Its final section contains the existing status, date and SMB Booking ID. No provider transaction identifiers or contact permissions were added.

At 390px, the Nigeria → UK completed modal retained both route flags, its invoice, original traveller/agent/items, and the correct booking reference/date/status in the final section. Keyboard scrolling reached the full payment section. At 320px, Canada → Nigeria had no heading/body overflow, retained 16px on every edge, withheld unpaid traveller details and had no invoice. Current captures are `history-flags-modal-top.png` and `history-flags-modal-payment.png`; browser measurements are `history-flags-checks.json` under `evidence/history-search-implementation/`. These supersede earlier modal previews headed by the booking ID or beginning with status/date.

Latest accordion evidence: `history-policy-accordion-mobile.png` and `policy-accordion-checks.json` under `evidence/history-search-implementation/`. Browser checks confirmed white heading/body text against `rgb(2, 7, 19)`, intermediate `.collapsing` heights in both directions, settled hidden/visible states and keyboard activation. Password login now opts out of the shared default success alert while preserving its existing immediate redirect. The real caller options are exercised through the shared callback test for successful login, invalid password and server-directed registration recovery; authenticated live login remains unverified.

The reported search issue was reproduced for routes: `Nigeria` returned no matches because route data was absent from DataTables' search content, while item/reference searches already worked. Explicit search data now includes route, quantity, compact quantity (`5kg`), reference, price and current status. A status observer invalidates that cell's cache when expiry/AJAX changes the status, including detached rows.

Fresh local browser checks used production assets and synthetic rows: route `Abuja` and `5kg` each filtered 14 bookings to 5 on desktop and mobile; reference filtered to one; unmatched text returned zero; clearing restored pagination. With an active Canceled filter, a page-two fixture expired and automatically increased matches from one to two. The normal 15-minute fixture was restored afterwards. All ten visible mobile card cells computed white backgrounds and no inset shadow. At both 320px and 390px widths, the settled dialog left exactly 16px on every edge and had no horizontal overflow. Accordion keyboard operation, details scrolling, close/Escape and trigger focus return passed. Final changes are self-reviewed, lower assurance; no live authenticated production proof is claimed.

Current captures: `history-refined-mobile.png`, `history-refined-modal.png`, `history-refined-desktop.png`; browser measurements are in `refined-browser-checks.json`, all under `evidence/history-search-implementation/`. The older comparison captures below document the initial implementation and are superseded where these requested refinements differ.

## Visual sources and implementation evidence

- Sources: `evidence/design-preview-20261103/search-loading-mockup.png` (1672 × 941), `mobile-booking-mockup.png` (1168 × 1346), `posting-instructions-mockup.png` (1926 × 816).
- Search: `evidence/history-search-implementation/search-loading.png`, 1280 × 720 CSS/pixels, loading state; `comparison-search.png` contains the normalized source and actual capture together.
- Mobile cards: `history-mobile.png`, a 390px-wide full-page capture; the actual viewport was 390 × 844. `comparison-mobile.png` compares the source's left panel and the implemented list excerpt at 390px width.
- Details: `history-details-mobile-top.png`, 390 × 844, completed booking at the top; `history-details-mobile.png` additionally proves the total is reachable by keyboard scrolling. `comparison-details.png` contains the normalized right source panel and the actual top state together.
- Notes: `history-desktop.png`, 1280 × 720; `comparison-notes.png` compares the source component and the actual component crop. The component reference is enlarged artwork, not a desktop viewport: the implementation retains the app's natural 14px desktop typography instead of scaling the whole raster to full page width.
- `browser-checks.json` contains browser-derived dimensions, states, colours and console results. All implementation screenshots come directly from the in-app browser. Comparison images only align/crop the source and unretouched captures for inspection.

The current PHP views and production assets were rendered with synthetic owner-scoped rows and a recorded AJAX preview adapter. This is UI runtime evidence, not an authenticated production-data or payment-provider test.

## Required surfaces

- Typography: original application family retained. Table and mobile card text use the user's latest `fs-2` requirement (12px), overriding the earlier mockup's larger card type. The details body uses 14px and its item table stays 12px. Existing heading tokens provide hierarchy.
- Layout: existing search header, route fields, desktop columns and DataTables state retained. Cards reuse the same rows below 768px. The dialog has 16px mobile edge spacing, a fixed close header/invoice footer, and an independently scrollable, keyboard-focusable body. No horizontal overflow was observed at 320px or 390px, or at 1280px desktop.
- Colours: existing navy/orange palette, white cards and subtle status backgrounds retained. Canceled uses the established red text class. The notes card's orange left border is removed as explicitly requested; its parcel icon remains orange.
- Assets: the user explicitly requested a newly created non-Lottie book matching the concept. The implementation uses local SVG geometry with CSS page turns, crisp at device scale. It is not a raster substitution or external animation. Matching animation frame contours is not meaningful across moving frames; navy outline, orange cover, off-white pages, scale, centre alignment and caption are preserved. Reduced-motion CSS supplies a still frame; OS preference toggling was not exercised.
- Content: all four posting instructions remain. The modal retains both permitted drop-offs, date, existing business support contact, agent details, item units/prices and stored total. These extra existing fields, absent from the simplified concept, intentionally remain and make the modal scroll. No unavailable traveller details or invoice actions are added to unpaid bookings.

## Comparison and correction history

1. Initial capture exposed missing dependencies in the isolated preview. Its script list was aligned with the existing footer (pricing and page helpers). Fresh browser logs are now empty; no production dependency was removed or replaced.
2. [P2, fixed] Disabled Search inherited the theme's blue colour. The scoped disabled style now retains orange; browser-computed colour is `rgb(243, 107, 36)`.
3. [P2, fixed] DataTables' odd-row inset shadow tinted white cards, and its content-box cells extended outside card borders. Scoped mobile shadow/box-sizing fixes now keep cards white and cells inside the border: a 280px row contains a 278px cell at 390px viewport width.
4. [P1, fixed, independent review] Detached page-two recovery controls were not initialized. All DataTables row nodes now initialize once, with a guarded draw hook. Browser page-two controls are enabled; automated redraw checks prove timers are not duplicated or controls reactivated.
5. [P2, fixed] Scrollable details needed a keyboard focus target. The named region is now focusable. PageDown reached the stored total while the invoice action stayed fixed.
6. Final combined comparisons were opened after these corrections. Remaining differences are intentional user overrides (notes border, `fs-2` table/cards), existing controls/content retained from the app (search/pagination, both drop-offs, business support contact), or moving animation frames. No actionable P0/P1/P2 findings remain.

## Interaction proof

- Search: native book is running while the recorded request is pending, then hidden/paused with Search re-enabled on results. Automated adapters also cover empty results, failures and duplicate submits without a production loading delay.
- History: filtering and page-two pagination work on the same DataTable; the later pending booking's recovery control is enabled. Clicking Resume sets its own busy state without opening details, then recovers after the recorded response.
- Details: completed and unpaid bookings open in the same modal with the original permission-dependent data/invoice availability. Escape/Close return focus to the triggering booking and retain list position. Keyboard body scrolling exposes all items and the total.
- Expiry: a dedicated 20-second synthetic page-two fixture changed its card and already-open modal to Canceled, removed the recovery button and kept invoice unavailable. Production remains at 15 minutes. The regular preview was regenerated afterwards.
- Responsive transition: 320px cards and modal showed no overflow; returning to 1280px closed the mobile modal and restored the six-column table.
- Fresh history, search and desktop browser logs contain no JavaScript errors. Independent code review and focused tests passed after the detached-row fix.

## Limits and follow-up

Live authenticated account data, physical-device touch, screen-reader operation and OS reduced-motion switching remain unverified. No SQL, payment, mail or production activation was performed for this UI update. The preview's request adapter and optional shortened expiry exist only in generated synthetic fixtures.

final result: passed
