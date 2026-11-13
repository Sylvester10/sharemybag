# Traveller payouts and dashboard — Phases 1–3

The payout ledger records settlement declarations, not bank transfers. It never calls a payment provider or sends email. Phase 1 created the foundation; Phase 2 adds the Arrivals payout workflow and finance summary. Phase 3 simplifies the dashboard.

## Data contract

- `traveller_payouts` is append-only settlement history: traveller/name snapshot, currency, total, source, actor, actual payment date when known, recording date, and explanation. `operation_key` is unique for retry protection.
- `traveller_payout_items` allocates each payout to specific booking IDs, booking references, traveller IDs, currencies and stored commission amounts. Header and allocations commit together. Operational record deletion cannot cascade into this history.
- `traveller_payout_opening_balances` holds one locked cutover record with the exact paid, unpaid and review booking IDs, cutoff date and maximum booking ID. Its status changes from `pending` to `applied` in the same transaction as the history and allocations. Replays do not classify new records again.

Amounts use existing `bookings.traveller_commission`, not current pricing. PHP calculations use integer cents. GBP and CAD remain separate, including legacy currency spellings. Active completed bookings use the same supported payment methods as finance, including offline/bank bookings. Canceled, pending and deleted bookings do not accrue outstanding liability.

Outstanding liability is calculated per booking for its **current traveller and currency** as `max(stored commission - matching payout allocations, 0)`. A later booking or increase remains owed. Cancellation does not delete history. Reassignment does not transfer a previous traveller's settlement. An over-settlement never offsets another traveller's liability.

## Opening declaration: 13 November 2026

The owner declared historical travellers paid except upcoming travellers and the traveller arriving that day. Only completed bookings created by that date, with valid departure and arrival dates both **before 2026-11-13**, are initialized as paid. Arrivals on or after that date and upcoming travellers stay unpaid. Missing/invalid/inconsistent dates, unsupported currencies or missing/archived travellers require review. Known-currency review amounts remain outstanding.

Historical actual payment dates and staff identities are unknown: `paid_at` and `admin_id` remain NULL. The history explicitly attributes these entries to the owner's opening declaration. UTC `recorded_at` is when the ledger was created, not an invented bank payment date.

An explicit historical traveller declaration can resolve missing arrival information when the departure date is valid and historical. These traveller IDs are retained in the opening audit. They cannot override upcoming, cutoff-day or future arrival exclusions, missing travellers, invalid departure dates, or unsupported amounts/currencies. The local 2025 travellers 122 and 145 have no arrival date and are included under the owner's confirmation that historical travellers were already paid. These local IDs must not be assumed to identify the same records on production without checking.

The first automatic initialization is allowed only on or before the agreed cutover day (Africa/Lagos). A **delayed first production deployment requires a reviewed historical booking snapshot**: today's completed status cannot establish when an older pending booking was paid. Applying the schema alone does not mark any traveler paid. Already-applied opening records remain safe to replay later.

## Local setup

`php scripts/setup_traveller_payouts.php --inspect` reads the development schema state. `--preview` reads and lists the proposed historical settlement and exceptions. `--apply` installs additive migration 028, records the opening once, and checks that all booking/traveller source rows are unchanged. The script rejects non-development/remote database configuration and is not a production deployment tool.

An optional `--paid-travellers=122,145` records the explicit historical declarations described above. Pass the same list to preview and apply. No operational dates are filled in or corrected by this operation.

Migration rollback intentionally refuses to drop financial history. Backups and any corrections must retain the original ledger.

## Phase 2 — manual settlement and finance

Super Admin can open **Traveller Payout** from an Arrivals action menu or Arrival Traveller profile. Other roles receive neither the controls nor payout status, and direct requests are rejected. Both endpoints require POST and the existing application CSRF protection. The model also rechecks the actor's stored Super Admin role under a database lock.

The modal displays separate outstanding GBP/CAD amounts, payout history and a required payment confirmation. Its submit button remains disabled while loading, ineligible, unchecked or saving. Field errors are inline and clear on editing. The action reloads only after a successful save.

Eligibility requires a valid departure date before today, a valid arrival date on/before today and not before departure, an active traveller, active completed bookings, and shipping records for **every** active completed booking. Upcoming, missing/invalid-date and incomplete-shipping travellers cannot be newly settled. Zero outstanding is already settled and creates no additional record.

On confirmation the transaction locks actor, shipping, bookings, traveller and allocations; rechecks current eligibility and stored commission; verifies the reviewed snapshot; and appends one batch per currency with exact booking allocations. Client amounts are ignored. Request identity and re-read allocations prevent double settlement across retries or competing Super Admins. A repeated old request cannot settle later bookings. All batches and allocations commit or roll back together. Existing booking and traveller data are never updated by this workflow.

The recorded date is the time Super Admin confirmed payment. The modal does not collect a bank transfer timestamp, so `paid_at` remains NULL rather than inventing one; payout history displays the factual recording date.

Paid/Unpaid status on Arrivals is calculated from current booking-level allocations in bulk. It is separate from red/green shipping completeness. A later booking or commission increase makes the traveller outstanding again.

Each finance currency page retains Total Revenue, Gross Profit and Traveller Payouts, and adds **Outstanding Traveller Payouts** using the shared stat tiles. This all-time summary remains above the existing Month/Year/Route table filters. Until the opening balance is applied the outstanding card shows N/A and setup guidance. **KG Offered** comes from the joined traveller's `original_bag_space`, immediately before purchased Total KG; it is not the remaining space and is not summed across booking rows. Typography, card styling, existing financial formulas and table filters are retained.

## Phase 3 — dashboard simplification

The dashboard activity-view call is commented out, preserving the graph/Recent Activity implementation for restoration. The page requests summary-only overview data, skipping the hidden activity queries and chart assets. Existing role-scoped stat cards remain first. Messaging occupies the existing eight-column space beside the four-column Needs Attention panel; both stack on mobile. Mail tabs, intent token, form actions, role restrictions, typography and panel styling are retained. This phase requires no database update.

## Production SQL

For a manually maintained live database still recording migration 23 even though 024–027 were pasted previously, use `database/027_reconcile_manual_migration_record.sql` first. It explicitly targets `u543551821_sharemybag` and checks the canonical 54 required columns (types, lengths/precision, nullability, unsigned and auto-increment properties), 16 index definitions and five InnoDB tables. It advances only a single version row from 23 to 27. No user preferences or business records are changed. If any check fails, leave the version unchanged and review the result; do not blindly set it to 27. Once the result is version 27 with all four check flags equal to 1, rerun the payout deployment in the application database. The correction is harmless to an already-27/28 record and never downgrades other versions.

`database/028_traveller_payouts_production.sql` is the standalone phpMyAdmin deployment script. It includes schema 028 and an atomic/idempotent historical opening declaration, preserving operational rows. It excludes the specifically identified **Vitalis Onyekachi** by normalized name (including surname-first order), upcoming travellers and other on/after-cutoff arrivals. It requires that the named exception exist rather than guessing a local ID. Blank historical arrival dates may use the owner's explicit declaration; invalid dates remain review items. Actual historical bank dates and staff IDs remain unknown.

The script uses a task-specific deployment procedure with rollback/resignal handling, then drops the procedure. The first initialization after 13 November 2026 fails closed and leaves the schema pending until a reviewed historical snapshot is prepared. Applied openings replay safely on later dates. Do not change the cutoff or bypass that check. The user operates live deployment manually; the corrected revision has been verified only against isolated local fixtures.

The deployment compares generated operation keys as binary identifiers when linking headers to allocations. This avoids MySQL error 1267 when existing ledger columns use `utf8mb4_general_ci` but the database/routine/temporary snapshot inherits `utf8mb4_unicode_ci`. No existing table or database collation is changed. Vitalis's name exclusion is unchanged and was not the failing comparison.

Any future reversal, overpayment recovery or reassignment of already-settled bookings needs an explicit accounting adjustment workflow; it must not rewrite history.

## Verification

- `php tests/traveller_payout_foundation_test.php`: isolated SQLite memory.
- `SMB_PAYOUT_MYSQL=1 php tests/traveller_payout_foundation_test.php`: real migration replay and model transactions in separately created, randomly named MySQL fixture databases, removed afterward.
- `php tests/traveller_payout_workflow_test.php`: actual model/controller eligibility, allocations, permissions, stale confirmations, retries and rollback. `SMB_PAYOUT_MYSQL=1` exercises it in disposable MySQL fixture databases.
- `php tests/traveller_payout_concurrency_test.php`: overlapping opening initialization and manual payout confirmations by different Super Admins in a separately created MySQL test database, removed afterward.
- `node tests/traveller_payout_modal_test.js`: real jQuery/Bootstrap modal and validation in headless Chrome, with fixture responses only.
- `php tests/finance_payout_ui_test.php`: actual finance query/JSON/headings, sorting, original capacity, summary data and opening readiness.
- `php tests/traveller_payout_production_sql_test.php`: standalone deployment SQL against disposable databases, including replay, exclusions, date guard and atomic rollback.
- `php tests/manual_migration_record_test.php`: guarded manual migration metadata repair in a separately created/deleted database, including phpMyAdmin's information_schema context, incomplete schemas, wrong precision/indexes/engines and version boundaries; operational rows remain unchanged.
- Local setup writes ignored `evidence/verification/traveller-payouts/migration-state.json` with migration hashes, schema fingerprint, unchanged-source fingerprint, opening counts and currency-separated liability totals.

An earlier MySQL failure fixture dropped a temporary table, revealing the real local table of the same name. Four identified FIX allocation rows were removed and source hashes/history counts verified against the Phase 1 baseline. Tests now use separate databases; table shadowing is not used for payout verification.

The local XAMPP server's stored-procedure catalog has an existing MariaDB version mismatch (`mysql.proc` expected 21 columns, found 20). It was not upgraded or repaired. Production SQL was verified on a fresh, socket-only MariaDB 10.4.28 instance under `/private/tmp`, then that instance was shut down. The test supports `SMB_PAYOUT_SQL_SOCKET=/private/tmp/smb-payout-sql.XXXXXX/mysql.sock` and checks that the server owns the corresponding isolated data directory. The normal application payout workflow uses direct transactions and does not require stored procedures.
