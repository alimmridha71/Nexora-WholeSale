<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $image
 * @property string $base_price
 * @property int $stock
 * @property string $sku
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'base_price',
        'stock',
        'sku',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Tiered bulk pricing, lowest quantity first.
     *
     * @return HasMany<BulkPrice, $this>
     */
    public function bulkPrices(): HasMany
    {
        return $this->hasMany(BulkPrice::class)->orderBy('min_quantity');
    }

    /**
     * Effective unit price for an order quantity.
     *
     * Picks the matching tier with the highest min_quantity; falls back to
     * base_price when no tier applies. Uses the eager-loaded relation when
     * available to avoid an extra query (and N+1 in listings).
     */
    public function getPriceForQuantity(int $quantity): string
    {
        if ($quantity < 1) {
            return $this->base_price;
        }

        $tiers = $this->relationLoaded('bulkPrices')
            ? $this->bulkPrices
            : $this->bulkPrices()->get();

        $tier = $tiers
            ->filter(fn (BulkPrice $tier) => $tier->appliesTo($quantity))
            ->sortByDesc('min_quantity')
            ->first();

        return $tier?->price_per_unit ?? $this->base_price;
    }

    /**
     * Largest discount (whole percent) a bulk tier gives versus base_price,
     * or 0 without tiers. Uses the eager-loaded relation when available.
     */
    public function maxBulkSavingPercent(): int
    {
        $base = (float) $this->base_price;

        if ($base <= 0) {
            return 0;
        }

        $tiers = $this->relationLoaded('bulkPrices') ? $this->bulkPrices : $this->bulkPrices()->get();
        $lowest = $tiers->min(fn (BulkPrice $tier) => (float) $tier->price_per_unit);

        return $lowest === null ? 0 : max(0, (int) floor((1 - $lowest / $base) * 100));
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term !== '') {
            // Escape LIKE wildcards so user input is matched literally.
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

            // whereLike(caseSensitive: false) compiles to ILIKE on PostgreSQL.
            $query->where(fn (Builder $q) => $q
                ->whereLike('name', $like, caseSensitive: false)
                ->orWhereLike('sku', $like, caseSensitive: false));
        }
    }
}
