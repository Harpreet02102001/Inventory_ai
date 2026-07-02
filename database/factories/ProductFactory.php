<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ProductFactory
 *
 * Generates realistic product data.
 * Automatically creates related Category and Supplier if not provided,
 * ensuring relational integrity in every generated product.
 */
class ProductFactory extends Factory
{
    /**
     * Define the default state for a product.
     *
     * purchase_price is generated first, then selling_price is set to
     * always be higher — respecting the business rule at factory level too.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(2, 10, 500);

        return [
            // If no category/supplier is passed, factory creates them automatically
            'category_id'         => Category::factory(),
            'supplier_id'         => Supplier::factory(),
            'name'                => fake()->words(3, true),
            'sku'                 => 'SKU-' . fake()->unique()->numerify('######'),
            'description'         => fake()->optional()->paragraph(),
            'purchase_price'      => $purchasePrice,
            'selling_price'       => fake()->randomFloat(2, $purchasePrice + 1, $purchasePrice + 500),
            'stock_quantity'      => fake()->numberBetween(0, 200),
            'low_stock_threshold' => 10,
            'image'               => null,
            'status'              => 'active',
        ];
    }

    /**
     * State: low stock product (stock at or below threshold).
     *
     * Usage: Product::factory()->lowStock()->create()
     * Useful for testing dashboard low-stock alerts.
     *
     * @return static
     */
    public function lowStock(): static
    {
        return $this->state([
            'stock_quantity'      => fake()->numberBetween(0, 10),
            'low_stock_threshold' => 10,
        ]);
    }

    /**
     * State: out of stock product.
     *
     * @return static
     */
    public function outOfStock(): static
    {
        return $this->state(['stock_quantity' => 0]);
    }
}
