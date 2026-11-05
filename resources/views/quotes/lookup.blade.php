@extends('layouts.app', ['title' => 'Check quote status'])

@section('content')
    <div class="mx-auto max-w-md card">
        <h1 class="text-2xl font-bold">Check a quote's status</h1>
        <p class="mt-1 text-sm text-slate-600">Enter your quote reference and the e-mail address of the account that submitted it.</p>

        <form method="POST" action="{{ route('quote-status.show') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="reference" class="label">Quote reference</label>
                <input id="reference" name="reference" type="text" value="{{ old('reference') }}" required maxlength="20" placeholder="NXQ-7KD9QX2M" class="input font-mono">
                @error('reference') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="label">Account e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="input">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full">Check status</button>
        </form>
    </div>
@endsection
