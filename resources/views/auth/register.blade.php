@extends('layouts.app', ['title' => 'Open a trade account'])

@section('content')
    <div class="mx-auto max-w-md card">
        <h1 class="text-2xl font-bold">Open a trade account</h1>
        <p class="mt-1 text-sm text-slate-600">Get access to bulk pricing and custom quotes.</p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="label">Business or contact name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="organization" class="input">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="label">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="input">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="label">Account type</label>
                <select id="role" name="role" required class="input">
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', 'retailer') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @error('role') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
            </div>

            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>
    </div>
@endsection
