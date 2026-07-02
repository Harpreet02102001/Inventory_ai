<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * AuthServiceProvider
 *
 * Bridges our custom RBAC system (HasRoles trait) with Laravel's native
 * Gate authorization system. After this provider boots, every call to
 * $user->can(), @can() in Blade, and $this->authorize() in controllers
 * automatically routes through our permission checks.
 *
 * Without this provider:
 *   $user->hasPermission('products.create') → works (direct trait call)
 *   $user->can('products.create')           → always false (Gate knows nothing)
 *
 * With this provider:
 *   Both work identically and consistently.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // No bindings needed here — Gates are registered in boot()
    }

    /**
     * Bootstrap authorization gates after all providers are loaded.
     *
     * Called after all service providers are registered, ensuring
     * the database connection and models are fully available.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPermissionGate();
    }

    /**
     * Register the universal permission gate using Gate::before().
     *
     * Gate::before() intercepts EVERY authorization check before any
     * other Gate or Policy runs. Returning true grants access immediately.
     * Returning null means "no opinion — continue to next gate/policy."
     * Returning false explicitly DENIES — used only for inactive accounts.
     *
     * We register ONE gate for ALL permissions instead of 34 individual
     * gates — the $ability parameter receives whatever string was passed
     * to can(), e.g. "products.create", "stock.adjust", etc.
     *
     * @return void
     */
    private function registerPermissionGate(): void
    {
        Gate::before(function (User $user, string $ability): bool|null {
            /**
             * Block inactive accounts at the gate level.
             *
             * An admin can deactivate an account while the user's session
             * is still valid. Returning false here ensures deactivated users
             * are denied ALL permissions immediately — not just redirected
             * by middleware, but denied at the authorization layer too.
             *
             * We return false (not null) intentionally — null would allow
             * other gates/policies to potentially grant access.
             */
            if (! $user->isActive()) {
                return false;
            }

            /**
             * Eager load roles with permissions if not already in memory.
             *
             * loadMissing() is smarter than load() — it checks if the
             * relationship is already hydrated before firing a query.
             * This means in a single request with 10 can() checks,
             * the database is only queried ONCE for roles.permissions,
             * not 10 times. This is how we prevent N+1 queries in auth.
             *
             * Dot notation 'roles.permissions' means:
             * "load the roles relationship, and for each role load its permissions"
             */
            $user->loadMissing('roles.permissions');

            /**
             * Check permission through our HasRoles trait.
             *
             * hasPermission() walks: User → Roles → Permissions
             * Returns true if ANY of the user's roles carries this permission.
             *
             * The ternary converts: true → true (grant), false → null (no opinion)
             * We never return false here — that would deny even when another
             * Policy might legitimately grant access.
             */
            return $user->hasPermission($ability) ?: null;
        });
    }
}
