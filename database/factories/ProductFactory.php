<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->paragraph(),
            'image' => null,
            'base_price' => fake()->randomFloat(2, 1, 500),
            'stock' => fake()->numberBetween(0, 50000),
            'sku' => strtoupper(Str::random(3)).'-'.fake()->unique()->numerify('#####'),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    /**
     * Adds three non-overlapping tiers at 5%, 12% and 20% off base price.
     */
    public function withBulkTiers(): static
    {
        return $this->afterCreating(function (Product $product): void {
            $base = (float) $product->base_price;

            $product->bulkPrices()->createMany([
                ['min_quantity' => 50, 'max_quantity' => 249, 'price_per_unit' => round($base * 0.95, 2)],
                ['min_quantity' => 250, 'max_quantity' => 999, 'price_per_unit' => round($base * 0.88, 2)],
                ['min_quantity' => 1000, 'max_quantity' => null, 'price_per_unit' => round($base * 0.80, 2)],
            ]);
        });
    }
}
