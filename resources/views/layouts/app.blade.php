<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}Nexora Wholesale</title>
    <meta name="description" content="Nexora Wholesale — B2B wholesale catalog with tiered bulk pricing and custom quotes.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-bold text-brand-900">
                Nexora <span class="text-brand-600">Wholesale</span>
            </a>

            <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-brand-600">Catalog</a>
                <a href="{{ route('quote-status.create') }}" class="hover:text-brand-600">Quote status</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-600">Dashboard</a>

                    @can('request-quotes')
                        <a href="{{ route('quotes.index') }}" class="hover:text-brand-600">My quotes</a>
                    @endcan

                    @can('access-admin')
                        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-600">Products</a>
                        <a href="{{ route('admin.quotes.index') }}" class="hover:text-brand-600">Quotes</a>
                    @endcan

                    <span class="badge bg-brand-50 text-brand-700">{{ auth()->user()->role->label() }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-brand-600">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-brand-600">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Open a trade account</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap justify-between gap-2 px-4 py-6 text-sm text-slate-500">
            <span>&copy; {{ date('Y') }} Nexora Wholesale. All rights reserved.</span>
            <span>B2B wholesale pricing for retailers, wholesalers and distributors.</span>
        </div>
    </footer>
</body>
</html>
