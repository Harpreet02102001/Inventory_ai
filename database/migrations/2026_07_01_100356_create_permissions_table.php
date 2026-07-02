<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_permissions_table
 *
 * Permissions are granular action identifiers using dot-notation:
 * "module.action" e.g. "products.create", "stock.adjust", "reports.export"
 *
 * The `group_name` column allows the UI to render permissions grouped by module,
 * which is essential when building a role-editing screen.
 *
 * Dependency: none — permissions are a root entity in the RBAC system.
 */
return new class extends Migration
{
    /**
     * Create the permissions table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // Dot-notation key: "products.create"
            $table->string('display_name');             // UI label: "Create Products"
            $table->string('group_name');               // Module group for UI: "Products", "Stock"
            $table->timestamps();
        });
    }

    /**
     * Drop the permissions table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
