<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * One-click sign-in to the seeded demo BUYER accounts on the public demo.
 *
 * - Disabled unless NEXORA_DEMO_MODE=true (404 otherwise).
 * - Only the configured business-buyer accounts; admin is impossible
 *   because the role is checked against UserRole::registrable().
 * - POST + CSRF + rate limited ("demo-login").
 * - No password is published anywhere.
 */
class DemoLoginController extends Controller
{
    public function store(Request $request, string $role): RedirectResponse
    {
        abort_unless(config('nexora.demo.enabled'), 404);
        abort_unless(in_array($role, UserRole::registrable(), true), 404);

        $email = config("nexora.demo.accounts.{$role}.email");

        $user = User::query()
            ->where('email', $email)
            ->where('role', $role)
            ->whereNotNull('email_verified_at')
            ->first();

        abort_if($user === null || $user->isAdmin(), 404);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', "Signed in to the {$user->role->label()} demo account.");
    }
}
