@extends('layouts.app', ['title' => 'Manage products'])

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">Products</h1>
        @can('create', App\Models\Product::class)
            <a href="{{ route('admin.products.create') }}" class="btn-primary">New product</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mt-6 flex gap-2" role="search">
        <label for="q" class="sr-only">Search</label>
        <input id="q" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Search by name or SKU" class="input">
        <button type="submit" class="btn-secondary">Search</button>
    </form>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>SKU</th><th>Name</th><th class="text-right">Base price</th><th class="text-right">Stock</th><th class="text-right">Tiers</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="font-mono">{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td class="text-right">@money($product->base_price)</td>
                        <td class="text-right {{ $product->stock < 100 ? 'text-amber-600 font-semibold' : '' }}">{{ number_format($product->stock) }}</td>
                        <td class="text-right">{{ $product->bulk_prices_count }}</td>
                        <td class="whitespace-nowrap text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-brand-600 hover:underline">Edit</a>
                            @can('delete', $product)
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="ml-3 inline" data-confirm="Delete {{ $product->name }} and all its price tiers?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-slate-500">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
