@extends('layouts.app', ['title' => 'Catalog'])

@section('content')
    <section class="mb-8 rounded-2xl bg-brand-900 px-8 py-10 text-white">
        <h1 class="text-3xl font-bold">Wholesale pricing that scales with you</h1>
        <p class="mt-2 max-w-2xl text-brand-100">
            Tiered bulk pricing for retailers, wholesalers and distributors. Need a volume beyond our tiers?
            Request a custom quote.
        </p>
    </section>

    <form method="GET" action="{{ route('home') }}" class="mb-6 flex gap-2" role="search">
        <label for="q" class="sr-only">Search products</label>
        <input id="q" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Search by name or SKU" class="input">
        <button type="submit" class="btn-primary">Search</button>
    </form>

    @if ($products->isEmpty())
        <p class="card text-slate-600">No products found.</p>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <article class="card flex flex-col">
                    @if ($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="mb-4 h-40 w-full rounded-lg object-cover" loading="lazy">
                    @endif
                    <p class="text-xs font-mono text-slate-500">{{ $product->sku }}</p>
                    <h2 class="mt-1 text-lg font-semibold">
                        <a href="{{ route('products.show', $product) }}" class="hover:text-brand-600">{{ $product->name }}</a>
                    </h2>
                    <p class="mt-2 text-2xl font-bold text-brand-700">@money($product->base_price)
                        <span class="text-sm font-normal text-slate-500">/ unit</span>
                    </p>
                    @if ($product->bulkPrices->isNotEmpty())
                        <p class="mt-1 text-sm text-emerald-700">
                            From @money($product->bulkPrices->min('price_per_unit')) in bulk
                        </p>
                    @endif
                    <p class="mt-auto pt-4 text-sm {{ $product->isInStock() ? 'text-slate-600' : 'text-red-600' }}">
                        {{ $product->isInStock() ? number_format($product->stock).' in stock' : 'Out of stock' }}
                    </p>
                </article>
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
@endsection
