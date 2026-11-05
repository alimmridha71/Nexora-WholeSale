<?php

namespace Database\Factories;

use App\Models\BulkPrice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BulkPrice>
 */
class BulkPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->numberBetween(10, 500);

        return [
            'product_id' => Product::factory(),
            'min_quantity' => $min,
            'max_quantity' => $min + fake()->numberBetween(100, 1000),
            'price_per_unit' => fake()->randomFloat(2, 1, 400),
        ];
    }

    public function openEnded(): static
    {
        return $this->state(fn () => ['max_quantity' => null]);
    }
}
