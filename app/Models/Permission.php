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
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'group_name',
    ];

    /**
     * Get all roles that have this permission assigned.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'permission_id',
            'role_id'
        );
    }
}
