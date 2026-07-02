<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * RoleSeeder
 *
 * Creates default system roles and assigns the correct permissions to each.
 * Uses the permission matrix defined below as the authoritative source
 * for what each role can and cannot do.
 *
 * Run order: SECOND — must run after PermissionSeeder.
 *
 * Command: php artisan db:seed --class=RoleSeeder
 */
class RoleSeeder extends Seeder
{
    /**
     * Permission matrix.
     *
     * Defines which permissions each role receives.
     * Key   → role machine name
     * Value → array of permission dot-notation names
     *
     * To modify a role's access: edit this array and re-run the seeder.
     * The sync() call below will add/remove permissions to match exactly.
     *
     * @var array<string, array<string>>
     */
    private array $rolePermissions = [

        'admin' => [
            // Admin has every permission in the system
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'stock.view',
            'stock.adjust',
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.approve',
            'purchase_orders.cancel',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.cancel',
            'reports.view',
            'reports.export',
            'dashboard.view',
        ],

        'manager' => [
            // Manager can do everything except user/role management
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'stock.view',
            'stock.adjust',
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.approve',
            'purchase_orders.cancel',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.cancel',
            'reports.view',
            'reports.export',
            'dashboard.view',
        ],

        'staff' => [
            // Staff can view products and adjust stock only
            'products.view',
            'stock.view',
            'stock.adjust',
            'dashboard.view',
        ],

        'sales_person' => [
            // Sales person can view products and manage sales
            'products.view',
            'stock.view',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.cancel',
            'dashboard.view',
        ],

        'normal_user' => [
            // Normal user has read-only access to products
            'products.view',
            'dashboard.view',
        ],

    ];

    /**
     * Role definitions.
     *
     * Machine name → display name + description.
     * These are upserted, so adding a new role here and re-running
     * the seeder will create it without affecting existing roles.
     *
     * @var array<string, array<string, string>>
     */
    private array $roles = [
        'admin' => [
            'display_name' => 'Administrator',
            'description'  => 'Full system access. Can manage users, roles, and all modules.',
        ],
        'manager' => [
            'display_name' => 'Manager',
            'description'  => 'Can manage inventory, orders, and sales. Cannot manage users or roles.',
        ],
        'staff' => [
            'display_name' => 'Staff',
            'description'  => 'Can view products and adjust stock quantities only.',
        ],
        'sales_person' => [
            'display_name' => 'Sales Person',
            'description'  => 'Can view products and manage sales transactions.',
        ],
        'normal_user' => [
            'display_name' => 'Normal User',
            'description'  => 'Read-only access to product listings.',
        ],
    ];

    /**
     * Seed roles and assign permissions to each role.
     *
     * Uses updateOrCreate for roles (idempotent) and sync() for permissions
     * so re-running the seeder updates permission assignments to match the
     * matrix above exactly — additions and removals both reflected.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->roles as $machineName => $details) {

            // Create or update the role record
            $role = Role::updateOrCreate(
                ['name' => $machineName],
                [
                    'display_name' => $details['display_name'],
                    'description'  => $details['description'],
                ]
            );

            // Fetch the permission IDs for this role from the matrix
            $permissionNames = $this->rolePermissions[$machineName] ?? [];

            $permissionIds = Permission::whereIn('name', $permissionNames)
                ->pluck('id')
                ->toArray();

            /**
             * sync() replaces ALL existing permission assignments for this role
             * with exactly the permissions listed in the matrix.
             * This ensures that if you remove a permission from the matrix
             * and re-run the seeder, it gets removed from the role too.
             */
            $role->permissions()->sync($permissionIds);

            $this->command->info("✅ Role [{$details['display_name']}] seeded with " . count($permissionIds) . " permissions.");
        }
    }
}
