-- =====================================================================
-- Nexora Wholesale - demo data (PostgreSQL 15+ / Supabase)
-- ---------------------------------------------------------------------
-- Load AFTER database/schema.sql (Supabase SQL Editor or psql -f).
-- PHP equivalent: php artisan db:seed (database/seeders/DatabaseSeeder.php)
--
-- SECURITY
--   * NO admin account is created here. Create the admin from environment
--     variables (SEED_ADMIN_EMAIL + SEED_ADMIN_PASSWORD with db:seed) or
--     interactively: php artisan nexora:create-admin you@company.com
--   * The three demo BUYER accounts are password-LOCKED: each password
--     column holds random bcrypt-formatted data that matches no password.
--     They are reachable only via the one-click demo sign-in, which works
--     only when NEXORA_DEMO_MODE=true.
-- =====================================================================

BEGIN;

TRUNCATE TABLE quotes, bulk_prices, products, sessions, users RESTART IDENTITY CASCADE;

-- ---------------------------------------------------------------------
-- Demo business buyers (one per role, password-locked)
-- ---------------------------------------------------------------------
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) VALUES
    (1, 'Harbor Street Retail',      'demo-retailer@nexora.test',    NOW(), '$2y$12$D7l0LRkQ2IBw164uUyt4NxyqEW9HFQlpe.7fWU9rzjzIr0Jylp1Uy', 'retailer',    NOW(), NOW()),
    (2, 'Atlas Wholesale Supply',    'demo-wholesaler@nexora.test',  NOW(), '$2y$12$iGB0Je/j2ktAYwpcr3QuIKZy1pwa7WRvY2xAOJcQgSofZE9fVy/HM', 'wholesaler',  NOW(), NOW()),
    (3, 'Meridian Distribution Co.', 'demo-distributor@nexora.test', NOW(), '$2y$12$xQW.15x.2DxaLtuBq1Z8n2CK.g8whuCwIA.V6maycX9kiH4XpAaPI', 'distributor', NOW(), NOW());

-- ---------------------------------------------------------------------
-- Products
-- ---------------------------------------------------------------------
INSERT INTO products (id, name, slug, description, image, base_price, stock, sku, created_at, updated_at) VALUES
    (1,  'Industrial Steel Bolts M8 (Box of 100)',     'industrial-steel-bolts-m8-box-of-100',    'Zinc-plated grade 8.8 hex bolts for structural assembly.', NULL, 18.50, 12000, 'BLT-M8-100',   NOW(), NOW()),
    (2,  'Recycled Kraft Shipping Boxes 12x12x12',     'recycled-kraft-shipping-boxes-12x12x12',  'Double-wall corrugated boxes, 100% recycled fiber.',       NULL,  1.85, 85000, 'BOX-KR-1212',  NOW(), NOW()),
    (3,  'Nitrile Gloves, Powder-Free (Case of 1000)', 'nitrile-gloves-powder-free-case-of-1000', 'Food-safe blue nitrile gloves, size L.',                   NULL, 64.00,  3400, 'GLV-NTR-1000', NOW(), NOW()),
    (4,  'LED Panel Light 600x600 40W',                'led-panel-light-600x600-40w',             '4000K neutral white, 4400 lm, flicker-free driver.',       NULL, 27.90,  5200, 'LED-PNL-6040', NOW(), NOW()),
    (5,  'Organic Arabica Coffee Beans 1kg',           'organic-arabica-coffee-beans-1kg',        'Single-origin Colombian, medium roast, whole bean.',       NULL, 21.00,  7800, 'COF-ARB-1KG',  NOW(), NOW()),
    (6,  'Stretch Wrap Film 500mm x 300m',             'stretch-wrap-film-500mm-x-300m',          '23-micron cast stretch film for pallet wrapping.',         NULL, 12.40, 15000, 'PKG-STR-500',  NOW(), NOW()),
    (7,  'Stainless Steel Water Bottle 750ml',         'stainless-steel-water-bottle-750ml',      'Vacuum-insulated, BPA-free, laser-engravable.',            NULL,  8.95, 22000, 'BTL-SS-750',   NOW(), NOW()),
    (8,  'A4 Copy Paper 80gsm (Box of 5 Reams)',       'a4-copy-paper-80gsm-box-of-5-reams',      'FSC-certified bright white multipurpose paper.',           NULL, 24.75,  9600, 'PPR-A4-80',    NOW(), NOW()),
    (9,  'Cotton Crew T-Shirt Blank, Assorted',        'cotton-crew-t-shirt-blank-assorted',      '180gsm ring-spun cotton, sizes S-XXL.',                    NULL,  3.60, 41000, 'TEE-CTN-BLK',  NOW(), NOW()),
    (10, 'Lithium AA Batteries (Pack of 24)',          'lithium-aa-batteries-pack-of-24',         'Long-life 1.5V lithium cells, 20-year shelf life.',        NULL, 29.99,    60, 'BAT-AA-24',    NOW(), NOW());

-- ---------------------------------------------------------------------
-- Bulk price tiers (non-overlapping; NULL max = open-ended)
-- ---------------------------------------------------------------------
INSERT INTO bulk_prices (product_id, min_quantity, max_quantity, price_per_unit, created_at, updated_at) VALUES
    (1,   10,   49, 17.25, NOW(), NOW()), (1,   50,  199, 15.90, NOW(), NOW()), (1,  200, NULL, 14.20, NOW(), NOW()),
    (2,  100,  999,  1.62, NOW(), NOW()), (2, 1000, 4999,  1.41, NOW(), NOW()), (2, 5000, NULL,  1.19, NOW(), NOW()),
    (3,    5,   19, 59.50, NOW(), NOW()), (3,   20,   99, 55.00, NOW(), NOW()), (3,  100, NULL, 49.75, NOW(), NOW()),
    (4,   20,   99, 25.40, NOW(), NOW()), (4,  100,  499, 22.80, NOW(), NOW()), (4,  500, NULL, 20.10, NOW(), NOW()),
    (5,   24,  119, 19.20, NOW(), NOW()), (5,  120,  599, 17.60, NOW(), NOW()), (5,  600, NULL, 15.90, NOW(), NOW()),
    (6,   36,  143, 11.30, NOW(), NOW()), (6,  144,  575, 10.25, NOW(), NOW()), (6,  576, NULL,  9.10, NOW(), NOW()),
    (7,   50,  249,  8.20, NOW(), NOW()), (7,  250,  999,  7.45, NOW(), NOW()), (7, 1000, NULL,  6.60, NOW(), NOW()),
    (8,   10,   49, 23.10, NOW(), NOW()), (8,   50,  249, 21.40, NOW(), NOW()), (8,  250, NULL, 19.80, NOW(), NOW()),
    (9,  100,  499,  3.25, NOW(), NOW()), (9,  500, 2499,  2.90, NOW(), NOW()), (9, 2500, NULL,  2.55, NOW(), NOW()),
    (10,  12,   47, 27.50, NOW(), NOW()), (10,  48, NULL, 24.99, NOW(), NOW());

-- ---------------------------------------------------------------------
-- Quotes (every status represented; random non-sequential references)
-- ---------------------------------------------------------------------
INSERT INTO quotes (reference, user_id, product_details_json, target_price, status, message, created_at, updated_at) VALUES
    ('NXQ-Z04F681D', 1, '[{"product_id": 7, "quantity": 3000, "notes": "Custom logo engraving"}]',
        18500.00, 'pending', 'Seasonal promotion order, delivery needed within 3 weeks.', NOW() - INTERVAL '2 days', NOW() - INTERVAL '2 days'),
    ('NXQ-XX92HDK1', 2, '[{"product_id": 2, "quantity": 20000, "notes": null}, {"product_id": 6, "quantity": 1200, "notes": "Palletised"}]',
        33000.00, 'under_review', 'Recurring monthly volume for our three warehouses.', NOW() - INTERVAL '5 days', NOW() - INTERVAL '1 day'),
    ('NXQ-GN6HV7S0', 3, '[{"product_id": 3, "quantity": 800, "notes": "Mixed sizes M/L/XL"}]',
        36000.00, 'approved', 'Net-30 payment terms requested.', NOW() - INTERVAL '12 days', NOW() - INTERVAL '8 days'),
    ('NXQ-EFREEH5P', 2, '[{"product_id": 10, "quantity": 5000, "notes": null}]',
        95000.00, 'rejected', 'Need full quantity in a single shipment.', NOW() - INTERVAL '15 days', NOW() - INTERVAL '10 days'),
    ('NXQ-8E729TK2', 1, '[{"product_id": 5, "quantity": 900, "notes": "Whole bean only"}]',
        NULL, 'expired', NULL, NOW() - INTERVAL '45 days', NOW() - INTERVAL '14 days'),
    ('NXQ-XK72R9T4', 3, '[{"product_id": 9, "quantity": 15000, "notes": "60% M, 40% L"}, {"product_id": 1, "quantity": 400, "notes": null}]',
        42000.00, 'pending', 'Can you match last quarter pricing?', NOW() - INTERVAL '1 day', NOW() - INTERVAL '1 day'),
    ('NXQ-AXD7JP2Q', 2, '[{"product_id": 4, "quantity": 2500, "notes": "DALI dimmable if available"}]',
        48000.00, 'under_review', 'Office fit-out project, phased delivery.', NOW() - INTERVAL '3 days', NOW() - INTERVAL '3 days'),
    ('NXQ-TYYHE480', 1, '[{"product_id": 8, "quantity": 600, "notes": null}]',
        11500.00, 'approved', NULL, NOW() - INTERVAL '20 days', NOW() - INTERVAL '18 days');

-- Move identity counters past the explicit ids above.
SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT MAX(id) FROM users));
SELECT setval(pg_get_serial_sequence('products', 'id'), (SELECT MAX(id) FROM products));

COMMIT;
