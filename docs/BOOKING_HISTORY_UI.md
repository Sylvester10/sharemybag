# Search loading and responsive booking history

Approved on 3 November 2026 from the search, mobile history/details and posting-instructions mockups in `evidence/design-preview-20261103/`. The user requested a locally drawn, animated book without Lottie, the notes panel without its orange left border, and `fs-2` throughout the history table.

## Search

The existing route form, validation, customer verification gates and traveller result cards remain in place. `#search-spinner` contains a local SVG open book whose pages turn with CSS transforms. The caption is **Searching for travellers…**. The existing AJAX lifecycle starts the animation and pauses/hides it on success, empty results or failure. Concurrent submissions are ignored while a search is running. Reduced-motion CSS provides a still book. There is no loading delay in production, animation file, player dependency or external animation request.

## History

Desktop retains the six-column DataTable with `fs-2` text, the established status colours, invoice availability and booking recovery checks. Statuses use rounded pills on desktop, mobile and details. Below 768px, the same table rows are displayed as consistently white cards led by the same area, city/state route as traveller search, such as Gatwick, Greater London → Oshodi, Lagos. Public route metadata is fetched in one batch for traveller IDs from the owner's existing booking list, including archived trips. Existing country labels remain the fallback for older routes without area/city data; otherwise the title is Route unavailable. No traveller contact information is fetched by this lookup.

Origin and destination occupy two equal columns, separated by a single central arrow. Each country's flag sits to the left of its location; long location names wrap within their own column. The right chevron is removed, while the whole card and its accessible route button still open details. Status pills explicitly use fs-2 (12px), matching the other card text and retaining their established colours. Flags use the existing local `countryflags/dist/flat16` assets, with scoped CSS to keep these routes independent of the header's older flag CDN override. Missing country metadata does not invent a flag. Purchased kg has a luggage icon and sits opposite the stored price. The next row puts the calendar/date on the left and the status on the right. Resume booking (or Check payment where applicable) is the final action underneath. Incomplete checkouts use the shorter **Not completed** badge on initial render and AJAX updates. The single status element remains above recovery controls on desktop and supplies the details modal, so expiry/AJAX updates have one source. Piece-priced items retain their unit (for example, `2 kg · 1 pc`); legacy records without item JSON use stored selected space. DataTables still owns filtering, ordering and pagination, so there is no second set of records or competing pagination.

Route and quantity are included in the payment cell's explicit search data, alongside its reference, price and current status. Both UK and United Kingdom, and compact quantities such as `5kg`, are searchable. Status mutations invalidate that cell's DataTables cache, including detached pages, and reapply any active filter. This fixes route searches returning no matches and status searches becoming stale after expiry; existing item/agent/date filtering remains intact.

The mobile search filter contains its floated theme label in a formatting context, with the label's float/margin reset locally. Previously the filter collapsed to zero height and the relatively positioned DataTables scroll body covered the input, preventing pointer focus even though programmatic filtering worked. Browser verification now includes real input clicks and hit targets as well as typed searches; desktop styling remains unchanged.

One Bootstrap modal displays copies of the selected row's existing permitted cells. Its header clones the same area/city route and flags as the card. The body sections are Traveller Details, Agent Details, Item Details, then Payment Details. Status, date and Booking ID (the existing SMB booking reference) are together in the final section; provider transaction identifiers are not added. On mobile it leaves 16px around every edge, with a scrollable body and fixed close/header and invoice footer. It preserves both drop-offs, the original contact details, all item units and prices, and the total. Traveller details withheld for unpaid bookings remain withheld; only completed bookings retain an invoice link. Customer-entered text is escaped before rendering. Opening details makes no API request and changes no booking state. Status changes are observed from the original row while the modal is open. Bootstrap provides dialog focus, Escape/close and focus return; closing retains the list position. If the modal script/Bootstrap is unavailable, mobile retains the original horizontally scrollable table.

Recovery actions are initialized across all DataTables row nodes, including detached pages, and guarded against duplicate initialization. Drawing a new page cannot restart a deadline or re-enable an expired/busy action. The 15-minute status-only expiry remains unchanged: no Find space replacement; verified unpaid recovery displays Canceled, while uncertain payments remain Checking payment.

## Notes

The initially collapsed accordion is headed **There is no refund or transfer of service to another traveler**, replacing Posting instructions. A circled information icon and white text sit on the app's `#020713` black background. The original parcel-label, return-address and Royal Mail/Evri instructions remain inside as a semantic list, restoring the original list-item icons, hanging alignment and spacing; the refund policy is not duplicated in the body. The existing Bootstrap collapse component animates height in both directions over 400ms with eased motion, maintains `aria-expanded` and supports pointer, Enter and Space. Reduced-motion preferences disable the transitions. There is no orange left border; the keyboard focus outline remains visible.

The accordion heading and note text are `fs-2` (12px) on mobile; the desktop heading retains its previous 14px size. Cancellation notifications retain the shared app convention of a toast title plus message, also used by the common success/error flash helpers and AJAX notifications.

## Local verification and preview

```sh
php scripts/verify_booking_checkout.php
php scripts/preview_booking_history.php
```

The preview renders the current PHP views and production scripts with synthetic records under `evidence/history-search-implementation/`. Its recorded AJAX adapter does not contact application APIs, payment providers or SMTP. Its 1.8-second response time is only a preview fixture for observing the loader; production has no artificial delay. `--expiry-demo` gives one page-two fixture a 20-second deadline for observing expiry and modal status synchronization; the production deadline remains 15 minutes.

Browser evidence and visual QA are local UI proof using these synthetic records. They do not prove authenticated production data, live payment processing, email delivery or physical-device behaviour. This UI update requires no additional SQL. Deploy the changed views, CSS, search/booking scripts, `booking-history.js` and footer together; the existing file modification cache parameters cover the changed assets.
