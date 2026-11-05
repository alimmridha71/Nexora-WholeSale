<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the admin and the demo buyer accounts. Safe to run repeatedly.
 *
 * - Admin: created ONLY when SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD are set
 *   in the environment. Nothing is hard-coded; otherwise use
 *   `php artisan nexora:create-admin`.
 * - Demo buyers: get a fresh random password that is never printed or
 *   stored anywhere. They are reachable only through the one-click demo
 *   login when NEXORA_DEMO_MODE=true.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedDemoBuyers();

        // A few extra random buyers for realistic admin listings (first run only).
        $missing = 6 - User::query()->where('email', 'like', '%@example.%')->count();

        if ($missing > 0) {
            User::factory()->count($missing)->create();
        }
    }

    private function seedAdmin(): void
    {
        $email = config('nexora.seed.admin_email');
        $password = config('nexora.seed.admin_password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('Admin not seeded: set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD, or run `php artisan nexora:create-admin`.');

            return;
        }

        $admin = User::firstOrNew(['email' => strtolower((string) $email)]);
        $admin->name = $admin->exists ? $admin->name : 'Nexora Administrator';
        $admin->password = (string) $password;
        $admin->role = UserRole::Admin;
        $admin->email_verified_at ??= now();
        $admin->save();
    }

    private function seedDemoBuyers(): void
    {
        foreach (config('nexora.demo.accounts') as $role => $account) {
            $user = User::firstOrNew(['email' => $account['email']]);
            $user->name = $account['name'];
            $user->role = UserRole::from($role);
            $user->email_verified_at ??= now();

            if (! $user->exists) {
                // Unknown random secret: password login is impossible.
                $user->password = Str::password(40);
            }

            $user->save();
        }
    }
}
