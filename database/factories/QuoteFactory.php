<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->wholesaler(),
            'product_details_json' => fn () => $this->lineItems(),
            'target_price' => fake()->optional(0.7)->randomFloat(2, 1000, 250000),
            'status' => QuoteStatus::Pending->value,
            'message' => fake()->optional(0.8)->sentence(14),
        ];
    }

    public function status(QuoteStatus $status): static
    {
        return $this->state(fn () => ['status' => $status->value]);
    }

    /**
     * @return list<array{product_id: int, quantity: int, notes: string|null}>
     */
    private function lineItems(): array
    {
        $ids = Product::query()->inRandomOrder()->limit(fake()->numberBetween(1, 3))->pluck('id');

        if ($ids->isEmpty()) {
            $ids = collect([Product::factory()->create()->id]);
        }

        return $ids->map(fn (int $id) => [
            'product_id' => $id,
            'quantity' => fake()->numberBetween(500, 50000),
            'notes' => fake()->optional(0.4)->sentence(6),
        ])->values()->all();
    }
}
