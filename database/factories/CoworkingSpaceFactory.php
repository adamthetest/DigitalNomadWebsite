<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoworkingSpace>
 */
class CoworkingSpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => null, // Will be set explicitly in tests
            'neighborhood_id' => null,
            'name' => fake()->company() . ' Coworking',
            'slug' => fake()->slug(),
            'description' => fake()->paragraphs(2, true),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'website' => fake()->url(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'type' => fake()->randomElement(['coworking', 'cafe', 'library', 'hotel_lobby', 'other']),
            'wifi_speed_mbps' => fake()->numberBetween(10, 1000),
            'wifi_reliability' => fake()->randomElement(['poor', 'fair', 'good', 'excellent']),
            'noise_level' => fake()->randomElement(['quiet', 'moderate', 'loud']),
            'seating_capacity' => fake()->numberBetween(10, 200),
            'has_power_outlets' => fake()->boolean(90),
            'has_air_conditioning' => fake()->boolean(80),
            'has_kitchen' => fake()->boolean(60),
            'has_meeting_rooms' => fake()->boolean(70),
            'has_printing' => fake()->boolean(50),
            'is_24_hours' => fake()->boolean(20),
            'daily_rate' => fake()->randomFloat(2, 10, 50),
            'monthly_rate' => fake()->randomFloat(2, 200, 800),
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
            'amenities' => fake()->randomElements([
                'WiFi', 'Coffee', 'Meeting Rooms', 'Printing', 'Kitchen',
                'Parking', 'Air Conditioning', '24/7 Access', 'Events'
            ], 5),
            'images' => fake()->optional()->randomElements([
                fake()->imageUrl(800, 600, 'business'),
                fake()->imageUrl(800, 600, 'business'),
            ], 2),
            'rating' => fake()->numberBetween(1, 5),
            'notes' => fake()->optional()->paragraph(),
            'is_verified' => fake()->boolean(30),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the coworking space is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the coworking space is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the coworking space is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_day' => fake()->numberBetween(40, 80),
            'price_per_month' => fake()->numberBetween(600, 1200),
        ]);
    }

    /**
     * Indicate that the coworking space is affordable.
     */
    public function affordable(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_day' => fake()->numberBetween(5, 20),
            'price_per_month' => fake()->numberBetween(100, 300),
        ]);
    }
}
