<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $reference
 * @property int $user_id
 * @property array<int, array{product_id: int, quantity: int, notes?: string|null}> $product_details_json
 * @property string|null $target_price
 * @property QuoteStatus $status
 * @property string|null $message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    /**
     * Crockford base32 (no I, L, O, U): unambiguous when read aloud or typed.
     */
    private const REFERENCE_ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    private const REFERENCE_LENGTH = 8;

    /**
     * `user_id`, `status` and `reference` are not mass assignable: the owner
     * comes from the authenticated user, status changes go through admin
     * actions and the reference is generated server-side.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_details_json',
        'target_price',
        'message',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_details_json' => 'array',
            'target_price' => 'decimal:2',
            'status' => QuoteStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Quote $quote): void {
            $quote->reference ??= static::newReference();
        });
    }

    /**
     * Random reference, e.g. "NXQ-7KD9QX2M" (32^8 ≈ 1.1e12 possibilities).
     * Uniqueness is guaranteed by the unique index; collisions are re-rolled.
     */
    public static function newReference(): string
    {
        $prefix = config('nexora.quotes.reference_prefix');

        do {
            $code = '';
            for ($i = 0; $i < self::REFERENCE_LENGTH; $i++) {
                $code .= self::REFERENCE_ALPHABET[random_int(0, strlen(self::REFERENCE_ALPHABET) - 1)];
            }
            $reference = "{$prefix}-{$code}";
        } while (static::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function totalQuantity(): int
    {
        return (int) collect($this->product_details_json)->sum('quantity');
    }

    /**
     * Products referenced by the line items, keyed by id, with tiers loaded
     * (one query; used by both the buyer and admin quote pages).
     *
     * @return Collection<int, Product>
     */
    public function lineItemProducts(): Collection
    {
        $ids = collect($this->product_details_json)->pluck('product_id')->unique()->all();

        return Product::with('bulkPrices')->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * @param  Builder<Quote>  $query
     */
    public function scopeStatus(Builder $query, QuoteStatus $status): void
    {
        $query->where('status', $status->value);
    }

    /**
     * @param  Builder<Quote>  $query
     */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }
}
