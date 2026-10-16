# Security Checklist — DRAFT (Stage 0, enforced from Stage 6)

- Auth on all admin routes + server-side Gates/Policies (never UI-only hiding)
- CSRF on all forms, validated Form Requests, Eloquent only (no raw SQL), Blade escaping by default
- Passwords: Laravel hashing only; rate-limited login; secure session + HTTPS-only cookies in prod
- Uploads: validate MIME/size/extension, store outside web root (`storage/app/public`), random filenames, never executable
- Secrets only in `.env` (never committed); `.env.example` has placeholders only
- Production: `APP_DEBUG=false`, generic error pages, dependency audit (`composer audit`)
- No invented compliance claims. Full checklist in Stage 6.
