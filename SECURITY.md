# Security policy

## Reporting a vulnerability

Please report security issues privately by opening a [GitHub security advisory](https://github.com/alimmridha71/Nexora-WholeSale/security/advisories/new) on this repository. Do not open a public issue. We aim to respond within 72 hours.

## Supported versions

| Version | Supported |
|---|---|
| 1.x | ✅ |

## Security design summary

- **Authentication:** session-based, bcrypt password hashes, login throttling (5/min per e-mail+IP), required e-mail verification.
- **Authorization:** `role:` middleware plus a policy check (`$this->authorize()`) in every controller action. See [docs/authorization-matrix.md](docs/authorization-matrix.md).
- **CSRF:** every state-changing request (Laravel `web` group).
- **Mass assignment:** `role`, `user_id`, `status`, `reference` and `product_id` are never fillable.
- **Public lookup:** random non-sequential reference **and** owner e-mail, one generic error, minimal fields, rate limited per IP and per reference.
- **Secrets:** none in the repository. The app and containers fail fast when required secrets are missing. `.env.example` holds placeholders only.
- **Seed data:** no admin account and no known password. The admin is created from environment variables or an interactive command. Demo buyer accounts are password-locked.
- **Database:** parameterised Eloquent and query-builder calls only. On Supabase, RLS is enabled on every table and `anon`/`authenticated` privileges are revoked.
- **Headers:** CSP (production), HSTS (HTTPS), `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`.
- **Dependencies:** lockfiles committed. CI runs `composer audit` and `npm audit --audit-level=high` on every push.
