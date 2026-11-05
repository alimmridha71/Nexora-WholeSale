<?php

/*
|--------------------------------------------------------------------------
| Nexora Wholesale application settings
|--------------------------------------------------------------------------
|
| The single place for app-specific configuration. Every value comes from
| the environment. Secrets have NO fallback: if one is missing the value is
| null and the code that needs it fails fast with a clear error.
|
*/

return [

    // Public URL of the hosted demo (footer, README).
    'demo_url' => env('NEXORA_DEMO_URL'),

    // ISO 4217 currency code used for all displayed prices.
    'currency' => env('NEXORA_CURRENCY', 'USD'),

    // Pending/under-review quotes older than this are expired by
    // the scheduled `nexora:expire-quotes` command.
    'quote_expiry_days' => (int) env('NEXORA_QUOTE_EXPIRY_DAYS', 30),

    'catalog' => [
        // Products per catalog page (4 full rows on wide screens).
        'per_page' => 16,
    ],

    'quotes' => [
        // Upper bound of line items accepted in one quote request.
        'max_items' => 50,
        // Empty rows rendered on the quote request form.
        'form_rows' => 5,
        // Prefix of the random, non-sequential public quote reference.
        'reference_prefix' => 'NXQ',
    ],

    // Product image uploads. Disable on hosts without persistent disk (Vercel).
    'uploads_enabled' => (bool) env('NEXORA_UPLOADS_ENABLED', true),

    // Shared secret for the HTTP cron endpoint (Vercel Cron). Endpoint is
    // disabled (404) while this is empty. No default on purpose.
    'cron_secret' => env('CRON_SECRET'),

    // Rate limits (requests per minute).
    'rate_limits' => [
        'login' => 5,
        'register' => 6,
        'demo_login' => 10,
        'quote_store' => 20,
        'quote_lookup_per_ip' => 60,
        'quote_lookup_per_reference' => 5,
    ],

    // Config keys (=> env variable that feeds them) that MUST be set before
    // the app serves traffic. Checked in AppServiceProvider::boot().
    'required_config' => [
        'app.key' => 'APP_KEY',
        'app.url' => 'APP_URL',
    ],

    /*
    | One-click demo accounts for the public live demo. Business-buyer roles
    | only (never admin), so no password needs to be published anywhere.
    | Disabled unless NEXORA_DEMO_MODE=true.
    */
    'demo' => [
        'enabled' => (bool) env('NEXORA_DEMO_MODE', false),
        'accounts' => [
            'retailer' => ['name' => 'Harbor Street Retail', 'email' => 'demo-retailer@nexora.test'],
            'wholesaler' => ['name' => 'Atlas Wholesale Supply', 'email' => 'demo-wholesaler@nexora.test'],
            'distributor' => ['name' => 'Meridian Distribution Co.', 'email' => 'demo-distributor@nexora.test'],
        ],
    ],

    // Seeder credentials. No defaults on purpose (see UserSeeder).
    'seed' => [
        'admin_email' => env('SEED_ADMIN_EMAIL'),
        'admin_password' => env('SEED_ADMIN_PASSWORD'),
    ],

];
