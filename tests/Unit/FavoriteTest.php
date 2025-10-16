<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\City;
use App\Models\Deal;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorite_can_be_created()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
            'category' => 'city',
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
            'category' => 'city',
        ]);
    }

    public function test_favorite_has_fillable_attributes()
    {
        $favorite = new Favorite;
        $fillable = $favorite->getFillable();

        $expectedFillable = [
            'user_id',
            'favoritable_id',
            'favoritable_type',
            'category',
            'notes',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_favorite_casts_notes_as_array()
    {
        $favorite = Favorite::factory()->create([
            'notes' => ['note1' => 'First note', 'note2' => 'Second note'],
        ]);

        $this->assertIsArray($favorite->notes);
        $this->assertEquals('First note', $favorite->notes['note1']);
        $this->assertEquals('Second note', $favorite->notes['note2']);
    }

    public function test_favorite_belongs_to_user()
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $favorite->user());
        $this->assertEquals($user->id, $favorite->user->id);
    }

    public function test_favorite_morphs_to_favoritable()
    {
        $city = City::factory()->create();
        $favorite = Favorite::factory()->create([
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $favorite->favoritable());
        $this->assertInstanceOf(City::class, $favorite->favoritable);
        $this->assertEquals($city->id, $favorite->favoritable->id);
    }

    public function test_scope_by_category()
    {
        Favorite::factory()->create(['category' => 'city']);
        Favorite::factory()->create(['category' => 'article']);
        Favorite::factory()->create(['category' => 'deal']);

        $cityFavorites = Favorite::byCategory('city')->get();
        $this->assertCount(1, $cityFavorites);
        $this->assertEquals('city', $cityFavorites->first()->category);
    }

    public function test_scope_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Favorite::factory()->create(['user_id' => $user1->id]);
        Favorite::factory()->create(['user_id' => $user1->id]);
        Favorite::factory()->create(['user_id' => $user2->id]);

        $user1Favorites = Favorite::byUser($user1->id)->get();
        $this->assertCount(2, $user1Favorites);

        $user2Favorites = Favorite::byUser($user2->id)->get();
        $this->assertCount(1, $user2Favorites);
    }

    public function test_is_favorited_static_method()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        // Initially not favorited
        $this->assertFalse(Favorite::isFavorited($user->id, $city->id, City::class));

        // Create favorite
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
        ]);

        // Now should be favorited
        $this->assertTrue(Favorite::isFavorited($user->id, $city->id, City::class));
    }

    public function test_toggle_static_method_adds_favorite()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $result = Favorite::toggle($user->id, $city->id, City::class, 'city', 'My notes');

        $this->assertTrue($result);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
            'category' => 'city',
        ]);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $city->id)
            ->where('favoritable_type', City::class)
            ->first();

        $this->assertEquals('My notes', $favorite->notes);
    }

    public function test_toggle_static_method_removes_favorite()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        // Create initial favorite
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
        ]);

        // Toggle should remove it
        $result = Favorite::toggle($user->id, $city->id, City::class);

        $this->assertFalse($result);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
        ]);
    }

    public function test_toggle_static_method_updates_existing_favorite()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        // Create initial favorite with different status
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => City::class,
            'category' => 'city',
        ]);

        // Toggle should update existing favorite
        $result = Favorite::toggle($user->id, $city->id, City::class, 'city', 'Updated notes');

        $this->assertTrue($result);

        $updatedFavorite = Favorite::find($favorite->id);
        $this->assertEquals('Updated notes', $updatedFavorite->notes);
    }

    public function test_toggle_static_method_without_category_and_notes()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $result = Favorite::toggle($user->id, $city->id, City::class);

        $this->assertTrue($result);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $city->id)
            ->where('favoritable_type', City::class)
            ->first();

        $this->assertNull($favorite->category);
        $this->assertNull($favorite->notes);
    }

    public function test_favorite_works_with_different_model_types()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $article = Article::factory()->create();
        $deal = Deal::factory()->create();

        // Test with City
        Favorite::toggle($user->id, $city->id, City::class, 'city');
        $this->assertTrue(Favorite::isFavorited($user->id, $city->id, City::class));

        // Test with Article
        Favorite::toggle($user->id, $article->id, Article::class, 'article');
        $this->assertTrue(Favorite::isFavorited($user->id, $article->id, Article::class));

        // Test with Deal
        Favorite::toggle($user->id, $deal->id, Deal::class, 'deal');
        $this->assertTrue(Favorite::isFavorited($user->id, $deal->id, Deal::class));

        // Verify all favorites exist
        $this->assertCount(3, Favorite::byUser($user->id)->get());
    }

    public function test_favorite_can_have_array_notes()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $notes = [
            'personal' => 'Great place to work',
            'cost' => 'Affordable living',
            'weather' => 'Perfect climate',
        ];

        Favorite::toggle($user->id, $city->id, City::class, 'city', $notes);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $city->id)
            ->where('favoritable_type', City::class)
            ->first();

        $this->assertIsArray($favorite->notes);
        $this->assertEquals('Great place to work', $favorite->notes['personal']);
        $this->assertEquals('Affordable living', $favorite->notes['cost']);
        $this->assertEquals('Perfect climate', $favorite->notes['weather']);
    }

    public function test_favorite_can_be_updated()
    {
        $favorite = Favorite::factory()->create(['notes' => 'Original notes']);

        $favorite->update(['notes' => 'Updated notes']);

        $this->assertEquals('Updated notes', $favorite->fresh()->notes);
    }

    public function test_favorite_can_be_deleted()
    {
        $favorite = Favorite::factory()->create();
        $favoriteId = $favorite->id;

        $favorite->delete();

        $this->assertDatabaseMissing('favorites', ['id' => $favoriteId]);
    }
}
