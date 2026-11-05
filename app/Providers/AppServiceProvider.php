<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Artisan commands that must work before the environment is fully
     * configured (e.g. generating APP_KEY or Composer's package discovery).
     * `test` only launches PHPUnit, which then loads phpunit.xml and boots
     * the app again with this check active.
     */
    private const BOOTSTRAP_COMMANDS = [
        'key:generate', 'package:discover', 'list', 'help', 'about', 'env', 'test',
        'config:clear', 'cache:clear', 'optimize:clear', 'view:clear', 'route:clear',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->ensureRequiredEnvironment();

        // Throw on lazy loading, silently discarded attributes and missing
        // attributes during development and testing.
        Model::shouldBeStrict(! $this->app->isProduction());

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(8));

        $this->defineGates();
        $this->defineRateLimiters();

        // @money($amount): escaped, currency-aware price output.
        Blade::directive('money', fn (string $expression) => "<?php echo e(\\App\\Support\\Money::format({$expression})); ?>");
    }

    private function defineGates(): void
    {
        Gate::define('access-admin', fn (User $user) => $user->role === UserRole::Admin);
        Gate::define('request-quotes', fn (User $user) => $user->role->isBusinessBuyer());
    }

    private function defineRateLimiters(): void
    {
        $limits = config('nexora.rate_limits');

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute($limits['login'])
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute($limits['register'])->by($request->ip()));

        RateLimiter::for('demo-login', fn (Request $request) => Limit::perMinute($limits['demo_login'])->by($request->ip()));

        RateLimiter::for('quote-store', fn (Request $request) => Limit::perMinute($limits['quote_store'])
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));

        // Public status lookup: per IP AND per reference code, so one
        // reference cannot be brute-forced from many addresses.
        RateLimiter::for('quote-lookup', fn (Request $request) => [
            Limit::perMinute($limits['quote_lookup_per_ip'])->by('ip|'.$request->ip()),
            Limit::perMinute($limits['quote_lookup_per_reference'])
                ->by('ref|'.strtoupper(trim((string) $request->input('reference')))),
        ]);
    }

    /**
     * Fail fast if critical secrets are missing, instead of booting with
     * insecure or broken defaults. Reads config() (not env()) so it also
     * works with `config:cache`.
     */
    private function ensureRequiredEnvironment(): void
    {
        if ($this->app->runningInConsole()
            && in_array($_SERVER['argv'][1] ?? '', self::BOOTSTRAP_COMMANDS, true)) {
            return;
        }

        foreach (config('nexora.required_config', []) as $key => $envName) {
            if (blank(config($key))) {
                throw new RuntimeException("Required environment variable [{$envName}] is not set.");
            }
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver !== 'sqlite'
            && blank(config("database.connections.{$connection}.password"))
            && blank(config("database.connections.{$connection}.url"))) {
            throw new RuntimeException("DB_PASSWORD is not set for the [{$connection}] database connection.");
        }
    }
}
