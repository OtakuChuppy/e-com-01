# Setup (Windows PowerShell) — DRAFT, Stage 0

> Full steps will be verified after PHP install. Do not paste secrets in chat.

## 1. Install prerequisites (run in Admin PowerShell)
```powershell
winget install -e --id PHP.PHP.8.3 --silent
winget install -e --id Composer.Composer --silent
# MySQL (pick one):
winget install -e --id Oracle.MySQL.8.0 --silent
# OR use XAMPP/Laragon which bundles PHP+MySQL if winget PHP path issues arise
```
Close + reopen terminal, then verify:
```powershell
php -v; composer --version; mysql --version; node -v; npm -v
php -m | Select-String "pdo|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|fileinfo"
```

## 2. Create Laravel project (once PHP ready)
```powershell
cd C:\Users\PC\Desktop\e-com
composer create-project laravel/laravel:^11.0 tmp-laravel
robocopy tmp-laravel . /E /MOVE
php artisan --version
cp .env.example .env
php artisan key:generate
```

## 3. Local dev (SQLite interim, MySQL when available)
```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
# .env: DB_CONNECTION=sqlite, DB_DATABASE=absolute path
php artisan migrate --seed
php artisan serve
npm install; npm run dev
```

## 4. Git
```powershell
git init; git add .; git commit -m "chore: stage 0 docs + inspection"
```

See also: docs/DEPLOYMENT.md, docs/SECURITY.md, docs/ADMIN_GUIDE.md (to be expanded per stage).
