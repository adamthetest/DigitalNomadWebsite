<?php

namespace Tests\Unit;

use App\Models\City;
use App\Models\Country;
use App\Models\Neighborhood;
use App\Models\CoworkingSpace;
use App\Models\CostItem;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_can_be_created()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create([
            'name' => 'Bangkok',
            'country_id' => $country->id,
        ]);

        $this->assertDatabaseHas('cities', [
            'name' => 'Bangkok',
            'country_id' => $country->id,
        ]);
    }

    public function test_city_has_fillable_attributes()
    {
        $city = new City();
        $fillable = $city->getFillable();

        $expectedFillable = [
            'country_id', 'name', 'slug', 'latitude', 'longitude',
            'description', 'overview', 'population', 'climate',
            'internet_speed_mbps', 'safety_score', 'cost_of_living_index',
            'best_time_to_visit', 'highlights', 'images', 'is_featured',
            'is_active'
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_city_casts_attributes_correctly()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create([
            'country_id' => $country->id,
            'latitude' => '13.7563',
            'longitude' => '100.5018',
            'cost_of_living_index' => '45.5',
            'highlights' => ['Temples', 'Street Food', 'Nightlife'],
            'images' => ['image1.jpg', 'image2.jpg'],
            'is_featured' => '1',
            'is_active' => '0'
        ]);

        $this->assertIsString($city->latitude);
        $this->assertIsString($city->longitude);
        $this->assertIsString($city->cost_of_living_index);
        $this->assertIsArray($city->highlights);
        $this->assertIsArray($city->images);
        $this->assertIsBool($city->is_featured);
        $this->assertIsBool($city->is_active);
    }

    public function test_city_belongs_to_country()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $city->country());
        $this->assertEquals($country->id, $city->country->id);
    }

    public function test_city_has_neighborhoods_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $city->neighborhoods());
        $this->assertTrue($city->neighborhoods->contains($neighborhood));
    }

    public function test_city_has_coworking_spaces_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        $coworkingSpace = CoworkingSpace::factory()->create(['city_id' => $city->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $city->coworkingSpaces());
        $this->assertTrue($city->coworkingSpaces->contains($coworkingSpace));
    }

    public function test_city_has_cost_items_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        $costItem = CostItem::factory()->create(['city_id' => $city->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $city->costItems());
        $this->assertTrue($city->costItems->contains($costItem));
    }

    public function test_city_has_articles_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        $user = \App\Models\User::factory()->create();
        $article = Article::factory()->create(['city_id' => $city->id, 'user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $city->articles());
        $this->assertTrue($city->articles->contains($article));
    }

    public function test_city_has_active_neighborhoods_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        Neighborhood::factory()->create(['city_id' => $city->id, 'is_active' => true]);
        Neighborhood::factory()->create(['city_id' => $city->id, 'is_active' => false]);

        $activeNeighborhoods = $city->activeNeighborhoods;
        $this->assertCount(1, $activeNeighborhoods);
        $this->assertTrue($activeNeighborhoods->first()->is_active);
    }

    public function test_city_has_active_coworking_spaces_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        CoworkingSpace::factory()->create(['city_id' => $city->id, 'is_active' => true]);
        CoworkingSpace::factory()->create(['city_id' => $city->id, 'is_active' => false]);

        $activeCoworkingSpaces = $city->activeCoworkingSpaces;
        $this->assertCount(1, $activeCoworkingSpaces);
        $this->assertTrue($activeCoworkingSpaces->first()->is_active);
    }

    public function test_city_has_published_articles_relationship()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        $user = \App\Models\User::factory()->create();
        Article::factory()->create(['city_id' => $city->id, 'user_id' => $user->id, 'status' => 'published']);
        Article::factory()->create(['city_id' => $city->id, 'user_id' => $user->id, 'status' => 'draft']);

        $publishedArticles = $city->publishedArticles;
        $this->assertCount(1, $publishedArticles);
        $this->assertEquals('published', $publishedArticles->first()->status);
    }

    public function test_city_slug_is_auto_generated_on_creation()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'name' => 'New York City']);

        $this->assertEquals('new-york-city', $city->slug);
    }

    public function test_city_slug_can_be_set_manually()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create([
            'country_id' => $country->id,
            'name' => 'New York City',
            'slug' => 'custom-slug'
        ]);

        $this->assertEquals('custom-slug', $city->slug);
    }

    public function test_city_slug_generation_with_special_characters()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'name' => 'São Paulo']);

        $this->assertEquals('sao-paulo', $city->slug);
    }

    public function test_city_slug_generation_with_numbers()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'name' => 'City 123']);

        $this->assertEquals('city-123', $city->slug);
    }

    public function test_city_can_have_highlights_array()
    {
        $highlights = [
            'Temples and Palaces',
            'Street Food Culture',
            'Nightlife Scene',
            'Shopping Malls'
        ];

        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'highlights' => $highlights]);

        $this->assertIsArray($city->highlights);
        $this->assertCount(4, $city->highlights);
        $this->assertContains('Temples and Palaces', $city->highlights);
        $this->assertContains('Street Food Culture', $city->highlights);
    }

    public function test_city_can_have_images_array()
    {
        $images = [
            'skyline.jpg',
            'street-food.jpg',
            'temple.jpg',
            'nightlife.jpg'
        ];

        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'images' => $images]);

        $this->assertIsArray($city->images);
        $this->assertCount(4, $city->images);
        $this->assertContains('skyline.jpg', $city->images);
        $this->assertContains('street-food.jpg', $city->images);
    }

    public function test_city_can_be_featured()
    {
        $country = Country::factory()->create();
        $featuredCity = City::factory()->create(['country_id' => $country->id, 'is_featured' => true]);
        $regularCity = City::factory()->create(['country_id' => $country->id, 'is_featured' => false]);

        $this->assertTrue($featuredCity->is_featured);
        $this->assertFalse($regularCity->is_featured);
    }

    public function test_city_can_be_active_or_inactive()
    {
        $country = Country::factory()->create();
        $activeCity = City::factory()->create(['country_id' => $country->id, 'is_active' => true]);
        $inactiveCity = City::factory()->create(['country_id' => $country->id, 'is_active' => false]);

        $this->assertTrue($activeCity->is_active);
        $this->assertFalse($inactiveCity->is_active);
    }

    public function test_city_can_have_coordinates()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create([
            'country_id' => $country->id,
            'latitude' => 13.7563,
            'longitude' => 100.5018
        ]);

        $this->assertEquals(13.7563, $city->latitude);
        $this->assertEquals(100.5018, $city->longitude);
    }

    public function test_city_can_have_cost_of_living_index()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'cost_of_living_index' => 45.5]);

        $this->assertEquals(45.5, $city->cost_of_living_index);
    }

    public function test_city_can_have_internet_speed()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'internet_speed_mbps' => 50.5]);

        $this->assertEquals(50.5, $city->internet_speed_mbps);
    }

    public function test_city_can_have_safety_score()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'safety_score' => 8.5]);

        $this->assertEquals(8.5, $city->safety_score);
    }

    public function test_city_can_have_population()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'population' => 10000000]);

        $this->assertEquals(10000000, $city->population);
    }

    public function test_city_can_have_climate_description()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'climate' => 'Tropical monsoon climate']);

        $this->assertEquals('Tropical monsoon climate', $city->climate);
    }

    public function test_city_can_have_best_time_to_visit()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'best_time_to_visit' => 'November to March']);

        $this->assertEquals('November to March', $city->best_time_to_visit);
    }

    public function test_city_can_have_description()
    {
        $description = 'Bangkok is the capital and most populous city of Thailand.';
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'description' => $description]);

        $this->assertEquals($description, $city->description);
    }

    public function test_city_can_have_overview()
    {
        $overview = 'A bustling metropolis known for its vibrant street life and cultural landmarks.';
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'overview' => $overview]);

        $this->assertEquals($overview, $city->overview);
    }

    public function test_city_can_be_updated()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Original Name']);
        
        $city->update(['name' => 'Updated Name']);
        
        $this->assertEquals('Updated Name', $city->fresh()->name);
    }

    public function test_city_can_be_deleted()
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);
        $cityId = $city->id;
        
        $city->delete();
        
        $this->assertDatabaseMissing('cities', ['id' => $cityId]);
    }
}
