# Admin Guide (Stages 5–6 live)

Demo brand: **DeshiMart (Demo)** — replace with client brand before launch.

## Login
- URL: `/admin/login`. Use the owner/staff email + password.
- Forgot password: `/admin/forgot-password` (with `MAIL_MAILER=log`, the link is written to `storage/logs/laravel.log`, not emailed — configure SMTP in production first).
- After 5 wrong attempts from one IP+email, logins pause for 2 minutes.

## First owner account (local dev, verified working)
```powershell
$env:OWNER_EMAIL='owner@example.com'; $env:OWNER_PASSWORD='Choose-A-Strong-Password!'
php artisan db:seed --class=Database\Seeders\OwnerSeeder --force
```
Production: set `OWNER_EMAIL`/`OWNER_PASSWORD`/`OWNER_NAME` in the server environment (never in chat or git), run the same seeder once, then unset the password variable.

## Daily work
- **Dashboard** (`/admin`): live cards — total, new (pending), on-hold, confirmed, delivered, cancelled — plus 7/30-day sales (confirmed+) and 8 recent orders. No fabricated analytics.
- **Orders**: search by reference/name/phone, filter by status, 15/page. Open an order for snapshot items, totals, delivery address, and history. Change status only via offered next steps (pending→confirmed/on-hold/cancelled, etc.); every change is logged with your name + timestamp. Confirm the dialog before submitting.
- **Products**: search + status filter, 15/page, low-stock flags. Create/edit with validated prices/stock. Publish/archive via buttons. Delete is **blocked** when the product appears in past orders — archive instead.
- **Inventory**: adjustment form (product, +/- qty, reason, reference) runs in a DB transaction and never lets stock go negative. Recent 15 movements listed; lowest-stock table below.
- **Settings**: delivery charges (Dhaka in/out, applied at checkout), tracking IDs (Pixel digits, GTM-XXXX, G-XXXX formats only), robots staging/live. Courier (Steadfast): **not connected** — mock panel only until docs + credentials + approval.
- **Staff & roles** (owner only): create accounts (min 10-char password), change role/active. You cannot demote/deactivate yourself or the last owner.

## Order SOP
1. New (pending) → call customer → Confirm or Hold/Cancel with note.
2. Confirmed → pack → Ship. 3. Shipped → Delivered (COD collected) or Cancelled.
4. Look up any order publicly via `/track-order` (reference + phone) for customer queries.

Remaining for Stage 9: CSV import template, full release checklist.
