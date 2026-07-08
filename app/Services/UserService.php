<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

/**
 * UserService
 *
 * Owns the business logic for creating and updating users, specifically
 * coordinating "save user fields" with "sync their role set" as one
 * atomic operation — and handling the password-is-optional-on-edit rule
 * in exactly one place, so no controller can forget it.
 */
class UserService
{
    /**
     * @param UserRepository $users
     */
    public function __construct(private readonly UserRepository $users) {}

    /**
     * Create a new user and assign their initial role(s).
     *
     * @param array<string, mixed> $data
     * @param array<int> $roleIds
     * @return User
     */
    public function createUser(array $data, array $roleIds): User
    {
        return DB::transaction(function () use ($data, $roleIds) {
            $user = $this->users->create($data);

            // Calling sync() directly on the roles() relationship (not the
            // HasRoles::syncRoles() helper) — that helper expects role NAMES
            // or Role instances and does a database lookup per entry. We
            // already have validated role IDs from the checkbox form
            // (exists:roles,id already confirmed they're real), so syncing
            // the pivot table directly is both correct and one query
            // instead of N lookup queries.
            $user->roles()->sync($roleIds);

            return $user->fresh('roles');
        });
    }

    /**
     * Update an existing user's fields and replace their role set.
     *
     * @param int $userId
     * @param array<string, mixed> $data
     * @param array<int> $roleIds
     * @return User
     */
    public function updateUser(int $userId, array $data, array $roleIds): User
    {
        return DB::transaction(function () use ($userId, $data, $roleIds) {
            $user = $this->users->update($userId, $data);
            $user->roles()->sync($roleIds);

            return $user->fresh('roles');
        });
    }
}
