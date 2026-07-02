<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CategoryFactory
 *
 * Generates realistic fake category data for development seeding and testing.
 * The definition() method returns the "default" state of a category.
 * States (like inactive()) can override specific fields.
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * fake()->unique() ensures no duplicate category names are generated
     * within a single factory run, respecting the unique DB constraint.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'status'      => 'active',
        ];
    }

    /**
     * State: inactive category.
     *
     * Usage: Category::factory()->inactive()->create()
     * Useful for testing that inactive categories are excluded from product forms.
     *
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
