{{-- Standalone error layout: no session, auth or database access, so it renders even when those fail. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · Nexora Wholesale</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 font-sans text-slate-900 antialiased">
    <main class="card mx-4 max-w-md text-center">
        <p class="text-5xl font-bold text-brand-600">@yield('code')</p>
        <h1 class="mt-4 text-xl font-semibold">@yield('title')</h1>
        <p class="mt-2 text-slate-600">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn-primary mt-6">Back to the catalog</a>
    </main>
</body>
</html>
