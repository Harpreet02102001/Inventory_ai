<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * RoleRepository
 *
 * Handles queries for the Role model. Roles are a small, rarely-changing
 * table (no pagination needed — an app has a handful of roles, not
 * thousands), so getAll-style methods are appropriate here unlike the
 * paginated approach used for Products/Sales/etc.
 */
class RoleRepository extends BaseRepository
{
    /**
     * @param Role $role Injected fresh by the service container
     */
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

    /**
     * All roles with their permission count and assigned-user count
     * attached — used on the Roles list page so an Admin can see at a
     * glance how many permissions and users each role has, without
     * navigating into each one individually.
     *
     * withCount() runs one additional lightweight COUNT query per
     * relationship (2 total here), NOT one query per role — avoiding
     * the N+1 problem the same way Category's index page does.
     *
     * @return Collection<int, Role>
     */
    public function getAllWithCounts(): Collection
    {
        return $this->model->withCount(['permissions', 'users'])->orderBy('name')->get();
    }

    /**
     * Find a role with its permissions eager loaded — used on the edit
     * form to pre-check which permission checkboxes this role currently has.
     *
     * @param int $id
     * @return Role
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findWithPermissions(int $id): Role
    {
        return $this->model->with('permissions')->findOrFail($id);
    }

    /**
     * Whether any user currently holds this role — checked before
     * deletion to avoid silently stripping access from active accounts.
     *
     * @param int $roleId
     * @return bool
     */
    public function hasUsers(int $roleId): bool
    {
        return $this->model->where('id', $roleId)->whereHas('users')->exists();
    }
}
