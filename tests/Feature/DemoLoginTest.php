<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_login_is_disabled_by_default(): void
    {
        config(['nexora.demo.enabled' => false]);
        $this->seed(UserSeeder::class);

        $this->post(route('demo-login', 'wholesaler'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_demo_login_signs_in_a_buyer_when_enabled(): void
    {
        config(['nexora.demo.enabled' => true]);
        $this->seed(UserSeeder::class);

        $this->post(route('demo-login', 'distributor'))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'demo-distributor@nexora.test')->sole());
    }

    public function test_demo_login_can_never_reach_admin(): void
    {
        config(['nexora.demo.enabled' => true]);

        $this->post('/demo-login/admin')->assertNotFound();
        $this->assertGuest();
    }

    public function test_seeder_never_creates_an_admin_without_env_credentials(): void
    {
        config(['nexora.seed.admin_email' => null, 'nexora.seed.admin_password' => null]);
        $this->seed(UserSeeder::class);

        $this->assertDatabaseMissing('users', ['role' => 'admin']);
    }
}
