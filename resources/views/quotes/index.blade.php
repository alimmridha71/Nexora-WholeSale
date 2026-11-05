@extends('layouts.app', ['title' => 'My quotes'])

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">My quotes</h1>
        <a href="{{ route('quotes.create') }}" class="btn-primary">Request a quote</a>
    </div>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Reference</th><th>Items</th><th class="text-right">Target price</th><th>Status</th><th>Submitted</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($quotes as $quote)
                    <tr>
                        <td><a href="{{ route('quotes.show', $quote) }}" class="font-mono text-brand-600 hover:underline">{{ $quote->reference }}</a></td>
                        <td>{{ count($quote->product_details_json) }} product(s), {{ number_format($quote->totalQuantity()) }} units</td>
                        <td class="text-right">@money($quote->target_price)</td>
                        <td><span class="badge-{{ $quote->status->value }}">{{ $quote->status->label() }}</span></td>
                        <td>{{ $quote->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">No quotes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $quotes->links() }}</div>
@endsection
