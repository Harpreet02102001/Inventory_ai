<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


/**
 * DatabaseSeeder
 *
 * The master seeder — entry point for php artisan db:seed.
 * Calls all seeders in strict dependency order.
 *
 * Essential seeders (safe for production) are called unconditionally.
 * Dev/fake data seeders are wrapped in an environment check.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run all database seeders in the correct order.
     *
     * @return void
     */
    public function run(): void
    {
        // ── Essential Data (always runs) ──────────────────────
        // These must exist for the application to function at all
        $this->call([
            PermissionSeeder::class,  // 1st: permissions are a root entity
            RoleSeeder::class,        // 2nd: roles depend on permissions
            UserSeeder::class,        // 3rd: users depend on roles
        ]);

        // ── Development / Demo Data (local + staging only) ────
        // Never seed fake data in production
        if (app()->environment(['local', 'staging'])) {
            $this->call([
                CategorySeeder::class,
                SupplierSeeder::class,
                ProductSeeder::class,
            ]);
        }
    }
}
