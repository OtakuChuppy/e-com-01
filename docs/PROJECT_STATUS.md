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
- [ ] Layout, design tokens (#FBFAF7, #344B3F, #A96E52, #242821, #77796F), Blade components
- [ ] Homepage: announcement, navbar, hero, categories, featured, product cards, footer, mobile nav
- [ ] Product listing (search/sort/filter/pagination 10pp), detail (gallery, variants, qty, add-to-cart)
- [ ] Cart drawer/page + checkout UI shell
- [ ] Demo seed data clearly marked

### Stage 2 — DB & Product Management
- [ ] Migrations: categories, products, variants, images, customers, addresses, orders, order_items, order_status_history, stock_movements, settings, users/audit
- [ ] Transactions + row-locking, no negative stock, preserve order history snapshots
- [ ] CSV import template + validation

### Stage 3 — Listing & Performance
- [ ] 10pp pagination preserving filters, DB-level search/sort, eager loads, indexes, image lazy-load, selective cache

### Stage 4 — Cart/Checkout/Customer
- [ ] Persistent guest+auth cart, server revalidation, BD checkout (name/phone/address/district), COD, delivery rules (Dhaka in/out configurable), unique order ref, anti-double-submit, lookup via token

### Stage 5 — Admin Panel
- [ ] Dashboard counts, product CRUD + archive, order list/detail/transitions, inventory history, SEO/sitemap/robots, tracking settings (Pixel/GTM/GA4), roles/permissions, audit log, courier settings mock

### Stage 6 — Auth & Security
- [ ] Breeze-equivalent auth, CSRF, validation, hashing, rate-limit, secure uploads, policies, .env.example, prod hardening, docs/SECURITY.md

### Stage 7 — Testing & Quality
- [ ] Feature tests (homepage, listing, pagination, detail, auth, RBAC, CRUD, cart/checkout, totals, delivery, stock, status, SEO, uploads, guest isolation), `npm run build`, UI checks

### Stage 8 — Deployment Prep
- [ ] docs/DEPLOYMENT.md full, prod checklist, backup/restore, smoke tests

### Stage 9 — Handover
- [ ] docs/SETUP.md + ADMIN_GUIDE.md complete, first-owner creation, release checklist

## Current focus
- Stage 1 next: design system + homepage + listing/detail/cart/checkout shell with demo seed data.
- No action needed from user. Non-blocking questions in docs/DECISIONS.md (brand name, MySQL choice, hosting).

## Test log (all actually run 2026-10-17)
- `Get-ChildItem e-com` → empty at start (confirmed)
- `php -v` → 8.3.33 (pass, after winget install + php.ini setup)
- `composer --version` → 2.10.3 (pass)
- `php artisan --version` → 13.37.0 (pass)
- `php artisan migrate --force` → "Nothing to migrate" = tables created (pass)
- `composer create-project laravel/laravel:^11.0` → BLOCKED by 8 security advisories (documented, resolved with latest stable)
