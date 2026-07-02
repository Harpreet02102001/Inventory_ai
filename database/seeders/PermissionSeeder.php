<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PermissionSeeder
 *
 * Seeds all system permissions from the central config/permissions.php file.
 * This seeder is safe to run multiple times (idempotent) — it uses updateOrCreate
 * so re-running it won't create duplicates or throw unique constraint errors.
 *
 * Run order: FIRST — roles depend on permissions existing.
 *
 * Command: php artisan db:seed --class=PermissionSeeder
 */
class PermissionSeeder extends Seeder
{
    /**
     * Seed all permissions from config/permissions.php.
     *
     * Iterates over each group and its permissions, upserting each one.
     * The group_name is taken from the config array key (e.g., "Products", "Stock").
     *
     * @return void
     */
    public function run(): void
    {
        // Load the permissions map from config
        $groups = config('permissions');

        foreach ($groups as $groupName => $permissions) {
            foreach ($permissions as $permission) {
                /**
                 * updateOrCreate:
                 *   First argument  → the unique identifier to search by (name)
                 *   Second argument → values to set (on create OR update)
                 *
                 * This means: "find a permission with this name, update it if found,
                 * create it if not" — makes the seeder safely re-runnable.
                 */
                Permission::updateOrCreate(
                    ['name' => $permission['name']],
                    [
                        'display_name' => $permission['display_name'],
                        'group_name'   => $groupName,
                    ]
                );
            }
        }

        $this->command->info('✅ Permissions seeded successfully.');
    }
}
