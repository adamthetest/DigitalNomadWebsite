<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'favoritable_id' => fake()->numberBetween(1, 100),
            'favoritable_type' => fake()->randomElement([
                'App\Models\City',
                'App\Models\Article',
                'App\Models\Deal'
            ]),
            'category' => fake()->randomElement(['city', 'article', 'deal']),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the favorite is for a city.
     */
    public function city(): static
    {
        return $this->state(fn (array $attributes) => [
            'favoritable_type' => 'App\Models\City',
            'category' => 'city',
        ]);
    }

    /**
     * Indicate that the favorite is for an article.
     */
    public function article(): static
    {
        return $this->state(fn (array $attributes) => [
            'favoritable_type' => 'App\Models\Article',
            'category' => 'article',
        ]);
    }

    /**
     * Indicate that the favorite is for a deal.
     */
    public function deal(): static
    {
        return $this->state(fn (array $attributes) => [
            'favoritable_type' => 'App\Models\Deal',
            'category' => 'deal',
        ]);
    }

    /**
     * Indicate that the favorite has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->paragraph(),
        ]);
    }

    /**
     * Indicate that the favorite has array notes.
     */
    public function withArrayNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => [
                'personal' => fake()->sentence(),
                'cost' => fake()->sentence(),
                'weather' => fake()->sentence(),
            ],
        ]);
    }
}
