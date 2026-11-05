# Nexora Wholesale

**Nexora Wholesale** is a B2B wholesale ordering platform built on **Laravel 13** and **Supabase PostgreSQL**. Retailers, wholesalers and distributors browse a catalog with **tiered bulk pricing** and send **custom quote requests** for large orders. Administrators manage products, price tiers and the quote pipeline.

**Live demo:** <https://nexora-wholesale.onrender.com>

On `/login`, click **Try the live demo** to sign in instantly as a sample retailer, wholesaler or distributor. No password is needed, and none is published. Admin access is provided to reviewers privately.

---

## Features

| Area | What it does |
|---|---|
| **Catalog** | Public product listing with search, a product page with a bulk price table and a live unit-price calculator. |
| **Tiered bulk pricing** | Non-overlapping quantity tiers per product. A `NULL` max means an open-ended top tier. Resolved by `Product::getPriceForQuantity()`. |
| **Custom quotes** | Business buyers request quotes with up to 50 line items, a target price and a message. Stored as validated JSON. |
| **Quote workflow** | `pending → under_review → approved / rejected / expired`, with transitions enforced server-side. A daily job expires stale quotes. |
| **Public status check** | Random, non-sequential reference (e.g. `NXQ-7KD9QX2M`) **plus** the owner's e-mail. Rate limited per IP and per reference, and only the status is disclosed. |
| **RBAC** | Roles `admin`, `retailer`, `wholesaler`, `distributor`. Enforced by `role:` middleware **and** `$this->authorize()` in every controller action. |
| **Authentication** | Session login with throttling, required e-mail verification. The admin role cannot be chosen at registration. |

## Security highlights

- CSRF on every state-changing route, plus a policy check inside every handler. See the [authorization matrix](docs/authorization-matrix.md).
- `role`, `user_id`, `status` and `reference` can never be mass assigned.
- No secrets in the repository, no default admin and no known password in seed data. The app **fails fast** if `APP_KEY`, `APP_URL` or the DB password is missing.
- Supabase: **RLS enabled on every table**, with `anon`/`authenticated` privileges revoked, so the publishable key cannot read any data.
- CSP (production), HSTS, `X-Frame-Options: DENY`, `nosniff` headers.
- Every foreign key has an explicit `ON DELETE` rule and an index. See the [ERD](docs/ERD.md).

## Tech stack

PHP 8.3+ · Laravel 13 · PostgreSQL 17 (Supabase) · Blade · Tailwind CSS 4 · Vite 7 · Docker (PHP-FPM + Nginx, non-root) · Render

Exact dependency versions are pinned in `composer.lock` and `package-lock.json`.

## Quick start

```bash
composer install
cp .env.example .env && php artisan key:generate   # then set the DB_* values
php artisan migrate --seed
php artisan nexora:create-admin you@your-company.com
npm ci && npm run build
php artisan serve
```

**[HowToSetup.md](HowToSetup.md)** has the full guide for local, Render + Supabase, VPS (Nginx + PHP-FPM + systemd) and Docker Compose, plus the branding guide.

## Documentation

| File | Contents |
|---|---|
| [HowToSetup.md](HowToSetup.md) | Prerequisites and step-by-step setup for four targets |
| [AGENTS.md](AGENTS.md) | Architecture, security rules and conventions for maintainers and AI agents |
| [docs/architecture.md](docs/architecture.md) | Layers, request lifecycle, deployment topologies |
| [docs/ERD.md](docs/ERD.md) | Entity-relationship diagram, FKs, RLS |
| [docs/authorization-matrix.md](docs/authorization-matrix.md) | Every route with its middleware and in-handler policy check |
| [SECURITY.md](SECURITY.md) | Vulnerability reporting and security design |
| [CHANGELOG.md](CHANGELOG.md) | Release history |
| [database/schema.sql](database/schema.sql) · [seed.sql](database/seed.sql) | PostgreSQL schema and demo data |

## Testing

```bash
php artisan test
```

CI runs the suite against SQLite and PostgreSQL 17, plus `composer audit`, `npm audit`, Pint and a Docker build, on every push.

## License

MIT © Nexora Wholesale
