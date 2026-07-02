<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_stock_histories_table
 *
 * Every stock change — whether by staff, admin, purchase order receipt, or sale —
 * is immutably recorded here. This table is append-only: we never update or delete
 * rows. It is a complete audit trail for inventory movement.
 *
 * The `type` enum is intentionally broader than just "add/reduce" so it covers
 * stock changes from purchase order receipts and sales later without schema changes.
 *
 * Dependency: products table, users table.
 */
return new class extends Migration
{
    /**
     * Create the stock_histories table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();  // Never delete a product that has stock history

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();  // Preserve history even if user is deactivated (not deleted)

            $table->unsignedInteger('old_quantity');        // Quantity before this change
            $table->integer('changed_quantity');            // Positive = added, Negative = reduced
            $table->unsignedInteger('new_quantity');        // Quantity after this change

            $table->enum('type', [
                'add',          // Manual stock addition by staff/admin
                'reduce',       // Manual stock reduction by staff/admin
                'purchase',     // Stock added via a received purchase order
                'sale',         // Stock reduced via a confirmed sale
                'adjustment',   // Correction entry by admin (e.g. after physical stock count)
            ]);

            $table->string('remarks')->nullable();          // Optional note explaining the change

            // No updated_at — stock history rows are immutable once created
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Drop the stock_histories table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
