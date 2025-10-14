<?php

namespace Tests\Unit;

use App\Models\Job;
use App\Models\Company;
use App\Models\JobUserInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_created()
    {
        $company = Company::factory()->create();
        $job = Job::factory()->create([
            'title' => 'Senior Developer',
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('job_postings', [
            'title' => 'Senior Developer',
            'company_id' => $company->id,
        ]);
    }

    public function test_job_has_fillable_attributes()
    {
        $job = new Job();
        $fillable = $job->getFillable();

        $expectedFillable = [
            'title', 'description', 'requirements', 'benefits', 'company_id',
            'type', 'remote_type', 'salary_min', 'salary_max', 'salary_currency',
            'salary_period', 'tags', 'timezone', 'visa_support', 'source',
            'source_url', 'apply_url', 'apply_email', 'featured', 'is_active',
            'expires_at', 'published_at', 'views_count', 'applications_count',
            'location', 'experience_level'
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_job_casts_attributes_correctly()
    {
        $job = Job::factory()->create([
            'visa_support' => '1',
            'featured' => '0',
            'is_active' => '1',
            'tags' => ['PHP', 'Laravel', 'JavaScript'],
            'experience_level' => ['senior', 'lead'],
            'salary_min' => '50000',
            'salary_max' => '80000'
        ]);

        $this->assertIsBool($job->visa_support);
        $this->assertIsBool($job->featured);
        $this->assertIsBool($job->is_active);
        $this->assertIsArray($job->tags);
        $this->assertIsArray($job->experience_level);
        $this->assertIsInt($job->salary_min);
        $this->assertIsInt($job->salary_max);
    }

    public function test_job_belongs_to_company()
    {
        $company = Company::factory()->create();
        $job = Job::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $job->company());
        $this->assertEquals($company->id, $job->company->id);
    }

    public function test_job_has_interactions_relationship()
    {
        $job = Job::factory()->create();
        $interaction = JobUserInteraction::factory()->create(['job_id' => $job->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $job->interactions());
        $this->assertTrue($job->interactions->contains($interaction));
    }

    public function test_job_has_saved_by_users_relationship()
    {
        $job = Job::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $job->savedByUsers());
    }

    public function test_job_has_applied_by_users_relationship()
    {
        $job = Job::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $job->appliedByUsers());
    }

    public function test_is_expired_method()
    {
        $expiredJob = Job::factory()->create([
            'expires_at' => now()->subDay()
        ]);

        $activeJob = Job::factory()->create([
            'expires_at' => now()->addDay()
        ]);

        $noExpiryJob = Job::factory()->create([
            'expires_at' => null
        ]);

        $this->assertTrue($expiredJob->isExpired());
        $this->assertFalse($activeJob->isExpired());
        $this->assertFalse($noExpiryJob->isExpired());
    }

    public function test_is_published_method()
    {
        $publishedJob = Job::factory()->create([
            'published_at' => now()->subDay()
        ]);

        $futureJob = Job::factory()->create([
            'published_at' => now()->addDay()
        ]);

        $unpublishedJob = Job::factory()->create([
            'published_at' => null
        ]);

        $this->assertTrue($publishedJob->isPublished());
        $this->assertFalse($futureJob->isPublished());
        $this->assertFalse($unpublishedJob->isPublished());
    }

    public function test_formatted_salary_attribute_with_both_min_max()
    {
        $job = Job::factory()->create([
            'salary_min' => 50000,
            'salary_max' => 80000,
            'salary_currency' => 'USD',
            'salary_period' => 'yearly'
        ]);

        $this->assertEquals('USD 50000 - 80000/year', $job->formatted_salary);
    }

    public function test_formatted_salary_attribute_with_only_min()
    {
        $job = Job::factory()->create([
            'salary_min' => 50000,
            'salary_max' => null,
            'salary_currency' => 'USD',
            'salary_period' => 'yearly'
        ]);

        $this->assertEquals('USD 50000+/year', $job->formatted_salary);
    }

    public function test_formatted_salary_attribute_with_only_max()
    {
        $job = Job::factory()->create([
            'salary_min' => null,
            'salary_max' => 80000,
            'salary_currency' => 'USD',
            'salary_period' => 'yearly'
        ]);

        $this->assertEquals('USD 80000/year', $job->formatted_salary);
    }

    public function test_formatted_salary_attribute_with_no_salary()
    {
        $job = Job::factory()->create([
            'salary_min' => null,
            'salary_max' => null,
            'salary_currency' => null,
            'salary_period' => null
        ]);

        $this->assertEquals('Salary not specified', $job->formatted_salary);
    }

    public function test_formatted_salary_attribute_with_monthly_period()
    {
        $job = Job::factory()->create([
            'salary_min' => 5000,
            'salary_max' => 8000,
            'salary_currency' => 'USD',
            'salary_period' => 'monthly'
        ]);

        $this->assertEquals('USD 5000 - 8000/month', $job->formatted_salary);
    }

    public function test_formatted_salary_attribute_with_hourly_period()
    {
        $job = Job::factory()->create([
            'salary_min' => 50,
            'salary_max' => 80,
            'salary_currency' => 'USD',
            'salary_period' => 'hourly'
        ]);

        $this->assertEquals('USD 50 - 80/hour', $job->formatted_salary);
    }

    public function test_type_label_attribute()
    {
        $testCases = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship',
            'other' => 'Other'
        ];

        foreach ($testCases as $type => $expectedLabel) {
            $job = Job::factory()->create(['type' => $type]);
            $this->assertEquals($expectedLabel, $job->type_label);
        }
    }

    public function test_remote_type_label_attribute()
    {
        $testCases = [
            'fully-remote' => 'Fully Remote',
            'hybrid' => 'Hybrid',
            'timezone-limited' => 'Timezone Limited',
            'onsite' => 'On-site',
            'other' => 'Other'
        ];

        foreach ($testCases as $remoteType => $expectedLabel) {
            $job = Job::factory()->create(['remote_type' => $remoteType]);
            $this->assertEquals($expectedLabel, $job->remote_type_label);
        }
    }

    public function test_increment_views_method()
    {
        $job = Job::factory()->create(['views_count' => 10]);
        
        $job->incrementViews();
        
        $this->assertEquals(11, $job->fresh()->views_count);
    }

    public function test_increment_applications_method()
    {
        $job = Job::factory()->create(['applications_count' => 5]);
        
        $job->incrementApplications();
        
        $this->assertEquals(6, $job->fresh()->applications_count);
    }

    public function test_scope_active()
    {
        Job::factory()->create(['is_active' => true]);
        Job::factory()->create(['is_active' => false]);

        $activeJobs = Job::active()->get();
        $this->assertCount(1, $activeJobs);
        $this->assertTrue($activeJobs->first()->is_active);
    }

    public function test_scope_published()
    {
        Job::factory()->create(['published_at' => now()->subDay()]);
        Job::factory()->create(['published_at' => now()->addDay()]);
        Job::factory()->create(['published_at' => null]);

        $publishedJobs = Job::published()->get();
        $this->assertCount(1, $publishedJobs);
    }

    public function test_scope_not_expired()
    {
        Job::factory()->create(['expires_at' => now()->subDay()]);
        Job::factory()->create(['expires_at' => now()->addDay()]);
        Job::factory()->create(['expires_at' => null]);

        $notExpiredJobs = Job::notExpired()->get();
        $this->assertCount(2, $notExpiredJobs);
    }

    public function test_scope_featured()
    {
        Job::factory()->create(['featured' => true]);
        Job::factory()->create(['featured' => false]);

        $featuredJobs = Job::featured()->get();
        $this->assertCount(1, $featuredJobs);
        $this->assertTrue($featuredJobs->first()->featured);
    }

    public function test_scope_by_type()
    {
        Job::factory()->create(['type' => 'full-time']);
        Job::factory()->create(['type' => 'part-time']);
        Job::factory()->create(['type' => 'contract']);

        $fullTimeJobs = Job::byType('full-time')->get();
        $this->assertCount(1, $fullTimeJobs);
        $this->assertEquals('full-time', $fullTimeJobs->first()->type);
    }

    public function test_scope_by_remote_type()
    {
        Job::factory()->create(['remote_type' => 'fully-remote']);
        Job::factory()->create(['remote_type' => 'hybrid']);
        Job::factory()->create(['remote_type' => 'onsite']);

        $remoteJobs = Job::byRemoteType('fully-remote')->get();
        $this->assertCount(1, $remoteJobs);
        $this->assertEquals('fully-remote', $remoteJobs->first()->remote_type);
    }

    public function test_scope_by_salary_range()
    {
        Job::factory()->create(['salary_min' => 40000, 'salary_max' => 60000]);
        Job::factory()->create(['salary_min' => 70000, 'salary_max' => 90000]);
        Job::factory()->create(['salary_min' => 30000, 'salary_max' => 50000]);

        $jobsInRange = Job::bySalaryRange(45000, 65000)->get();
        $this->assertCount(2, $jobsInRange);
    }

    public function test_scope_by_salary_range_without_max()
    {
        Job::factory()->create(['salary_min' => 40000, 'salary_max' => 60000]);
        Job::factory()->create(['salary_min' => 70000, 'salary_max' => 90000]);

        $jobsAboveMin = Job::bySalaryRange(50000)->get();
        $this->assertCount(2, $jobsAboveMin);
    }

    public function test_scope_by_tags()
    {
        Job::factory()->create(['tags' => ['PHP', 'Laravel']]);
        Job::factory()->create(['tags' => ['JavaScript', 'React']]);
        Job::factory()->create(['tags' => ['PHP', 'Vue']]);

        $phpJobs = Job::byTags(['PHP'])->get();
        $this->assertCount(2, $phpJobs);

        $laravelJobs = Job::byTags(['Laravel'])->get();
        $this->assertCount(1, $laravelJobs);
    }

    public function test_scope_visa_friendly()
    {
        Job::factory()->create(['visa_support' => true]);
        Job::factory()->create(['visa_support' => false]);

        $visaFriendlyJobs = Job::visaFriendly()->get();
        $this->assertCount(1, $visaFriendlyJobs);
        $this->assertTrue($visaFriendlyJobs->first()->visa_support);
    }

    public function test_scope_recent()
    {
        Job::factory()->create(['created_at' => now()->subDays(3)]);
        Job::factory()->create(['created_at' => now()->subDays(10)]);
        Job::factory()->create(['created_at' => now()->subDays(15)]);

        $recentJobs = Job::recent(7)->get();
        $this->assertCount(1, $recentJobs);

        $recentJobs14Days = Job::recent(14)->get();
        $this->assertCount(2, $recentJobs14Days);
    }
}
