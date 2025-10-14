<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => null, // Will be set explicitly in tests
            'name' => fake()->city(),
            'slug' => null, // Let the model generate it from name
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'description' => fake()->paragraphs(2, true),
            'overview' => fake()->paragraphs(1, true),
            'population' => fake()->numberBetween(100000, 20000000),
            'climate' => fake()->randomElement(['Tropical', 'Temperate', 'Desert', 'Mediterranean', 'Continental']),
            'internet_speed_mbps' => fake()->numberBetween(10, 100),
            'safety_score' => fake()->numberBetween(1, 10),
            'cost_of_living_index' => fake()->numberBetween(20, 100),
            'best_time_to_visit' => fake()->randomElement(['Year-round', 'Spring', 'Summer', 'Fall', 'Winter']),
            'highlights' => fake()->randomElements([
                'Historic Sites', 'Beaches', 'Mountains', 'Nightlife', 'Food Scene',
                'Shopping', 'Museums', 'Parks', 'Temples', 'Markets'
            ], 3),
            'images' => [
                fake()->imageUrl(800, 600, 'city'),
                fake()->imageUrl(800, 600, 'city'),
                fake()->imageUrl(800, 600, 'city')
            ],
            'is_featured' => fake()->boolean(30), // 30% chance of being featured
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the city is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the city is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the city has high cost of living.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_of_living_index' => fake()->numberBetween(80, 100),
        ]);
    }

    /**
     * Indicate that the city has low cost of living.
     */
    public function affordable(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_of_living_index' => fake()->numberBetween(20, 40),
        ]);
    }

    /**
     * Indicate that the city has fast internet.
     */
    public function fastInternet(): static
    {
        return $this->state(fn (array $attributes) => [
            'internet_speed_mbps' => fake()->numberBetween(50, 100),
        ]);
    }

    /**
     * Indicate that the city is very safe.
     */
    public function safe(): static
    {
        return $this->state(fn (array $attributes) => [
            'safety_score' => fake()->numberBetween(8, 10),
        ]);
    }
}
