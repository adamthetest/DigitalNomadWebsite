<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobUserInteraction>
 */
class JobUserInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null, // Will be set explicitly in tests
            'job_id' => null, // Will be set explicitly in tests
            'status' => fake()->randomElement(['saved', 'applied', 'rejected', 'shortlisted', 'interviewed', 'offered']),
            'applied_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'application_data' => fake()->optional()->randomElements([
                'cover_letter' => fake()->paragraphs(2, true),
                'resume_url' => fake()->url(),
                'portfolio_url' => fake()->url(),
            ]),
        ];
    }

    /**
     * Indicate that the interaction is saved.
     */
    public function saved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'saved',
            'applied_at' => null,
            'application_data' => null,
        ]);
    }

    /**
     * Indicate that the interaction is applied.
     */
    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'applied',
            'applied_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'application_data' => [
                'cover_letter' => fake()->paragraphs(2, true),
                'resume_url' => fake()->url(),
                'portfolio_url' => fake()->optional()->url(),
            ],
        ]);
    }

    /**
     * Indicate that the interaction is viewed.
     */
    public function viewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'saved',
            'applied_at' => null,
            'application_data' => null,
        ]);
    }
}
