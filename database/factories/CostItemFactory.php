<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostItem>
 */
class CostItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => fake()->randomElement([
                'Apartment (1 bedroom)', 'Apartment (2 bedroom)', 'Studio Apartment',
                'Meal at restaurant', 'Coffee', 'Beer', 'Taxi ride', 'Public transport',
                'Gym membership', 'Internet', 'Mobile phone', 'Groceries',
            ]),
            'category' => fake()->randomElement(['accommodation', 'food', 'transport', 'entertainment', 'utilities']),
            'price_min' => fake()->numberBetween(5, 50),
            'price_max' => fake()->numberBetween(50, 500),
            'currency' => fake()->randomElement(['USD', 'EUR', 'THB', 'GBP']),
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the cost item is for accommodation.
     */
    public function accommodation(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'accommodation',
            'name' => fake()->randomElement(['Apartment (1 bedroom)', 'Apartment (2 bedroom)', 'Studio Apartment']),
            'price_min' => fake()->numberBetween(200, 1000),
            'price_max' => fake()->numberBetween(1000, 3000),
        ]);
    }

    /**
     * Indicate that the cost item is for food.
     */
    public function food(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'food',
            'name' => fake()->randomElement(['Meal at restaurant', 'Coffee', 'Beer', 'Groceries']),
            'price_min' => fake()->numberBetween(2, 20),
            'price_max' => fake()->numberBetween(20, 100),
        ]);
    }

    /**
     * Indicate that the cost item is for transport.
     */
    public function transport(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'transport',
            'name' => fake()->randomElement(['Taxi ride', 'Public transport', 'Uber ride']),
            'price_min' => fake()->numberBetween(1, 10),
            'price_max' => fake()->numberBetween(10, 50),
        ]);
    }
}
