<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_categories_table
 *
 * Categories group products for filtering, reporting, and UI organization.
 * Soft deletes are used instead of hard deletes so that historical product
 * records still reference a valid category even after it's "deleted" from the UI.
 *
 * Dependency: none.
 */
return new class extends Migration
{
    /**
     * Create the categories table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();               // e.g., "Electronics" — must be distinct
            $table->text('description')->nullable();        // Optional, no max enforced at DB level (handle in validation)
            $table->enum('status', ['active', 'inactive'])
                ->default('active');                      // Inactive categories can't be assigned to new products
            $table->timestamps();
            $table->softDeletes();                          // Adds deleted_at — enables $model->delete() without DB removal
        });
    }

    /**
     * Drop the categories table.
     *
     * Products reference this table via category_id, so products must be
     * dropped first — handled by Laravel's reverse migration order.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
