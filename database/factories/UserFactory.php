<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'bio' => fake()->optional()->paragraph(),
            'tagline' => fake()->optional()->sentence(),
            'job_title' => fake()->optional()->jobTitle(),
            'company' => fake()->optional()->company(),
            'skills' => fake()->optional()->randomElements(['PHP', 'Laravel', 'JavaScript', 'React', 'Vue', 'Python', 'Node.js'], 3),
            'work_type' => fake()->optional()->randomElement(['freelancer', 'employee', 'entrepreneur']),
            'availability' => fake()->optional()->randomElement(['available', 'busy', 'unavailable']),
            'location' => fake()->optional()->city() . ', ' . fake()->country(),
            'location_current' => fake()->optional()->city() . ', ' . fake()->country(),
            'location_next' => fake()->optional()->city() . ', ' . fake()->country(),
            'travel_timeline' => fake()->optional()->randomElements([
                ['city' => 'Bangkok', 'country' => 'Thailand', 'arrived_at' => '2024-01-01'],
                ['city' => 'Chiang Mai', 'country' => 'Thailand', 'arrived_at' => '2024-03-01']
            ], 1),
            'profile_image' => fake()->optional()->imageUrl(200, 200, 'people'),
            'website' => fake()->optional()->url(),
            'twitter' => fake()->optional()->userName(),
            'instagram' => fake()->optional()->userName(),
            'linkedin' => fake()->optional()->url(),
            'github' => fake()->optional()->userName(),
            'behance' => fake()->optional()->userName(),
            'is_public' => fake()->boolean(80),
            'id_verified' => fake()->boolean(20),
            'premium_status' => fake()->boolean(10),
            'last_active' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'visibility' => fake()->randomElement(['public', 'members', 'hidden']),
            'location_precise' => fake()->boolean(70),
            'show_social_links' => fake()->boolean(60),
            'timezone' => fake()->optional()->timezone(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
