@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h1>
            <p class="text-slate-600">{{ auth()->user()->role->label() }} account</p>
        </div>
        <a href="{{ route('quotes.create') }}" class="btn-primary">Request a quote</a>
    </div>

    <div class="card mt-8 overflow-x-auto p-0">
        <h2 class="px-6 pt-6 text-lg font-semibold">Your recent quotes</h2>
        <table class="table mt-2">
            <thead><tr><th>Reference</th><th>Items</th><th>Status</th><th>Submitted</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($recentQuotes as $quote)
                    <tr>
                        <td><a href="{{ route('quotes.show', $quote) }}" class="font-mono text-brand-600 hover:underline">{{ $quote->reference }}</a></td>
                        <td>{{ count($quote->product_details_json) }} product(s), {{ number_format($quote->totalQuantity()) }} units</td>
                        <td><span class="badge-{{ $quote->status->value }}">{{ $quote->status->label() }}</span></td>
                        <td>{{ $quote->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-slate-500">You haven't requested any quotes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
