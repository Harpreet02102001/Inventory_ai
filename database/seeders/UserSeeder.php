<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 *
 * Seeds the default system users for each role.
 * These credentials are for development and demo purposes.
 * In production, change passwords immediately after first login.
 *
 * Run order: THIRD — must run after RoleSeeder.
 *
 * Command: php artisan db:seed --class=UserSeeder
 */
class UserSeeder extends Seeder
{
    /**
     * Default users to seed.
     *
     * Each entry defines the user's data and which role to assign.
     * Passwords are plain text here — Hash::make() is applied below
     * before inserting (the 'hashed' cast on User model also handles
     * this, but we Hash::make explicitly for clarity).
     *
     * @var array<int, array<string, string>>
     */
    private array $users = [
        [
            'name'     => 'System Administrator',
            'email'    => 'admin@inventory.com',
            'password' => 'Admin@12345',
            'status'   => 'active',
            'role'     => 'admin',
        ],
        [
            'name'     => 'Store Manager',
            'email'    => 'manager@inventory.com',
            'password' => 'Manager@12345',
            'status'   => 'active',
            'role'     => 'manager',
        ],
        [
            'name'     => 'Staff Member',
            'email'    => 'staff@inventory.com',
            'password' => 'Staff@12345',
            'status'   => 'active',
            'role'     => 'staff',
        ],
        [
            'name'     => 'Sales Person',
            'email'    => 'sales@inventory.com',
            'password' => 'Sales@12345',
            'status'   => 'active',
            'role'     => 'sales_person',
        ],
    ];

    /**
     * Seed default users and assign their roles.
     *
     * Uses updateOrCreate on email so re-running the seeder
     * won't create duplicate users if they already exist.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->users as $userData) {

            // Extract role before creating user (not a DB column)
            $roleName = $userData['role'];
            unset($userData['role']);

            // Create or update the user
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'status'   => $userData['status'],
                ]
            );

            /**
             * assignRole() comes from our custom HasRoles trait.
             * It resolves the role name to an ID and syncs without detaching,
             * so existing roles are preserved if any were manually added.
             */
            $user->assignRole($roleName);

            $this->command->info("✅ User [{$userData['email']}] seeded with role [{$roleName}].");
        }
    }
}
