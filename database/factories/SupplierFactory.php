<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SupplierFactory
 *
 * Generates realistic supplier data using Faker's company, person, and contact methods.
 */
class SupplierFactory extends Factory
{
    /**
     * Define the default state for a supplier.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => fake()->name(),
            'email'        => fake()->unique()->companyEmail(),
            'phone'        => fake()->phoneNumber(),
            'address'      => fake()->address(),
            'company_name' => fake()->company(),
            'status'       => 'active',
        ];
    }

    /**
     * State: inactive supplier.
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
