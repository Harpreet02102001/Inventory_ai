<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission Model
 *
 * Represents a single granular action in the system using dot-notation naming.
 * Example permission names: "products.create", "stock.adjust", "reports.export"
 *
 * Permissions are assigned to Roles, never directly to Users.
 * To check if a user can perform an action, you check their roles' permissions.
 *
 * @property int    $id
 * @property string $name          Machine-readable dot-notation key
 * @property string $display_name  Human-readable label for UI
 * @property string $group_name    Module group for UI grouping
 */
class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * Mass assignment protection means only these columns can be set via
     * Permission::create([...]) or $permission->fill([...]). Any column
     * not listed here will be silently ignored, protecting against
     * malicious input injecting unexpected fields.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'group_name',
    ];

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    /**
     * Get all roles that have this permission assigned.
     *
     * Defines a many-to-many relationship between Permission and Role
     * through the role_has_permissions pivot table.
     *
     * Usage: $permission->roles  → Collection of Role models
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,            // Related model
            'role_has_permissions', // Pivot table name
            'permission_id',        // FK on pivot pointing to THIS model
            'role_id'               // FK on pivot pointing to the RELATED model
        );
    }
}