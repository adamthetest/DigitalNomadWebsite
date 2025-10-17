<?php

namespace Tests\Unit;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_has_fillable_attributes()
    {
        $user = new User;
        $fillable = $user->getFillable();

        $expectedFillable = [
            'name', 'email', 'password', 'bio', 'tagline', 'job_title',
            'company', 'skills', 'work_type', 'availability', 'location',
            'location_current', 'location_next', 'travel_timeline', 'profile_image',
            'website', 'twitter', 'instagram', 'linkedin', 'github', 'behance',
            'is_public', 'id_verified', 'premium_status', 'last_active',
            'visibility', 'location_precise', 'show_social_links', 'timezone',
            // Phase 2: AI-ready fields
            'profession_details', 'technical_skills', 'soft_skills', 'experience_years',
            'education_level', 'certifications', 'preferred_climates', 'preferred_activities',
            'budget_monthly_min', 'budget_monthly_max', 'budget_currency', 'visa_flexible',
            'preferred_work_schedule', 'work_environment_preferences', 'requires_stable_internet',
            'min_internet_speed_mbps', 'lifestyle_tags', 'pet_friendly_needed',
            'family_friendly_needed', 'dietary_restrictions', 'ai_profile_summary',
            'ai_preferences_vector', 'ai_profile_updated_at', 'ai_data_collection_consent',
            'personalized_recommendations', 'data_sharing_preferences', 'is_admin',
            // Phase 3: Job Matching fields
            'profile_embedding', 'skills_embedding', 'experience_embedding',
            'job_matching_preferences', 'preferred_job_types', 'preferred_remote_types',
            'salary_expectations', 'timezone_preferences', 'ai_skills_analysis',
            'ai_career_insights', 'ai_resume_optimization_tips', 'matching_metadata',
            'last_profile_update', 'last_embedding_update', 'resume_content',
            'resume_file_path', 'resume_metadata', 'cover_letter_template',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_user_has_hidden_attributes()
    {
        $user = new User;
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_casts_attributes_correctly()
    {
        $user = User::factory()->create([
            'is_public' => '1',
            'id_verified' => '0',
            'premium_status' => '1',
            'location_precise' => '0',
            'show_social_links' => '1',
            'skills' => ['PHP', 'Laravel', 'JavaScript'],
            'travel_timeline' => [
                ['city' => 'Bangkok', 'country' => 'Thailand', 'arrived_at' => '2024-01-01'],
            ],
        ]);

        $this->assertIsBool($user->is_public);
        $this->assertIsBool($user->id_verified);
        $this->assertIsBool($user->premium_status);
        $this->assertIsBool($user->location_precise);
        $this->assertIsBool($user->show_social_links);
        $this->assertIsArray($user->skills);
        $this->assertIsArray($user->travel_timeline);
    }

    public function test_user_has_favorites_relationship()
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->favorites());
        $this->assertTrue($user->favorites->contains($favorite));
    }

    public function test_user_has_favorite_cities_relationship()
    {
        $user = User::factory()->create();
        $cityFavorite = Favorite::factory()->create([
            'user_id' => $user->id,
            'category' => 'city',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->favoriteCities());
    }

    public function test_user_has_favorite_articles_relationship()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->favoriteArticles());
    }

    public function test_user_has_favorite_deals_relationship()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->favoriteDeals());
    }

    public function test_user_has_job_interactions_relationship()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->jobInteractions());
    }

    public function test_profile_image_url_attribute()
    {
        $user = User::factory()->create(['profile_image' => 'profile.jpg']);

        $this->assertEquals(asset('storage/profile.jpg'), $user->profile_image_url);
    }

    public function test_profile_image_url_fallback_to_avatar()
    {
        $user = User::factory()->create(['profile_image' => null, 'name' => 'John Doe']);

        $expectedUrl = 'https://ui-avatars.com/api/?name='.urlencode('John Doe').'&color=7F9CF5&background=EBF4FF';
        $this->assertEquals($expectedUrl, $user->profile_image_url);
    }

    public function test_display_name_attribute()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertEquals('John Doe', $user->display_name);

        $userWithoutName = User::factory()->create(['name' => 'Anonymous User']);
        $this->assertEquals('Anonymous User', $userWithoutName->display_name);
    }

    public function test_social_links_attribute_when_show_social_links_is_false()
    {
        $user = User::factory()->create([
            'show_social_links' => false,
            'website' => 'https://example.com',
            'twitter' => '@johndoe',
        ]);

        $this->assertEquals([], $user->social_links);
    }

    public function test_social_links_attribute_when_show_social_links_is_true()
    {
        $user = User::factory()->create([
            'show_social_links' => true,
            'website' => 'https://example.com',
            'twitter' => '@johndoe',
            'instagram' => null,
            'linkedin' => 'https://linkedin.com/in/johndoe',
            'github' => null,
            'behance' => null,
        ]);

        $expectedLinks = [
            'website' => 'https://example.com',
            'twitter' => '@johndoe',
            'linkedin' => 'https://linkedin.com/in/johndoe',
        ];

        $this->assertEquals($expectedLinks, $user->social_links);
    }

    public function test_has_complete_profile_method()
    {
        $completeUser = User::factory()->create([
            'bio' => 'Software developer',
            'location_current' => 'Bangkok, Thailand',
            'profile_image' => 'profile.jpg',
            'tagline' => 'Digital nomad',
        ]);

        $incompleteUser = User::factory()->create([
            'bio' => 'Software developer',
            'location_current' => null,
            'profile_image' => 'profile.jpg',
            'tagline' => 'Digital nomad',
        ]);

        $this->assertTrue($completeUser->hasCompleteProfile());
        $this->assertFalse($incompleteUser->hasCompleteProfile());
    }

    public function test_profile_completion_attribute()
    {
        $user = User::factory()->create([
            'bio' => 'Software developer',
            'tagline' => 'Digital nomad',
            'location_current' => 'Bangkok, Thailand',
            'profile_image' => 'profile.jpg',
            'job_title' => 'Developer',
            'skills' => ['PHP', 'Laravel'],
            'work_type' => 'freelancer',
        ]);

        // All 7 fields filled = 100%
        $this->assertEquals(100, $user->profile_completion);

        $partialUser = User::factory()->create([
            'bio' => 'Software developer',
            'tagline' => 'Digital nomad',
            'location_current' => 'Bangkok, Thailand',
            'profile_image' => 'profile.jpg',
            'job_title' => 'Developer',
            'skills' => ['PHP', 'Laravel'],
            'work_type' => 'freelancer',
        ]);

        // All fields filled = 100%
        $this->assertEquals(100, $partialUser->profile_completion);
    }

    public function test_current_location_attribute_with_precise_location()
    {
        $user = User::factory()->create([
            'location_current' => 'Bangkok, Thailand',
            'location_precise' => true,
        ]);

        $this->assertEquals('Bangkok, Thailand', $user->current_location);
    }

    public function test_current_location_attribute_without_precise_location()
    {
        $user = User::factory()->create([
            'location_current' => 'Bangkok, Thailand',
            'location_precise' => false,
        ]);

        $this->assertEquals('Thailand', $user->current_location);
    }

    public function test_current_location_attribute_fallback()
    {
        $user = User::factory()->create([
            'location_current' => null,
            'location' => 'Somewhere',
        ]);

        $this->assertEquals('Somewhere', $user->current_location);

        $userWithNoLocation = User::factory()->create([
            'location_current' => null,
            'location' => null,
        ]);

        $this->assertEquals('Location not set', $userWithNoLocation->current_location);
    }

    public function test_verification_badges_attribute()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'id_verified' => true,
            'premium_status' => true,
        ]);

        $expectedBadges = ['email_verified', 'id_verified', 'premium'];
        $this->assertEquals($expectedBadges, $user->verification_badges);

        $userWithNoBadges = User::factory()->create([
            'email_verified_at' => null,
            'id_verified' => false,
            'premium_status' => false,
        ]);

        $this->assertEquals([], $userWithNoBadges->verification_badges);
    }

    public function test_is_online_method()
    {
        $onlineUser = User::factory()->create([
            'last_active' => now()->subMinutes(10),
        ]);

        $offlineUser = User::factory()->create([
            'last_active' => now()->subMinutes(20),
        ]);

        $neverActiveUser = User::factory()->create([
            'last_active' => null,
        ]);

        $this->assertTrue($onlineUser->isOnline());
        $this->assertFalse($offlineUser->isOnline());
        $this->assertFalse($neverActiveUser->isOnline());
    }

    public function test_update_last_active_method()
    {
        $user = User::factory()->create(['last_active' => null]);

        $user->updateLastActive();

        $this->assertNotNull($user->fresh()->last_active);
        $this->assertTrue($user->fresh()->last_active->isAfter(now()->subMinute()));
    }

    public function test_add_to_travel_timeline_method()
    {
        $user = User::factory()->create(['travel_timeline' => null]);

        $user->addToTravelTimeline('Bangkok', 'Thailand', '2024-01-01', '2024-03-01');

        $timeline = $user->fresh()->travel_timeline;
        $this->assertIsArray($timeline);
        $this->assertCount(1, $timeline);
        $this->assertEquals('Bangkok', $timeline[0]['city']);
        $this->assertEquals('Thailand', $timeline[0]['country']);
        $this->assertEquals('2024-01-01', $timeline[0]['arrived_at']);
        $this->assertEquals('2024-03-01', $timeline[0]['left_at']);
    }

    public function test_add_to_travel_timeline_with_default_arrived_at()
    {
        $user = User::factory()->create(['travel_timeline' => null]);

        $user->addToTravelTimeline('Bangkok', 'Thailand');

        $timeline = $user->fresh()->travel_timeline;
        $this->assertEquals(now()->toDateString(), $timeline[0]['arrived_at']);
        $this->assertNull($timeline[0]['left_at']);
    }

    public function test_scope_public()
    {
        User::factory()->create(['visibility' => 'public']);
        User::factory()->create(['visibility' => 'hidden']);
        User::factory()->create(['visibility' => 'members']);

        $publicUsers = User::public()->get();
        $this->assertCount(1, $publicUsers);
        $this->assertEquals('public', $publicUsers->first()->visibility);
    }

    public function test_scope_members()
    {
        User::factory()->create(['visibility' => 'public']);
        User::factory()->create(['visibility' => 'hidden']);
        User::factory()->create(['visibility' => 'members']);

        $memberUsers = User::members()->get();
        $this->assertCount(2, $memberUsers);
    }

    public function test_scope_premium()
    {
        User::factory()->create(['premium_status' => true]);
        User::factory()->create(['premium_status' => false]);

        $premiumUsers = User::premium()->get();
        $this->assertCount(1, $premiumUsers);
        $this->assertTrue($premiumUsers->first()->premium_status);
    }

    public function test_scope_verified()
    {
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $verifiedUsers = User::verified()->get();
        $this->assertCount(1, $verifiedUsers);
        $this->assertNotNull($verifiedUsers->first()->email_verified_at);
    }

    public function test_scope_by_location()
    {
        User::factory()->create(['location_current' => 'Bangkok, Thailand']);
        User::factory()->create(['location_next' => 'Chiang Mai, Thailand']);
        User::factory()->create(['location_current' => 'Tokyo, Japan']);

        $thailandUsers = User::byLocation('Thailand')->get();
        $this->assertGreaterThanOrEqual(2, $thailandUsers->count());
        $this->assertTrue($thailandUsers->contains(function ($user) {
            return str_contains($user->location_current, 'Thailand') || str_contains($user->location_next, 'Thailand');
        }));
    }

    public function test_scope_by_skills()
    {
        User::factory()->create(['skills' => ['PHP', 'Laravel']]);
        User::factory()->create(['skills' => ['JavaScript', 'React']]);
        User::factory()->create(['skills' => ['PHP', 'Vue']]);

        $phpUsers = User::bySkills(['PHP'])->get();
        $this->assertCount(2, $phpUsers);

        $laravelUsers = User::bySkills(['Laravel'])->get();
        $this->assertCount(1, $laravelUsers);
    }

    public function test_scope_by_work_type()
    {
        User::factory()->create(['work_type' => 'freelancer']);
        User::factory()->create(['work_type' => 'employee']);
        User::factory()->create(['work_type' => 'entrepreneur']);

        $freelancerUsers = User::byWorkType('freelancer')->get();
        $this->assertCount(1, $freelancerUsers);
        $this->assertEquals('freelancer', $freelancerUsers->first()->work_type);
    }
}
