@extends('layouts.app', ['title' => $product->name])

@section('content')
    <a href="{{ route('home') }}" class="text-sm text-brand-600 hover:underline">&larr; Back to catalog</a>

    <div class="mt-4 grid gap-8 lg:grid-cols-2">
        <div class="card">
            @if ($product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="mb-6 w-full rounded-lg object-cover">
            @endif
            <p class="font-mono text-sm text-slate-500">{{ $product->sku }}</p>
            <h1 class="mt-1 text-3xl font-bold">{{ $product->name }}</h1>
            @if ($product->description)
                <p class="mt-4 whitespace-pre-line text-slate-700">{{ $product->description }}</p>
            @endif
            <p class="mt-4 text-sm {{ $product->isInStock() ? 'text-slate-600' : 'text-red-600' }}">
                {{ $product->isInStock() ? number_format($product->stock).' units in stock' : 'Out of stock' }}
            </p>
        </div>

        <div class="space-y-6">
            <div class="card">
                <h2 class="text-lg font-semibold">Bulk pricing</h2>
                <table class="table mt-4">
                    <thead>
                        <tr><th>Quantity</th><th class="text-right">Price / unit</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td>Standard</td>
                            <td class="text-right">@money($product->base_price)</td>
                        </tr>
                        @foreach ($product->bulkPrices as $tier)
                            <tr>
                                <td>
                                    {{ number_format($tier->min_quantity) }}
                                    {{ $tier->max_quantity === null ? '+' : '– '.number_format($tier->max_quantity) }}
                                </td>
                                <td class="text-right font-semibold text-emerald-700">@money($tier->price_per_unit)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="GET" action="{{ route('products.show', $product) }}" class="card">
                <label for="quantity" class="label">Price calculator</label>
                <div class="flex gap-2">
                    <input id="quantity" name="quantity" type="number" min="1" max="1000000" value="{{ $quantity }}" class="input" data-tier-quantity>
                    <button type="submit" class="btn-secondary">Calculate</button>
                </div>
                <p class="mt-3 text-lg font-semibold text-brand-700" data-tier-output>
                    @money($unitPrice) / unit · @money($unitPrice * $quantity) total
                </p>
                <script type="application/json" id="tiers">@json($priceTable)</script>
            </form>

            @can('request-quotes')
                <a href="{{ route('quotes.create', ['product' => $product->id]) }}" class="btn-primary w-full">Request a custom quote</a>
            @else
                @guest
                    <p class="text-sm text-slate-600">
                        <a href="{{ route('register') }}" class="text-brand-600 hover:underline">Open a trade account</a> to request custom quotes.
                    </p>
                @endguest
            @endcan
        </div>
    </div>
@endsection
