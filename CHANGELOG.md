# Changelog

All notable changes to **Nexora Wholesale** are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.0] - 2026-11-05

### Added
- Laravel 13 application (PHP 8.3+) targeting **Supabase PostgreSQL** through the session pooler (`sslmode=require`). MySQL 8 and SQLite also work through the migrations.
- Schema: `users` (role enum), `products`, `bulk_prices`, `quotes`, with explicit `ON DELETE` rules and indexes on every foreign key.
- Migration `2026_11_05_000001_lock_down_supabase_data_api`: RLS on every table and `anon`/`authenticated` privileges revoked.
- `database/schema.sql` (PostgreSQL, creates from empty in one transaction) and `database/seed.sql` (no admin, password-locked demo buyers).
- Eloquent models with enum casts (`UserRole`, `QuoteStatus`), JSON casts and relationships.
- Tiered bulk pricing with `Product::getPriceForQuantity()` and server-side rejection of overlapping tiers.
- Public catalog with search, product pages and a live price calculator.
- Custom quote requests (up to 50 line items) with random non-sequential references (`NXQ-XXXXXXXX`), buyer history and withdrawal while pending.
- Admin product, price-tier and quote management with an enforced status workflow.
- Public quote status lookup: reference + e-mail, rate limited per IP and per reference, minimal disclosure.
- Session authentication with login throttling, registration (admin role cannot be self-assigned) and required e-mail verification.
- One-click demo buyer sign-in for the public demo (`NEXORA_DEMO_MODE`, off by default, never admin).
- RBAC through `role:` middleware plus a policy check as the first statement of every controller action.
- Security headers middleware (CSP in production, HSTS, frame denial).
- Fail-fast boot and container start when `APP_KEY`, `APP_URL` or the database password is missing.
- `nexora:create-admin` (hidden password prompt) and the scheduled `nexora:expire-quotes` command.
- Idempotent seeders (`DatabaseSeeder`, `UserSeeder`, `ProductSeeder`, `QuoteSeeder`) with no hard-coded passwords.
- `@money` Blade directive and `App\Support\Money` as the single price-formatting point. All tunables are in `config/nexora.php`.
- Standalone error pages (403, 404, 419, 429, 500, 503).
- Multi-stage Dockerfile (PHP 8.3-FPM + Nginx, non-root, builds without secrets), `docker-compose.yml` with mandatory-secret guards and an optional local PostgreSQL profile, and a `render.yaml` Blueprint.
- GitHub Actions CI: composer lockfile generation and validation, Pint, tests on SQLite **and** PostgreSQL 17, a `schema.sql` + `seed.sql` from-empty check, `composer audit`, `npm audit`, and a Docker build.
- Documentation: README, HowToSetup (local, Render + Supabase, VPS with Nginx + PHP-FPM + systemd, Docker Compose, branding), AGENTS, CLAUDE, SECURITY, `docs/architecture.md`, `docs/ERD.md`, `docs/authorization-matrix.md`.

[Unreleased]: https://github.com/alimmridha71/Nexora-WholeSale/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/alimmridha71/Nexora-WholeSale/releases/tag/v1.0.0
