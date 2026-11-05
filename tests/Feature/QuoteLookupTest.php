<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_references_are_random_and_not_sequential(): void
    {
        $first = Quote::factory()->create();
        $second = Quote::factory()->create();

        $this->assertMatchesRegularExpression('/^NXQ-[0-9A-HJKMNP-TV-Z]{8}$/', $first->reference);
        $this->assertNotSame($first->reference, $second->reference);
        $this->assertStringNotContainsString((string) $first->id, substr($first->reference, 4));
    }

    public function test_lookup_requires_matching_reference_and_email(): void
    {
        $owner = User::factory()->wholesaler()->create(['email' => 'buyer@example.com']);
        $quote = Quote::factory()->for($owner)->create();

        $this->post(route('quote-status.show'), [
            'reference' => strtolower($quote->reference),
            'email' => 'BUYER@example.com',
        ])->assertOk()->assertSee($quote->reference)->assertSee('Pending');

        $this->from(route('quote-status.create'))->post(route('quote-status.show'), [
            'reference' => $quote->reference,
            'email' => 'someone-else@example.com',
        ])->assertRedirect(route('quote-status.create'))->assertSessionHasErrors('reference');
    }

    public function test_lookup_does_not_disclose_quote_details(): void
    {
        $owner = User::factory()->wholesaler()->create(['email' => 'buyer@example.com']);
        $quote = Quote::factory()->for($owner)->create(['message' => 'Confidential pricing note']);

        $this->post(route('quote-status.show'), [
            'reference' => $quote->reference,
            'email' => 'buyer@example.com',
        ])->assertOk()->assertDontSee('Confidential pricing note');
    }

    public function test_a_single_reference_is_rate_limited(): void
    {
        $payload = ['reference' => 'NXQ-ABCD2345', 'email' => 'x@example.com'];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('quote-status.show'), $payload)->assertRedirect();
        }

        $this->post(route('quote-status.show'), $payload)->assertStatus(429);
    }

    public function test_lookups_are_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->post(route('quote-status.show'), [
                'reference' => sprintf('NXQ-%08d', $i),
                'email' => 'x@example.com',
            ]);
        }

        $this->post(route('quote-status.show'), ['reference' => 'NXQ-ZZZZZZZZ', 'email' => 'x@example.com'])
            ->assertStatus(429);
    }
}
