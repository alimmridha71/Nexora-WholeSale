<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
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

        return view('catalog.index', compact('products', 'search'));
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
