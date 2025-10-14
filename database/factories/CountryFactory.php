<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->country(),
            'code' => fake()->unique()->countryCode(),
            'currency_code' => fake()->currencyCode(),
            'currency_symbol' => fake()->randomElement(['$', '€', '£', '¥', '₹', '₽']),
            'timezone' => fake()->timezone(),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the country is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the country is in Asia.
     */
    public function asia(): static
    {
        return $this->state(fn (array $attributes) => [
            'continent' => 'Asia',
        ]);
    }

    /**
     * Indicate that the country is in Europe.
     */
    public function europe(): static
    {
        return $this->state(fn (array $attributes) => [
            'continent' => 'Europe',
        ]);
    }

    /**
     * Indicate that the country is in North America.
     */
    public function northAmerica(): static
    {
        return $this->state(fn (array $attributes) => [
            'continent' => 'North America',
        ]);
    }
}
