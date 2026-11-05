<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_public(): void
    {
        $product = Product::factory()->withBulkTiers()->create();

        $this->get('/')->assertOk()->assertSee($product->name);
        $this->get(route('products.show', $product))->assertOk();
    }

    public function test_guests_cannot_reach_admin_or_quotes(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
        $this->post(route('quotes.store'))->assertRedirect(route('login'));
    }

    public function test_business_buyers_cannot_manage_products(): void
    {
        $buyer = User::factory()->wholesaler()->create();

        $this->actingAs($buyer)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($buyer)->post(route('admin.products.store'), [
            'name' => 'Hack', 'sku' => 'HACK-1', 'base_price' => 1, 'stock' => 1,
        ])->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'HACK-1']);
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Pallet Jack 2.5t',
            'sku' => 'plt-jck-25',
            'base_price' => '349.00',
            'stock' => 40,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['sku' => 'PLT-JCK-25', 'slug' => 'pallet-jack-25t']);
    }

    public function test_overlapping_price_tiers_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->withBulkTiers()->create(); // 50-249, 250-999, 1000+

        $this->actingAs($admin)
            ->post(route('admin.products.bulk-prices.store', $product), [
                'min_quantity' => 200, 'max_quantity' => 300, 'price_per_unit' => 1.00,
            ])
            ->assertSessionHasErrors('min_quantity');
    }

    public function test_unverified_users_are_sent_to_verification(): void
    {
        $user = User::factory()->retailer()->unverified()->create();

        $this->actingAs($user)->get(route('quotes.index'))->assertRedirect(route('verification.notice'));
    }

    public function test_buyer_can_submit_a_quote(): void
    {
        $buyer = User::factory()->distributor()->create();
        $product = Product::factory()->create();

        $this->actingAs($buyer)->post(route('quotes.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5000, 'notes' => 'Palletised'],
                ['product_id' => '', 'quantity' => ''],
            ],
            'target_price' => '12000.00',
            'message' => 'Quarterly order',
        ])->assertRedirect();

        $quote = Quote::sole();
        $this->assertSame($buyer->id, $quote->user_id);
        $this->assertSame(QuoteStatus::Pending, $quote->status);
        $this->assertSame([['product_id' => $product->id, 'quantity' => 5000, 'notes' => 'Palletised']], $quote->product_details_json);
    }

    public function test_status_and_user_id_cannot_be_mass_assigned_by_buyers(): void
    {
        $buyer = User::factory()->retailer()->create();
        $other = User::factory()->retailer()->create();
        $product = Product::factory()->create();

        $this->actingAs($buyer)->post(route('quotes.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 10]],
            'status' => 'approved',
            'user_id' => $other->id,
        ]);

        $quote = Quote::sole();
        $this->assertSame($buyer->id, $quote->user_id);
        $this->assertSame(QuoteStatus::Pending, $quote->status);
    }

    public function test_buyers_cannot_view_other_buyers_quotes(): void
    {
        $quote = Quote::factory()->create();
        $intruder = User::factory()->retailer()->create();

        $this->actingAs($intruder)->get(route('quotes.show', $quote))->assertForbidden();
        $this->actingAs($intruder)->delete(route('quotes.destroy', $quote))->assertForbidden();
    }

    public function test_admin_cannot_apply_invalid_status_transition(): void
    {
        $admin = User::factory()->admin()->create();
        $quote = Quote::factory()->status(QuoteStatus::UnderReview)->create();

        $this->actingAs($admin)
            ->patch(route('admin.quotes.update', $quote), ['status' => 'pending'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->patch(route('admin.quotes.update', $quote), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertSame(QuoteStatus::Approved, $quote->fresh()->status);
    }

    public function test_registration_cannot_self_assign_admin(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Mallory',
            'email' => 'mallory@example.com',
            'password' => 'test-password-not-real-A1!',
            'password_confirmation' => 'test-password-not-real-A1!',
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'mallory@example.com']);
    }
}
