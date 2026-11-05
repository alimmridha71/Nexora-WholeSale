<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level RBAC. Usage: ->middleware('role:admin') or 'role:retailer,wholesaler'.
 *
 * This is the first layer only: controllers additionally call
 * $this->authorize(...) against policies (defense in depth).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($roles === [] || ! $user->hasRole(...$roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Your account role does not have access to this area.');
        }

        return $next($request);
    }
}
