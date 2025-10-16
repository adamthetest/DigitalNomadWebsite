<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'benefits' => fake()->paragraphs(2, true),
            'company_id' => Company::factory(),
            'type' => fake()->randomElement(['full-time', 'part-time', 'contract', 'freelance', 'internship']),
            'remote_type' => fake()->randomElement(['fully-remote', 'hybrid', 'timezone-limited', 'onsite']),
            'salary_min' => fake()->numberBetween(30000, 80000),
            'salary_max' => fake()->numberBetween(80000, 150000),
            'salary_currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
            'salary_period' => fake()->randomElement(['yearly', 'monthly', 'hourly']),
            'tags' => fake()->randomElements(['PHP', 'Laravel', 'JavaScript', 'React', 'Vue', 'Python', 'Node.js'], 3),
            'timezone' => fake()->timezone(),
            'visa_support' => fake()->boolean(),
            'source' => fake()->randomElement(['manual', 'scraped', 'api']),
            'source_url' => fake()->url(),
            'apply_url' => fake()->url(),
            'apply_email' => fake()->email(),
            'featured' => fake()->boolean(20), // 20% chance of being featured
            'is_active' => true,
            'expires_at' => fake()->dateTimeBetween('now', '+3 months'),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'views_count' => fake()->numberBetween(0, 1000),
            'applications_count' => fake()->numberBetween(0, 50),
            'location' => fake()->city().', '.fake()->country(),
            'experience_level' => fake()->randomElements(['junior', 'mid', 'senior', 'lead'], 2),
        ];
    }

    /**
     * Indicate that the job is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the job is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the job is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the job supports visa.
     */
    public function visaFriendly(): static
    {
        return $this->state(fn (array $attributes) => [
            'visa_support' => true,
        ]);
    }
}
