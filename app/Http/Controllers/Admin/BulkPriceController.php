<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBulkPriceRequest;
use App\Models\BulkPrice;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

/**
 * Nested under products with scoped bindings, so a tier can only be
 * reached through the product it belongs to.
 */
class BulkPriceController extends Controller
{
    public function store(StoreBulkPriceRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->bulkPrices()->create($request->validated());

        return redirect()->route('admin.products.edit', $product)->with('status', 'Price tier added.');
    }

    public function destroy(Product $product, BulkPrice $bulkPrice): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($bulkPrice->product_id === $product->id, 404);

        $bulkPrice->delete();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Price tier removed.');
    }
}
