@extends('layouts.app', ['title' => 'Quote status'])

@section('content')
    <div class="mx-auto max-w-md card text-center">
        <p class="text-sm text-slate-500">Quote</p>
        <h1 class="font-mono text-2xl font-bold">{{ $quote->reference }}</h1>
        <p class="mt-4"><span class="badge-{{ $quote->status->value }} text-base">{{ $quote->status->label() }}</span></p>
        <dl class="mt-6 grid grid-cols-2 gap-2 text-sm">
            <dt class="text-slate-500">Submitted</dt><dd>{{ $quote->created_at->toFormattedDateString() }}</dd>
            <dt class="text-slate-500">Last update</dt><dd>{{ $quote->updated_at->toFormattedDateString() }}</dd>
        </dl>
        <p class="mt-6 text-sm text-slate-600">
            <a href="{{ route('login') }}" class="text-brand-600 hover:underline">Log in</a> to see full quote details.
        </p>
    </div>
@endsection
