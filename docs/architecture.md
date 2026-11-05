# Architecture

## Overview

```
Browser ──HTTPS──▶ Nginx ──FastCGI──▶ PHP-FPM (Laravel 13)
                                        │
                                        ├── Blade views + Vite/Tailwind assets (public/build)
                                        ├── Session auth (database sessions, encrypted)
                                        └── Eloquent ──TLS──▶ Supabase PostgreSQL (session pooler)
Scheduler (schedule:work) ──▶ nexora:expire-quotes (daily 02:00)
```

- **Rendering:** server-side Blade, no SPA. JavaScript lives in one dependency-free module, `resources/js/app.js` (confirm dialogs, price calculator). It is wired through `data-*` attributes so the production CSP needs no `unsafe-inline`.
- **Auth:** Laravel session guard with required e-mail verification. Sessions are stored encrypted in the `sessions` table.
- **Database:** PostgreSQL on Supabase, reached through the IPv4 **session pooler** with `sslmode=require`. The Supabase Data API is locked out with RLS and revoked grants (see [ERD.md](ERD.md)). MySQL 8 and SQLite also work through the same migrations.

## Layers

| Layer | Location | Responsibility |
|---|---|---|
| Routing | `routes/web.php` | URL → controller, middleware stack (`auth`, `verified`, `role:`, `throttle:`) |
| Middleware | `app/Http/Middleware` | `EnsureUserHasRole` (RBAC), `SecurityHeaders` (CSP, HSTS, framing) |
| Validation | `app/Http/Requests` | All input rules, normalisation, policy pre-check |
| Authorization | `app/Policies`, gates in `AppServiceProvider` | Who may do what to which record |
| Controllers | `app/Http/Controllers` | Thin orchestration. `$this->authorize()` is the first statement of every action |
| Domain | `app/Models`, `app/Enums` | Pricing (`getPriceForQuantity`), quote references, status workflow |
| Presentation helpers | `app/Support/Money`, `@money` directive | Single currency formatting point |
| Configuration | `config/nexora.php` | Every app-specific setting, env-driven, no secret defaults |

## Request lifecycle (example: admin deletes a product)

1. `DELETE /admin/products/{slug}`. The `web` group checks the CSRF token.
2. `auth` → `verified` → `role:admin` reject guests, unverified users and non-admins.
3. `ProductController::destroy` calls `$this->authorize('delete', $product)` (`ProductPolicy`).
4. The product is deleted in a transaction. Its `bulk_prices` rows go through `ON DELETE CASCADE`. The image file is removed.

## Configuration and secrets

- All runtime settings come from environment variables. `config/nexora.php` is the only app config file, and `.env.example` is the only template.
- `AppServiceProvider::ensureRequiredEnvironment()` throws at boot if `APP_KEY`, `APP_URL` or the database password is missing. The Docker entrypoint does the same before starting.
- The Docker **build** needs no secrets. Configuration is read when the container starts (`config:cache` in the entrypoint).

## Deployment topologies

| Target | Web | Scheduler | Database |
|---|---|---|---|
| Render (live demo) | Docker web service (`render.yaml`) | inside the container (`RUN_SCHEDULER=true`) | Supabase |
| VPS | Nginx + PHP-FPM (systemd) | `nexora-scheduler.service` (systemd) | Supabase or local PostgreSQL |
| Docker Compose | `app` service | `scheduler` service | Supabase, or `--profile local-db` |

The full steps for each target are in [HowToSetup.md](../HowToSetup.md).
