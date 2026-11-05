@extends('layouts.app', ['title' => 'Manage quotes'])

@section('content')
    <h1 class="text-2xl font-bold">Quote requests</h1>

    <nav class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="Filter by status">
        <a href="{{ route('admin.quotes.index') }}" class="{{ $status === null ? 'btn-primary' : 'btn-secondary' }}">All</a>
        @foreach ($statuses as $option)
            <a href="{{ route('admin.quotes.index', ['status' => $option->value]) }}"
               class="{{ $status === $option ? 'btn-primary' : 'btn-secondary' }}">{{ $option->label() }}</a>
        @endforeach
    </nav>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="table">
            <thead><tr><th>Reference</th><th>Customer</th><th>Role</th><th>Items</th><th class="text-right">Target</th><th>Status</th><th>Received</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($quotes as $quote)
                    <tr>
                        <td><a href="{{ route('admin.quotes.show', $quote) }}" class="font-mono text-brand-600 hover:underline">{{ $quote->reference }}</a></td>
                        <td>{{ $quote->user->name }}<br><span class="text-xs text-slate-500">{{ $quote->user->email }}</span></td>
                        <td>{{ $quote->user->role->label() }}</td>
                        <td>{{ number_format($quote->totalQuantity()) }} units</td>
                        <td class="text-right">@money($quote->target_price)</td>
                        <td><span class="badge-{{ $quote->status->value }}">{{ $quote->status->label() }}</span></td>
                        <td>{{ $quote->created_at->toFormattedDateString() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-slate-500">No quotes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $quotes->links() }}</div>
@endsection
