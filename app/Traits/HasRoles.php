<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * HasRoles Trait
 *
 * Provides role and permission management methods to any Eloquent model.
 * Mix into a model with: use HasRoles;
 *
 * This trait powers:
 * - Assigning/removing roles to a user
 * - Checking if a user has a role
 * - Checking if a user has a permission (by inspecting their roles' permissions)
 * - Syncing roles
 *
 * All permission checks walk the chain: User → Roles → Permissions
 * This means you NEVER check role names for authorization — always check permissions.
 */
trait HasRoles
{
    // ─────────────────────────────────────────────
    // Relationship
    // ─────────────────────────────────────────────

    /**
     * Get all roles assigned to this model.
     *
     * Uses a polymorphic many-to-many relationship through model_has_roles.
     * model_type will be set to the fully-qualified class name of this model
     * (e.g., "App\Models\User") automatically by Eloquent's morphToMany.
     *
     * Usage: $user->roles  → Collection of Role models (with their permissions if eager-loaded)
     *
     * @return MorphToMany<Role>
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            Role::class,        // Related model
            'model',            // Morph name — maps to model_type + model_id on pivot
            'model_has_roles',  // Pivot table
            'model_id',         // FK on pivot pointing to THIS model
            'role_id'           // FK on pivot pointing to roles
        ); // Pivot has no timestamps — suppress Eloquent adding them
    }

    // ─────────────────────────────────────────────
    // Role Assignment Methods
    // ─────────────────────────────────────────────

    /**
     * Assign one or more roles to this user.
     *
     * Accepts a role name string, a Role model instance, or an array/collection of either.
     * Uses syncWithoutDetaching so existing roles are preserved — this is an additive operation.
     *
     * @param  string|Role|array<string|Role>|Collection<Role> $roles
     * @return void
     *
     * Example:
     *   $user->assignRole('admin');
     *   $user->assignRole(['staff', 'manager']);
     *   $user->assignRole($roleModel);
     */
    public function assignRole(string|Role|array|Collection $roles): void
    {
        $roleIds = $this->resolveRoleIds($roles);
        $this->roles()->syncWithoutDetaching($roleIds);
    }

    /**
     * Remove one or more roles from this user.
     *
     * @param  string|Role|array<string|Role>|Collection<Role> $roles
     * @return void
     *
     * Example:
     *   $user->removeRole('staff');
     */
    public function removeRole(string|Role|array|Collection $roles): void
    {
        $roleIds = $this->resolveRoleIds($roles);
        $this->roles()->detach($roleIds);
    }

    /**
     * Replace all current roles with the given set.
     *
     * Unlike assignRole (which is additive), this removes ALL existing roles
     * and sets exactly the roles provided. Use this in role-editing UIs.
     *
     * @param  string|Role|array<string|Role>|Collection<Role> $roles
     * @return void
     *
     * Example:
     *   $user->syncRoles(['manager', 'staff']); // user now has ONLY these two roles
     */
    public function syncRoles(string|Role|array|Collection $roles): void
    {
        $roleIds = $this->resolveRoleIds($roles);
        $this->roles()->sync($roleIds);
    }

    // ─────────────────────────────────────────────
    // Role Checking Methods
    // ─────────────────────────────────────────────

    /**
     * Check if the user has a specific role by name.
     *
     * Note: Prefer hasPermission() for authorization checks.
     * hasRole() is useful for UI decisions like "show admin panel link if admin".
     *
     * @param  string $roleName  Machine name of the role e.g. "admin", "staff"
     * @return bool
     *
     * Example:
     *   if ($user->hasRole('admin')) { ... }
     */
    public function hasRole(string $roleName): bool
    {
        // Uses the loaded roles collection if available — avoids extra query
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Check if the user has ANY of the given roles.
     *
     * @param  array<string> $roleNames  Array of role machine names
     * @return bool
     *
     * Example:
     *   if ($user->hasAnyRole(['admin', 'manager'])) { ... }
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles->whereIn('name', $roleNames)->isNotEmpty();
    }

    // ─────────────────────────────────────────────
    // Permission Checking Methods
    // ─────────────────────────────────────────────

    /**
     * Check if the user has a specific permission.
     *
     * Walks the chain: User → Roles → Permissions
     * This is the PRIMARY method you should use for authorization.
     * It checks ALL roles the user has and returns true if ANY role carries the permission.
     *
     * Performance note: This method works best when roles and their permissions are
     * eager-loaded: User::with('roles.permissions')->find($id)
     * Without eager loading, this triggers N+1 queries (one per role).
     *
     * @param  string $permissionName  Dot-notation permission e.g. "products.create"
     * @return bool
     *
     * Example:
     *   if ($user->hasPermission('products.delete')) { ... }
     */
    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the user has ALL of the given permissions.
     *
     * Use this when an action requires multiple capabilities simultaneously.
     *
     * @param  array<string> $permissionNames
     * @return bool
     *
     * Example:
     *   if ($user->hasAllPermissions(['products.create', 'categories.view'])) { ... }
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a flat collection of all permission names this user has.
     *
     * Useful for passing to a frontend to control UI visibility,
     * or for caching the user's permissions in a session.
     *
     * @return Collection<string>
     *
     * Example:
     *   $user->getAllPermissions();
     *   // Returns: ["products.view", "products.create", "stock.adjust", ...]
     */
    public function getAllPermissions(): Collection
    {
        return $this->roles
            ->flatMap(fn(Role $role) => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }

    // ─────────────────────────────────────────────
    // Internal Helper
    // ─────────────────────────────────────────────

    /**
     * Resolve mixed role input into an array of role IDs.
     *
     * Normalizes the many input formats (string name, Role instance, array of either)
     * into a simple array of integer IDs suitable for sync/attach/detach operations.
     *
     * @param  string|Role|array<string|Role>|Collection<Role> $roles
     * @return array<int>
     */
    private function resolveRoleIds(string|Role|array|Collection $roles): array
    {
        // Normalize to array
        $roles = $roles instanceof Collection ? $roles->all() : (array) $roles;

        return array_map(function ($role) {
            // If it's already a Role model, return its ID directly
            if ($role instanceof Role) {
                return $role->id;
            }

            // It's a string name — look it up in DB (fail loudly if not found)
            return Role::where('name', $role)->firstOrFail()->id;
        }, $roles);
    }
}
