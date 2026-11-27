# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Inventory & item-lending app. Laravel 12 + Inertia v2 + Vue 3 (TypeScript) + Naive UI + Tailwind 3, MySQL. UI copy is Indonesian.

Originally built for one school (per "jurusan": TJKT, TE, TO, AK, TU, KKPI); it is being generalized into a **multi-organization SaaS for any sector**. Product overview, terminology and feature roadmap: **`docs/PRODUCT.md`**. In new code use the generic terms from `docs/ARCHITECTURE.md` §3.1 (`Organization`, `Unit`, `Uom`, `Category`, `responsible_name`, `usage_location`), never `major`/jurusan/ruangan/pengawas; UI labels come from organization terminology (`useTerms()`), not hardcoded.

## Commands

```bash
composer dev            # serve + queue:listen + pail logs + vite, all at once
npm run dev             # vite only
npm run build           # production assets -> public/build
npx vue-tsc --noEmit    # type-check (repo has pre-existing errors; filter output to the files you touched)
php artisan test                                   # all tests
php artisan test --filter=TenancyTest              # single test class / method
php artisan migrate:fresh --seed --drop-views       # rebuild schema + demo data (daily_borrowers is a VIEW)
```

- Tests use `.env.testing` → MySQL database `inventory_db_test` with `RefreshDatabase` (the sqlite lines in `phpunit.xml` are commented out). Never point tests at `inventory_db`.
- Seed accounts: `internal_developer` (platform admin), `superadmin` (owner of demo org "SMK Demo"), `admin_tjkt|ak|te|to|tu|kkpi` (unit admins). Passwords in `database/seeders/*`.
- To verify a frontend build without touching `public/build`, use `npx vite build --outDir <scratch dir> --emptyOutDir`.
- Deployment: `Dockerfile` (node build stage → php-fpm + nginx + supervisor, configs in `docker/`).

## Multi-tenancy (schema rewritten on branch `refactor/saas-foundation`)

- Tenant = `organizations`. Every business table has `organization_id`; owner unit = `unit_id` (table `units`, was `major`). Renamed tables: `major`→`units`, `unit_products`→`uoms` (satuan), `sub_type_products`→`categories`, `warehouse`→`warehouses`, `borrower`→`borrowers` (`room_name`→`usage_location`, `supervisor_name`→`responsible_name`), `transaction`→`transactions`, `request_item`→`request_items` (`brand_name`→`item_name`). `product_logs`, `transaction_lines` keep their names. Schema lives in the `2026_12_01_*` migrations; old migrations were removed (clean start, no data kept).
- Access model replaces `roles`/`users.major_id`: `users.is_platform_admin` (SaaS owner), `users.organization_role` owner|admin|member, and `unit_members(unit_id, user_id, role admin|staff|viewer)`. `User::accessCode()` maps to the legacy codes the frontend menu uses (`DEVELOPER` / `SUPER` / `ADMIN`).
- Context: middleware `ResolveOrganization` (web group) sets the scoped singleton `OrganizationContext` from the logged-in user, rejects users without an organization (403) and renders `Errors/OrganizationInactive` for suspended/cancelled orgs. Platform admins get no tenant context.
- Isolation: Eloquent models use trait `BelongsToOrganization` (global scope + auto-fill `organization_id`). **Query-builder code must call `OrgScope::apply($q, 'alias')`** and `OrgScope::units($q, 'alias.unit_id', $unitId)` (unit must be in `CurrentOrganization::unitIds()`); `OrgScope` throws without context so unscoped queries fail loudly. Code in `app/Domain/Organization`.
- Shared props: `organization` (incl. `terms` from template + `settings.terms`), `units` (accessible units). Frontend: `useTerms().t('unit')`.
- **Status on this branch:** the whole app is ported to the domain structure on the new schema; the legacy layer (Services/Repositories/Dto, old controllers, v1 `Items*`, old pages) is deleted. Tests: `tests/Feature/{Organization,Platform,Domain}` with helper trait `tests/Feature/Concerns/InteractsWithTenant`.
- Authorization helper `App\Domain\Organization\Support\Access` (`canView/canOperate/canManage`, `managesOrganization`, `authorize*`, `manageableUnitIds`, `operableUnitIds`). Unit roles: admin = manage products/stock/master data/requests, staff = borrow & return, viewer = read only. Org owner/admin manage everything.

## Backend architecture

Architecture doc: **`docs/ARCHITECTURE.md`** (modular monolith, CQRS-lite). Request flow: **thin Controller (`app/Http/Controllers/<Domain>`) → FormRequest (`app/Http/Requests/<Domain>`) → Action (write) / Query (read) in `app/Domain/<Domain>`**.

- Domains: `Organization` (tenant, units, access), `Platform` (SaaS billing), `MasterData` (units/warehouses/categories/uoms via one `MasterDataType` enum + `master-data.*` routes with `{type}`), `Inventory` (products, `StockLedger`), `Borrowing` (borrow/return, `LoanStatusQuery`), `Procurement` (request items), `Identity` (organization members), `Reporting` (dashboards, Excel exports).
- Routes per domain in `routes/{master-data,inventory,borrowing,procurement,members,platform}.php`, required from `web.php`. Main names: `products.*` (+`restock`, `write-off`, `stock` JSON, `export`), `borrowing.*` (+`return`, `return.store`), `movements.{loaned,consumed,restocks,write-offs}`, `procurement.*` (+`process`, `export`), `members.*`, `master-data.*`, `platform.*`.
- FormRequests expose their DTO via `payload()` (not `data()`, which clashes with Laravel 12's `Request::data()`).
- Platform area (SaaS owner, `DEVELOPER`): routes behind the `platform` middleware (`EnsurePlatformAdmin`), pages in `Pages/Platform`. Billing rule violations throw `BillingException` (renders as redirect back with `errors.billing` + `flash.failed`). Money is integer Rupiah. Creating an organization also creates a default unit; `platform.organizations.owner` creates the owner account and flashes `generated_password` once.
- `DashboardController`: platform admin → `platform.dashboard`; org owner/admin → `DashboardOrganization`; others → `Dashboard` (operational, scoped to their units).

### Stock ledger (core concept)

Stock is **never stored as a column**; it is derived from `product_logs`, and **`App\Domain\Inventory\Ledger\StockLedger` is the only writer**:

- Each row: `product_id`, `warehouse_id`, `condition` (`GOOD|DAMAGED|LOST|OUT|OFF`), `move_type` (`OPENING|RESTOCK|BORROW_OUT|RETURN|WRITE_OFF|ADJUSTMENT_POS|ADJUSTMENT_NEG`), `quantity`, signed `delta`, `ref_type`/`ref_id`, `reason`, `created_by`, `occurred_at`.
- On-hand for a bucket = `SUM(delta)` per product/warehouse/condition. `OFF` = written off / retired ("Barang Dihapuskan" in the UI), excluded from stock. Write-off moves stock into `OFF` (two rows); editing a product's stock reconciles with adjustment rows. Insufficient stock throws `InsufficientStock`.
- Borrowing: `transactions` (`tx_type` BORROW/RETURN, `parent_borrow_tx_id`, numbers `PJM-`/`KMB-` via `TransactionNumber`) + `transaction_lines`; a return line points to its borrow line via `borrow_line_id`. Actions `BorrowItems` / `ReturnItems` write the ledger inside `DB::transaction`.
- **`transactions.status` is always `POSTED`.** Real status comes from `LoanStatusQuery::subquery()` (borrowed vs returned per borrow line, DURABLE only) → `computed_status` = `POSTED` / `PARTIAL_RETURNED` / `COMPLETED`. `CONSUMABLE` products are never returned.

## Frontend architecture

- Pages: `resources/js/Pages/<Domain>/*.vue`, rendered by controllers via `Inertia::render('<Domain>/<Page>')`. Layout set per page with `defineOptions({ layout: AuthenticatedLayout })`.
- Sidebar menu per role: `resources/js/Utils/menu-config.ts`. Naive UI theme overrides: `resources/js/Utils/global-theme-config.ts`.
- Shared Inertia props (`HandleInertiaRequests`): `auth.user` (`role {code,name}`, `organization_role`, `is_platform_admin`, `profile_image_url` nullable), `organization` (+`terms`), `units`, `flash.success|failed|generated_password`, `errors`.
- Server flash is shown per page via `Composables/useFlash.ts` (not globally). Terminology labels via `Composables/useTerms.ts`.
- Menu is built by `menuFor(accessCode, terms)` in `Utils/menu-config.ts`.

### Gotchas

- **`route()` (Ziggy) is only available in `<script>`**, injected globally by `@routes` in the Blade view. `ZiggyVue` is intentionally disabled in `app.ts`, so never call `route()` inside `<template>`; compute the URL / wrap navigation in a script function.
- `<script setup>` files must not `export` types. Put shared types in a sibling `.ts` file (e.g. `Components/Feature/Products/product-form.ts`).
- `@tailwindcss/forms` styles every `<input>`; `resources/css/app.css` resets Naive UI's internal inputs (`.n-base-selection-input`, `.n-input__input-el`, …). Keep that reset when adding Naive inputs.
- `RefreshButton` defaults to `size="medium"` to match default input height.

### Reusable building blocks (prefer these over ad-hoc markup)

- `Components/DataTable/DataTable.vue`: headless TanStack Table (`@tanstack/vue-table`) wrapper. Columns via `createColumnHelper`; per-column `meta` (`align`, `width`, `minWidth`, `sticky`, `class`), declared in `DataTable/types.ts`. Slots: `toolbar`, `mobile-row` (card list below `lg`), `empty`. Takes a Laravel paginator object as `pagination` and emits `page-change` / `row-click`.
- `Composables/useTableFilters.ts`: URL-query-backed server filters with debounced text keys; `apply(page)` does the Inertia `router.get`.
- `Components/FormSection.vue` (numbered form card), `ExportExcelButton.vue`, `TitlePage.vue` (`eyebrow`, `title`, `subTitle`, `#action` slot).
- Feature components under `Components/Feature/<Domain>/` (e.g. `ProductForm`, `UserForm` (members), `MasterDataPage` for all master-data types, status badges).

### UI conventions

Brand `#2A126C` (accent `#F5D90A`), 4px radius (`rounded`), `border-slate-200` cards with no shadows, small uppercase "eyebrow" labels, `tabular-nums` for numbers. Responsive first: desktop table → mobile card list, sticky bottom action bar on mobile forms. Status colors: amber = pending/borrowed, emerald = done/good, rose = rejected/damaged, sky = partial.
