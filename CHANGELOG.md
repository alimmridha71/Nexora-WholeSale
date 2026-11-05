# Changelog

All notable changes to **Nexora Wholesale** are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- Real product photos for the whole demo catalog, plus a warehouse hero photo. All are free Unsplash License images, bundled as WebP in `public/images/` and credited in `docs/CREDITS.md`.
- Product cards list their volume price breaks and a "save up to X% at volume" line.
- Tests: bundled image URLs resolve, every seeded product has an existing photo file, and the home page shows the hero and the tier rows.

### Changed
- Visual redesign with a restrained distributor look: navy and amber palette, utility bar, photo hero with product search, square-cornered cards, and a plain light footer. Gradient glows, grid patterns and gradient text are removed.
- Demo catalog: "Stretch Wrap Film" is replaced by "EUR Wooden Pallets 1200x800" (`PLT-EUR-1208`) and "Lithium AA Batteries" by "White Stoneware Mugs (Case of 12)" (`MUG-CRM-12`), because no license-clean photos without brand logos were available. IDs, prices and tiers are unchanged.
- `ProductSeeder` no longer adds Faker-generated products, so the demo catalog contains real items only.

### Fixed
- Admin product update and delete never remove bundled catalog photos.

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
- **Vercel** deployment: `vercel.json` + `api/index.php` (`vercel-php@0.7.4`, PHP 8.3), `/tmp` cache paths, database cache store (`cache` / `cache_locks` tables) for shared rate limits, Vercel Cron endpoint `GET /cron/expire-quotes` (Bearer `CRON_SECRET`), and the `NEXORA_UPLOADS_ENABLED` toggle.
- `App\Actions\ExpireStaleQuotes`, shared by the console command and the cron endpoint.
- Multi-stage Dockerfile (PHP 8.3-FPM + Nginx, non-root, builds without secrets), `docker-compose.yml` with mandatory-secret guards and an optional local PostgreSQL profile, and a `render.yaml` Blueprint.
- GitHub Actions CI: composer lockfile generation and validation, Pint, tests on SQLite **and** PostgreSQL 17, a `schema.sql` + `seed.sql` from-empty check, `composer audit`, `npm audit`, and a Docker build.
- Documentation: README, HowToSetup (local, Render + Supabase, VPS with Nginx + PHP-FPM + systemd, Docker Compose, branding), AGENTS, CLAUDE, SECURITY, `docs/architecture.md`, `docs/ERD.md`, `docs/authorization-matrix.md`.

[Unreleased]: https://github.com/alimmridha71/Nexora-WholeSale/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/alimmridha71/Nexora-WholeSale/releases/tag/v1.0.0
