<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserIsActive Middleware
 *
 * Runs on every authenticated request to verify the user's account
 * is still active. Handles the scenario where an admin deactivates
 * an account while that user's session is still valid.
 *
 * Without this: a deactivated user continues using the system
 * until their session naturally expires (hours or days later).
 *
 * With this: deactivation takes effect on the very next request.
 *
 * Applied to: all routes inside the 'auth' middleware group
 * via bootstrap/app.php — no need to add it per-route.
 */
class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * Checks the authenticated user's status on every request.
     * If inactive: logs out, invalidates session, redirects to login
     * with a clear explanation message.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next     The next middleware or controller action
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->isActive()) {
            // Full logout sequence — invalidate session and regenerate CSRF token
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your account has been deactivated. Please contact an administrator.',
                ]);
        }

        return $next($request);
    }
}
