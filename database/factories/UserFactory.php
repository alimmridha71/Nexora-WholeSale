<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Hashed once per process. A random secret, never a known password.
     */
    protected static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make(Str::password(32)),
            'role' => fake()->randomElement(UserRole::registrable()),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function role(UserRole $role): static
    {
        return $this->state(fn () => ['role' => $role->value]);
    }

    public function admin(): static
    {
        return $this->role(UserRole::Admin);
    }

    public function retailer(): static
    {
        return $this->role(UserRole::Retailer);
    }

    public function wholesaler(): static
    {
        return $this->role(UserRole::Wholesaler);
    }

    public function distributor(): static
    {
        return $this->role(UserRole::Distributor);
    }
}
