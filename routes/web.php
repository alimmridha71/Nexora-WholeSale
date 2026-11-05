<?php

use App\Http\Controllers\Admin\BulkPriceController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\QuoteController as AdminQuoteController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteLookupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security model (full matrix: docs/authorization-matrix.md)
|--------------------------------------------------------------------------
| - All routes use the "web" group: sessions + CSRF (ValidateCsrfToken).
| - Mutating routes require auth + verified + a role: guard, AND every
|   controller action calls $this->authorize() against a policy.
| - Public lookups are throttled and require random reference + e-mail.
*/

// ---- Public catalog -------------------------------------------------------
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [CatalogController::class, 'show'])->name('products.show');

// ---- Public quote status lookup (reference + e-mail, rate limited) -------
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/quote-status', [QuoteLookupController::class, 'create'])->name('quote-status.create');
    Route::post('/quote-status', [QuoteLookupController::class, 'show'])
        ->middleware('throttle:quote-lookup')
        ->name('quote-status.show');
});

// ---- Scheduled jobs for serverless hosts (Bearer CRON_SECRET) -----------
Route::get('/cron/expire-quotes', [CronController::class, 'expireQuotes'])
    ->middleware('throttle:10,1')
    ->name('cron.expire-quotes');

// ---- Guest authentication -------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:register')->name('register.store');

    // Public demo only (NEXORA_DEMO_MODE=true); buyer roles only.
    Route::post('/demo-login/{role}', [DemoLoginController::class, 'store'])
        ->whereIn('role', ['retailer', 'wholesaler', 'distributor'])
        ->middleware('throttle:demo-login')
        ->name('demo-login');
});

// ---- Authenticated (e-mail verification flow) ----------------------------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// ---- Authenticated + verified --------------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Business buyers: retailer, wholesaler, distributor.
    Route::middleware('role:retailer,wholesaler,distributor')->group(function () {
        Route::resource('quotes', QuoteController::class)->only(['index', 'create', 'show', 'destroy']);
        Route::post('quotes', [QuoteController::class, 'store'])->middleware('throttle:quote-store')->name('quotes.store');
    });

    // Administration.
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', AdminProductController::class)->except('show');

        Route::post('products/{product}/bulk-prices', [BulkPriceController::class, 'store'])
            ->name('products.bulk-prices.store');
        Route::delete('products/{product}/bulk-prices/{bulkPrice}', [BulkPriceController::class, 'destroy'])
            ->scopeBindings()
            ->name('products.bulk-prices.destroy');

        Route::resource('quotes', AdminQuoteController::class)->only(['index', 'show', 'update']);
    });
});
