<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\CoworkingSpace;
use App\Models\CostItem;
use App\Models\VisaRule;
use App\Models\Article;
use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\Deal;
use App\Models\NewsletterSubscriber;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get cities for relationships
        $bangkok = City::where('slug', 'bangkok')->first();
        $chiangMai = City::where('slug', 'chiang-mai')->first();
        $lisbon = City::where('slug', 'lisbon')->first();
        $barcelona = City::where('slug', 'barcelona')->first();
        $mexicoCity = City::where('slug', 'mexico-city')->first();
        $playaDelCarmen = City::where('slug', 'playa-del-carmen')->first();
        $medellin = City::where('slug', 'medellin')->first();

        // Create neighborhoods
        $this->createNeighborhoods($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin);

        // Create coworking spaces
        $this->createCoworkingSpaces($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin);

        // Create cost items
        $this->createCostItems($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin);

        // Create visa rules
        $this->createVisaRules();

        // Create articles
        $this->createArticles($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin);

        // Create affiliate links and deals
        $this->createAffiliateData();

        // Create newsletter subscribers
        $this->createNewsletterSubscribers();
    }

    private function createNeighborhoods($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin)
    {
        $neighborhoods = [
            // Bangkok neighborhoods
            [
                'city_id' => $bangkok->id,
                'name' => 'Sukhumvit',
                'slug' => 'sukhumvit',
                'description' => 'Modern district with excellent transport links and international amenities.',
                'latitude' => 13.7307,
                'longitude' => 100.5232,
                'type' => 'business',
                'cost_level' => 'high',
                'safety_score' => 8.0,
                'internet_speed_mbps' => 50.0,
                'amenities' => json_encode(['BTS Skytrain', 'Shopping malls', 'International restaurants', 'Coworking spaces', 'Hotels']),
                'transportation' => json_encode(['BTS Skytrain', 'MRT', 'Taxis', 'Grab']),
                'is_active' => true,
            ],
            [
                'city_id' => $bangkok->id,
                'name' => 'Silom',
                'slug' => 'silom',
                'description' => 'Financial district with great nightlife and dining options.',
                'latitude' => 13.7246,
                'longitude' => 100.5340,
                'type' => 'business',
                'cost_level' => 'high',
                'safety_score' => 7.8,
                'internet_speed_mbps' => 48.0,
                'amenities' => json_encode(['MRT', 'Business centers', 'Restaurants', 'Bars', 'Hotels']),
                'transportation' => json_encode(['MRT', 'BTS Skytrain', 'Taxis', 'Buses']),
                'is_active' => true,
            ],

            // Chiang Mai neighborhoods
            [
                'city_id' => $chiangMai->id,
                'name' => 'Nimmanhaemin',
                'slug' => 'nimmanhaemin',
                'description' => 'Trendy area popular with digital nomads and expats.',
                'latitude' => 18.8000,
                'longitude' => 98.9500,
                'type' => 'residential',
                'cost_level' => 'medium',
                'safety_score' => 8.5,
                'internet_speed_mbps' => 45.0,
                'amenities' => json_encode(['Coworking spaces', 'Cafes', 'Restaurants', 'Shopping', 'Gyms']),
                'transportation' => json_encode(['Songthaew', 'Motorbike', 'Walking', 'Grab']),
                'is_active' => true,
            ],
            [
                'city_id' => $chiangMai->id,
                'name' => 'Old City',
                'slug' => 'old-city',
                'description' => 'Historic center with temples and traditional architecture.',
                'latitude' => 18.7883,
                'longitude' => 98.9853,
                'type' => 'historic',
                'cost_level' => 'low',
                'safety_score' => 8.2,
                'internet_speed_mbps' => 35.0,
                'amenities' => json_encode(['Temples', 'Markets', 'Guesthouses', 'Restaurants', 'Tourist attractions']),
                'transportation' => json_encode(['Walking', 'Songthaew', 'Motorbike', 'Tuk-tuk']),
                'is_active' => true,
            ],

            // Lisbon neighborhoods
            [
                'city_id' => $lisbon->id,
                'name' => 'Chiado',
                'slug' => 'chiado',
                'description' => 'Elegant district with historic charm and modern amenities.',
                'latitude' => 38.7105,
                'longitude' => -9.1426,
                'type' => 'historic',
                'cost_level' => 'high',
                'safety_score' => 8.3,
                'internet_speed_mbps' => 55.0,
                'amenities' => json_encode(['Metro', 'Shopping', 'Restaurants', 'Cafes', 'Cultural sites']),
                'transportation' => json_encode(['Metro', 'Tram', 'Bus', 'Walking']),
                'is_active' => true,
            ],
            [
                'city_id' => $lisbon->id,
                'name' => 'Alfama',
                'slug' => 'alfama',
                'description' => 'Traditional neighborhood with narrow streets and Fado music.',
                'latitude' => 38.7115,
                'longitude' => -9.1267,
                'type' => 'historic',
                'cost_level' => 'medium',
                'safety_score' => 8.0,
                'internet_speed_mbps' => 45.0,
                'amenities' => json_encode(['Historic sites', 'Fado venues', 'Restaurants', 'Guesthouses', 'Viewpoints']),
                'transportation' => json_encode(['Tram', 'Walking', 'Bus']),
                'is_active' => true,
            ],
        ];

        foreach ($neighborhoods as $neighborhood) {
            Neighborhood::firstOrCreate(
                ['city_id' => $neighborhood['city_id'], 'slug' => $neighborhood['slug']],
                $neighborhood
            );
        }
    }

    private function createCoworkingSpaces($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin)
    {
        $spaces = [
            // Bangkok coworking spaces
            [
                'city_id' => $bangkok->id,
                'neighborhood_id' => Neighborhood::where('slug', 'sukhumvit')->first()->id,
                'name' => 'Hubba',
                'slug' => 'hubba-bangkok',
                'description' => 'Modern coworking space with excellent facilities and community events.',
                'address' => 'Sukhumvit Soi 31, Bangkok',
                'latitude' => 13.7307,
                'longitude' => 100.5232,
                'website' => 'https://hubba.co.th',
                'phone' => '+66 2 123 4567',
                'email' => 'info@hubba.co.th',
                'type' => 'coworking',
                'wifi_speed_mbps' => 100.0,
                'wifi_reliability' => 'excellent',
                'noise_level' => 'moderate',
                'seating_capacity' => 50,
                'has_power_outlets' => true,
                'has_air_conditioning' => true,
                'has_meeting_rooms' => true,
                'has_kitchen' => true,
                'has_printing' => true,
                'is_24_hours' => false,
                'daily_rate' => 15.00,
                'monthly_rate' => 300.00,
                'currency' => 'USD',
                'amenities' => json_encode(['High-speed WiFi', 'Meeting rooms', 'Kitchen', 'Coffee', 'Community events']),
                'images' => json_encode(['https://images.unsplash.com/photo-1497366216548-37526070297c?w=800']),
                'rating' => 4.5,
                'notes' => 'Popular with digital nomads and startups',
                'is_verified' => true,
                'is_active' => true,
            ],

            // Chiang Mai coworking spaces
            [
                'city_id' => $chiangMai->id,
                'neighborhood_id' => Neighborhood::where('slug', 'nimmanhaemin')->first()->id,
                'name' => 'Punspace',
                'slug' => 'punspace-chiang-mai',
                'description' => 'Digital nomad favorite with great community and affordable rates.',
                'address' => 'Nimmanhaemin Road, Chiang Mai',
                'latitude' => 18.8000,
                'longitude' => 98.9500,
                'website' => 'https://punspace.com',
                'phone' => '+66 53 123 456',
                'email' => 'hello@punspace.com',
                'type' => 'coworking',
                'wifi_speed_mbps' => 80.0,
                'wifi_reliability' => 'excellent',
                'noise_level' => 'quiet',
                'seating_capacity' => 30,
                'has_power_outlets' => true,
                'has_air_conditioning' => true,
                'has_meeting_rooms' => true,
                'has_kitchen' => true,
                'has_printing' => false,
                'is_24_hours' => false,
                'daily_rate' => 8.00,
                'monthly_rate' => 150.00,
                'currency' => 'USD',
                'amenities' => json_encode(['High-speed WiFi', 'Meeting rooms', 'Kitchen', 'Coffee', 'Community', 'Parking']),
                'images' => json_encode(['https://images.unsplash.com/photo-1497366216548-37526070297c?w=800']),
                'rating' => 4.7,
                'notes' => 'Very popular with digital nomads',
                'is_verified' => true,
                'is_active' => true,
            ],

            // Lisbon coworking spaces
            [
                'city_id' => $lisbon->id,
                'neighborhood_id' => Neighborhood::where('slug', 'chiado')->first()->id,
                'name' => 'Second Home',
                'slug' => 'second-home-lisbon',
                'description' => 'Beautiful coworking space with plants and natural light.',
                'address' => 'Rua da Prata, Lisbon',
                'latitude' => 38.7105,
                'longitude' => -9.1426,
                'website' => 'https://secondhome.io',
                'phone' => '+351 21 123 4567',
                'email' => 'lisbon@secondhome.io',
                'type' => 'coworking',
                'wifi_speed_mbps' => 90.0,
                'wifi_reliability' => 'excellent',
                'noise_level' => 'moderate',
                'seating_capacity' => 40,
                'has_power_outlets' => true,
                'has_air_conditioning' => true,
                'has_meeting_rooms' => true,
                'has_kitchen' => true,
                'has_printing' => true,
                'is_24_hours' => false,
                'daily_rate' => 25.00,
                'monthly_rate' => 400.00,
                'currency' => 'EUR',
                'amenities' => json_encode(['High-speed WiFi', 'Meeting rooms', 'Kitchen', 'Coffee', 'Plants', 'Shower']),
                'images' => json_encode(['https://images.unsplash.com/photo-1497366216548-37526070297c?w=800']),
                'rating' => 4.6,
                'notes' => 'Beautiful design with lots of natural light',
                'is_verified' => true,
                'is_active' => true,
            ],
        ];

        foreach ($spaces as $space) {
            CoworkingSpace::firstOrCreate(
                ['slug' => $space['slug']],
                $space
            );
        }
    }

    private function createCostItems($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin)
    {
        $costItems = [
            // Bangkok cost items
            [
                'city_id' => $bangkok->id,
                'category' => 'accommodation',
                'name' => '1-bedroom apartment (city center)',
                'description' => 'Modern apartment in central Bangkok',
                'price_min' => 400.00,
                'price_max' => 800.00,
                'price_average' => 600.00,
                'currency' => 'USD',
                'unit' => 'per month',
                'price_range' => 'mid_range',
                'details' => json_encode(['Utilities included', 'Furnished', 'Air conditioning', 'WiFi']),
                'notes' => 'Prices vary by neighborhood',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'city_id' => $bangkok->id,
                'category' => 'food',
                'name' => 'Street food meal',
                'description' => 'Local street food dish',
                'price_min' => 1.50,
                'price_max' => 3.00,
                'price_average' => 2.25,
                'currency' => 'USD',
                'unit' => 'per meal',
                'price_range' => 'budget',
                'details' => json_encode(['Pad Thai', 'Som Tam', 'Grilled meat', 'Rice dishes']),
                'notes' => 'Excellent value and taste',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'city_id' => $bangkok->id,
                'category' => 'transportation',
                'name' => 'BTS Skytrain ticket',
                'description' => 'Single journey on BTS',
                'price_min' => 0.50,
                'price_max' => 1.50,
                'price_average' => 1.00,
                'currency' => 'USD',
                'unit' => 'per trip',
                'price_range' => 'budget',
                'details' => json_encode(['Air-conditioned', 'Fast', 'Reliable']),
                'notes' => 'Distance-based pricing',
                'last_updated' => now(),
                'is_active' => true,
            ],

            // Chiang Mai cost items
            [
                'city_id' => $chiangMai->id,
                'category' => 'accommodation',
                'name' => '1-bedroom apartment (Nimman area)',
                'description' => 'Apartment in popular Nimmanhaemin area',
                'price_min' => 250.00,
                'price_max' => 500.00,
                'price_average' => 375.00,
                'currency' => 'USD',
                'unit' => 'per month',
                'price_range' => 'budget',
                'details' => json_encode(['Utilities included', 'Furnished', 'Air conditioning', 'WiFi', 'Near coworking spaces']),
                'notes' => 'Very affordable compared to Bangkok',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'city_id' => $chiangMai->id,
                'category' => 'coworking',
                'name' => 'Monthly coworking pass',
                'description' => 'Unlimited access to coworking space',
                'price_min' => 120.00,
                'price_max' => 200.00,
                'price_average' => 160.00,
                'currency' => 'USD',
                'unit' => 'per month',
                'price_range' => 'budget',
                'details' => json_encode(['High-speed WiFi', 'Meeting rooms', 'Coffee', 'Community events']),
                'notes' => 'Great value for digital nomads',
                'last_updated' => now(),
                'is_active' => true,
            ],

            // Lisbon cost items
            [
                'city_id' => $lisbon->id,
                'category' => 'accommodation',
                'name' => '1-bedroom apartment (city center)',
                'description' => 'Apartment in central Lisbon',
                'price_min' => 600.00,
                'price_max' => 1200.00,
                'price_average' => 900.00,
                'currency' => 'EUR',
                'unit' => 'per month',
                'price_range' => 'mid_range',
                'details' => json_encode(['Utilities included', 'Furnished', 'Historic building', 'WiFi']),
                'notes' => 'More affordable than other European capitals',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'city_id' => $lisbon->id,
                'category' => 'food',
                'name' => 'Restaurant meal (mid-range)',
                'description' => 'Dinner at a typical Portuguese restaurant',
                'price_min' => 15.00,
                'price_max' => 25.00,
                'price_average' => 20.00,
                'currency' => 'EUR',
                'unit' => 'per person',
                'price_range' => 'mid_range',
                'details' => json_encode(['Main course', 'Wine', 'Dessert', 'Coffee']),
                'notes' => 'Excellent Portuguese cuisine',
                'last_updated' => now(),
                'is_active' => true,
            ],
        ];

        foreach ($costItems as $item) {
            CostItem::firstOrCreate(
                ['city_id' => $item['city_id'], 'name' => $item['name']],
                $item
            );
        }
    }

    private function createVisaRules()
    {
        $visaRules = [
            [
                'country_id' => 1, // Thailand
                'nationality' => 'US',
                'visa_type' => 'visa_free',
                'stay_duration_days' => 30,
                'validity_days' => 90,
                'cost_usd' => 0.00,
                'requirements' => 'Valid passport with 6 months validity',
                'application_process' => 'Visa on arrival or visa-free entry',
                'official_website' => 'https://thaiembassy.org',
                'restrictions' => json_encode(['Cannot work', 'Cannot extend']),
                'notes' => 'Can extend for 30 days at immigration office',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'country_id' => 1, // Thailand
                'nationality' => 'US',
                'visa_type' => 'visa_free',
                'stay_duration_days' => 60,
                'validity_days' => 180,
                'cost_usd' => 40.00,
                'requirements' => 'Valid passport, passport photo, proof of funds',
                'application_process' => 'Apply at Thai embassy or consulate',
                'official_website' => 'https://thaiembassy.org',
                'restrictions' => json_encode(['Cannot work', 'Single entry']),
                'notes' => 'Can extend for 30 days at immigration office',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'country_id' => 2, // Portugal
                'nationality' => 'US',
                'visa_type' => 'visa_free',
                'stay_duration_days' => 90,
                'validity_days' => 180,
                'cost_usd' => 0.00,
                'requirements' => 'Valid passport with 3 months validity',
                'application_process' => 'Visa-free entry for US citizens',
                'official_website' => 'https://portugal.com',
                'restrictions' => json_encode(['Cannot work', 'Schengen area rules']),
                'notes' => 'Part of Schengen area - 90 days per 180 days',
                'last_updated' => now(),
                'is_active' => true,
            ],
            [
                'country_id' => 4, // Mexico
                'nationality' => 'US',
                'visa_type' => 'visa_free',
                'stay_duration_days' => 180,
                'validity_days' => 180,
                'cost_usd' => 0.00,
                'requirements' => 'Valid passport',
                'application_process' => 'Visa-free entry for US citizens',
                'official_website' => 'https://mexico.com',
                'restrictions' => json_encode(['Cannot work', 'Tourist activities only']),
                'notes' => 'Can stay up to 180 days per year',
                'last_updated' => now(),
                'is_active' => true,
            ],
        ];

        foreach ($visaRules as $rule) {
            VisaRule::firstOrCreate(
                ['country_id' => $rule['country_id'], 'nationality' => $rule['nationality'], 'visa_type' => $rule['visa_type']],
                $rule
            );
        }
    }

    private function createArticles($bangkok, $chiangMai, $lisbon, $barcelona, $mexicoCity, $playaDelCarmen, $medellin)
    {
        $user = User::first(); // Use the admin user

        $articles = [
            [
                'user_id' => $user->id,
                'city_id' => $bangkok->id,
                'title' => 'Complete Digital Nomad Guide to Bangkok',
                'slug' => 'complete-digital-nomad-guide-bangkok',
                'excerpt' => 'Everything you need to know about working remotely in Bangkok, from coworking spaces to cost of living.',
                'content' => '<h2>Why Bangkok is Perfect for Digital Nomads</h2><p>Bangkok offers an incredible combination of modern amenities and cultural experiences that make it ideal for remote workers...</p><h2>Best Coworking Spaces</h2><p>Hubba and other spaces provide excellent facilities...</p><h2>Cost of Living</h2><p>Bangkok offers great value for money...</p>',
                'type' => 'guide',
                'status' => 'published',
                'tags' => json_encode(['bangkok', 'digital nomad', 'coworking', 'thailand', 'remote work']),
                'is_featured' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(5),
            ],
            [
                'user_id' => $user->id,
                'city_id' => $chiangMai->id,
                'title' => 'Chiang Mai: The Digital Nomad Capital of Asia',
                'slug' => 'chiang-mai-digital-nomad-capital-asia',
                'excerpt' => 'Discover why Chiang Mai has become the go-to destination for digital nomads in Southeast Asia.',
                'content' => '<h2>The Nomad Community</h2><p>Chiang Mai has one of the largest digital nomad communities in the world...</p><h2>Affordable Living</h2><p>Costs are significantly lower than Bangkok...</p><h2>Quality of Life</h2><p>The mountain setting provides a perfect work-life balance...</p>',
                'type' => 'guide',
                'status' => 'published',
                'tags' => json_encode(['chiang mai', 'digital nomad', 'thailand', 'affordable', 'community']),
                'is_featured' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(3),
            ],
            [
                'user_id' => $user->id,
                'city_id' => $lisbon->id,
                'title' => 'Working Remotely in Lisbon: A European Dream',
                'slug' => 'working-remotely-lisbon-european-dream',
                'excerpt' => 'Lisbon combines European charm with modern amenities, making it perfect for digital nomads.',
                'content' => '<h2>European Lifestyle</h2><p>Lisbon offers the best of European culture...</p><h2>Growing Tech Scene</h2><p>The startup ecosystem is rapidly expanding...</p><h2>Cost Comparison</h2><p>More affordable than other European capitals...</p>',
                'type' => 'guide',
                'status' => 'published',
                'tags' => json_encode(['lisbon', 'portugal', 'europe', 'digital nomad', 'startup']),
                'is_featured' => false,
                'is_pinned' => false,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($articles as $article) {
            Article::firstOrCreate(
                ['slug' => $article['slug']],
                $article
            );
        }
    }

    private function createAffiliateData()
    {
        // Create affiliate links
        $affiliateLinks = [
            [
                'name' => 'Nomad Insurance - SafetyWing',
                'slug' => 'safetywing-nomad-insurance',
                'description' => 'Comprehensive health insurance for digital nomads',
                'original_url' => 'https://safetywing.com',
                'affiliate_url' => 'https://safetywing.com?ref=digitalnomadguide',
                'affiliate_provider' => 'SafetyWing',
                'category' => 'insurance',
                'commission_type' => 'percentage',
                'commission_rate' => 10.00,
                'currency' => 'USD',
                'tracking_params' => json_encode(['utm_source' => 'digitalnomadguide', 'utm_medium' => 'affiliate']),
                'is_featured' => true,
                'is_active' => true,
                'click_count' => 0,
                'conversion_count' => 0,
                'total_commission' => 0.00,
            ],
            [
                'name' => 'Accommodation - Airbnb',
                'slug' => 'airbnb-accommodation',
                'description' => 'Find short-term rentals worldwide',
                'original_url' => 'https://airbnb.com',
                'affiliate_url' => 'https://airbnb.com?ref=digitalnomadguide',
                'affiliate_provider' => 'Airbnb',
                'category' => 'accommodation',
                'commission_type' => 'percentage',
                'commission_rate' => 5.00,
                'currency' => 'USD',
                'tracking_params' => json_encode(['utm_source' => 'digitalnomadguide', 'utm_medium' => 'affiliate']),
                'is_featured' => true,
                'is_active' => true,
                'click_count' => 0,
                'conversion_count' => 0,
                'total_commission' => 0.00,
            ],
        ];

        foreach ($affiliateLinks as $link) {
            $affiliateLink = AffiliateLink::firstOrCreate(
                ['slug' => $link['slug']],
                $link
            );

            // Create deals for each affiliate link
            Deal::firstOrCreate(
                ['slug' => 'safetywing-10-percent-off'],
                [
                    'affiliate_link_id' => $affiliateLink->id,
                    'title' => 'SafetyWing Nomad Insurance - 10% Off First Month',
                    'slug' => 'safetywing-10-percent-off',
                    'description' => 'Get 10% off your first month of nomad insurance',
                    'deal_url' => $affiliateLink->affiliate_url,
                    'provider' => 'SafetyWing',
                    'category' => 'insurance',
                    'original_price' => 45.00,
                    'discounted_price' => 40.50,
                    'discount_percentage' => 10.00,
                    'currency' => 'USD',
                    'promo_code' => 'NOMAD10',
                    'valid_from' => now(),
                    'valid_until' => now()->addMonths(3),
                    'terms_conditions' => json_encode(['Valid for new customers only', 'Cannot be combined with other offers']),
                    'is_featured' => true,
                    'is_active' => true,
                    'click_count' => 0,
                    'conversion_count' => 0,
                ]
            );
        }
    }

    private function createNewsletterSubscribers()
    {
        $subscribers = [
            [
                'email' => 'john@example.com',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'country_code' => 'US',
                'interests' => json_encode(['coworking', 'visa', 'cost-of-living']),
                'status' => 'active',
                'source' => 'website',
                'utm_data' => json_encode(['utm_source' => 'homepage', 'utm_medium' => 'newsletter']),
                'subscribed_at' => now()->subDays(10),
            ],
            [
                'email' => 'sarah@example.com',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'country_code' => 'CA',
                'interests' => json_encode(['accommodation', 'coworking']),
                'status' => 'active',
                'source' => 'article',
                'utm_data' => json_encode(['utm_source' => 'bangkok-guide', 'utm_medium' => 'article']),
                'subscribed_at' => now()->subDays(5),
            ],
            [
                'email' => 'mike@example.com',
                'first_name' => 'Mike',
                'last_name' => 'Brown',
                'country_code' => 'UK',
                'interests' => json_encode(['visa', 'cost-of-living', 'coworking']),
                'status' => 'active',
                'source' => 'admin',
                'utm_data' => json_encode([]),
                'subscribed_at' => now()->subDays(2),
            ],
        ];

        foreach ($subscribers as $subscriber) {
            NewsletterSubscriber::firstOrCreate(
                ['email' => $subscriber['email']],
                $subscriber
            );
        }
    }
}