@extends('layouts.app', ['title' => 'Wholesale catalog'])

@section('hero')
    <section class="relative isolate overflow-hidden bg-ink-950 text-white">
        <div class="grid-pattern absolute inset-0 -z-10"></div>
        <div class="hero-glow -top-32 -left-24 -z-10 size-[28rem] bg-brand-600/40"></div>
        <div class="hero-glow -right-24 -bottom-40 -z-10 size-[26rem] bg-accent-500/20"></div>

        <div class="container-page grid items-center gap-12 py-16 lg:grid-cols-12 lg:py-24">
            <div class="lg:col-span-7">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium text-brand-100 backdrop-blur">
                    <span class="size-1.5 rounded-full bg-accent-400"></span>
                    B2B pricing for retailers, wholesalers &amp; distributors
                </span>

                <h1 class="mt-6 text-4xl font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                    Wholesale pricing that
                    <span class="bg-linear-to-r from-accent-300 via-accent-400 to-brand-300 bg-clip-text text-transparent">scales with you.</span>
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-300">
                    Order more, pay less, automatically. Transparent volume tiers on every product, and custom quotes when your volume goes beyond the tiers.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#catalog" class="btn-gold px-6 py-3 text-base">Browse the catalog</a>
                    @auth
                        @can('request-quotes')
                            <a href="{{ route('quotes.create') }}" class="btn-ghost-light px-6 py-3 text-base">Request a custom quote</a>
                        @endcan
                    @else
                        <a href="{{ route('register') }}" class="btn-ghost-light px-6 py-3 text-base">Open a trade account</a>
                    @endauth
                </div>

                <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-white/10 pt-8">
                    <div>
                        <dt class="text-xs tracking-wide text-slate-400 uppercase">Products</dt>
                        <dd class="mt-1 text-3xl font-bold">{{ number_format($stats['products']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs tracking-wide text-slate-400 uppercase">Price tiers</dt>
                        <dd class="mt-1 text-3xl font-bold">{{ number_format($stats['tiers']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs tracking-wide text-slate-400 uppercase">Bulk savings</dt>
                        <dd class="mt-1 text-3xl font-bold text-accent-300">up to {{ $stats['max_saving'] }}%</dd>
                    </div>
                </dl>
            </div>

            {{-- Tier illustration --}}
            <div class="lg:col-span-5">
                <div class="relative rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold">How tiered pricing works</p>
                        <span class="badge bg-emerald-400/15 text-emerald-300 ring-1 ring-emerald-400/30">Live pricing</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Example: industrial bolts, price per box</p>

                    <ul class="mt-6 space-y-3">
                        @foreach ([['1 – 9 boxes', 18.50, 'Standard', 'w-full', 'bg-white/10'], ['10 – 49 boxes', 17.25, '−7%', 'w-11/12', 'bg-brand-500/30'], ['50 – 199 boxes', 15.90, '−14%', 'w-10/12', 'bg-brand-500/50'], ['200+ boxes', 14.20, '−23%', 'w-9/12', 'bg-linear-to-r from-accent-400/70 to-brand-500/70']] as [$range, $price, $tag, $width, $bar])
                            <li class="rounded-2xl border border-white/10 bg-ink-900/60 p-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-300">{{ $range }}</span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-xs text-accent-300">{{ $tag }}</span>
                                        <span class="font-semibold">@money($price)</span>
                                    </span>
                                </div>
                                <div class="mt-3 h-1.5 rounded-full bg-white/5">
                                    <div class="h-1.5 rounded-full {{ $width }} {{ $bar }}"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-5 text-xs text-slate-400">The right tier is applied automatically to every quantity.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="container-page grid gap-6 py-6 text-sm text-slate-600 sm:grid-cols-3">
            @foreach ([['Transparent tiers', 'Every price break is shown up front.'], ['Custom quotes', 'Up to 50 line items per request.'], ['Secure by design', 'Role-based access, encrypted sessions.']] as [$head, $body])
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-brand-100" aria-hidden="true">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                    </span>
                    <p><span class="font-semibold text-ink-900">{{ $head }}.</span> {{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@section('content')
    {{-- Catalog --}}
    <section id="catalog" class="scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Catalog</p>
                <h2 class="section-title mt-2">{{ $search !== '' ? 'Results for “'.$search.'”' : 'Bulk-ready products' }}</h2>
                <p class="mt-2 text-slate-600">{{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} with volume pricing.</p>
            </div>

            <form method="GET" action="{{ route('home') }}#catalog" class="relative w-full sm:w-96" role="search">
                <label for="q" class="sr-only">Search products</label>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.47 9.77l3.13 3.13a.75.75 0 1 0 1.06-1.06l-3.13-3.13A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
                <input id="q" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Search by name or SKU…" class="input pr-24 pl-10">
                <button type="submit" class="btn-primary absolute top-1/2 right-1.5 -translate-y-1/2 px-3 py-1.5">Search</button>
            </form>
        </div>

        @if ($products->isEmpty())
            <div class="card mt-10 text-center">
                <p class="text-lg font-semibold text-ink-900">No products match your search.</p>
                <p class="mt-1 text-slate-600">Try a different name or SKU.</p>
                <a href="{{ route('home') }}#catalog" class="btn-secondary mt-6">Clear search</a>
            </div>
        @else
            @php($tints = ['from-brand-100 via-brand-50 to-white', 'from-amber-100 via-orange-50 to-white', 'from-emerald-100 via-teal-50 to-white', 'from-sky-100 via-indigo-50 to-white'])

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    @php($saving = $product->maxBulkSavingPercent())
                    <article class="product-card group">
                        <a href="{{ route('products.show', $product) }}" class="product-media bg-linear-to-br {{ $tints[$product->id % count($tints)] }}" tabindex="-1" aria-hidden="true">
                            @if ($product->imageUrl())
                                <img src="{{ $product->imageUrl() }}" alt="" class="absolute inset-0 size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                            @else
                                <span class="flex size-16 items-center justify-center rounded-2xl bg-white/80 font-mono text-lg font-bold text-brand-700 shadow-soft ring-1 ring-slate-200/70 transition duration-300 group-hover:scale-110">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->sku, 0, 3)) }}
                                </span>
                            @endif

                            @if ($saving > 0)
                                <span class="badge absolute top-3 left-3 bg-ink-900 text-accent-300 shadow">Save up to {{ $saving }}%</span>
                            @endif
                            @unless ($product->isInStock())
                                <span class="badge absolute top-3 right-3 bg-red-600 text-white">Out of stock</span>
                            @endunless
                        </a>

                        <div class="flex flex-1 flex-col p-5">
                            <p class="font-mono text-[11px] tracking-wider text-slate-400">{{ $product->sku }}</p>
                            <h3 class="mt-1 font-semibold leading-snug text-ink-900">
                                <a href="{{ route('products.show', $product) }}" class="after:absolute after:inset-0 hover:text-brand-700">{{ $product->name }}</a>
                            </h3>

                            <div class="mt-4 flex items-end justify-between gap-2">
                                <div>
                                    <p class="text-2xl font-bold tracking-tight text-ink-900">@money($product->base_price)</p>
                                    <p class="text-xs text-slate-500">per unit, standard</p>
                                </div>
                                @if ($product->bulkPrices->isNotEmpty())
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-emerald-600">@money($product->bulkPrices->min('price_per_unit'))</p>
                                        <p class="text-xs text-slate-500">best bulk price</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs">
                                <span class="flex items-center gap-1.5 {{ $product->isInStock() ? 'text-slate-600' : 'text-red-600' }}">
                                    <span class="size-1.5 rounded-full {{ $product->isInStock() ? ($product->stock < 100 ? 'bg-amber-400' : 'bg-emerald-500') : 'bg-red-500' }}"></span>
                                    {{ $product->isInStock() ? number_format($product->stock).' in stock' : 'Unavailable' }}
                                </span>
                                <span class="font-semibold text-brand-600 transition group-hover:translate-x-0.5">View tiers →</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $products->fragment('catalog')->links() }}</div>
        @endif
    </section>

    {{-- How it works --}}
    <section class="mt-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="eyebrow">How it works</p>
            <h2 class="section-title mt-2">From catalog to custom quote in three steps</h2>
        </div>

        <ol class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['01', 'Open a trade account', 'Register as a retailer, wholesaler or distributor and verify your e-mail.'],
                ['02', 'Compare volume tiers', 'Every product shows its price breaks; the calculator applies the right tier instantly.'],
                ['03', 'Request a custom quote', 'Beyond the tiers? Send up to 50 line items with a target price and track the status.'],
            ] as [$step, $head, $body])
                <li class="card relative overflow-hidden">
                    <span class="absolute -top-4 -right-2 font-mono text-7xl font-bold text-brand-50 select-none" aria-hidden="true">{{ $step }}</span>
                    <span class="relative flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-brand-500 to-brand-700 text-sm font-bold text-white shadow-md shadow-brand-700/25">{{ $step }}</span>
                    <h3 class="relative mt-5 text-lg font-semibold text-ink-900">{{ $head }}</h3>
                    <p class="relative mt-2 text-sm leading-relaxed text-slate-600">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Call to action --}}
    <section class="relative isolate mt-24 overflow-hidden rounded-3xl bg-ink-900 px-8 py-14 text-white sm:px-14">
        <div class="hero-glow -top-24 right-0 -z-10 size-80 bg-brand-600/40"></div>
        <div class="hero-glow -bottom-24 -left-10 -z-10 size-72 bg-accent-500/20"></div>

        <div class="flex flex-wrap items-center justify-between gap-8">
            <div class="max-w-xl">
                <h2 class="text-3xl font-bold tracking-tight">Ordering at volume? Let's talk numbers.</h2>
                <p class="mt-3 text-slate-300">Send a custom quote request and our team will come back with pricing for your exact quantities.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @auth
                    @can('request-quotes')
                        <a href="{{ route('quotes.create') }}" class="btn-gold px-6 py-3 text-base">Request a quote</a>
                    @endcan
                    <a href="{{ route('dashboard') }}" class="btn-ghost-light px-6 py-3 text-base">Go to dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn-gold px-6 py-3 text-base">Open a trade account</a>
                    <a href="{{ route('login') }}" class="btn-ghost-light px-6 py-3 text-base">Try the live demo</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
