<?php

namespace App\Models;

use Database\Factories\BulkPriceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property int $min_quantity
 * @property int|null $max_quantity
 * @property string $price_per_unit
 */
class BulkPrice extends Model
{
    /** @use HasFactory<BulkPriceFactory> */
    use HasFactory;

    /**
     * `product_id` is set through the relationship
     * (`$product->bulkPrices()->create(...)`), not mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'min_quantity',
        'max_quantity',
        'price_per_unit',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'price_per_unit' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function appliesTo(int $quantity): bool
    {
        return $quantity >= $this->min_quantity
            && ($this->max_quantity === null || $quantity <= $this->max_quantity);
    }

    /**
     * Unit price this tier yields for a quantity, or null if it does not apply.
     */
    public function getPriceForQuantity(int $quantity): ?string
    {
        return $this->appliesTo($quantity) ? $this->price_per_unit : null;
    }

    /**
     * Tiers that cover the given quantity.
     *
     * @param  Builder<BulkPrice>  $query
     */
    public function scopeForQuantity(Builder $query, int $quantity): void
    {
        $query->where('min_quantity', '<=', $quantity)
            ->where(fn (Builder $q) => $q
                ->whereNull('max_quantity')
                ->orWhere('max_quantity', '>=', $quantity));
    }

    /**
     * Tiers whose range overlaps [min, max] (max null = unbounded).
     *
     * @param  Builder<BulkPrice>  $query
     */
    public function scopeOverlapping(Builder $query, int $min, ?int $max): void
    {
        $query->where(fn (Builder $q) => $q
            ->whereNull('max_quantity')
            ->orWhere('max_quantity', '>=', $min));

        if ($max !== null) {
            $query->where('min_quantity', '<=', $max);
        }
    }
}
