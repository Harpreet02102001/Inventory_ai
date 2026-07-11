<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Support\Collection;

/**
 * PermissionRepository
 *
 * Handles queries for the Permission model. Permissions are reference data —
 * seeded once from config/permissions.php, rarely change after setup.
 * No pagination needed.
 *
 * Inherits standard methods from BaseRepository:
 *   - findById(), findOrFail(), getAll(), getActive(), getActiveForSelect()
 *   - create(), update(), delete()
 *   - count(), countActive()
 *
 * This repository adds domain-specific queries for role/permission assignment UIs.
 */
class PermissionRepository extends BaseRepository
{
    /**
     * @param Permission $permission Injected by the service container
     */
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

    /**
     * All permissions grouped by their module (group_name).
     *
     * Used on the role create/edit forms to render permission checkboxes
     * organized into labeled sections per module (e.g., "Products", "Stock").
     *
     * groupBy() here is a Laravel Collection method (runs in PHP after fetching),
     * not a SQL GROUP BY. Transforms:
     *   [Permission(id:1, name:'products.view', group_name:'Products'), ...]
     * Into:
     *   ['Products' => [Permission(...), ...], 'Stock' => [...], ...]
     *
     * The Blade view iterates these groups and renders one <fieldset> per module.
     *
     * Ordered by group_name for consistent UI grouping.
     *
     * @return Collection<string, Collection<int, Permission>>  Permissions grouped by module
     */
    public function getAllGroupedByModule(): Collection
    {
        return $this->model
            ->orderBy('group_name')
            ->get()
            ->groupBy('group_name');
    }

    /**
     * Active permissions for use in dropdown/select elements.
     *
     * Inherits from BaseRepository::getActiveForSelect() but can be overridden
     * if you need different column selection or ordering.
     *
     * Currently uses the default: ['id', 'name'] ordered by name.
     *
     * @return Collection<int, Permission>  Active permissions with id and name only
     */
    // Inherits from BaseRepository, override if needed:
    // public function getActiveForSelect(): Collection
    // {
    //     return $this->model
    //         ->active()
    //         ->orderBy('name')
    //         ->get(['id', 'name', 'display_name']);
    // }
}
