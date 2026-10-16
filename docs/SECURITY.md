# Security — Implemented (Stages 5–6, verified 2026-10-17)

## Authentication
- Staff login at `/admin/login` (session guard, bcrypt via `hashed` cast — never custom crypto).
- Custom per-account rate limit: 5 attempts per email+IP per 2 min, plus route `throttle:20,1`.
- Login rejects inactive accounts and non-staff roles; session regenerated on login/logout (fixation defense).
- Password reset via Laravel broker (60-min expiry, 60-sec throttle); responses are identical whether or not the email exists (no enumeration). With default `MAIL_MAILER=log`, links go to server logs — documented honestly in UI.
- `EnsureRole` middleware on every admin route group: `any-staff` gate, then `owner,admin` for products/inventory/settings, `owner` for users. **Server-side on every request — sidebar hiding is cosmetic only.**
- Guests are redirected to `admin.login` via `redirectGuestsTo` (no `Route [login]` crash).

## Authorization matrix (enforced + tested)
| Area | owner | admin | order_manager | guest |
|---|---|---|---|---|
| Dashboard, orders (view + status) | ✓ | ✓ | ✓ | → login |
| Products (CRUD/archive/delete) | ✓ | ✓ | 403 | → login |
| Inventory adjustments | ✓ | ✓ | 403 | → login |
| Settings (delivery/tracking/robots) | ✓ | ✓ | 403 | → login |
| Staff & roles | ✓ | 403 | 403 | → login |
- Last active owner cannot be demoted/deactivated (checked in controller + tested).
- `order_manager` hitting `/admin/products`, `/admin/inventory`, `/admin/settings`, `/admin/users` or posting adjustments/products gets **403** (covered by AdminTest).

## Transport & session
- CSRF on all POST/PATCH/DELETE (Laravel default VerifyCsrfToken; 419 on missing token — verified).
- DB sessions, 120-min lifetime. Production must set `SESSION_SECURE_COOKIE=true` + HTTPS (see DEPLOYMENT.md).
- No secrets in JS, logs, or error pages. `/health` returns only `{status, database, app}` — tested for absence of keys/passwords.

## Validation & injection
- All inputs validated (price/stock numeric ranges, phone regex, SKU uniqueness, tracking-ID format regexes, password min 10).
- Eloquent only — no raw SQL. Blade `{{ }}` escaping everywhere; tracking renders validated IDs, never arbitrary scripts.

## Orders & stock
- Checkout transaction + `lockForUpdate`, no negative stock, snapshot order items, idempotency keys, token/phone-gated lookup.

## Verified this stage
- `php artisan test` 18/18 green (incl. 9 new AdminTest: guest redirects, role boundaries, invalid transitions, last-owner guard, delete-block, rate limit, health secrecy, sitemap/robots).
- `composer audit` → no advisories. `npm run build` clean.

## Honest limitations
- No email verification flow yet (needs real SMTP; MAIL_MAILER=log locally).
- No 2FA, no file-upload feature yet (upload rules defined, endpoint not built).
- SQLite locally; concurrency guarantees must be re-verified on MySQL 8 before launch.
- Not a compliance certification — standard Laravel hardening only.
