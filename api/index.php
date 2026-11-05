<?php

/*
|--------------------------------------------------------------------------
| Vercel serverless entry point (runtime: vercel-php, see vercel.json)
|--------------------------------------------------------------------------
|
| Vercel functions have a read-only filesystem except /tmp, so Laravel's
| writable paths are redirected there and logs go to stderr. These are
| NON-SECRET defaults only: they apply when the variable is not already
| set in the Vercel project. Secrets (APP_KEY, DB_PASSWORD, ...) must be
| configured in Vercel > Settings > Environment Variables.
|
*/

$defaults = [
    // Writable locations
    'APP_CONFIG_CACHE' => '/tmp/laravel/config.php',
    'APP_EVENTS_CACHE' => '/tmp/laravel/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/laravel/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/laravel/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/laravel/services.php',
    'VIEW_COMPILED_PATH' => '/tmp/laravel/views',

    // Stateless-friendly drivers (shared across function instances)
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'database',
    'SESSION_ENCRYPT' => 'true',
    'SESSION_SECURE_COOKIE' => 'true',
    'CACHE_STORE' => 'database',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'log',

    // Supabase PostgreSQL (host, user and password come from Vercel env)
    'DB_CONNECTION' => 'pgsql',
    'DB_PORT' => '5432',
    'DB_DATABASE' => 'postgres',
    'DB_SSLMODE' => 'require',

    // Uploaded files would not persist on Vercel's ephemeral disk.
    'NEXORA_UPLOADS_ENABLED' => 'false',
];

foreach ($defaults as $key => $value) {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

if (! is_dir('/tmp/laravel/views')) {
    mkdir('/tmp/laravel/views', 0755, true);
}

require __DIR__.'/../public/index.php';
