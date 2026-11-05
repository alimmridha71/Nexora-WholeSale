<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CronAndUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cron_endpoint_is_disabled_without_a_secret(): void
    {
        config(['nexora.cron_secret' => null]);

        $this->get(route('cron.expire-quotes'))->assertNotFound();
    }

    public function test_cron_endpoint_rejects_a_wrong_token(): void
    {
        config(['nexora.cron_secret' => 'test-cron-secret-not-real']);

        $this->get(route('cron.expire-quotes'))->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer wrong')->get(route('cron.expire-quotes'))->assertUnauthorized();
    }

    public function test_cron_endpoint_expires_only_stale_open_quotes(): void
    {
        config(['nexora.cron_secret' => 'test-cron-secret-not-real', 'nexora.quote_expiry_days' => 30]);

        $stale = Quote::factory()->create(['created_at' => now()->subDays(40)]);
        $fresh = Quote::factory()->create(['created_at' => now()->subDays(5)]);
        $approved = Quote::factory()->status(QuoteStatus::Approved)->create(['created_at' => now()->subDays(40)]);

        $this->withHeader('Authorization', 'Bearer test-cron-secret-not-real')
            ->get(route('cron.expire-quotes'))
            ->assertOk()
            ->assertJson(['expired' => 1]);

        $this->assertSame(QuoteStatus::Expired, $stale->fresh()->status);
        $this->assertSame(QuoteStatus::Pending, $fresh->fresh()->status);
        $this->assertSame(QuoteStatus::Approved, $approved->fresh()->status);
    }

    public function test_image_uploads_are_rejected_when_disabled(): void
    {
        config(['nexora.uploads_enabled' => false]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Pallet Wrap',
            'sku' => 'PLT-WRP-1',
            'base_price' => '10.00',
            'stock' => 5,
            'image' => UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', ['sku' => 'PLT-WRP-1']);
    }
}
