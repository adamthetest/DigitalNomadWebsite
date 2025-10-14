<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Neighborhood>
 */
class NeighborhoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => null, // Will be set explicitly in tests
            'name' => fake()->streetName(),
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'type' => fake()->randomElement(['residential', 'business', 'tourist', 'mixed']),
            'cost_level' => fake()->numberBetween(1, 5),
            'safety_score' => fake()->numberBetween(1, 10),
            'internet_speed_mbps' => fake()->numberBetween(10, 1000),
            'amenities' => fake()->randomElements(['Grocery Store', 'Restaurant', 'Gym', 'Park', 'Public Transport'], 3),
            'transportation' => [
                'metro' => fake()->boolean(),
                'bus' => fake()->boolean(),
                'taxi' => fake()->boolean(),
            ],
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the neighborhood is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the neighborhood is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_level' => 'high',
        ]);
    }

    /**
     * Indicate that the neighborhood is affordable.
     */
    public function affordable(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_level' => 'low',
        ]);
    }

    /**
     * Indicate that the neighborhood is safe.
     */
    public function safe(): static
    {
        return $this->state(fn (array $attributes) => [
            'safety_rating' => fake()->numberBetween(8, 10),
        ]);
    }
}
