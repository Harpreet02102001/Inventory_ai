<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 *
 * Guards routes by checking whether the authenticated user has
 * at least one of the specified roles. Supports multiple roles
 * via pipe-separated syntax.
 *
 * Usage in routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:admin|manager')  ← user needs ANY ONE of these
 *
 * When to use role vs permission middleware:
 *   - Use 'permission' for granular action-level checks (preferred)
 *   - Use 'role' for broad section-level access e.g. "admin panel only"
 *     where the entire section is off-limits to non-admins regardless
 *     of individual permissions.
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Resolves the pipe-separated role string into an array and checks
     * if the authenticated user has any of those roles via our HasRoles trait.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next     The next middleware in the pipeline
     * @param  string   $roles    Pipe-separated role names e.g. "admin|manager"
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Load roles if not yet hydrated — prevents N+1 on role checks
        $user->loadMissing('roles');

        // Explode pipe-separated string into array: "admin|manager" → ['admin', 'manager']
        $roleArray = explode('|', $roles);

        if (! $user->hasAnyRole($roleArray)) {
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => 'Access denied. Insufficient role privileges.'],
                    Response::HTTP_FORBIDDEN
                );
            }

            abort(Response::HTTP_FORBIDDEN, 'Access denied. Insufficient role privileges.');
        }

        return $next($request);
    }
}
