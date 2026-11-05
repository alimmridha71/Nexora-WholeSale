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

    public function test_max_bulk_saving_percent(): void
    {
        // base 10.00, best tier 7.00 => 30%
        $this->assertSame(30, $this->productWithTiers()->load('bulkPrices')->maxBulkSavingPercent());
        $this->assertSame(0, Product::factory()->create()->load('bulkPrices')->maxBulkSavingPercent());
    }

    public function test_home_page_shows_hero_stats_and_savings_badge(): void
    {
        $this->productWithTiers();

        $this->get('/')
            ->assertOk()
            ->assertSee('Business supplies, priced by volume.')
            ->assertSee('Save up to 30% at volume')
            ->assertSee('Largest bulk discount')
            ->assertSee('10+ units');
    }

    public function test_bundled_images_resolve_to_public_assets(): void
    {
        $bundled = Product::factory()->create(['image' => 'images/products/blt-m8-100.webp']);
        $uploaded = Product::factory()->create(['image' => 'products/upload.webp']);

        $this->assertSame(asset('images/products/blt-m8-100.webp'), $bundled->imageUrl());
        $this->assertStringContainsString('/storage/products/upload.webp', (string) $uploaded->imageUrl());
        $this->assertNull(Product::factory()->create(['image' => null])->imageUrl());
    }

    public function test_every_seeded_product_has_an_existing_photo(): void
    {
        $this->seed(\Database\Seeders\ProductSeeder::class);

        $this->assertSame(10, Product::count());

        foreach (Product::all() as $product) {
            $this->assertNotNull($product->image, "{$product->sku} has no image");
            $this->assertFileExists(public_path($product->image), "{$product->sku} photo file is missing");
        }
    }

    public function test_deleting_a_product_cascades_to_its_tiers(): void
    {
        $product = $this->productWithTiers();
        $product->delete();

        $this->assertDatabaseCount('bulk_prices', 0);
    }
}
