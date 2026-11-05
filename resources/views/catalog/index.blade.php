@extends('layouts.app', ['title' => 'Wholesale catalog'])

@section('hero')
    <section class="hero-photo text-white">
        <div class="container-page py-16 sm:py-20 lg:py-24">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-accent-300">For retailers, wholesalers &amp; distributors</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">Business supplies, priced by volume.</h1>
                <p class="mt-5 text-lg leading-relaxed text-slate-200">
                    Every product lists its quantity breaks up front, so you know your unit price before you order. Buying beyond the tiers? Send a custom quote request.
                </p>

                <form method="GET" action="{{ route('home') }}#catalog" class="mt-8 flex max-w-xl gap-2" role="search">
                    <label for="hero-q" class="sr-only">Search products</label>
                    <input id="hero-q" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Search by product name or SKU" class="input h-12 border-0 text-base">
                    <button type="submit" class="btn-accent h-12 px-6 text-base">Search</button>
                </form>

                <dl class="mt-10 flex flex-wrap gap-x-10 gap-y-4 text-sm">
                    <div>
                        <dt class="text-slate-300">Products</dt>
                        <dd class="text-2xl font-bold">{{ number_format($stats['products']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-300">Volume price breaks</dt>
                        <dd class="text-2xl font-bold">{{ number_format($stats['tiers']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-300">Largest bulk discount</dt>
                        <dd class="text-2xl font-bold">{{ $stats['max_saving'] }}%</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section class="border-b border-slate-200 bg-white">
        <div class="container-page grid divide-y divide-slate-200 text-sm sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            @foreach ([
                ['Published price breaks', 'Unit prices drop automatically at each quantity tier.'],
                ['Custom quotes', 'Up to '.config('nexora.quotes.max_items').' line items with your target price.'],
                ['Trade accounts', 'Separate access for retailers, wholesalers and distributors.'],
            ] as [$head, $body])
                <div class="px-0 py-5 sm:px-6 first:sm:pl-0">
                    <p class="font-semibold text-ink-900">{{ $head }}</p>
                    <p class="mt-1 text-slate-600">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@section('content')
    <section id="catalog" class="scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h2 class="section-title">{{ $search !== '' ? 'Results for “'.$search.'”' : 'Product catalog' }}</h2>
                <p class="mt-1 text-sm text-slate-600">
                    {{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}
                    @if ($search !== '')
                        &middot; <a href="{{ route('home') }}#catalog" class="font-medium text-brand-600 hover:underline">Clear search</a>
                    @endif
                </p>
            </div>
            <p class="text-sm text-slate-500">Prices per unit, excluding tax.</p>
        </div>

        @if ($products->isEmpty())
            <div class="card mt-8 text-center">
                <p class="font-semibold text-ink-900">No products match your search.</p>
                <p class="mt-1 text-sm text-slate-600">Try a different product name or SKU.</p>
                <a href="{{ route('home') }}#catalog" class="btn-secondary mt-5">Show all products</a>
            </div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    @php($saving = $product->maxBulkSavingPercent())
                    <article class="product-card group">
                        <a href="{{ route('products.show', $product) }}" class="product-media" tabindex="-1" aria-hidden="true">
                            @if ($product->imageUrl())
                                <img src="{{ $product->imageUrl() }}" alt="" width="800" height="600" class="size-full object-cover transition duration-300 group-hover:scale-[1.03]" loading="lazy" decoding="async">
                            @else
                                <span class="flex size-full items-center justify-center font-mono text-sm text-slate-400">{{ $product->sku }}</span>
                            @endif

                            @unless ($product->isInStock())
                                <span class="badge absolute top-3 left-3 bg-white text-red-700 shadow-sm">Out of stock</span>
                            @endunless
                        </a>

                        <div class="flex flex-1 flex-col p-4">
                            <p class="text-xs text-slate-500">SKU {{ $product->sku }}</p>
                            <h3 class="mt-1 text-[15px] leading-snug font-semibold text-ink-900">
                                <a href="{{ route('products.show', $product) }}" class="after:absolute after:inset-0 hover:text-brand-700">{{ $product->name }}</a>
                            </h3>

                            <p class="mt-3 text-xl font-bold text-ink-900">@money($product->base_price) <span class="text-xs font-normal text-slate-500">/ unit</span></p>

                            @if ($product->bulkPrices->isNotEmpty())
                                <table class="mt-3 w-full text-xs">
                                    <caption class="sr-only">Volume pricing for {{ $product->name }}</caption>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($product->bulkPrices->take(3) as $tier)
                                            <tr>
                                                <th scope="row" class="py-1 text-left font-normal text-slate-500">{{ number_format($tier->min_quantity) }}+ units</th>
                                                <td class="py-1 text-right font-semibold text-slate-800">@money($tier->price_per_unit)</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if ($saving > 0)
                                    <p class="mt-2 text-xs font-medium text-emerald-700">Save up to {{ $saving }}% at volume</p>
                                @endif
                            @endif

                            <p class="mt-auto flex items-center gap-1.5 pt-4 text-xs {{ $product->isInStock() ? 'text-slate-600' : 'text-red-700' }}">
                                <span class="size-1.5 rounded-full {{ $product->isInStock() ? ($product->stock < 100 ? 'bg-amber-500' : 'bg-emerald-600') : 'bg-red-600' }}"></span>
                                {{ $product->isInStock() ? ($product->stock < 100 ? 'Low stock: '.number_format($product->stock).' left' : number_format($product->stock).' in stock') : 'Currently unavailable' }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $products->fragment('catalog')->links() }}</div>
        @endif
    </section>

    <section class="mt-20 grid gap-10 rounded-lg border border-slate-200 bg-white p-8 lg:grid-cols-3 lg:p-10">
        <div>
            <h2 class="section-title">How buying works</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                Open a trade account, compare the published price breaks, and request a quote when you need volumes or terms beyond the list.
            </p>
        </div>
        <ol class="grid gap-6 sm:grid-cols-3 lg:col-span-2">
            @foreach ([
                ['Open a trade account', 'Register as a retailer, wholesaler or distributor and confirm your e-mail.'],
                ['Check your unit price', 'Each product page has a calculator that applies the right tier to your quantity.'],
                ['Request a quote', 'Send several products at once with a target price, then track it by reference.'],
            ] as $i => [$head, $body])
                <li>
                    <span class="flex size-8 items-center justify-center rounded-full border-2 border-brand-600 text-sm font-bold text-brand-700">{{ $i + 1 }}</span>
                    <h3 class="mt-3 font-semibold text-ink-900">{{ $head }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mt-10 flex flex-wrap items-center justify-between gap-6 rounded-lg bg-brand-800 px-8 py-10 text-white lg:px-10">
        <div class="max-w-xl">
            <h2 class="text-2xl font-bold">Ordering at volume?</h2>
            <p class="mt-2 text-brand-100">Send us the quantities you need and we'll come back with pricing for your order.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @auth
                @can('request-quotes')
                    <a href="{{ route('quotes.create') }}" class="btn-accent px-5">Request a quote</a>
                @endcan
                <a href="{{ route('dashboard') }}" class="btn-outline-light px-5">Go to dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-accent px-5">Open a trade account</a>
                <a href="{{ route('login') }}" class="btn-outline-light px-5">Try the demo</a>
            @endauth
        </div>
    </section>
@endsection
