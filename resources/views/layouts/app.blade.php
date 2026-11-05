<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0e2644">
    <title>{{ isset($title) ? $title.' · ' : '' }}Nexora Wholesale</title>
    <meta name="description" content="Nexora Wholesale — B2B supplies with volume pricing and custom quotes for retailers, wholesalers and distributors.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-900 antialiased">
    {{-- Utility bar --}}
    <div class="hidden bg-brand-900 text-xs text-brand-100 md:block">
        <div class="container-page flex h-9 items-center justify-between">
            <p>Volume pricing on every SKU &middot; Custom quotes up to {{ config('nexora.quotes.max_items') }} line items</p>
            <a href="{{ route('quote-status.create') }}" class="hover:text-white">Track a quote</a>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
        <nav class="container-page flex h-16 items-center justify-between gap-4" aria-label="Main">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="Nexora Wholesale home">
                @include('partials.logo-mark')
                <span class="text-base font-bold tracking-tight whitespace-nowrap text-ink-900 sm:text-lg">Nexora <span class="font-semibold text-brand-600">Wholesale</span></span>
            </a>

            <div class="flex items-center justify-end gap-1 overflow-x-auto">
                <a href="{{ route('home') }}" class="nav-link hidden sm:inline-flex">Catalog</a>
                <a href="{{ route('quote-status.create') }}" class="nav-link hidden sm:inline-flex">Quote status</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>

                    @can('request-quotes')
                        <a href="{{ route('quotes.index') }}" class="nav-link">My quotes</a>
                    @endcan

                    @can('access-admin')
                        <a href="{{ route('admin.products.index') }}" class="nav-link">Products</a>
                        <a href="{{ route('admin.quotes.index') }}" class="nav-link">Quotes</a>
                    @endcan

                    <span class="badge ml-1 bg-slate-100 text-slate-700">{{ auth()->user()->role->label() }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary ml-1 hidden sm:inline-flex">Open a trade account</a>
                @endauth
            </div>
        </nav>
    </header>

    @yield('hero')

    <main class="container-page flex-1 py-10">
        @if (session('status'))
            <div class="mb-6 flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <svg class="size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.86a.75.75 0 0 0-1.22-.88l-3.24 4.5-1.62-1.62a.75.75 0 1 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.09l3.75-5.22Z" clip-rule="evenodd"/></svg>
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-auto border-t border-slate-200 bg-white text-slate-600">
        <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    @include('partials.logo-mark')
                    <span class="text-lg font-bold text-ink-900">Nexora <span class="font-semibold text-brand-600">Wholesale</span></span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-relaxed">
                    Business supplies for retailers, wholesalers and distributors, with published volume pricing and custom quotes for larger orders.
                </p>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-ink-900">Buying</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700">Product catalog</a></li>
                    <li><a href="{{ route('quote-status.create') }}" class="hover:text-brand-700">Track a quote</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-brand-700">Open a trade account</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-ink-900">Account</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="hover:text-brand-700">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-brand-700">Log in</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-200">
            <div class="container-page flex flex-wrap items-center justify-between gap-3 py-5 text-xs text-slate-500">
                <span>&copy; {{ date('Y') }} Nexora Wholesale. All rights reserved.</span>
                <span>Product photos via Unsplash.</span>
            </div>
        </div>
    </footer>
</body>
</html>
