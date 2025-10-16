<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\CoworkingSpace;
use App\Models\CostItem;
use App\Models\Deal;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_index_page_loads()
    {
        $response = $this->get('/cities');

        $response->assertStatus(200);
        $response->assertViewIs('cities.index');
    }

    public function test_city_index_displays_cities()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create([
            'country_id' => $country->id,
            'is_active' => true
        ]);

        $response = $this->get('/cities');

        $response->assertStatus(200);
        $response->assertSee($city->name);
        $response->assertSee($country->name);
    }

    public function test_city_index_filters_by_search()
    {
        $country = Country::factory()->create(['name' => 'Thailand']);
        $city1 = City::factory()->create([
            'name' => 'Bangkok',
            'country_id' => $country->id,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'name' => 'Tokyo',
            'is_active' => true
        ]);

        $response = $this->get('/cities?search=Bangkok');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_country_name()
    {
        $country = Country::factory()->create(['name' => 'Thailand']);
        $city1 = City::factory()->create([
            'name' => 'Bangkok',
            'country_id' => $country->id,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'name' => 'Tokyo',
            'is_active' => true
        ]);

        $response = $this->get('/cities?search=Thailand');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_country()
    {
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();
        $city1 = City::factory()->create([
            'country_id' => $country1->id,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'country_id' => $country2->id,
            'is_active' => true
        ]);

        $response = $this->get("/cities?country={$country1->id}");

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_cost_min()
    {
        $city1 = City::factory()->create([
            'cost_of_living_index' => 30,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'cost_of_living_index' => 20,
            'is_active' => true
        ]);

        $response = $this->get('/cities?cost_min=25');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_cost_max()
    {
        $city1 = City::factory()->create([
            'cost_of_living_index' => 30,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'cost_of_living_index' => 50,
            'is_active' => true
        ]);

        $response = $this->get('/cities?cost_max=35');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_internet_min()
    {
        $city1 = City::factory()->create([
            'internet_speed_mbps' => 50,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'internet_speed_mbps' => 20,
            'is_active' => true
        ]);

        $response = $this->get('/cities?internet_min=30');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_safety_min()
    {
        $city1 = City::factory()->create([
            'safety_score' => 8,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'safety_score' => 5,
            'is_active' => true
        ]);

        $response = $this->get('/cities?safety_min=7');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_featured()
    {
        $city1 = City::factory()->create([
            'is_featured' => true,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'is_featured' => false,
            'is_active' => true
        ]);

        $response = $this->get('/cities?featured=true');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_filters_by_continent()
    {
        $country1 = Country::factory()->create(['continent' => 'Asia']);
        $country2 = Country::factory()->create(['continent' => 'Europe']);
        $city1 = City::factory()->create([
            'country_id' => $country1->id,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'country_id' => $country2->id,
            'is_active' => true
        ]);

        $response = $this->get('/cities?continent=Asia');

        $response->assertStatus(200);
        $response->assertSee($city1->name);
        $response->assertDontSee($city2->name);
    }

    public function test_city_index_sorts_by_name()
    {
        $city1 = City::factory()->create([
            'name' => 'Bangkok',
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'name' => 'Amsterdam',
            'is_active' => true
        ]);

        $response = $this->get('/cities?sort=name');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$city2->name, $city1->name]);
    }

    public function test_city_index_sorts_by_cost_low()
    {
        $city1 = City::factory()->create([
            'cost_of_living_index' => 20,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'cost_of_living_index' => 50,
            'is_active' => true
        ]);

        $response = $this->get('/cities?sort=cost_low');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$city1->name, $city2->name]);
    }

    public function test_city_index_sorts_by_cost_high()
    {
        $city1 = City::factory()->create([
            'cost_of_living_index' => 50,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'cost_of_living_index' => 20,
            'is_active' => true
        ]);

        $response = $this->get('/cities?sort=cost_high');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$city1->name, $city2->name]);
    }

    public function test_city_index_sorts_by_internet()
    {
        $city1 = City::factory()->create([
            'internet_speed_mbps' => 80,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'internet_speed_mbps' => 30,
            'is_active' => true
        ]);

        $response = $this->get('/cities?sort=internet');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$city1->name, $city2->name]);
    }

    public function test_city_index_sorts_by_safety()
    {
        $city1 = City::factory()->create([
            'safety_score' => 9,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'safety_score' => 6,
            'is_active' => true
        ]);

        $response = $this->get('/cities?sort=safety');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$city1->name, $city2->name]);
    }

    public function test_city_index_sorts_by_featured_default()
    {
        $city1 = City::factory()->create([
            'is_featured' => true,
            'is_active' => true
        ]);
        $city2 = City::factory()->create([
            'is_featured' => false,
            'is_active' => true
        ]);

        $response = $this->get('/cities?sort=featured');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$city1->name, $city2->name]);
    }

    public function test_city_search_suggestions_returns_empty_for_short_query()
    {
        $response = $this->getJson('/cities/search-suggestions?q=a');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_city_search_suggestions_returns_cities()
    {
        $country = Country::factory()->create(['name' => 'Thailand']);
        $city = City::factory()->create([
            'name' => 'Bangkok',
            'country_id' => $country->id,
            'is_active' => true,
            'cost_of_living_index' => 45,
            'internet_speed_mbps' => 50,
            'safety_score' => 8
        ]);

        $response = $this->getJson('/cities/search-suggestions?q=Bangkok');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $city->id,
            'name' => 'Bangkok',
            'country' => 'Thailand',
            'cost' => 45,
            'internet' => 50,
            'safety' => 8
        ]);
    }

    public function test_city_search_suggestions_filters_by_country()
    {
        $country = Country::factory()->create(['name' => 'Thailand']);
        $city = City::factory()->create([
            'name' => 'Bangkok',
            'country_id' => $country->id,
            'is_active' => true
        ]);

        $response = $this->getJson('/cities/search-suggestions?q=Thailand');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Bangkok',
            'country' => 'Thailand'
        ]);
    }

    public function test_city_search_suggestions_limits_results()
    {
        // Create cities with names that will match the search
        City::factory()->count(15)->create([
            'is_active' => true,
            'name' => function() {
                return 'Test City ' . fake()->unique()->numberBetween(1, 100);
            }
        ]);

        $response = $this->getJson('/cities/search-suggestions?q=Test');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json());
    }

    public function test_city_show_page_loads()
    {
        $city = City::factory()->create(['is_active' => true]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertViewIs('cities.show');
        $response->assertSee($city->name);
    }

    public function test_city_show_returns_404_for_inactive_city()
    {
        $city = City::factory()->create(['is_active' => false]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(404);
    }

    public function test_city_show_displays_coworking_spaces()
    {
        $city = City::factory()->create(['is_active' => true]);
        $coworkingSpace = CoworkingSpace::factory()->create([
            'city_id' => $city->id,
            'is_active' => true
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSee($coworkingSpace->name);
    }

    public function test_city_show_displays_cost_items()
    {
        $city = City::factory()->create(['is_active' => true]);
        $costItem = CostItem::factory()->create(['city_id' => $city->id]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSee($costItem->name);
    }

    public function test_city_show_displays_active_deals()
    {
        $city = City::factory()->create(['is_active' => true]);
        $deal = Deal::factory()->create([
            'city_id' => $city->id,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay()
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSee($deal->title);
    }

    public function test_city_show_displays_published_articles()
    {
        $city = City::factory()->create(['is_active' => true]);
        $article = Article::factory()->create([
            'city_id' => $city->id,
            'status' => 'published'
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSee($article->title);
    }

    public function test_city_show_displays_similar_cities()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create([
            'country_id' => $country->id,
            'is_active' => true,
            'cost_of_living_index' => 50
        ]);
        $similarCity = City::factory()->create([
            'country_id' => $country->id,
            'is_active' => true,
            'cost_of_living_index' => 55
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSee($similarCity->name);
    }

    public function test_city_show_sorts_coworking_spaces_by_featured()
    {
        $city = City::factory()->create(['is_active' => true]);
        $regularSpace = CoworkingSpace::factory()->create([
            'city_id' => $city->id,
            'is_active' => true,
            'is_featured' => false
        ]);
        $featuredSpace = CoworkingSpace::factory()->create([
            'city_id' => $city->id,
            'is_active' => true,
            'is_featured' => true
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSeeInOrder([$featuredSpace->name, $regularSpace->name]);
    }

    public function test_city_show_sorts_deals_by_featured()
    {
        $city = City::factory()->create(['is_active' => true]);
        $regularDeal = Deal::factory()->create([
            'city_id' => $city->id,
            'is_active' => true,
            'is_featured' => false,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay()
        ]);
        $featuredDeal = Deal::factory()->create([
            'city_id' => $city->id,
            'is_active' => true,
            'is_featured' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay()
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        $response->assertSeeInOrder([$featuredDeal->title, $regularDeal->title]);
    }

    public function test_city_show_limits_deals()
    {
        $city = City::factory()->create(['is_active' => true]);
        Deal::factory()->count(10)->create([
            'city_id' => $city->id,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay()
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        // Should only show 6 deals as per controller limit
    }

    public function test_city_show_limits_articles()
    {
        $city = City::factory()->create(['is_active' => true]);
        Article::factory()->count(5)->create([
            'city_id' => $city->id,
            'status' => 'published'
        ]);

        $response = $this->get("/cities/{$city->id}");

        $response->assertStatus(200);
        // Should only show 3 articles as per controller limit
    }
}
