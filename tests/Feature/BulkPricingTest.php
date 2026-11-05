<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkPricingTest extends TestCase
{
    use RefreshDatabase;

    private function productWithTiers(): Product
    {
        $product = Product::factory()->create(['base_price' => 10.00]);

        $product->bulkPrices()->createMany([
            ['min_quantity' => 10, 'max_quantity' => 99, 'price_per_unit' => 9.00],
            ['min_quantity' => 100, 'max_quantity' => 499, 'price_per_unit' => 8.00],
            ['min_quantity' => 500, 'max_quantity' => null, 'price_per_unit' => 7.00],
        ]);

        return $product;
    }

    public function test_base_price_applies_below_the_first_tier(): void
    {
        $this->assertSame('10.00', $this->productWithTiers()->getPriceForQuantity(9));
    }

    public function test_tier_boundaries_resolve_correctly(): void
    {
        $product = $this->productWithTiers();

        $this->assertSame('9.00', $product->getPriceForQuantity(10));
        $this->assertSame('9.00', $product->getPriceForQuantity(99));
        $this->assertSame('8.00', $product->getPriceForQuantity(100));
        $this->assertSame('8.00', $product->getPriceForQuantity(499));
    }

    public function test_open_ended_top_tier_applies_to_any_large_quantity(): void
    {
        $this->assertSame('7.00', $this->productWithTiers()->getPriceForQuantity(1_000_000));
    }

    public function test_eager_loaded_tiers_are_used(): void
    {
        $product = $this->productWithTiers()->fresh()->load('bulkPrices');

        $this->assertSame('8.00', $product->getPriceForQuantity(250));
    }

    public function test_deleting_a_product_cascades_to_its_tiers(): void
    {
        $product = $this->productWithTiers();
        $product->delete();

        $this->assertDatabaseCount('bulk_prices', 0);
    }
}
