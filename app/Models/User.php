<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property UserRole $role
 * @property Carbon|null $email_verified_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * `role` is deliberately NOT mass assignable, so a crafted request can
     * never escalate privileges. Set it explicitly: `$user->role = ...`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'retailer',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Custom B2B quote requests submitted by this user.
     *
     * @return HasMany<Quote, $this>
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function hasRole(UserRole|string ...$roles): bool
    {
        foreach ($roles as $role) {
            $value = $role instanceof UserRole ? $role : UserRole::tryFrom($role);

            if ($value !== null && $this->role === $value) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
