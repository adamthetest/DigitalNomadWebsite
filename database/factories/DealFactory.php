<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraphs(2, true),
            'deal_url' => fake()->url(),
            'provider' => fake()->company(),
            'category' => fake()->randomElement(['accommodation', 'transport', 'insurance', 'banking', 'vpn', 'gear', 'other']),
            'original_price' => fake()->numberBetween(50, 500),
            'discounted_price' => fake()->numberBetween(20, 300),
            'currency' => fake()->randomElement(['USD', 'EUR', 'THB', 'GBP']),
            'discount_percentage' => fake()->numberBetween(10, 70),
            'promo_code' => fake()->optional()->regexify('[A-Z0-9]{6,10}'),
            'valid_from' => fake()->dateTimeBetween('-1 month', 'now'),
            'valid_until' => fake()->dateTimeBetween('now', '+3 months'),
            'terms_conditions' => fake()->optional()->paragraph(),
            'image' => fake()->optional()->imageUrl(400, 300, 'business'),
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
            'click_count' => fake()->numberBetween(0, 1000),
            'conversion_count' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the deal is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the deal is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the deal is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_until' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the deal is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => fake()->dateTimeBetween('now', '+1 month'),
            'valid_until' => fake()->dateTimeBetween('+1 month', '+3 months'),
        ]);
    }

    /**
     * Indicate that the deal has a high discount.
     */
    public function highDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => fake()->numberBetween(50, 80),
        ]);
    }
}
