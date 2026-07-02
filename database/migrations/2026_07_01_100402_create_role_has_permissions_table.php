<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_role_has_permissions_table
 *
 * This is a many-to-many pivot table between roles and permissions.
 * One role can have many permissions; one permission can belong to many roles.
 *
 * We use a composite primary key (role_id + permission_id) instead of a
 * separate auto-increment id — because the combination itself is the unique
 * identifier. This also prevents accidental duplicate assignments.
 *
 * Dependency: roles table, permissions table — both must exist first.
 */
return new class extends Migration
{
    /**
     * Create the role_has_permissions pivot table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {
            // Composite primary key — the pair is the identity
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');

            $table->primary(['role_id', 'permission_id']); // Composite PK prevents duplicates

            // If a role is deleted, its permission assignments are also removed
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            // If a permission is deleted, its role assignments are also removed
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });
    }

    /**
     * Drop the role_has_permissions pivot table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
    }
};
