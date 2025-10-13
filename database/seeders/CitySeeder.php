<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Country;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Thailand
            [
                'country_id' => 1, // Thailand
                'name' => 'Bangkok',
                'slug' => 'bangkok',
                'latitude' => 13.7563,
                'longitude' => 100.5018,
                'description' => 'Thailand\'s bustling capital city with excellent digital nomad infrastructure.',
                'overview' => 'Bangkok offers a perfect blend of modern amenities and rich culture. With excellent internet speeds, affordable living costs, and a thriving expat community, it\'s a top destination for digital nomads.',
                'population' => 10539000,
                'climate' => 'Tropical - Hot and humid year-round with monsoon season from May to October',
                'internet_speed_mbps' => 45.2,
                'safety_score' => 7.5,
                'cost_of_living_index' => 45.2,
                'best_time_to_visit' => 'November to March (cooler, dry season)',
                'highlights' => json_encode([
                    'Excellent coworking spaces',
                    'Affordable accommodation',
                    'Great street food scene',
                    'Modern public transport',
                    'Vibrant nightlife',
                    'Rich cultural heritage'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800',
                    'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'country_id' => 1, // Thailand
                'name' => 'Chiang Mai',
                'slug' => 'chiang-mai',
                'latitude' => 18.7883,
                'longitude' => 98.9853,
                'description' => 'The digital nomad capital of Southeast Asia with a relaxed mountain atmosphere.',
                'overview' => 'Chiang Mai has become the unofficial capital of digital nomads in Asia. With its low cost of living, excellent internet, and vibrant nomad community, it\'s perfect for remote workers.',
                'population' => 131000,
                'climate' => 'Tropical savanna - Cooler than Bangkok, dry season from November to April',
                'internet_speed_mbps' => 38.7,
                'safety_score' => 8.2,
                'cost_of_living_index' => 32.1,
                'best_time_to_visit' => 'November to March (coolest and driest)',
                'highlights' => json_encode([
                    'Digital nomad hub',
                    'Affordable living costs',
                    'Mountain scenery',
                    'Temple culture',
                    'Night markets',
                    'Coworking community'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
            
            // Portugal
            [
                'country_id' => 2, // Portugal
                'name' => 'Lisbon',
                'slug' => 'lisbon',
                'latitude' => 38.7223,
                'longitude' => -9.1393,
                'description' => 'Portugal\'s charming capital with a growing digital nomad scene.',
                'overview' => 'Lisbon combines old-world charm with modern amenities. The city offers excellent weather, affordable living costs compared to other European capitals, and a growing startup ecosystem.',
                'population' => 545000,
                'climate' => 'Mediterranean - Mild winters, warm summers, moderate rainfall',
                'internet_speed_mbps' => 52.8,
                'safety_score' => 8.1,
                'cost_of_living_index' => 58.3,
                'best_time_to_visit' => 'April to October (warmest and driest)',
                'highlights' => json_encode([
                    'Historic architecture',
                    'Affordable European living',
                    'Growing tech scene',
                    'Great weather',
                    'Coastal location',
                    'Rich culture'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'country_id' => 2, // Portugal
                'name' => 'Porto',
                'slug' => 'porto',
                'latitude' => 41.1579,
                'longitude' => -8.6291,
                'description' => 'Portugal\'s second city with a vibrant creative scene and lower costs.',
                'overview' => 'Porto offers a more affordable alternative to Lisbon while maintaining Portugal\'s charm. The city has a growing digital nomad community and excellent quality of life.',
                'population' => 237000,
                'climate' => 'Mediterranean - Similar to Lisbon but slightly cooler',
                'internet_speed_mbps' => 48.9,
                'safety_score' => 8.3,
                'cost_of_living_index' => 52.1,
                'best_time_to_visit' => 'May to September (warmest)',
                'highlights' => json_encode([
                    'Lower cost than Lisbon',
                    'Historic center',
                    'Port wine culture',
                    'Creative community',
                    'River views',
                    'Authentic Portuguese experience'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => false,
                'is_active' => true,
            ],
            
            // Spain
            [
                'country_id' => 3, // Spain
                'name' => 'Barcelona',
                'slug' => 'barcelona',
                'latitude' => 41.3851,
                'longitude' => 2.1734,
                'description' => 'Spain\'s vibrant coastal city with excellent digital nomad infrastructure.',
                'overview' => 'Barcelona combines Mediterranean lifestyle with modern amenities. The city offers excellent weather, beautiful architecture, and a thriving international community.',
                'population' => 1620000,
                'climate' => 'Mediterranean - Mild winters, hot summers, moderate rainfall',
                'internet_speed_mbps' => 55.2,
                'safety_score' => 7.8,
                'cost_of_living_index' => 65.4,
                'best_time_to_visit' => 'April to June, September to October',
                'highlights' => json_encode([
                    'Mediterranean lifestyle',
                    'Gaudi architecture',
                    'Beach access',
                    'International community',
                    'Great food scene',
                    'Cultural attractions'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
            
            // Mexico
            [
                'country_id' => 4, // Mexico
                'name' => 'Mexico City',
                'slug' => 'mexico-city',
                'latitude' => 19.4326,
                'longitude' => -99.1332,
                'description' => 'Mexico\'s vibrant capital with a growing digital nomad community.',
                'overview' => 'Mexico City offers an authentic Latin American experience with modern amenities. The city has excellent food, rich culture, and affordable living costs.',
                'population' => 9200000,
                'climate' => 'Subtropical highland - Mild year-round with dry winters',
                'internet_speed_mbps' => 42.1,
                'safety_score' => 6.8,
                'cost_of_living_index' => 38.7,
                'best_time_to_visit' => 'October to April (dry season)',
                'highlights' => json_encode([
                    'Rich culture',
                    'Excellent food',
                    'Affordable living',
                    'Historic center',
                    'Art scene',
                    'Growing nomad community'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'country_id' => 4, // Mexico
                'name' => 'Playa del Carmen',
                'slug' => 'playa-del-carmen',
                'latitude' => 20.6296,
                'longitude' => -87.0731,
                'description' => 'Caribbean beach town with a thriving digital nomad scene.',
                'overview' => 'Playa del Carmen offers the perfect combination of beach life and remote work. The town has excellent internet, beautiful beaches, and a strong international community.',
                'population' => 150000,
                'climate' => 'Tropical - Hot and humid year-round',
                'internet_speed_mbps' => 35.8,
                'safety_score' => 7.2,
                'cost_of_living_index' => 42.3,
                'best_time_to_visit' => 'November to April (dry season)',
                'highlights' => json_encode([
                    'Beach lifestyle',
                    'Caribbean waters',
                    'Nomad community',
                    'Water activities',
                    'International restaurants',
                    'Relaxed atmosphere'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
            
            // Colombia
            [
                'country_id' => 5, // Colombia
                'name' => 'Medellín',
                'slug' => 'medellin',
                'latitude' => 6.2442,
                'longitude' => -75.5812,
                'description' => 'Colombia\'s innovative city with eternal spring weather.',
                'overview' => 'Medellín has transformed into a modern, innovative city perfect for digital nomads. The eternal spring weather, affordable costs, and growing tech scene make it increasingly popular.',
                'population' => 2500000,
                'climate' => 'Tropical highland - Eternal spring, mild year-round',
                'internet_speed_mbps' => 28.9,
                'safety_score' => 7.1,
                'cost_of_living_index' => 29.8,
                'best_time_to_visit' => 'Year-round (eternal spring)',
                'highlights' => json_encode([
                    'Eternal spring weather',
                    'Affordable living',
                    'Innovation hub',
                    'Friendly locals',
                    'Modern infrastructure',
                    'Growing tech scene'
                ]),
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800',
                    'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800'
                ]),
                'is_featured' => true,
                'is_active' => true,
            ],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(
                ['slug' => $city['slug']],
                $city
            );
        }
    }
}