@extends('layouts.app', ['title' => 'Verify your e-mail'])

@section('content')
    <div class="mx-auto max-w-md card">
        <h1 class="text-2xl font-bold">Verify your e-mail</h1>
        <p class="mt-2 text-slate-600">
            We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
            Click it to activate your trade account.
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
            @csrf
            <button type="submit" class="btn-primary w-full">Resend verification e-mail</button>
        </form>
    </div>
@endsection
