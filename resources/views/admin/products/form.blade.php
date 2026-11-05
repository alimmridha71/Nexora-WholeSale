@extends('layouts.app', ['title' => $product->exists ? 'Edit '.$product->name : 'New product'])

@section('content')
    <a href="{{ route('admin.products.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Products</a>
    <h1 class="mt-4 text-2xl font-bold">{{ $product->exists ? 'Edit product' : 'New product' }}</h1>

    <form method="POST"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          enctype="multipart/form-data"
          class="card mt-6 grid gap-4 sm:grid-cols-2">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="sm:col-span-2">
            <label for="name" class="label">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required maxlength="255" class="input">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sku" class="label">SKU</label>
            <input id="sku" name="sku" type="text" value="{{ old('sku', $product->sku) }}" required maxlength="64" class="input font-mono uppercase">
            @error('sku') <p class="error">{{ $message }}</p> @enderror
        </div>

        @if ($product->exists)
            <div>
                <span class="label">Slug</span>
                <p class="py-2 font-mono text-sm text-slate-600">{{ $product->slug }}</p>
            </div>
        @endif

        <div>
            <label for="base_price" class="label">Base price ({{ config('nexora.currency') }} / unit)</label>
            <input id="base_price" name="base_price" type="number" step="0.01" min="0.01" value="{{ old('base_price', $product->base_price) }}" required class="input">
            @error('base_price') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="stock" class="label">Stock (units)</label>
            <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="input">
            @error('stock') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" rows="4" maxlength="10000" class="input">{{ old('description', $product->description) }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="image" class="label">Image (JPG, PNG or WebP, max 2 MB)</label>
            @if ($product->exists && $product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="" class="mb-2 h-24 rounded-lg object-cover">
                <label class="mb-2 flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remove_image" value="1"> Remove current image
                </label>
            @endif
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
            @error('image') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-2 sm:col-span-2">
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
        </div>
    </form>

    @if ($product->exists)
        <section class="card mt-8">
            <h2 class="text-lg font-semibold">Bulk price tiers</h2>
            <p class="text-sm text-slate-600">Tiers must not overlap. Leave “max” empty for an open-ended top tier.</p>

            <table class="table mt-4">
                <thead><tr><th>Min qty</th><th>Max qty</th><th class="text-right">Price / unit</th><th></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($product->bulkPrices as $tier)
                        <tr>
                            <td>{{ number_format($tier->min_quantity) }}</td>
                            <td>{{ $tier->max_quantity === null ? '∞' : number_format($tier->max_quantity) }}</td>
                            <td class="text-right">@money($tier->price_per_unit)</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.products.bulk-prices.destroy', [$product, $tier]) }}" data-confirm="Remove this price tier?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">No tiers yet — base price applies to all quantities.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <form method="POST" action="{{ route('admin.products.bulk-prices.store', $product) }}" class="mt-6 grid gap-3 sm:grid-cols-4 sm:items-end">
                @csrf
                <div>
                    <label for="min_quantity" class="label">Min qty</label>
                    <input id="min_quantity" name="min_quantity" type="number" min="1" value="{{ old('min_quantity') }}" required class="input">
                    @error('min_quantity') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_quantity" class="label">Max qty (optional)</label>
                    <input id="max_quantity" name="max_quantity" type="number" min="1" value="{{ old('max_quantity') }}" class="input">
                    @error('max_quantity') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="price_per_unit" class="label">Price / unit</label>
                    <input id="price_per_unit" name="price_per_unit" type="number" step="0.01" min="0.01" value="{{ old('price_per_unit') }}" required class="input">
                    @error('price_per_unit') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary">Add tier</button>
            </form>
        </section>
    @endif
@endsection
