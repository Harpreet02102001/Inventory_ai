<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission Middleware
 *
 * Guards routes by checking a specific permission against the authenticated
 * user via Laravel's Gate system (which routes through our RBAC via
 * AuthServiceProvider::registerPermissionGate()).
 *
 * Usage in routes:
 *   ->middleware('permission:products.create')
 *   ->middleware('permission:stock.adjust')
 *
 * The permission string after the colon is passed as $permission parameter.
 * It must match exactly a permission name defined in config/permissions.php.
 *
 * Prefer this over 'role' middleware for granular action-level protection.
 * Example: a Manager and Admin both have 'products.edit' — one middleware
 * covers both without listing every role that should have access.
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Checks the given permission via Gate::allows(), which routes through
     * our AuthServiceProvider Gate::before() registration — the full RBAC
     * chain is checked: User → Roles → Permissions.
     *
     * @param  Request  $request     The incoming HTTP request
     * @param  Closure  $next        The next middleware in the pipeline
     * @param  string   $permission  Dot-notation permission e.g. "products.create"
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! Gate::allows($permission)) {
            // API/AJAX requests expect JSON — return 403 with message
            if ($request->expectsJson()) {
                return response()->json(
                    ['message' => 'You do not have permission to perform this action.'],
                    Response::HTTP_FORBIDDEN
                );
            }

            // Web requests get an abort with a clean 403 page
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
