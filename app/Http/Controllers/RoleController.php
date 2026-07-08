<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\RoleInUseException;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * RoleController
 *
 * Handles HTTP requests for Role management. Permission-syncing logic
 * lives in RoleService; this controller only translates HTTP in and out.
 */
class RoleController extends Controller
{
    /**
     * @param RoleService $roleService
     * @param RoleRepository $roles
     * @param PermissionRepository $permissions Populates the grouped checkbox list
     */
    public function __construct(
        private readonly RoleService $roleService,
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions,
    ) {}

    /**
     * Display all roles with permission/user counts.
     *
     * @return View
     */
    public function index(): View
    {
        return view('roles.index', [
            'roles' => $this->roles->getAllWithCounts(),
        ]);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return View
     */
    public function create(): View
    {
        return view('roles.create', [
            'groupedPermissions' => $this->permissions->getAllGroupedByModule(),
        ]);
    }

    /**
     * Persist a new role with its permission set.
     *
     * @param StoreRoleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        try {
            $role = $this->roleService->createRole(
                data: $request->safe()->only(['name', 'display_name', 'description']),
                permissionIds: $request->validated('permissions', []),
            );
        } catch (Throwable $e) {
            Log::error('Role creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the role. Please try again.');
        }

        return redirect()
            ->route('roles.index')
            ->with('success', "Role \"{$role->display_name}\" created successfully.");
    }

    /**
     * Show the pre-filled edit form, with current permissions checked.
     *
     * @param Role $role
     * @return View
     */
    public function edit(Role $role): View
    {
        return view('roles.edit', [
            'role'               => $this->roles->findWithPermissions($role->id),
            'groupedPermissions' => $this->permissions->getAllGroupedByModule(),
        ]);
    }

    /**
     * Persist changes to a role and its permission set.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return RedirectResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->updateRole(
                roleId: $role->id,
                data: $request->safe()->only(['name', 'display_name', 'description']),
                permissionIds: $request->validated('permissions', []),
            );
        } catch (Throwable $e) {
            Log::error('Role update failed', ['role_id' => $role->id, 'error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the role. Please try again.');
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role, refusing if any user still holds it.
     *
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roleService->deleteRole($role->id);
        } catch (RoleInUseException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Role deletion failed', ['role_id' => $role->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Something went wrong while deleting the role. Please try again.');
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
