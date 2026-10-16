# Technical Decisions

Updated: 2026-10-17 — Stage 0 complete (Laravel 13.37.0 on PHP 8.3.33 installed).

## D1 — Framework: Laravel 13 monolith (Blade + Tailwind v4 + Vite)
**Decision:** Laravel **13.37.0** (latest stable, installed 2026-10-17, zero security advisories) as monolith. Blade + Tailwind CSS v4 + Vite, light Alpine.js for drawer/mobile nav.
**Why:** `laravel/laravel:^11.0` skeleton was blocked by 8 upstream security advisories; latest stable is the secure default and satisfies "stable version compatible with available PHP". Single deployable, no microservice overhead.
**Status:** Installed, `composer install` done, SQLite migrated, app key set.

## D2 — PHP version: 8.3 (fallback 8.2)
**Decision:** Target PHP 8.3 LTS-friendly; accept 8.2 minimum for Laravel 11.
**Why:** Laravel 11 supports 8.2+, 8.3 has performance + security life longest on Win10.
**Install:** `winget install -e --id PHP.PHP.8.3 --silent` (or XAMPP/Laragon if winget blocked).

## D3 — Database: MySQL 8 (prod) + SQLite (local dev bootstrap)
**Decision:** Schema designed for MySQL/InnoDB. Local dev may start on SQLite file so storefront work is unblocked before MySQL installed; production docs mandate MySQL 8 + least-privilege user.
**Why:** User has no MySQL yet; SQLite lets migrations/tests run immediately. All migrations kept MySQL-compatible (no SQLite-only SQL).
**Risk:** Minor SQL dialect differences — mitigated by Eloquent + testing on MySQL before release.

## D4 — Auth: Laravel Breeze (Blade) — not custom hashing
**Decision:** Use Breeze Blade stack for auth (login/register/password-reset/email-verify). Roles via `role` column + Gates/Policies (`owner`, `admin`, `staff-order`).
**Why:** Supported, secure defaults (hashing, rate-limit, session). No custom password crypto.

## D5 — Design tokens (locked for Stage 1)
- bg #FBFAF7, primary #344B3F, accent #A96E52, text #242821, muted #77796F
- Font: Inter (Google) + system fallback; serif display optional Fraunces for hero only.
- Rules: no gradients/glass/floating blobs, subtle dividers, refined product cards, accessible contrast, reduced-motion respect.
- Demo brand: neutral "Aarong-style"? NO — use neutral `Nirbochorito / Demo Store` placeholder editable via config until client brand supplied. No fake perfume/furniture identity.

## D6 — Money & stock safety
- Prices: `decimal(12,2)` + BDT formatting; quantities: unsigned integers. Checkout wraps in DB transaction + `lockForUpdate()` on product/variant rows. Never trust browser totals. Preserve snapshot (name/sku/price/qty) in order_items.

## D7 — Pagination default 10
- 10 per page, numbered links preserving query string (`->withQueryString()`), DB-level filter/sort, eager loads.

## D8 — Delivery charges configurable
- `settings` table keys: `delivery_inside_dhaka`, `delivery_outside_dhaka`, `free_shipping_threshold` (nullable). No hardcoded business rates in code.

## D9 — Docs-first workflow
- Maintain docs/PROJECT_STATUS.md, DECISIONS.md, SETUP.md, DEPLOYMENT.md, SECURITY.md, ADMIN_GUIDE.md incrementally. No invented credentials/test results/deploy URLs.

## Open questions for owner (non-blocking, defaults chosen)
1. Real business name/category/logo? → default neutral demo brand until supplied.
2. MySQL preference: local MySQL vs managed (e.g., PlanetScale/Hostinger DB)? → default local MySQL 8 via winget/XAMPP + SQLite interim.
3. Hosting provider/domain? → default generic Laravel-shared/VPS guide until chosen.
