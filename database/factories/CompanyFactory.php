<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->slug(),
            'description' => fake()->paragraphs(2, true),
            'logo' => fake()->optional()->imageUrl(200, 200, 'business'),
            'website' => fake()->optional()->url(),
            'remote_policy' => fake()->optional()->paragraph(),
            'industry' => fake()->randomElement([
                'Technology', 'Finance', 'Healthcare', 'Education', 'E-commerce',
                'Marketing', 'Consulting', 'Manufacturing', 'Retail', 'Media',
            ]),
            'size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'headquarters' => fake()->city().', '.fake()->country(),
            'verified' => fake()->boolean(20),
            'subscription_plan' => fake()->randomElement(['basic', 'premium', 'enterprise']),
            'benefits' => fake()->optional()->randomElements([
                'Health Insurance', 'Remote Work', 'Flexible Hours', 'Learning Budget',
                'Gym Membership', 'Stock Options', 'Unlimited PTO',
            ], 3),
            'tech_stack' => fake()->optional()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'React', 'Vue', 'Python', 'Node.js',
            ], 4),
            'contact_email' => fake()->optional()->email(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the company is a startup.
     */
    public function startup(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => fake()->randomElement(['1-10', '11-50']),
            'founded_year' => fake()->numberBetween(2020, 2023),
        ]);
    }

    /**
     * Indicate that the company is large.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => fake()->randomElement(['201-500', '500+']),
            'founded_year' => fake()->numberBetween(1990, 2010),
        ]);
    }

    /**
     * Indicate that the company is in tech.
     */
    public function tech(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'Technology',
        ]);
    }
}
