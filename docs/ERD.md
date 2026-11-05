# Entity-relationship diagram

```mermaid
erDiagram
    USERS ||--o{ QUOTES : "submits (ON DELETE RESTRICT)"
    USERS ||--o{ SESSIONS : "has (ON DELETE CASCADE)"
    PRODUCTS ||--o{ BULK_PRICES : "has tiers (ON DELETE CASCADE)"
    QUOTES }o..o{ PRODUCTS : "product_details_json[].product_id (logical)"

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        timestamp email_verified_at
        varchar password "bcrypt hash"
        enum role "admin | retailer | wholesaler | distributor (indexed)"
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }
    PRODUCTS {
        bigint id PK
        varchar name "index (name, base_price)"
        varchar slug UK
        text description
        varchar image
        decimal base_price "12,2"
        int stock "unsigned"
        varchar sku UK
        timestamp created_at
        timestamp updated_at
    }
    BULK_PRICES {
        bigint id PK
        bigint product_id FK "index (product_id, min_quantity, max_quantity)"
        int min_quantity
        int max_quantity "NULL = open-ended"
        decimal price_per_unit "12,2"
        timestamp created_at
        timestamp updated_at
    }
    QUOTES {
        bigint id PK
        varchar reference UK "random, non-sequential"
        bigint user_id FK "index (user_id, status)"
        json product_details_json
        decimal target_price "12,2, nullable"
        enum status "pending | under_review | approved | rejected | expired"
        text message
        timestamp created_at "index (status, created_at)"
        timestamp updated_at
    }
    SESSIONS {
        varchar id PK
        bigint user_id FK "indexed"
        varchar ip_address
        text user_agent
        text payload
        int last_activity "indexed"
    }
```

## Foreign keys

| Column | References | On delete | Index |
|---|---|---|---|
| `bulk_prices.product_id` | `products.id` | CASCADE | leading column of `(product_id, min_quantity, max_quantity)` |
| `quotes.user_id` | `users.id` | RESTRICT | leading column of `(user_id, status)` |
| `sessions.user_id` | `users.id` | CASCADE | `sessions_user_id_index` |

`quotes.product_details_json` refers to products **logically**, as a historical snapshot. It deliberately has no FK, so deleting a product never rewrites or blocks past quotes. Pages show "no longer listed" for such items.

## Row Level Security (Supabase)

RLS is **enabled on every table**, with **no policies**, and all privileges are revoked from `anon` and `authenticated` (migration `2026_11_05_000001`, mirrored in `database/schema.sql`). The Supabase Data API therefore cannot read or write any table with the publishable key. The Laravel application connects as the table owner, which is unaffected by RLS.

## Domain rules enforced outside the schema

- **Tier overlap**: no two tiers of a product may overlap (`StoreBulkPriceRequest::after()`).
- **Status transitions**: `QuoteStatus::allowedTransitions()`. Approved, rejected and expired are terminal.
- **Effective price**: `Product::getPriceForQuantity()` uses the matching tier with the highest `min_quantity`, otherwise `base_price`.
