<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Favorite;
use App\Models\City;
use App\Models\Article;
use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_toggle_requires_authentication()
    {
        $response = $this->postJson('/favorites/toggle', [
            'favoritable_id' => 1,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);

        $response->assertStatus(401);
    }

    public function test_favorites_toggle_adds_city_favorite()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city',
            'notes' => 'Great place to work'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorited' => true,
            'message' => 'Added to favorites'
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);
    }

    public function test_favorites_toggle_removes_city_favorite()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorited' => false,
            'message' => 'Removed from favorites'
        ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City'
        ]);
    }

    public function test_favorites_toggle_adds_article_favorite()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => $article->id,
            'favoritable_type' => 'App\Models\Article',
            'category' => 'article'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorited' => true,
            'message' => 'Added to favorites'
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $article->id,
            'favoritable_type' => 'App\Models\Article',
            'category' => 'article'
        ]);
    }

    public function test_favorites_toggle_adds_deal_favorite()
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => $deal->id,
            'favoritable_type' => 'App\Models\Deal',
            'category' => 'deal'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorited' => true,
            'message' => 'Added to favorites'
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_id' => $deal->id,
            'favoritable_type' => 'App\Models\Deal',
            'category' => 'deal'
        ]);
    }

    public function test_favorites_toggle_validates_required_fields()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['favoritable_id', 'favoritable_type']);
    }

    public function test_favorites_toggle_validates_favoritable_type()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => 1,
            'favoritable_type' => 'Invalid\Model'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['favoritable_type']);
    }

    public function test_favorites_toggle_validates_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => 1,
            'favoritable_type' => 'App\Models\City',
            'category' => 'invalid'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_favorites_toggle_validates_notes_length()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => 1,
            'favoritable_type' => 'App\Models\City',
            'notes' => str_repeat('a', 1001)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['notes']);
    }

    public function test_favorites_index_requires_authentication()
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_favorites_index_displays_user_favorites()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertViewIs('favorites.index');
        $response->assertSee($city->name);
    }

    public function test_favorites_index_filters_by_category()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $article = Article::factory()->create();
        
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $article->id,
            'favoritable_type' => 'App\Models\Article',
            'category' => 'article'
        ]);

        $response = $this->actingAs($user)->get('/favorites?category=city');

        $response->assertStatus(200);
        $response->assertSee($city->name);
        $response->assertDontSee($article->title);
    }

    public function test_favorites_index_shows_all_categories_by_default()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $article = Article::factory()->create();
        
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city'
        ]);
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $article->id,
            'favoritable_type' => 'App\Models\Article',
            'category' => 'article'
        ]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee($city->name);
        $response->assertSee($article->title);
    }

    public function test_favorites_destroy_requires_authentication()
    {
        $favorite = Favorite::factory()->create();

        $response = $this->deleteJson("/favorites/{$favorite->id}");

        $response->assertStatus(401);
    }

    public function test_favorites_destroy_removes_favorite()
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/favorites/{$favorite->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Removed from favorites'
        ]);

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_favorites_destroy_prevents_unauthorized_access()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->deleteJson("/favorites/{$favorite->id}");

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized']);

        $this->assertDatabaseHas('favorites', ['id' => $favorite->id]);
    }

    public function test_favorites_update_notes_requires_authentication()
    {
        $favorite = Favorite::factory()->create();

        $response = $this->putJson("/favorites/{$favorite->id}/notes", [
            'notes' => 'Updated notes'
        ]);

        $response->assertStatus(401);
    }

    public function test_favorites_update_notes_updates_favorite()
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'notes' => 'Original notes'
        ]);

        $response = $this->actingAs($user)->putJson("/favorites/{$favorite->id}/notes", [
            'notes' => 'Updated notes'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Notes updated successfully'
        ]);

        $this->assertEquals('Updated notes', $favorite->fresh()->notes);
    }

    public function test_favorites_update_notes_prevents_unauthorized_access()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->putJson("/favorites/{$favorite->id}/notes", [
            'notes' => 'Updated notes'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_favorites_update_notes_validates_notes_length()
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/favorites/{$favorite->id}/notes", [
            'notes' => str_repeat('a', 1001)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['notes']);
    }

    public function test_favorites_update_notes_allows_empty_notes()
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'notes' => 'Original notes'
        ]);

        $response = $this->actingAs($user)->putJson("/favorites/{$favorite->id}/notes", [
            'notes' => ''
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Notes updated successfully'
        ]);

        $this->assertNull($favorite->fresh()->notes);
    }

    public function test_favorites_get_count_returns_count_and_status()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        
        // Create favorites from other users
        Favorite::factory()->count(3)->create([
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City'
        ]);

        $response = $this->getJson('/favorites/count', [
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'count' => 3,
            'is_favorited' => false
        ]);
    }

    public function test_favorites_get_count_shows_user_favorite_status()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        
        Favorite::factory()->create([
            'user_id' => $user->id,
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City'
        ]);

        $response = $this->actingAs($user)->getJson('/favorites/count', [
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'count' => 1,
            'is_favorited' => true
        ]);
    }

    public function test_favorites_get_count_validates_required_fields()
    {
        $response = $this->getJson('/favorites/count');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['favoritable_id', 'favoritable_type']);
    }

    public function test_favorites_toggle_auto_detects_category()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City'
            // No category provided
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorited' => true
        ]);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $city->id)
            ->where('favoritable_type', 'App\Models\City')
            ->first();

        $this->assertEquals('city', $favorite->category);
    }

    public function test_favorites_toggle_handles_array_notes()
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $notes = [
            'personal' => 'Great place to work',
            'cost' => 'Affordable living',
            'weather' => 'Perfect climate'
        ];

        $response = $this->actingAs($user)->postJson('/favorites/toggle', [
            'favoritable_id' => $city->id,
            'favoritable_type' => 'App\Models\City',
            'category' => 'city',
            'notes' => $notes
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorited' => true
        ]);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favoritable_id', $city->id)
            ->where('favoritable_type', 'App\Models\City')
            ->first();

        $this->assertEquals($notes, $favorite->notes);
    }
}
