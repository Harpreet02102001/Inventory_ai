<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // SupplierSeeder
    public function run(): void
    {
        Supplier::factory()->count(10)->create();

        $this->command->info('✅ Suppliers seeded.');
    }
}
