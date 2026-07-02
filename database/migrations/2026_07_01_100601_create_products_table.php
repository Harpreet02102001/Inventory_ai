<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_products_table
 *
 * The central entity of the inventory system. A product belongs to one category
 * and one supplier, has pricing, stock tracking, and an optional image.
 *
 * `low_stock_threshold` is per-product configurable — not every product has
 * the same criticality level. This beats hardcoding "< 10" everywhere in code.
 *
 * Dependency: categories table, suppliers table.
 */
return new class extends Migration
{
    /**
     * Create the products table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Foreign keys — constrained() is shorthand for ->references('id')->on('categories')
            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();   // Prevent deleting a category that still has products

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();   // Prevent deleting a supplier that still has products

            $table->string('name');
            $table->string('sku')->unique();                // Stock Keeping Unit — must be globally unique
            $table->text('description')->nullable();

            // decimal(total_digits, decimal_places) — never use float for money
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);        // Must be > purchase_price (enforced in validation layer)

            $table->unsignedInteger('stock_quantity')
                ->default(0);                             // UNSIGNED = no negatives at DB level (also enforced in app)
            $table->unsignedInteger('low_stock_threshold')
                ->default(10);                            // Per-product configurable low stock alert level

            $table->string('image')->nullable();            // Stores relative path from storage/app/public/
            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Drop the products table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
