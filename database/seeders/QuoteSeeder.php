<?php

namespace Database\Seeders;

use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo quotes across every status, owned by business buyers.
 * Runs only when the quotes table is empty (safe to re-run).
 */
class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        if (Quote::exists()) {
            return;
        }

        $buyers = User::query()->where('role', '!=', UserRole::Admin->value)->get();

        if ($buyers->isEmpty()) {
            return;
        }

        foreach (QuoteStatus::cases() as $status) {
            foreach ($buyers->random(min(3, $buyers->count())) as $buyer) {
                Quote::factory()
                    ->for($buyer)
                    ->status($status)
                    ->create([
                        'created_at' => $status === QuoteStatus::Expired ? now()->subDays(45) : now()->subDays(random_int(0, 20)),
                    ]);
            }
        }
    }
}
