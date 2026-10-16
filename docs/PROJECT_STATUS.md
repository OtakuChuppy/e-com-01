# Project Status — Bangladesh E-commerce (e-com)

**Workspace:** `c:\Users\PC\Desktop\e-com`
**Updated:** 2026-10-17 (Stage 0 COMPLETE — Laravel 13.37.0 live on PHP 8.3.33)
**Stack:** Laravel 13.37.0 + PHP 8.3.33 + SQLite (local dev) / MySQL 8 (prod target) + Blade + Tailwind v4 + Vite + Alpine.js (light)

## Environment (verified 2026-10-17)
- [x] Workspace scaffolded — Laravel 13 + docs/, git initialized
- [x] OS: Windows 10 Pro 64-bit
- [x] Node v24.20.0, npm 11.19.0 — OK
- [x] PHP 8.3.33 (winget, php.ini configured: curl/fileinfo/gd/mbstring/openssl/pdo_mysql/pdo_sqlite/zip) — OK
- [x] Composer 2.10.3 (`C:\php-tools\composer.bat`, PATH persisted) — OK
- [x] `php artisan --version` → Laravel Framework 13.37.0 — OK
- [x] SQLite migrated (users/cache/jobs tables), app key set — OK
- [ ] MySQL — NOT INSTALLED (SQLite interim; MySQL 8 required before production)

## Checklist
### Stage 0 — Inspect & Plan
- [x] Inspect workspace, OS, Node, PHP, Composer, DB, Git
- [x] Install PHP 8.3 + Composer via winget
- [x] `git init` + docs skeletons (this commit)
- [x] Scaffold Laravel 13 project (latest stable; v11 skeleton blocked by advisories — secure default)
- [x] Verify `php -v` (8.3.33), `composer --version` (2.10.3), `php artisan --version` (13.37.0)

### Stage 1 — Storefront (demo-ready)
- [x] Layout, design tokens (#FBFAF7, #344B3F, #A96E52, #242821, #77796F), Blade components
- [x] Homepage: announcement, navbar, hero, categories, featured, product cards, footer, mobile nav
- [x] Product listing (search/sort/filter/pagination 10pp), detail (gallery, variants, qty, add-to-cart)
- [x] Cart drawer/page + checkout flow (live, tested)
- [x] Demo seed data clearly marked (20 products, "(Demo)" suffix, `is_demo` flag)

### Stage 2 — DB & Product Management
- [x] Migrations: categories, products, variants, images, customers, addresses, orders, order_items, order_status_history, stock_movements, settings, users/audit
- [x] Transactions + row-locking, no negative stock, preserve order history snapshots
- [ ] CSV import template + validation (Stage 5 with admin)

### Stage 3 — Listing & Performance
- [ ] 10pp pagination preserving filters, DB-level search/sort, eager loads, indexes, image lazy-load, selective cache

### Stage 4 — Cart/Checkout/Customer
- [x] Persistent guest cart (session+cookie), server revalidation, BD checkout (name/phone/address/district), COD, delivery rules (Dhaka in/out configurable), unique order ref, anti-double-submit, lookup via phone+reference

### Stage 5 — Admin Panel
- [x] Dashboard counts (total/new/on-hold/confirmed/delivered/cancelled + 7/30-day sales + recent orders)
- [x] Paginated order list (search/filter/15pp) + detail + valid-transition status updates with confirm + history + audit
- [x] Product CRUD + publish/archive + delete-blocked-if-in-orders + search/filter + low-stock flags
- [x] Inventory adjustments (transactional, reasoned, never negative) + movement history
- [x] Settings (delivery charges, Pixel/GTM/GA4 validated IDs, robots mode) + courier mock (not connected)
- [x] Users & roles (owner/admin/order_manager) + last-owner protection + audit log
- [x] SEO: product/category meta fields, sitemap.xml (published only), robots.txt (staging/live), admin noindex

### Stage 6 — Auth & Security
- [x] Staff login/logout (session guard, bcrypt hashing, session regeneration), CSRF, validation, rate limiting (5/email+IP + throttle:20,1), secure sessions, role middleware on every admin route
- [x] Password reset via broker (identical responses, log-mailer behavior documented); email verification deferred until SMTP configured
- [x] File-upload endpoint not built yet (rules documented); .env.example placeholders only; prod hardening in DEPLOYMENT.md; docs/SECURITY.md complete

### Stage 7 — Testing & Quality
- [x] 18 tests green (StorefrontTest 7 + AdminTest 9 + Example 2): homepage, listing, pagination, detail, auth, RBAC boundaries, CRUD guards, cart/checkout, totals, delivery, stock, status transitions, audit, SEO/robots, health secrecy, login throttle
- [x] `npm run build` clean, `./vendor/bin/pint` clean, `composer audit` no advisories, live HTTP checks incl. /admin/login 200, /health ok, /sitemap.xml, guest /admin → login redirect

### Stage 8 — Deployment Prep
- [ ] docs/DEPLOYMENT.md full, prod checklist, backup/restore, smoke tests

### Stage 9 — Handover
- [ ] docs/SETUP.md + ADMIN_GUIDE.md complete, first-owner creation, release checklist

## Current focus
- [x] Stage 1 COMPLETE: homepage, listing (10pp + search/filter/sort), detail (gallery/variants/qty), cart drawer+page, BD checkout (COD, Dhaka 60/outside 120), order success + lookup, policies — all live at http://127.0.0.1:8000 with 20 demo products
- [x] Stage 2 COMPLETE: schema (categories/products/variants/images/customers/addresses/carts/orders/history/stock/settings/audit) + DemoSeeder + transactions + row locks + snapshot order items
- [x] Stage 3 PARTIAL: 10pp pagination w/ query-string, DB-level filter/sort, eager loads, lazy images — indexes in migrations; cache rules + MySQL FULLTEXT pending prod
- [x] Stage 4 COMPLETE: session+cookie guest cart, server revalidation, BD phone/district validation, COD, configurable delivery, non-guessable refs, idempotency, token-gated success, phone+ref lookup
- NEXT: Stage 5 admin panel + Stage 6 auth (login/register/roles/policies) + Stage 7 more tests + Stage 8/9 docs
- No action needed from user. Non-blocking questions in docs/DECISIONS.md (brand name, MySQL choice, hosting).

## Test log (all actually run 2026-10-17)
- `php artisan test` → 18 passed (7 StorefrontTest + 9 AdminTest + 2 Example)
- `npm run build` → built in 1.22s, 105KB JS / 51KB CSS (pass)
- `./vendor/bin/pint` → formatted, clean (pass)
- Live HTTP: `/health` 200 (secrets-free), `/sitemap.xml` 200, `/robots.txt` 200, guest `/admin` → 302 login, `/admin/login` 200
- `php artisan migrate:fresh --seed` → 20 demo products + 6 categories + variant example (pass)

## Independent audit (Stages 5–6) — 2026-10-17
- Full battery re-run (test + pint + build + composer audit + live HTTP + security probes). See `docs/AUDIT_NOTES.md`.
- **Two runtime defects found and fixed** (both reproduced via probes, then fixed):
  1. Empty-cart `/checkout` returned **500** instead of 302 redirect (`CheckoutController::index`).
  2. `HealthController::show` ran `Artisan::call('migrate --pretend')` on every probe (dead code / no-op); removed.
- Pre-existing cleanup not part of this commit: orphaned `use AuditLog` imports in `OrderController` and `InventoryController` confirmed IDE-only (logic unaffected); kept as doctrine note.

