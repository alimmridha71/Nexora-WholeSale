# AGENTS.md: Nexora Wholesale architecture and contribution guide

This file is for AI coding agents and human maintainers. Read it before changing code. The rules here are **requirements**, not suggestions.

---

## 1. Project at a glance

- **Product:** Nexora Wholesale, a B2B wholesale catalog with tiered bulk pricing and custom quote requests.
- **Stack:** PHP 8.3+, Laravel 13, PostgreSQL 17 on Supabase (MySQL 8 / SQLite also supported via migrations), Blade, Tailwind CSS 4, Vite 7.
- **Database access:** Laravel connects to Supabase through the session pooler as the table owner. The Supabase Data API (anon/publishable key) is locked out: RLS is on for every table with no policies, and grants are revoked. Never add RLS policies that open tables to `anon`/`authenticated`, and never use the Supabase API keys from PHP.
- **Auth:** session-based (no JWT, no API tokens). E-mail verification is required for all business features.
- **Roles:** `admin`, `retailer`, `wholesaler`, `distributor` (`App\Enums\UserRole`).

## 2. Directory map

```
app/
  Enums/                 UserRole, QuoteStatus (backed enums; single source of truth for values)
  Http/
    Controllers/         Public + buyer controllers
      Admin/             Admin-only controllers (products, bulk prices, quotes)
      Auth/              Login, registration, e-mail verification
    Middleware/          EnsureUserHasRole (alias "role"), SecurityHeaders
    Requests/            FormRequests: ALL input validation lives here
  Models/                User, Product, BulkPrice, Quote
  Policies/              ProductPolicy, QuotePolicy (auto-discovered)
  Providers/             AppServiceProvider: fail-fast env checks, gates, rate limiters, @money
  Support/               Money (single price-formatting point)
bootstrap/app.php        Routing, middleware aliases, proxies
config/nexora.php        App-specific settings (env-driven, no secret defaults)
database/
  migrations/            Schema source of truth
  factories/, seeders/   Demo + test data
  schema.sql, seed.sql   SQL mirrors of migrations / seeders. KEEP IN SYNC.
resources/views/         Blade templates (no inline JS/CSS: strict CSP in production)
routes/web.php           All HTTP routes
routes/console.php       Artisan commands + schedule
tests/                   PHPUnit (Feature + Unit)
docker/, Dockerfile, docker-compose.yml
```

## 3. Domain model

| Table | Key points |
|---|---|
| `users` | `role` enum, default `retailer`. `role` is **not** fillable. |
| `products` | `slug` and `sku` unique. Index on `(name, base_price)`. Route key = `slug`. |
| `bulk_prices` | FK `product_id` with **cascadeOnDelete**. Index on `(product_id, min_quantity, max_quantity)`. `max_quantity NULL` = open-ended. Tiers must not overlap (enforced in `StoreBulkPriceRequest`). |
| `quotes` | Random unique `reference` (`Quote::newReference()`, never sequential). FK `user_id` with **restrictOnDelete**. `product_details_json` is cast to `array`. `status` is cast to `QuoteStatus`. Index on `(user_id, status)`. |

`product_details_json` shape (validated by `StoreQuoteRequest`):

```json
[{ "product_id": 1, "quantity": 5000, "notes": "optional, max 500 chars" }]
```

**Pricing:** `Product::getPriceForQuantity(int $qty)` returns the price of the matching tier with the highest `min_quantity`, or `base_price` if no tier matches. It uses the eager-loaded `bulkPrices` relation when present, so load it in listings with `->with('bulkPrices')`.

**Quote lifecycle:** `QuoteStatus::allowedTransitions()` is the only place transitions are defined. `approved`, `rejected` and `expired` are terminal.

## 4. Security rules (non-negotiable)

1. **Every mutating route** (POST/PUT/PATCH/DELETE) must:
   - sit inside the `web` middleware group (CSRF);
   - require `auth` and `verified`, except the login/register/verification/logout flows;
   - carry a `role:` middleware matching its audience;
   - call `$this->authorize(...)` against a policy **inside the controller action**, even when a FormRequest's `authorize()` already checks it. Never rely on the route prefix alone.
2. **Never mass-assign** `role`, `user_id`, `product_id`, `status` or `reference`. Set them explicitly or through a relationship (`$user->quotes()->create(...)`).
3. **Validate everything** in a FormRequest. No `$request->all()` into models. Use `$request->validated()` or `$request->safe()`.
4. **Public lookups** must require two independent factors (random reference + e-mail), return one generic error for any mismatch, select only the fields shown, and be throttled per IP **and** per reference (`throttle:60,1` + `throttle:quote-lookup`).
5. **No secrets in code or config defaults.** Never write `env('X', 'some-password')`. Secrets come from the environment. Missing secrets must fail fast (see `AppServiceProvider::ensureRequiredEnvironment`).
6. **Do not call `env()` outside `config/`.** It returns `null` once config is cached.
7. **Blade:** always use `{{ }}` (escaped). `{!! !!}` is forbidden unless the content is sanitized server-side and reviewed. No inline executable `<script>`, `style=` or `on*=` attributes, because the production CSP blocks them. Non-executable `<script type="application/json">` data blocks rendered with `@json` are allowed. Put behaviour in `resources/js/app.js` and use `data-*` hooks.
8. **File uploads:** validate with `image|mimes:jpg,jpeg,png,webp|max:2048`, store on the `public` disk under a generated name, and delete replaced files. Bundled catalog photos (`image` starting with `images/`, files in `public/images/`) are part of the repository and must never be deleted by code.
   **Photos and media:** use only images you are licensed to use (e.g. the free Unsplash License, never Unsplash+). Record every bundled photo in `docs/CREDITS.md`. Load images from the app's own origin so the CSP (`img-src 'self'`) holds.
9. **Seed data and docs never contain a usable password.** No admin is seeded unless `SEED_ADMIN_EMAIL`/`SEED_ADMIN_PASSWORD` are set. Demo buyers are password-locked and reachable only through demo login (`NEXORA_DEMO_MODE`). Test passwords must be obviously fake (e.g. `test-password-not-real-A1!`).
10. **PostgreSQL safety:** put `bail` before `exists:` on integer IDs (a non-numeric value would otherwise raise an SQL error). Use `whereLike(..., caseSensitive: false)` for search. Integer columns max out at 2147483647.
11. **Foreign keys:** every new FK needs explicit `cascadeOnDelete()` or `restrictOnDelete()` (or `nullOnDelete()` with justification) and an index (a leading column of a composite index counts).

## 5. Coding conventions

- PSR-12 via **Laravel Pint** (`vendor/bin/pint`). Typed properties, return types and `declare` of generics in PHPDoc.
- Use enums (`UserRole`, `QuoteStatus`), never raw strings, in PHP code. Raw strings are acceptable only in migrations and SQL files.
- Controllers stay thin: validation in FormRequests, authorization in Policies, domain logic on Models or Enums.
- Use eager loading. `Model::shouldBeStrict()` is enabled outside production and will throw on lazy loading.
- Money is stored as `DECIMAL(12,2)` and cast `decimal:2` (string). Never use floats for persisted money. Display prices only with `@money(...)`.
- No magic values: limits, prefixes, rate limits and demo accounts live in `config/nexora.php`.
- Routes are named. Views use `route()` and never hard-coded URLs.

## 6. Changing the schema

1. Add a **new** migration. Never edit a migration that has shipped.
2. Update `database/schema.sql` (PostgreSQL DDL, including RLS for new tables) and, if relevant, `database/seed.sql`. CI applies both to an empty PostgreSQL database.
3. Update the model's `$fillable`, `casts()` and relationships, plus its factory.
4. Add or adjust tests.
5. Add a `CHANGELOG.md` entry.

## 7. Adding a feature checklist

- [ ] Route in the correct middleware group with a `role:` guard
- [ ] FormRequest with complete rules
- [ ] Policy method + `$this->authorize()` in the action
- [ ] Feature test covering the allowed role **and** at least one forbidden role
- [ ] No N+1 queries (strict mode passes)
- [ ] Docs and CHANGELOG updated

## 8. Commands

```bash
php artisan test                         # tests
vendor/bin/pint                          # format
composer audit && npm audit              # dependency CVE scan
php artisan nexora:create-admin <email>  # create/reset an admin (hidden password prompt)
php artisan nexora:expire-quotes         # expire stale quotes (scheduled daily 02:00)
```

## 9. Dependencies

- Keep `composer.lock` and `package-lock.json` committed and in sync with their manifests.
- Add a dependency only when the framework cannot reasonably do the job. Run `composer audit` / `npm audit` after any change.
- Engines: PHP `^8.3`, Node `>=20`.
