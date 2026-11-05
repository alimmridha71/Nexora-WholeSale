<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Routes: auth + verified + role:admin. Every action also authorizes
 * against ProductPolicy (defense in depth).
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $search = mb_substr((string) $request->query('q', ''), 0, 100);

        $products = Product::query()
            ->search($search)
            ->withCount('bulkPrices')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.form', ['product' => new Product]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Product created. You can now add bulk price tiers.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('bulkPrices');

        return view('admin.products.form', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->safe()->except(['image', 'remove_image']);
        $oldImage = $product->image;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $product->update($data);

        if ($oldImage && $oldImage !== $product->image) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $image = $product->image;

        // bulk_prices rows are removed by the cascadeOnDelete foreign key.
        DB::transaction(fn () => $product->delete());

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }
}
