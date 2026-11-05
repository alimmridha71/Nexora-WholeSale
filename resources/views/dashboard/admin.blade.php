@extends('layouts.app', ['title' => 'Admin dashboard'])

@section('content')
    <h1 class="text-2xl font-bold">Admin dashboard</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.products.index') }}" class="card hover:border-brand-600">
            <p class="text-sm text-slate-500">Products</p>
            <p class="mt-1 text-3xl font-bold">{{ number_format($productCount) }}</p>
        </a>
        <div class="card">
            <p class="text-sm text-slate-500">Low stock (&lt; 100 units)</p>
            <p class="mt-1 text-3xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : '' }}">{{ number_format($lowStockCount) }}</p>
        </div>
        <a href="{{ route('admin.quotes.index') }}" class="card hover:border-brand-600">
            <p class="text-sm text-slate-500">Open quotes</p>
            <p class="mt-1 text-3xl font-bold text-brand-700">{{ number_format($openQuoteCount) }}</p>
        </a>
    </div>

    <div class="card mt-8 overflow-x-auto p-0">
        <h2 class="px-6 pt-6 text-lg font-semibold">Latest quotes</h2>
        <table class="table mt-2">
            <thead><tr><th>Reference</th><th>Customer</th><th>Status</th><th>Received</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($recentQuotes as $quote)
                    <tr>
                        <td><a href="{{ route('admin.quotes.show', $quote) }}" class="font-mono text-brand-600 hover:underline">{{ $quote->reference }}</a></td>
                        <td>{{ $quote->user->name }}</td>
                        <td><span class="badge-{{ $quote->status->value }}">{{ $quote->status->label() }}</span></td>
                        <td>{{ $quote->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-slate-500">No quotes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
