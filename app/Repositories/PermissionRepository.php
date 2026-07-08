<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Support\Collection;

/**
 * PermissionRepository
 *
 * Permissions have no CRUD UI in this app — they're seeded once from
 * config/permissions.php and rarely change. This repository exists
 * solely to READ them in the shape the Role create/edit forms need:
 * grouped by module, for rendering as labeled checkbox sections.
 */
class PermissionRepository extends BaseRepository
{
    /**
     * @param Permission $permission Injected fresh by the service container
     */
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

    /**
     * All permissions, grouped by their group_name (e.g. "Products", "Stock").
     *
     * groupBy() here is an Eloquent Collection method, not a SQL GROUP BY —
     * it runs in PHP after fetching, turning a flat list into
     * ['Products' => Collection[...], 'Stock' => Collection[...], ...],
     * which the Blade view iterates to render one labeled section per module.
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    public function getAllGroupedByModule(): Collection
    {
        return $this->model->orderBy('group_name')->get()->groupBy('group_name');
    }
}
