@extends('layouts.app', ['title' => 'Quote '.$quote->reference])

@section('content')
    <a href="{{ route('quotes.index') }}" class="text-sm text-brand-600 hover:underline">&larr; My quotes</a>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">Quote <span class="font-mono">{{ $quote->reference }}</span></h1>
        <span class="badge-{{ $quote->status->value }}">{{ $quote->status->label() }}</span>
    </div>
    <p class="text-sm text-slate-500">Submitted {{ $quote->created_at->toDayDateTimeString() }}</p>

    <div class="card mt-6 overflow-x-auto">
        @include('quotes._details')
    </div>

    @if ($quote->message)
        <div class="card mt-6">
            <h2 class="font-semibold">Your message</h2>
            <p class="mt-2 whitespace-pre-line text-slate-700">{{ $quote->message }}</p>
        </div>
    @endif

    @can('delete', $quote)
        <form method="POST" action="{{ route('quotes.destroy', $quote) }}" class="mt-6" data-confirm="Withdraw this quote request?">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">Withdraw quote</button>
        </form>
    @endcan
@endsection
