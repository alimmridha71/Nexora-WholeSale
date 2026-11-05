<?php

namespace App\Http\Controllers;

use App\Models\BulkPrice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $search = mb_substr((string) $request->query('q', ''), 0, 100);

        $products = Product::query()
            ->search($search)
            ->with('bulkPrices')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'search' => $search,
            'stats' => $this->catalogStats(),
        ]);
    }

    /**
     * Headline numbers for the hero. Cached briefly: they change rarely.
     *
     * @return array{products: int, tiers: int, max_saving: int}
     */
    private function catalogStats(): array
    {
        return Cache::remember('catalog.stats', now()->addMinutes(10), function (): array {
            $maxRatio = BulkPrice::query()
                ->join('products', 'products.id', '=', 'bulk_prices.product_id')
                ->where('products.base_price', '>', 0)
                ->max(DB::raw('1 - bulk_prices.price_per_unit / products.base_price'));

            return [
                'products' => Product::count(),
                'tiers' => BulkPrice::count(),
                'max_saving' => max(0, (int) floor(((float) $maxRatio) * 100)),
            ];
        });
    }

    public function show(Request $request, Product $product): View
    {
        $this->authorize('view', $product);

        $product->load('bulkPrices');

        $quantity = max(1, min(1_000_000, (int) $request->query('quantity', 1)));
        $unitPrice = $product->getPriceForQuantity($quantity);

        // Serialized for the client-side calculator (non-executable JSON block).
        $priceTable = [
            'currency' => config('nexora.currency'),
            'base' => (float) $product->base_price,
            'tiers' => $product->bulkPrices->map(fn ($tier) => [
                'min' => $tier->min_quantity,
                'max' => $tier->max_quantity,
                'price' => (float) $tier->price_per_unit,
            ])->values(),
        ];

        return view('catalog.show', compact('product', 'quantity', 'unitPrice', 'priceTable'));
    }
}
