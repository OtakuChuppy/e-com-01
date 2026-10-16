# Deployment Guide — DRAFT (Stage 0, expanded in Stage 8)

> Never deploy to a live domain, modify DNS, or paste secrets in chat without explicit owner approval.

## Requirements (Laravel 13 + PHP 8.3)
- PHP 8.3+, extensions: curl, fileinfo, gd, mbstring, openssl, pdo_mysql, zip, tokenizer, xml, ctype, json, bcmath
- Composer 2.x, Node 20+ (for `npm run build`), MySQL 8 (InnoDB), web server (Nginx/Apache) with document root → `public/`
- Storage: app + DB + ~2GB media headroom minimum for 100 SKUs

## Release procedure (summary)
1. Set `.env` on server (never commit): APP_ENV=production, APP_DEBUG=false, DB_* least-privilege creds, mail, queue.
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci && npm run build`
4. `php artisan migrate --force` (never `migrate:fresh` on prod)
5. `php artisan config:cache route:cache view:cache; php artisan storage:link`
6. Verify: homepage, product page, checkout (COD sandbox), admin login, `APP_DEBUG=false` check.

Backups, queue/scheduler, HTTPS, DNS, rollback plan → Stage 8.
