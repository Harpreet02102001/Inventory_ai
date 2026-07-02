<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_model_has_roles_table
 *
 * This pivot table assigns roles to any Eloquent model, not just users.
 * The `model_type` + `model_id` pattern is called a polymorphic relationship.
 *
 * Why polymorphic? Today roles are assigned to Users. Tomorrow you might have
 * an API Client model or a Team model that also needs roles — this table handles
 * all of them without schema changes. You simply insert a different model_type.
 *
 * Example row:
 *   role_id = 1
 *   model_type = "App\Models\User"
 *   model_id = 5
 * → User #5 has Role #1
 *
 * Dependency: roles table and users table must exist first.
 */
return new class extends Migration
{
    /**
     * Create the model_has_roles polymorphic pivot table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');               // Fully-qualified class name e.g. "App\Models\User"
            $table->unsignedBigInteger('model_id');     // Primary key of the related model

            // Composite primary key: one model can't have the same role twice
            $table->primary(['role_id', 'model_type', 'model_id']);

            // Index on model_type + model_id speeds up "fetch all roles for this user"
            $table->index(['model_type', 'model_id'], 'model_has_roles_model_index');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Drop the model_has_roles table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_roles');
    }
};
