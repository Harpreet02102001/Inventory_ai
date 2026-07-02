<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // CategorySeeder
    public function run(): void
    {
        // Create 10 active + 3 inactive categories
        Category::factory()->count(10)->create();
        Category::factory()->count(3)->inactive()->create();

        $this->command->info('✅ Categories seeded.');
    }
}
