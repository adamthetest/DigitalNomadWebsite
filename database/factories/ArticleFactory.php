<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
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
            'city_id' => City::factory(),
            'title' => fake()->sentence(),
            'slug' => fake()->slug(),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'featured_image' => fake()->imageUrl(800, 600, 'city'),
            'images' => fake()->optional()->randomElements([
                fake()->imageUrl(800, 600, 'city'),
                fake()->imageUrl(800, 600, 'city'),
            ], 2),
            'type' => fake()->randomElement(['guide', 'news', 'review', 'comparison', 'tips']),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'tags' => fake()->randomElements(['travel', 'digital nomad', 'city guide', 'tips', 'review'], 3),
            'meta_data' => [
                'meta_title' => fake()->sentence(),
                'meta_description' => fake()->paragraph(),
            ],
            'view_count' => fake()->numberBetween(0, 1000),
            'like_count' => fake()->numberBetween(0, 100),
            'is_featured' => fake()->boolean(20),
            'is_pinned' => fake()->boolean(10),
            'published_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the article is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the article is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the article is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
