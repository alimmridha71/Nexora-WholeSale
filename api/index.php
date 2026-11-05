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

// TEMPORARY deployment diagnostics: booleans and names only, never values.
if (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/__nexora_diag') {
    header('Content-Type: application/json');
    $root = dirname(__DIR__);
    $names = ['APP_KEY', 'APP_URL', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'CRON_SECRET', 'NEXORA_DEMO_MODE'];
    $result = [
        'php' => PHP_VERSION,
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'intl' => extension_loaded('intl'),
        'vendor_autoload' => is_file($root.'/vendor/autoload.php'),
        'build_manifest' => is_file($root.'/public/build/manifest.json'),
        'bootstrap_cache_writable' => is_writable($root.'/bootstrap/cache'),
        'tmp_writable' => is_writable('/tmp/laravel/views'),
        'env_set' => array_combine($names, array_map(fn ($n) => getenv($n) !== false && getenv($n) !== '', $names)),
    ];
    try {
        require $root.'/vendor/autoload.php';
        $app = require $root.'/bootstrap/app.php';
        $app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
        $result['laravel_boot'] = 'ok';
        $app['db']->connection()->getPdo();
        $result['database'] = 'ok';
    } catch (Throwable $e) {
        $result['error_class'] = get_class($e);
        $result['error_message'] = preg_replace('/(password|pwd|secret|key)[=:]\S+/i', '$1=***', $e->getMessage());
        $result['error_at'] = basename($e->getFile()).':'.$e->getLine();
    }
    echo json_encode($result, JSON_PRETTY_PRINT);

    return;
}

require __DIR__.'/../public/index.php';
