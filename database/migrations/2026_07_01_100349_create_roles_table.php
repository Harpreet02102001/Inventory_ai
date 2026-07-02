<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_roles_table
 *
 * Roles are named groups of permissions. A role itself does nothing —
 * it is simply a label that carries a set of permissions. Users are then
 * assigned one or more roles via the model_has_roles pivot table.
 *
 * Dependency: none — roles are a root entity in the RBAC system.
 */
return new class extends Migration
{
    /**
     * Create the roles table.
     *
     * We separate `name` (machine-readable slug used in code, e.g. "sales_person")
     * from `display_name` (human-readable label for UI, e.g. "Sales Person").
     * This prevents UI label changes from breaking any code that checks role names.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // Machine name: admin, staff, manager, sales_person
            $table->string('display_name');             // UI label: "Sales Person", "Warehouse Manager"
            $table->string('description')->nullable();  // Optional: explains what this role is for
            $table->timestamps();
        });
    }

    /**
     * Drop the roles table.
     *
     * role_has_permissions and model_has_roles both reference this table,
     * so their down() methods must run first (Laravel runs in reverse order).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
