<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // ProductSeeder
    public function run(): void
    {
        // Fetch existing categories and suppliers — reuse them
        // instead of letting the factory create new ones for every product
        $categories = Category::where('status', 'active')->pluck('id');
        $suppliers   = Supplier::where('status', 'active')->pluck('id');

        // 40 regular products using existing categories and suppliers
        Product::factory()
            ->count(40)
            ->create([
                'category_id' => fake()->randomElement($categories),
                'supplier_id' => fake()->randomElement($suppliers),
            ]);

        // 10 low stock products for dashboard testing
        Product::factory()
            ->count(10)
            ->lowStock()
            ->create([
                'category_id' => fake()->randomElement($categories),
                'supplier_id' => fake()->randomElement($suppliers),
            ]);

        $this->command->info('✅ Products seeded (40 regular + 10 low stock).');
    }
}
