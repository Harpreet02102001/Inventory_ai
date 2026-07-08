<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RoleInUseException;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;

/**
 * RoleService
 *
 * Owns the business logic for creating and updating roles, specifically
 * the "sync permissions to this role" operation — a role's fields and
 * its permission set are saved together as one atomic unit, so a
 * failure partway through never leaves a role with the wrong name but
 * correct permissions, or vice versa.
 */
class RoleService
{
    /**
     * @param RoleRepository $roles
     */
    public function __construct(private readonly RoleRepository $roles) {}

    /**
     * Create a new role and attach its initial set of permissions.
     *
     * @param array<string, mixed> $data Validated fields: name, display_name, description
     * @param array<int> $permissionIds IDs of permissions to grant this role
     * @return Role The created role, with permissions loaded
     */
    public function createRole(array $data, array $permissionIds): Role
    {
        return DB::transaction(function () use ($data, $permissionIds) {
            $role = $this->roles->create($data);

            // sync() on a freshly created role is equivalent to "attach
            // all of these" — there's nothing to remove yet, but using
            // sync() consistently (rather than attach() here and sync()
            // in update()) means both methods behave identically and
            // there's only one pivot-saving pattern to remember.
            $role->permissions()->sync($permissionIds);

            return $role->fresh('permissions');
        });
    }

    /**
     * Update an existing role's fields and replace its permission set.
     *
     * @param int $roleId
     * @param array<string, mixed> $data Validated fields
     * @param array<int> $permissionIds The COMPLETE new set of permission IDs —
     *        any permission not in this array will be removed from the role
     * @return Role
     */
    public function updateRole(int $roleId, array $data, array $permissionIds): Role
    {
        return DB::transaction(function () use ($roleId, $data, $permissionIds) {
            $role = $this->roles->update($roleId, $data);
            $role->permissions()->sync($permissionIds);

            return $role->fresh('permissions');
        });
    }

    /**
     * Delete a role, refusing if any user currently holds it.
     *
     * @param int $roleId
     * @return void
     *
     * @throws RoleInUseException If one or more users still have this role
     */
    public function deleteRole(int $roleId): void
    {
        if ($this->roles->hasUsers($roleId)) {
            throw new RoleInUseException(
                'Cannot delete this role — one or more users are still assigned to it. Reassign them first.'
            );
        }

        DB::transaction(function () use ($roleId) {
            $this->roles->delete($roleId);
        });
    }
}
