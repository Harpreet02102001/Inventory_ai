<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AuthenticatedSessionController
 *
 * Handles the login and logout flow for web authentication.
 * Uses Breeze's LoginRequest for rate-limited authentication,
 * and implements role-based redirect after successful login.
 *
 * Why role-based redirect?
 * Different roles have different home screens. An admin lands on the
 * admin dashboard with full stats. A staff member lands on the stock
 * adjustment view. A sales person lands on the sales module.
 * Sending everyone to the same /dashboard and then redirecting is
 * an unnecessary extra hop — we redirect correctly on first login.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * If the user is already authenticated, redirect them to their
     * appropriate dashboard rather than showing the login form again.
     *
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->getRedirectUrl());
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Delegates credential checking and rate limiting to LoginRequest::authenticate().
     * On success: regenerates session ID (prevents session fixation attacks),
     * then redirects the user to their role-appropriate home screen.
     *
     * @param  LoginRequest  $request  Validated and rate-limited login request
     * @return RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validates credentials and throws ValidationException on failure
        // Also handles rate limiting — 5 failed attempts locks for 1 minute
        $request->authenticate();

        // Regenerate session ID after login to prevent session fixation attacks
        // Session fixation = attacker sets a known session ID before login,
        // then uses it after victim logs in. Regeneration defeats this.
        $request->session()->regenerate();

        return redirect()->to($this->getRedirectUrl());
    }

    /**
     * Destroy an authenticated session (logout).
     *
     * Full logout sequence:
     * 1. Log out from the guard (clears auth state)
     * 2. Invalidate the session (destroys all session data)
     * 3. Regenerate CSRF token (prevents token reuse after logout)
     *
     * @param  Request  $request  The current HTTP request
     * @return RedirectResponse  Redirect to login page
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Invalidate destroys the entire session — clears all stored data
        $request->session()->invalidate();

        // Regenerate the CSRF token so old forms can't be replayed
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Determine the correct redirect URL based on the authenticated user's role.
     *
     * Checks roles in priority order — if a user somehow has multiple roles,
     * the highest-privilege role wins. Falls back to /dashboard for any
     * role not explicitly listed (e.g., normal_user).
     *
     * Why check roles here instead of permissions?
     * Redirect destination is a UI decision, not an authorization decision.
     * "Where does this person go after login?" is about role identity,
     * not about what actions they can perform. Using roles here is correct.
     *
     * @return string  The URL to redirect to after successful login
     */
    private function getRedirectUrl(): string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Load roles if not already in memory
        $user->loadMissing('roles');

        return match (true) {
            $user->hasRole('admin')       => route('dashboard'),
            $user->hasRole('manager')     => route('dashboard'),
            $user->hasRole('staff')       => route('dashboard'),
            $user->hasRole('sales_person') => route('dashboard'),
            default                       => route('dashboard'),
        };

        // Note: Currently all roles go to the same dashboard — the dashboard
        // itself will show different widgets based on permissions.
        // Once we build role-specific dashboards, update the routes here.
        // e.g.: $user->hasRole('staff') => route('stock.index')
    }
}
