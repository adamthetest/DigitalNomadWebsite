<?php

namespace App\Jobs;

use App\Models\AiContext;
use App\Models\City;
use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiContextData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    /**
     * The context type to process.
     */
    public string $contextType;

    /**
     * The context ID to process.
     */
    public ?int $contextId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $contextType, ?int $contextId = null)
    {
        $this->contextType = $contextType;
        $this->contextId = $contextId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Processing AI context data for type: {$this->contextType}", [
            'context_id' => $this->contextId,
        ]);

        switch ($this->contextType) {
            case 'city':
                $this->processCities();
                break;
            case 'job':
                $this->processJobs();
                break;
            case 'user':
                $this->processUsers();
                break;
            default:
                Log::warning("Unknown context type: {$this->contextType}");
        }
    }

    /**
     * Process cities for AI context data.
     */
    private function processCities(): void
    {
        $query = City::where('is_active', true);

        if ($this->contextId) {
            $query->where('id', $this->contextId);
        }

        $cities = $query->get();

        foreach ($cities as $city) {
            $this->createCityAiContext($city);
        }

        Log::info("Processed {$cities->count()} cities for AI context");
    }

    /**
     * Process jobs for AI context data.
     */
    private function processJobs(): void
    {
        $query = Job::where('is_active', true);

        if ($this->contextId) {
            $query->where('id', $this->contextId);
        }

        $jobs = $query->get();

        foreach ($jobs as $job) {
            $this->createJobAiContext($job);
        }

        Log::info("Processed {$jobs->count()} jobs for AI context");
    }

    /**
     * Process users for AI context data.
     */
    private function processUsers(): void
    {
        $query = User::query();

        if ($this->contextId) {
            $query->where('id', $this->contextId);
        }

        $users = $query->get();

        foreach ($users as $user) {
            $this->createUserAiContext($user);
        }

        Log::info("Processed {$users->count()} users for AI context");
    }

    /**
     * Create AI context for a city.
     */
    private function createCityAiContext(City $city): void
    {
        $contextData = [
            'name' => $city->name,
            'country' => $city->country->name ?? null,
            'population' => $city->population,
            'climate' => $city->climate,
            'cost_of_living_index' => $city->cost_of_living_index,
            'internet_speed_mbps' => $city->internet_speed_mbps,
            'safety_score' => $city->safety_score,
            'cost_accommodation_monthly' => $city->cost_accommodation_monthly,
            'cost_food_monthly' => $city->cost_food_monthly,
            'cost_transport_monthly' => $city->cost_transport_monthly,
            'cost_coworking_monthly' => $city->cost_coworking_monthly,
            'visa_options' => $city->visa_options,
            'visa_duration_days' => $city->visa_duration_days,
            'coworking_spaces_count' => $city->coworking_spaces_count,
            'english_widely_spoken' => $city->english_widely_spoken,
            'female_safe' => $city->female_safe,
            'lgbtq_friendly' => $city->lgbtq_friendly,
            'description' => $city->description,
            'overview' => $city->overview,
            'highlights' => $city->highlights,
        ];

        $aiContext = AiContext::updateOrCreate(
            [
                'context_type' => 'city',
                'context_id' => $city->id,
                'context_model' => City::class,
            ],
            [
                'context_data' => $contextData,
                'ai_model_version' => '1.0.0',
                'last_ai_update' => now(),
            ]
        );

        // Generate AI summary and tags (placeholder for now)
        $aiContext->updateAiData([
            'summary' => [
                'text' => "{$city->name} is a digital nomad destination with a cost of living index of {$city->cost_of_living_index}. ".
                         "It offers internet speeds of {$city->internet_speed_mbps} Mbps and has a safety score of {$city->safety_score}/10.",
                'highlights' => $city->highlights ?? [],
            ],
            'tags' => $this->generateCityTags($city),
            'insights' => $this->generateCityInsights($city),
        ], '1.0.0');
    }

    /**
     * Create AI context for a job.
     */
    private function createJobAiContext(Job $job): void
    {
        $contextData = [
            'title' => $job->title,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'benefits' => $job->benefits,
            'company_name' => $job->company->name ?? null,
            'type' => $job->type,
            'remote_type' => $job->remote_type,
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'salary_currency' => $job->salary_currency,
            'tags' => $job->tags,
            'experience_level' => $job->experience_level,
            'visa_support' => $job->visa_support,
            'timezone' => $job->timezone,
            'location' => $job->location,
        ];

        $aiContext = AiContext::updateOrCreate(
            [
                'context_type' => 'job',
                'context_id' => $job->id,
                'context_model' => Job::class,
            ],
            [
                'context_data' => $contextData,
                'ai_model_version' => '1.0.0',
                'last_ai_update' => now(),
            ]
        );

        // Generate AI summary and tags (placeholder for now)
        $aiContext->updateAiData([
            'summary' => [
                'text' => "{$job->title} at {$job->company->name} - {$job->type} {$job->remote_type} position. ".
                         "Salary range: {$job->formatted_salary}",
                'key_points' => array_filter([
                    $job->visa_support ? 'Visa support available' : null,
                    $job->remote_type === 'fully-remote' ? 'Fully remote' : null,
                    $job->tags ? 'Skills: '.implode(', ', array_slice($job->tags, 0, 5)) : null,
                ]),
            ],
            'tags' => $this->generateJobTags($job),
            'insights' => $this->generateJobInsights($job),
        ], '1.0.0');
    }

    /**
     * Create AI context for a user.
     */
    private function createUserAiContext(User $user): void
    {
        $contextData = [
            'name' => $user->name,
            'bio' => $user->bio,
            'tagline' => $user->tagline,
            'job_title' => $user->job_title,
            'company' => $user->company,
            'skills' => $user->skills,
            'technical_skills' => $user->technical_skills,
            'soft_skills' => $user->soft_skills,
            'experience_years' => $user->experience_years,
            'work_type' => $user->work_type,
            'location_current' => $user->location_current,
            'location_next' => $user->location_next,
            'preferred_climates' => $user->preferred_climates,
            'preferred_activities' => $user->preferred_activities,
            'budget_monthly_min' => $user->budget_monthly_min,
            'budget_monthly_max' => $user->budget_monthly_max,
            'visa_flexible' => $user->visa_flexible,
            'lifestyle_tags' => $user->lifestyle_tags,
        ];

        $aiContext = AiContext::updateOrCreate(
            [
                'context_type' => 'user',
                'context_id' => $user->id,
                'context_model' => User::class,
            ],
            [
                'context_data' => $contextData,
                'ai_model_version' => '1.0.0',
                'last_ai_update' => now(),
            ]
        );

        // Generate AI summary and tags (placeholder for now)
        $aiContext->updateAiData([
            'summary' => [
                'text' => "{$user->name} is a {$user->job_title} with {$user->experience_years} years of experience. ".
                         "Currently in {$user->location_current} and looking for {$user->work_type} opportunities.",
                'profile_highlights' => array_filter([
                    $user->technical_skills ? 'Technical skills: '.implode(', ', array_slice($user->technical_skills, 0, 3)) : null,
                    $user->preferred_climates ? 'Prefers: '.implode(', ', $user->preferred_climates) : null,
                    $user->budget_monthly_max ? 'Budget: up to $'.number_format($user->budget_monthly_max) : null,
                ]),
            ],
            'tags' => $this->generateUserTags($user),
            'insights' => $this->generateUserInsights($user),
        ], '1.0.0');
    }

    /**
     * Generate tags for a city.
     */
    private function generateCityTags(City $city): array
    {
        $tags = [];

        if ($city->cost_of_living_index < 50) {
            $tags[] = 'budget-friendly';
        } elseif ($city->cost_of_living_index > 80) {
            $tags[] = 'expensive';
        }

        if ($city->internet_speed_mbps > 50) {
            $tags[] = 'fast-internet';
        }

        if ($city->safety_score > 7) {
            $tags[] = 'safe';
        }

        if ($city->english_widely_spoken) {
            $tags[] = 'english-friendly';
        }

        if ($city->female_safe) {
            $tags[] = 'female-friendly';
        }

        if ($city->lgbtq_friendly) {
            $tags[] = 'lgbtq-friendly';
        }

        if ($city->coworking_spaces_count > 5) {
            $tags[] = 'coworking-hub';
        }

        return array_unique($tags);
    }

    /**
     * Generate insights for a city.
     */
    private function generateCityInsights(City $city): array
    {
        $insights = [];

        if ($city->cost_of_living_index) {
            $insights['cost_category'] = $city->cost_of_living_index < 50 ? 'budget' :
                                       ($city->cost_of_living_index < 80 ? 'moderate' : 'expensive');
        }

        if ($city->internet_speed_mbps) {
            $insights['internet_category'] = $city->internet_speed_mbps > 50 ? 'excellent' :
                                            ($city->internet_speed_mbps > 25 ? 'good' : 'basic');
        }

        if ($city->safety_score) {
            $insights['safety_category'] = $city->safety_score > 8 ? 'very-safe' :
                                         ($city->safety_score > 6 ? 'safe' : 'moderate');
        }

        return $insights;
    }

    /**
     * Generate tags for a job.
     */
    private function generateJobTags(Job $job): array
    {
        $tags = [];

        if ($job->visa_support) {
            $tags[] = 'visa-support';
        }

        if ($job->remote_type === 'fully-remote') {
            $tags[] = 'fully-remote';
        }

        if ($job->salary_max && $job->salary_max > 100000) {
            $tags[] = 'high-salary';
        }

        if ($job->tags) {
            $tags = array_merge($tags, array_slice($job->tags, 0, 5));
        }

        return array_unique($tags);
    }

    /**
     * Generate insights for a job.
     */
    private function generateJobInsights(Job $job): array
    {
        $insights = [];

        if ($job->salary_max) {
            $insights['salary_category'] = $job->salary_max > 150000 ? 'high' :
                                          ($job->salary_max > 80000 ? 'mid' : 'entry');
        }

        $insights['remote_flexibility'] = $job->remote_type;
        $insights['visa_friendly'] = $job->visa_support;

        return $insights;
    }

    /**
     * Generate tags for a user.
     */
    private function generateUserTags(User $user): array
    {
        $tags = [];

        if ($user->technical_skills) {
            $tags = array_merge($tags, array_slice($user->technical_skills, 0, 5));
        }

        if ($user->experience_years) {
            if ($user->experience_years < 2) {
                $tags[] = 'entry-level';
            } elseif ($user->experience_years < 5) {
                $tags[] = 'mid-level';
            } else {
                $tags[] = 'senior-level';
            }
        }

        if ($user->work_type) {
            $tags[] = $user->work_type;
        }

        if ($user->visa_flexible) {
            $tags[] = 'visa-flexible';
        }

        return array_unique($tags);
    }

    /**
     * Generate insights for a user.
     */
    private function generateUserInsights(User $user): array
    {
        $insights = [];

        if ($user->experience_years) {
            $insights['experience_level'] = $user->experience_years < 2 ? 'entry' :
                                          ($user->experience_years < 5 ? 'mid' : 'senior');
        }

        if ($user->budget_monthly_max) {
            $insights['budget_category'] = $user->budget_monthly_max < 2000 ? 'budget' :
                                          ($user->budget_monthly_max < 5000 ? 'moderate' : 'premium');
        }

        $insights['work_preference'] = $user->work_type;
        $insights['visa_flexibility'] = $user->visa_flexible;

        return $insights;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAiContextData job failed', [
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
