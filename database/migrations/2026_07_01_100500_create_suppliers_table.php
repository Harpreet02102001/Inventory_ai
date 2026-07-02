<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_suppliers_table
 *
 * Suppliers are the source of inventory. Each product is linked to one supplier.
 * Soft deletes ensure purchase order history is intact even after a supplier
 * relationship ends.
 *
 * Dependency: none.
 */
return new class extends Migration
{
    /**
     * Create the suppliers table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Supplier contact person's name
            $table->string('email')->unique();              // Business contact email, unique constraint
            $table->string('phone');                        // Stored as string to support formats like +91-98765-43210
            $table->text('address')->nullable();            // Full mailing address
            $table->string('company_name');                 // The supplier's company/business name
            $table->enum('status', ['active', 'inactive'])
                  ->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Drop the suppliers table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};