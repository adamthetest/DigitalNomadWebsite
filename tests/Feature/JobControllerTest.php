<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\Company;
use App\Models\JobUserInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_index_page_loads()
    {
        $response = $this->get('/jobs');

        $response->assertStatus(200);
        $response->assertViewIs('jobs.index');
    }

    public function test_job_index_displays_jobs()
    {
        $company = Company::factory()->create();
        $job = Job::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs');

        $response->assertStatus(200);
        $response->assertSee($job->title);
        $response->assertSee($company->name);
    }

    public function test_job_index_filters_by_search()
    {
        $company = Company::factory()->create(['name' => 'Tech Corp']);
        $job1 = Job::factory()->create([
            'title' => 'PHP Developer',
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'title' => 'JavaScript Developer',
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?search=PHP');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_company_name()
    {
        $company = Company::factory()->create(['name' => 'Tech Corp']);
        $job1 = Job::factory()->create([
            'title' => 'Developer',
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'title' => 'Developer',
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?search=Tech');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_type()
    {
        $job1 = Job::factory()->create([
            'type' => 'full-time',
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'type' => 'part-time',
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?type=full-time');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_remote_type()
    {
        $job1 = Job::factory()->create([
            'remote_type' => 'fully-remote',
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'remote_type' => 'onsite',
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?remote_type=fully-remote');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_salary_range()
    {
        $job1 = Job::factory()->create([
            'salary_min' => 50000,
            'salary_max' => 80000,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'salary_min' => 30000,
            'salary_max' => 50000,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?salary_min=45000&salary_max=75000');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_tags()
    {
        $job1 = Job::factory()->create([
            'tags' => ['PHP', 'Laravel'],
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'tags' => ['JavaScript', 'React'],
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?tags=PHP');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_visa_support()
    {
        $job1 = Job::factory()->create([
            'visa_support' => true,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'visa_support' => false,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?visa_support=1');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_filters_by_date_posted()
    {
        $job1 = Job::factory()->create([
            'created_at' => now()->subDays(3),
            'is_active' => true,
            'published_at' => now()->subDays(3)
        ]);
        $job2 = Job::factory()->create([
            'created_at' => now()->subDays(10),
            'is_active' => true,
            'published_at' => now()->subDays(10)
        ]);

        $response = $this->get('/jobs?date_posted=7d');

        $response->assertStatus(200);
        $response->assertSee($job1->title);
        $response->assertDontSee($job2->title);
    }

    public function test_job_index_sorts_by_newest()
    {
        $job1 = Job::factory()->create([
            'created_at' => now()->subDay(),
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'created_at' => now()->subDays(2),
            'is_active' => true,
            'published_at' => now()->subDays(2)
        ]);

        $response = $this->get('/jobs?sort=newest');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$job1->title, $job2->title]);
    }

    public function test_job_index_sorts_by_salary_high()
    {
        $job1 = Job::factory()->create([
            'salary_max' => 100000,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'salary_max' => 50000,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?sort=salary_high');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$job1->title, $job2->title]);
    }

    public function test_job_index_sorts_by_featured()
    {
        $job1 = Job::factory()->create([
            'featured' => true,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'featured' => false,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get('/jobs?sort=featured');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$job1->title, $job2->title]);
    }

    public function test_job_show_page_loads()
    {
        $job = Job::factory()->create([
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(200);
        $response->assertViewIs('jobs.show');
        $response->assertSee($job->title);
    }

    public function test_job_show_returns_404_for_inactive_job()
    {
        $job = Job::factory()->create(['is_active' => false]);

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(404);
    }

    public function test_job_show_returns_404_for_unpublished_job()
    {
        $job = Job::factory()->create([
            'is_active' => true,
            'published_at' => now()->addDay()
        ]);

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(404);
    }

    public function test_job_show_increments_view_count()
    {
        $job = Job::factory()->create([
            'is_active' => true,
            'published_at' => now()->subDay(),
            'views_count' => 10
        ]);

        $this->get("/jobs/{$job->id}");

        $this->assertEquals(11, $job->fresh()->views_count);
    }

    public function test_job_show_displays_related_jobs()
    {
        $company = Company::factory()->create();
        $job1 = Job::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $job2 = Job::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get("/jobs/{$job1->id}");

        $response->assertStatus(200);
        $response->assertSee($job2->title);
    }

    public function test_job_toggle_save_requires_authentication()
    {
        $job = Job::factory()->create();

        $response = $this->postJson("/jobs/{$job->id}/toggle-save");

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Authentication required']);
    }

    public function test_job_toggle_save_adds_favorite()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create();

        $response = $this->actingAs($user)->postJson("/jobs/{$job->id}/toggle-save");

        $response->assertStatus(200);
        $response->assertJson(['saved' => true]);
        $this->assertDatabaseHas('job_user_interactions', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'saved'
        ]);
    }

    public function test_job_toggle_save_removes_favorite()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create();
        JobUserInteraction::factory()->create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'saved'
        ]);

        $response = $this->actingAs($user)->postJson("/jobs/{$job->id}/toggle-save");

        $response->assertStatus(200);
        $response->assertJson(['saved' => false]);
        $this->assertDatabaseMissing('job_user_interactions', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'saved'
        ]);
    }

    public function test_job_toggle_save_updates_existing_interaction()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create();
        JobUserInteraction::factory()->create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'viewed'
        ]);

        $response = $this->actingAs($user)->postJson("/jobs/{$job->id}/toggle-save");

        $response->assertStatus(200);
        $response->assertJson(['saved' => true]);
        $this->assertDatabaseHas('job_user_interactions', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'saved'
        ]);
    }

    public function test_job_apply_requires_authentication()
    {
        $job = Job::factory()->create();

        $response = $this->post("/jobs/{$job->id}/apply");

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Please login to apply for jobs.');
    }

    public function test_job_apply_submits_application()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create();

        $response = $this->actingAs($user)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'I am interested in this position.',
            'resume_url' => 'https://example.com/resume.pdf'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Application submitted successfully!');
        $this->assertDatabaseHas('job_user_interactions', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'applied'
        ]);
    }

    public function test_job_apply_increments_applications_count()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create(['applications_count' => 5]);

        $this->actingAs($user)->post("/jobs/{$job->id}/apply");

        $this->assertEquals(6, $job->fresh()->applications_count);
    }

    public function test_job_apply_prevents_duplicate_applications()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create();
        JobUserInteraction::factory()->create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'applied'
        ]);

        $response = $this->actingAs($user)->post("/jobs/{$job->id}/apply");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You have already applied to this job.');
    }

    public function test_job_apply_updates_existing_interaction()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create();
        JobUserInteraction::factory()->create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'saved'
        ]);

        $response = $this->actingAs($user)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'I am interested in this position.'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Application submitted successfully!');
        $this->assertDatabaseHas('job_user_interactions', [
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'applied'
        ]);
    }

    public function test_job_company_page_loads()
    {
        $company = Company::factory()->create();
        $job = Job::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get("/jobs/company/{$company->id}");

        $response->assertStatus(200);
        $response->assertViewIs('jobs.company');
        $response->assertSee($company->name);
        $response->assertSee($job->title);
    }

    public function test_job_company_page_shows_only_active_jobs()
    {
        $company = Company::factory()->create();
        $activeJob = Job::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $inactiveJob = Job::factory()->create([
            'company_id' => $company->id,
            'is_active' => false,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get("/jobs/company/{$company->id}");

        $response->assertStatus(200);
        $response->assertSee($activeJob->title);
        $response->assertDontSee($inactiveJob->title);
    }

    public function test_job_company_page_sorts_by_featured()
    {
        $company = Company::factory()->create();
        $regularJob = Job::factory()->create([
            'company_id' => $company->id,
            'featured' => false,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);
        $featuredJob = Job::factory()->create([
            'company_id' => $company->id,
            'featured' => true,
            'is_active' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->get("/jobs/company/{$company->id}");

        $response->assertStatus(200);
        $response->assertSeeInOrder([$featuredJob->title, $regularJob->title]);
    }
}
