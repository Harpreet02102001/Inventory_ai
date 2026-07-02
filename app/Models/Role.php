<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model
 *
 * A Role is a named group of permissions assigned to users.
 * Examples: admin, staff, manager, sales_person, normal_user
 *
 * Roles connect to Users via the model_has_roles polymorphic pivot table.
 * Roles connect to Permissions via the role_has_permissions pivot table.
 *
 * @property int    $id
 * @property string $name          Machine name used in code: "sales_person"
 * @property string $display_name  UI label: "Sales Person"
 * @property string $description   What this role is for
 */
class Role extends Model
{
    /**
     * Mass assignable attributes.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    /**
     * Get all permissions belonging to this role.
     *
     * Many-to-many through role_has_permissions pivot.
     *
     * Usage: $role->permissions  → Collection of Permission models
     *        $role->permissions()->pluck('name')  → ["products.create", "stock.adjust"]
     *
     * @return BelongsToMany<Permission>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all users who have this role assigned.
     *
     * This is a polymorphic many-to-many — we specify the related model (User),
     * the pivot table, and the morph name so Laravel knows model_type = "App\Models\User"
     *
     * Usage: $role->users  → Collection of User models
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',                // Morph name — maps to model_type and model_id columns
            'model_has_roles',      // Pivot table
            'role_id',              // FK pointing to roles
            'model_id'              // FK pointing to the morphable (user)
        );
    }

    // ─────────────────────────────────────────────
    // Helper Methods
    // ─────────────────────────────────────────────

    /**
     * Check if this role has a specific permission by name.
     *
     * Uses the loaded permissions collection if already eager-loaded,
     * avoiding an extra query.
     *
     * @param  string $permissionName  Dot-notation permission e.g. "products.create"
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }
}
