<?php

use App\Actions\ExpireStaleQuotes;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/*
| Create (or promote) an administrator interactively. The password is read
| from a hidden prompt and never passed on the command line or stored in code.
*/
Artisan::command('nexora:create-admin {email?} {--name=Administrator}', function (?string $email = null) {
    $email = strtolower(trim($email ?? text('Admin e-mail', required: true)));
    $secret = password('Password (min 12 chars, mixed case, number, symbol)', required: true);

    $validator = Validator::make(
        ['email' => $email, 'password' => $secret],
        ['email' => ['required', 'email', 'max:255'], 'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()]],
    );

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    $user = User::firstOrNew(['email' => $email]);
    $user->name = $user->exists ? $user->name : (string) $this->option('name');
    $user->password = $secret;
    $user->role = UserRole::Admin;
    $user->email_verified_at ??= now();
    $user->save();

    $this->info("Administrator [{$email}] is ready.");

    return 0;
})->purpose('Create or promote a Nexora Wholesale administrator');

/*
| Expire stale open quotes (pending / under review) older than the configured age.
*/
Artisan::command('nexora:expire-quotes', function (ExpireStaleQuotes $expire) {
    $count = $expire();

    $this->info("Expired {$count} quote(s) older than ".config('nexora.quote_expiry_days').' days.');
})->purpose('Mark stale open quotes as expired');

Schedule::command('nexora:expire-quotes')->dailyAt('02:00')->withoutOverlapping();
