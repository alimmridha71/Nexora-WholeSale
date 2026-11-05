# Authorization matrix

Every HTTP route in [`routes/web.php`](../routes/web.php), with its guards. All routes run inside Laravel's `web` middleware group, which sets up sessions and **CSRF** (`ValidateCsrfToken`) for every POST, PUT, PATCH and DELETE.

Authorization is layered. Each layer works on its own, so a missing middleware still cannot expose a mutation:

1. **Route middleware**: `auth` → `verified` → `role:<roles>` ([`EnsureUserHasRole`](../app/Http/Middleware/EnsureUserHasRole.php)).
2. **FormRequest `authorize()`**: checks the policy before validation runs.
3. **Controller `$this->authorize()`**: the **first statement** of every mutating action, checked against a policy.
4. **Query scoping**: buyer reads go through `Quote::ownedBy($user)`, and ownership comes from relationships (`$user->quotes()->create()`), never from input.

## Public

| Method | URI | Name | Middleware | Handler check |
|---|---|---|---|---|
| GET | `/` | home | — | `authorize('viewAny', Product)` |
| GET | `/products/{product:slug}` | products.show | — | `authorize('view', $product)` |
| GET | `/quote-status` | quote-status.create | `throttle:60,1` | — |
| POST | `/quote-status` | quote-status.show | `throttle:60,1`, `throttle:quote-lookup` | random reference **and** owner e-mail, generic error |
| GET | `/up` | — | — | health check |

## Guest authentication

| Method | URI | Name | Middleware | Notes |
|---|---|---|---|---|
| GET/POST | `/login` | login, login.store | `guest`, POST: `throttle:login` (5/min per e-mail+IP) | generic failure message |
| GET/POST | `/register` | register, register.store | `guest`, POST: `throttle:register` | `role` ∈ retailer/wholesaler/distributor only |
| POST | `/demo-login/{role}` | demo-login | `guest`, `throttle:demo-login`, `role` ∈ buyer roles | 404 unless `NEXORA_DEMO_MODE=true`; never admin |

## Authenticated (verification flow)

| Method | URI | Name | Middleware |
|---|---|---|---|
| POST | `/logout` | logout | `auth` |
| GET | `/email/verify` | verification.notice | `auth` |
| GET | `/email/verify/{id}/{hash}` | verification.verify | `auth`, `signed`, `throttle:6,1` |
| POST | `/email/verification-notification` | verification.send | `auth`, `throttle:6,1` |

## Business buyers: `auth` + `verified` + `role:retailer,wholesaler,distributor`

| Method | URI | Name | Handler check (first statement) |
|---|---|---|---|
| GET | `/dashboard` | dashboard | (auth + verified only; reads are scoped to the user) |
| GET | `/quotes` | quotes.index | `authorize('viewAny', Quote)`, then `Quote::ownedBy($user)` |
| GET | `/quotes/create` | quotes.create | `authorize('create', Quote)` |
| POST | `/quotes` | quotes.store | `authorize('create', Quote)`, plus `throttle:quote-store` |
| GET | `/quotes/{quote}` | quotes.show | `authorize('view', $quote)`: owner or admin |
| DELETE | `/quotes/{quote}` | quotes.destroy | `authorize('delete', $quote)`: owner **and** status = pending |

## Administration: `auth` + `verified` + `role:admin`

| Method | URI | Name | Handler check (first statement) |
|---|---|---|---|
| GET | `/admin/products` | admin.products.index | `authorize('viewAny', Product)` |
| GET | `/admin/products/create` | admin.products.create | `authorize('create', Product)` |
| POST | `/admin/products` | admin.products.store | `authorize('create', Product)` |
| GET | `/admin/products/{product}/edit` | admin.products.edit | `authorize('update', $product)` |
| PUT/PATCH | `/admin/products/{product}` | admin.products.update | `authorize('update', $product)` |
| DELETE | `/admin/products/{product}` | admin.products.destroy | `authorize('delete', $product)` |
| POST | `/admin/products/{product}/bulk-prices` | admin.products.bulk-prices.store | `authorize('update', $product)` |
| DELETE | `/admin/products/{product}/bulk-prices/{bulkPrice}` | admin.products.bulk-prices.destroy | `authorize('update', $product)`, scoped binding + ownership `abort_unless` |
| GET | `/admin/quotes` | admin.quotes.index | `authorize('viewAny', Quote)` |
| GET | `/admin/quotes/{quote}` | admin.quotes.show | `authorize('view', $quote)` |
| PATCH | `/admin/quotes/{quote}` | admin.quotes.update | `authorize('update', $quote)`: admin **and** quote open; transition validated by `QuoteStatus::allowedTransitions()` |

## Code excerpts

**Role middleware** ([`app/Http/Middleware/EnsureUserHasRole.php`](../app/Http/Middleware/EnsureUserHasRole.php)):

```php
if (! $user instanceof User) {
    abort(Response::HTTP_UNAUTHORIZED);
}
if ($roles === [] || ! $user->hasRole(...$roles)) {
    abort(Response::HTTP_FORBIDDEN, 'Your account role does not have access to this area.');
}
```

**In-handler guard** ([`app/Http/Controllers/Admin/ProductController.php`](../app/Http/Controllers/Admin/ProductController.php)):

```php
public function destroy(Product $product): RedirectResponse
{
    $this->authorize('delete', $product);
    // ...
}
```

**Ownership** ([`app/Policies/QuotePolicy.php`](../app/Policies/QuotePolicy.php)):

```php
public function view(User $user, Quote $quote): bool
{
    return $user->isAdmin() || $quote->user_id === $user->id;
}

public function delete(User $user, Quote $quote): bool
{
    return $quote->user_id === $user->id && $quote->status === QuoteStatus::Pending;
}
```

**Public lookup** ([`app/Http/Controllers/QuoteLookupController.php`](../app/Http/Controllers/QuoteLookupController.php)). Parameterised query-builder calls only, both factors required, and only status columns selected:

```php
$quote = Quote::query()
    ->where('reference', $request->validated('reference'))
    ->whereHas('user', fn ($q) => $q->where('email', $email))
    ->first(['id', 'reference', 'status', 'created_at', 'updated_at']);
```

## Mass-assignment protection

| Model | Not fillable | Set by |
|---|---|---|
| `User` | `role` | `$user->role = UserRole::from(...)` after validation against `UserRole::registrable()` |
| `Quote` | `user_id`, `status`, `reference` | relationship, admin action, `Quote::newReference()` |
| `BulkPrice` | `product_id` | `$product->bulkPrices()->create()` |

## Tests proving the rules

[`tests/Feature/AuthorizationTest.php`](../tests/Feature/AuthorizationTest.php), [`QuoteLookupTest.php`](../tests/Feature/QuoteLookupTest.php) and [`DemoLoginTest.php`](../tests/Feature/DemoLoginTest.php) cover these cases:
- Guests are redirected from admin and buyer mutations.
- Buyers get 403 on admin routes and cannot create products.
- Buyers cannot read or withdraw another buyer's quote.
- `status` and `user_id` cannot be injected.
- Invalid status transitions are rejected.
- Registration cannot self-assign admin.
- Lookup needs both factors, discloses no details, and is rate limited per reference and per IP.
- Demo login is off by default and can never reach admin.
