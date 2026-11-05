# How to set up Nexora Wholesale

This guide goes from a clean machine to a running Nexora Wholesale. It covers five install targets:

| Target | Best for | Section |
|---|---|---|
| **A. Local development** | Working on the code | [§3](#3-target-a--local-development) |
| **B. Vercel (serverless) + Supabase** | Free hosted live demo | [§4](#4-target-b--vercel-serverless--supabase) |
| **C. Render (Docker) + Supabase** | Hosted, always-on container | [§5](#5-target-c--render-docker--supabase) |
| **D. VPS: Nginx + PHP-FPM + systemd** | Your own Ubuntu server | [§6](#6-target-d--vps-ubuntu-nginx--php-fpm--systemd) |
| **E. Docker Compose** | Any Docker host | [§7](#7-target-e--docker-compose) |

---

## 1. Prerequisites

| Tool | Version | Needed for |
|---|---|---|
| PHP | **8.3+** (Vercel provides it) with `pdo_pgsql`, `pdo_sqlite`, `mbstring`, `openssl`, `bcmath`, `intl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `curl` | A, C |
| Composer | 2.x | A, C |
| Node.js | **20+** (npm 10+) | A, C (asset build) |
| PostgreSQL | 15+: a **Supabase** project (free tier is fine) or a local server | all |
| Docker | Engine 24+ with Compose v2 | C (built by Render), E |
| Git | any | all |

- **Windows:** [Laravel Herd](https://herd.laravel.com) installs PHP and Composer. Get Node.js from <https://nodejs.org>.
- **macOS:** `brew install php composer node`
- **Ubuntu 24.04:** see [§6.1](#61-install-packages).

---

## 2. Create the database (Supabase)

1. Go to <https://supabase.com/dashboard> → **New project**. Pick a region close to your users and set a strong **database password**.
2. Open **Connect** (top bar) → **Session pooler**. Note these values:
   - Host, e.g. `aws-0-<region>.pooler.supabase.com`
   - Port `5432`, Database `postgres`
   - User `postgres.<project-ref>`

   Use the **session** pooler (port 5432): it works over IPv4 and supports prepared statements. Don't use the transaction pooler (6543).
3. Create the schema with **one** of these:
   - **SQL Editor (no tools needed):** Supabase → *SQL Editor* → paste [`database/schema.sql`](database/schema.sql) → **Run**. Optionally paste [`database/seed.sql`](database/seed.sql) → **Run** for demo data.
   - **psql CLI:**
     ```bash
     psql "postgresql://postgres.<ref>:<password>@<pooler-host>:5432/postgres?sslmode=require" -f database/schema.sql
     psql "postgresql://postgres.<ref>:<password>@<pooler-host>:5432/postgres?sslmode=require" -f database/seed.sql
     ```
   - **Laravel migrations** (same result): `php artisan migrate --force`, then optionally `php artisan db:seed --force`. Docker targets do this automatically on start.

   `schema.sql` enables Row Level Security on every table and revokes access from Supabase's `anon` / `authenticated` roles, so your publishable key cannot read any data. Laravel only needs the `DB_*` values. It never uses the Supabase API keys or JWT secret.

---

## 3. Target A: local development

```bash
git clone https://github.com/alimmridha71/Nexora-WholeSale.git nexora-wholesale
cd nexora-wholesale
composer install                  # installs exactly what composer.lock pins
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

| Key | Value |
|---|---|
| `APP_ENV` / `APP_DEBUG` | `local` / `true` |
| `APP_URL` | `http://127.0.0.1:8000` |
| `SESSION_SECURE_COOKIE` | `false` (plain HTTP locally) |
| `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` | from §2 (or a local PostgreSQL: `DB_HOST=127.0.0.1`, `DB_SSLMODE=prefer`) |
| `NEXORA_DEMO_MODE` | `true` to show the one-click demo buyer sign-in |

> **Quickest start without PostgreSQL:** set `DB_CONNECTION=sqlite`, delete the other `DB_*` lines, and run `touch database/database.sqlite`.

```bash
php artisan migrate --seed        # skip if you applied schema.sql/seed.sql in §2
php artisan nexora:create-admin you@your-company.com   # hidden password prompt
npm ci
npm run build                     # or: npm run dev   (hot reload)
php artisan storage:link
php artisan serve                 # http://127.0.0.1:8000
```

Run the scheduler in a second terminal: `php artisan schedule:work`.

---

## 4. Target B: Vercel (serverless) + Supabase

Vercel runs Laravel as one serverless PHP function (`api/index.php`, runtime `vercel-php@0.7.4` = PHP 8.3). [`vercel.json`](vercel.json) holds the build, routing and cron settings, so **no build settings need to be changed in the Vercel UI**.

1. **Database first.** Vercel does not run migrations, so complete [§2](#2-create-the-database-supabase) and apply `database/schema.sql` (and optionally `database/seed.sql`) in the Supabase **SQL Editor**.
2. **Admin account.** From any machine with PHP, put the Supabase `DB_*` values in `.env` and run `php artisan nexora:create-admin you@your-company.com`.
3. **Import.** <https://vercel.com/new> → *Import Git Repository* → select this repository. Keep *Framework Preset* on **Other**. `vercel.json` sets `"framework": null`, `npm run build` and the `public` output directory.
4. **Environment Variables** (*Settings → Environment Variables*, Production):

   | Variable | Value |
   |---|---|
   | `APP_KEY` | `base64:…` (see [§5](#5-target-c--render-docker--supabase) step 2 for the command) |
   | `APP_URL` | `https://<project>.vercel.app` |
   | `DB_HOST` | Supabase session-pooler host |
   | `DB_USERNAME` | `postgres.<project-ref>` |
   | `DB_PASSWORD` | Supabase database password |
   | `CRON_SECRET` | long random string (`openssl rand -hex 32`). Vercel Cron sends it as a Bearer token |
   | `NEXORA_DEMO_MODE` | `true` for the public demo (one-click buyer sign-in), otherwise `false` |

   Non-secret defaults are applied in `api/index.php`, and any of them can be overridden with an env var. They include `APP_ENV=production`, pgsql on port 5432 with `sslmode=require`, database sessions and cache, stderr logs, `/tmp` view and bootstrap caches, and `NEXORA_UPLOADS_ENABLED=false`.
5. **Deploy** (or *Redeploy* after changing variables). Vercel Cron calls `GET /cron/expire-quotes` daily at 02:00 UTC.

**Limitations:** product image uploads are disabled because Vercel's disk is not persistent. Use Render, a VPS or Docker if you need them. The first request after an idle period is slower (cold start).

---

## 5. Target C: Render (Docker) + Supabase

Render builds the repository's `Dockerfile` and runs it as a web service. The Blueprint [`render.yaml`](render.yaml) declares the service and every environment variable.

1. Complete [§2](#2-create-the-database-supabase). You don't need to run any SQL: the container migrates and seeds on start.
2. Generate an `APP_KEY` (any machine):
   ```bash
   php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```
   or with Node: `node -e "console.log('base64:'+require('crypto').randomBytes(32).toString('base64'))"`
3. Go to <https://dashboard.render.com> → **New → Blueprint** → connect your GitHub account → select this repository → **Apply**.
4. Render asks for the secret values (`sync: false`):

   | Variable | Value |
   |---|---|
   | `APP_KEY` | from step 2 |
   | `APP_URL` | `https://<service-name>.onrender.com` |
   | `DB_HOST` | session pooler host from §2 |
   | `DB_USERNAME` | `postgres.<project-ref>` |
   | `DB_PASSWORD` | your Supabase database password |
   | `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD` | your admin login (min. 12 chars, mixed case, number, symbol). Never published |

5. Deploy. The entrypoint runs `migrate --force` and the idempotent `db:seed --force`. The health check is `GET /up`. `RUN_SCHEDULER=true` runs the daily quote-expiry job inside the container.
6. Open the URL. `/login` shows **Try the live demo** buttons (retailer / wholesaler / distributor). No password is needed or published.

**Free tier note:** free Render services sleep after 15 minutes idle, so the first request takes about a minute to wake the service.

---

## 6. Target D: VPS (Ubuntu, Nginx + PHP-FPM + systemd)

### 6.1 Install packages

```bash
sudo apt update
sudo apt install -y nginx git unzip curl \
  php8.3-fpm php8.3-cli php8.3-pgsql php8.3-sqlite3 php8.3-mbstring php8.3-xml \
  php8.3-bcmath php8.3-intl php8.3-curl php8.3-zip
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash - && sudo apt install -y nodejs
```

(Ubuntu 22.04 needs `sudo add-apt-repository ppa:ondrej/php` first to get PHP 8.3.)

### 6.2 Deploy the code

```bash
sudo mkdir -p /var/www/nexora && sudo chown $USER:www-data /var/www/nexora
git clone https://github.com/alimmridha71/Nexora-WholeSale.git /var/www/nexora
cd /var/www/nexora
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
nano .env        # APP_URL=https://your-domain, DB_* from §2, APP_ENV=production, APP_DEBUG=false
php artisan migrate --force
php artisan nexora:create-admin you@your-company.com
npm ci && npm run build
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 6.3 Nginx reverse proxy to PHP-FPM

`/etc/nginx/sites-available/nexora`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/nexora/public;
    index index.php;
    client_max_body_size 4m;
    server_tokens off;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/nexora /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo apt install -y certbot python3-certbot-nginx && sudo certbot --nginx -d your-domain.com   # HTTPS
```

### 6.4 systemd service for the scheduler

`/etc/systemd/system/nexora-scheduler.service`:

```ini
[Unit]
Description=Nexora Wholesale scheduler
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/nexora
ExecStart=/usr/bin/php artisan schedule:work
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now nexora-scheduler php8.3-fpm nginx
```

### 6.5 Updating

```bash
cd /var/www/nexora && git pull
composer install --no-dev --optimize-autoloader && npm ci && npm run build
php artisan migrate --force && php artisan optimize
sudo systemctl reload php8.3-fpm && sudo systemctl restart nexora-scheduler
```

---

## 7. Target E: Docker Compose

```bash
cp .env.example .env        # fill APP_KEY, APP_URL and DB_* (Supabase from §2)
docker compose up -d --build
docker compose exec app php artisan nexora:create-admin you@your-company.com
```

- The app is at `http://localhost:8080` (`APP_PORT`). Migrations run on start. Set `RUN_SEED=true` to load demo data.
- **Fully local database instead of Supabase:** set `DB_HOST=db`, `DB_SSLMODE=disable`, then run `docker compose --profile local-db up -d --build`.
- Compose refuses to start when a required variable is missing (`${VAR:?error}`). A literal `$` written **inside** `docker-compose.yml` must be escaped as `$$`.

---

## 8. Admin account

There is **no default admin and no default password**. Use either:

- `php artisan nexora:create-admin you@company.com`, which reads the password from a hidden prompt (min. 12 chars, mixed case, number, symbol), or
- `SEED_ADMIN_EMAIL` + `SEED_ADMIN_PASSWORD` in the environment before `php artisan db:seed` (this is how Render does it).

## 9. Branding

| What | Where |
|---|---|
| Product name | `APP_NAME` in `.env`. The wordmark is in `resources/views/layouts/app.blade.php` (header) |
| Brand colours | `@theme` block in `resources/css/app.css`: `--color-brand-*` (navy, actions and links), `--color-ink-*` (text and dark surfaces), `--color-accent-*` (amber highlights). Tailwind 4 has no `tailwind.config.js`; the theme lives in CSS |
| Hero photo | `public/images/hero-warehouse.webp` (1920×960). Referenced by `.hero-photo` in `resources/css/app.css` |
| Product photos | `public/images/products/<lower-case-sku>.webp` (800×600). Credit each one in [docs/CREDITS.md](docs/CREDITS.md) |
| Fonts | `--font-sans` in the same `@theme` block |
| Buttons, cards, badges | `@layer components` in `resources/css/app.css` |
| Currency | `NEXORA_CURRENCY` (ISO 4217, e.g. `EUR`), formatted everywhere by `@money` |
| Hero copy | `resources/views/catalog/index.blade.php` |

Run `npm run build` after any change to CSS or JS.

## 10. Verify

```bash
php artisan test                               # feature + unit tests (SQLite in-memory)
composer audit                                 # PHP CVE check against composer.lock
npm audit --omit=dev --audit-level=high        # JS CVE check
curl -f https://your-domain/up                 # health endpoint
```

CI (`.github/workflows/ci.yml`) runs all of these on every push. It tests against both SQLite and PostgreSQL 17, and also checks that `schema.sql` + `seed.sql` create the database from empty.

## 11. Production checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`
- [ ] Strong unique `APP_KEY` and database password. `.env` never committed.
- [ ] Admin created through `nexora:create-admin` or the env seed (never a shared password)
- [ ] `NEXORA_DEMO_MODE=false` unless this is the public demo
- [ ] Real SMTP (`MAIL_*`) so verification e-mails are delivered
- [ ] Scheduler running (systemd unit, compose `scheduler` service, `RUN_SCHEDULER=true`, or Vercel Cron with `CRON_SECRET`)
- [ ] Database backups enabled (Supabase → Database → Backups)
