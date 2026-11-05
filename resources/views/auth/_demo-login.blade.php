{{-- One-click demo sign-in (NEXORA_DEMO_MODE=true only). No passwords are shown or needed. --}}
<div class="mt-8 border-t border-slate-200 pt-6">
    <h2 class="text-sm font-semibold text-slate-700">Try the live demo</h2>
    <p class="mt-1 text-xs text-slate-500">Sign in instantly as a sample business buyer. Demo data is shared and may be reset.</p>

    <div class="mt-3 grid gap-2 sm:grid-cols-3">
        @foreach (array_keys(config('nexora.demo.accounts')) as $role)
            <form method="POST" action="{{ route('demo-login', $role) }}">
                @csrf
                <button type="submit" class="btn-secondary w-full">{{ ucfirst($role) }}</button>
            </form>
        @endforeach
    </div>
</div>
