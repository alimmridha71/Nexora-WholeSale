@extends('layouts.app', ['title' => 'Log in'])

@section('content')
    <div class="mx-auto max-w-md card">
        <h1 class="text-2xl font-bold">Log in to Nexora Wholesale</h1>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="label">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300"> Remember me
            </label>

            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            New to Nexora? <a href="{{ route('register') }}" class="text-brand-600 hover:underline">Open a trade account</a>
        </p>

        @if (config('nexora.demo.enabled'))
            @include('auth._demo-login')
        @endif
    </div>
@endsection
