<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Nexora Wholesale demo data set.
     *
     *   php artisan migrate --seed
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            QuoteSeeder::class,
        ]);
    }
}
