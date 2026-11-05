@extends('layouts.app', ['title' => 'Quote '.$quote->reference])

@section('content')
    <a href="{{ route('admin.quotes.index') }}" class="text-sm text-brand-600 hover:underline">&larr; All quotes</a>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">Quote <span class="font-mono">{{ $quote->reference }}</span></h1>
        <span class="badge-{{ $quote->status->value }}">{{ $quote->status->label() }}</span>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2 overflow-x-auto">
            @include('quotes._details')
        </div>

        <div class="space-y-6">
            <div class="card">
                <h2 class="font-semibold">Customer</h2>
                <p class="mt-2">{{ $quote->user->name }}</p>
                <p class="text-sm text-slate-600">{{ $quote->user->email }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ $quote->user->role->label() }}</p>
                <p class="mt-3 text-xs text-slate-500">Submitted {{ $quote->created_at->toDayDateTimeString() }}</p>
            </div>

            @if ($quote->message)
                <div class="card">
                    <h2 class="font-semibold">Message</h2>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $quote->message }}</p>
                </div>
            @endif

            @can('update', $quote)
                <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="card space-y-3">
                    @csrf
                    @method('PATCH')
                    <label for="status" class="label">Change status</label>
                    <select id="status" name="status" required class="input">
                        @foreach ($quote->status->allowedTransitions() as $next)
                            <option value="{{ $next->value }}">{{ $next->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="error">{{ $message }}</p> @enderror
                    <button type="submit" class="btn-primary w-full">Update status</button>
                </form>
            @else
                <p class="card text-sm text-slate-600">This quote is closed; its status can no longer change.</p>
            @endcan
        </div>
    </div>
@endsection
