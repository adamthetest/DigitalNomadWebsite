<?php

namespace App\Services;

use App\Models\AiGeneratedContent;
use App\Models\City;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AI Content Generation Service
 *
 * Handles automated generation of blog posts, newsletters, and summaries
 * using AI to create SEO-optimized content for the digital nomad community.
 */
class AiContentGenerationService
{
    protected OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Generate a blog post about top digital nomad cities.
     */
    public function generateTopCitiesBlogPost(?int $year = null): ?AiGeneratedContent
    {
        $year = $year ?? date('Y');
        $title = "Top 10 Digital Nomad Cities in {$year}";

        // Get top cities data
        $topCities = City::where('is_active', true)
            ->orderBy('cost_of_living_index', 'asc')
            ->orderBy('internet_speed_mbps', 'desc')
            ->orderBy('safety_score', 'desc')
            ->limit(10)
            ->get();

        if ($topCities->isEmpty()) {
            Log::warning('No cities found for top cities blog post');

            return null;
        }

        $prompt = $this->buildTopCitiesPrompt($topCities, $year);
        $content = $this->openAiService->generateContent($prompt, [
            'max_tokens' => 3000,
            'temperature' => 0.7,
        ]);

        if (! $content) {
            Log::warning('OpenAI unavailable, using fallback content for top cities blog post');
            $content = $this->generateFallbackTopCitiesContent($topCities, $year);
        }

        return $this->createContent([
            'content_type' => 'blog_post',
            'title' => $title,
            'content' => $content,
            'metadata' => [
                'generation_type' => 'top_cities',
                'year' => $year,
                'cities_count' => $topCities->count(),
                'cities_data' => $topCities->map(fn ($city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'country' => $city->country->name ?? 'Unknown',
                    'cost_index' => $city->cost_of_living_index,
                    'internet_speed' => $city->internet_speed_mbps,
                    'safety_score' => $city->safety_score,
                ])->toArray(),
            ],
            'seo_data' => [
                'meta_description' => "Discover the top 10 digital nomad cities in {$year} based on cost of living, internet speed, safety, and nomad-friendly amenities.",
                'keywords' => ['digital nomad cities', 'remote work destinations', 'nomad-friendly cities', 'best cities for digital nomads', $year],
            ],
            'tags' => ['digital nomads', 'remote work', 'travel', 'cities', 'cost of living'],
            'categories' => ['destinations', 'guides'],
        ]);
    }

    /**
     * Generate a blog post about trending nomad destinations.
     */
    public function generateTrendingDestinationsPost(): ?AiGeneratedContent
    {
        $title = 'Where Digital Nomads Are Moving This Month';

        // Get recent job data and user activity
        $recentJobs = Job::where('is_active', true)
            ->where('created_at', '>=', now()->subMonth())
            ->with('company')
            ->get();

        // Get trending cities based on job locations (simplified approach)
        $trendingCities = City::where('is_active', true)
            ->where('is_featured', true)
            ->limit(5)
            ->get();

        $prompt = $this->buildTrendingDestinationsPrompt($trendingCities, $recentJobs);
        $content = $this->openAiService->generateContent($prompt, [
            'max_tokens' => 2500,
            'temperature' => 0.8,
        ]);

        if (! $content) {
            Log::warning('OpenAI unavailable, using fallback content for trending destinations post');
            $content = $this->generateFallbackTrendingContent($trendingCities, $recentJobs);
        }

        return $this->createContent([
            'content_type' => 'blog_post',
            'title' => $title,
            'content' => $content,
            'metadata' => [
                'generation_type' => 'trending_destinations',
                'jobs_count' => $recentJobs->count(),
                'trending_cities' => $trendingCities->map(fn ($city) => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'jobs_count' => $city->jobs_count,
                ])->toArray(),
            ],
            'seo_data' => [
                'meta_description' => 'Discover the trending destinations where digital nomads are moving this month based on job opportunities and community activity.',
                'keywords' => ['trending destinations', 'digital nomad trends', 'remote work locations', 'nomad hotspots'],
            ],
            'tags' => ['trending', 'destinations', 'digital nomads', 'remote work'],
            'categories' => ['trends', 'destinations'],
        ]);
    }

    /**
     * Generate a weekly newsletter.
     */
    public function generateWeeklyNewsletter(): ?AiGeneratedContent
    {
        $title = 'Digital Nomad Weekly - '.now()->format('F j, Y');

        // Get weekly data
        $weeklyStats = $this->getWeeklyStats();
        $topJobs = Job::where('is_active', true)
            ->where('created_at', '>=', now()->subWeek())
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $prompt = $this->buildNewsletterPrompt($weeklyStats, $topJobs);
        $content = $this->openAiService->generateContent($prompt, [
            'max_tokens' => 2000,
            'temperature' => 0.6,
        ]);

        if (! $content) {
            Log::warning('OpenAI unavailable, using fallback content for newsletter');
            $content = $this->generateFallbackNewsletterContent($weeklyStats, $topJobs);
        }

        return $this->createContent([
            'content_type' => 'newsletter',
            'title' => $title,
            'content' => $content,
            'metadata' => [
                'generation_type' => 'weekly_newsletter',
                'week_start' => now()->subWeek()->format('Y-m-d'),
                'week_end' => now()->format('Y-m-d'),
                'stats' => $weeklyStats,
            ],
            'seo_data' => [
                'meta_description' => 'Weekly roundup of digital nomad news, job opportunities, and community highlights.',
                'keywords' => ['digital nomad newsletter', 'remote work news', 'nomad community'],
            ],
            'tags' => ['newsletter', 'weekly', 'digital nomads', 'community'],
            'categories' => ['newsletter'],
        ]);
    }

    /**
     * Generate a community discussion summary.
     */
    public function generateCommunitySummary(array $discussions): ?AiGeneratedContent
    {
        $title = 'Community Discussion Summary - '.now()->format('M j, Y');

        if (empty($discussions)) {
            Log::warning('No discussions provided for community summary');

            return null;
        }

        $prompt = $this->buildCommunitySummaryPrompt($discussions);
        $content = $this->openAiService->generateContent($prompt, [
            'max_tokens' => 1500,
            'temperature' => 0.5,
        ]);

        if (! $content) {
            Log::error('Failed to generate community summary content');

            return null;
        }

        return $this->createContent([
            'content_type' => 'summary',
            'title' => $title,
            'content' => $content,
            'metadata' => [
                'generation_type' => 'community_summary',
                'discussions_count' => count($discussions),
                'discussions' => $discussions,
            ],
            'seo_data' => [
                'meta_description' => 'Summary of key discussions and insights from the digital nomad community this week.',
                'keywords' => ['community summary', 'digital nomad discussions', 'community insights'],
            ],
            'tags' => ['community', 'summary', 'discussions', 'insights'],
            'categories' => ['community'],
        ]);
    }

    /**
     * Generate SEO-optimized content for a specific city.
     */
    public function generateCityGuide(City $city): ?AiGeneratedContent
    {
        $title = "Complete Digital Nomad Guide to {$city->name}";

        $prompt = $this->buildCityGuidePrompt($city);
        $content = $this->openAiService->generateContent($prompt, [
            'max_tokens' => 4000,
            'temperature' => 0.6,
        ]);

        if (! $content) {
            Log::error('Failed to generate city guide content', ['city_id' => $city->id]);

            return null;
        }

        return $this->createContent([
            'content_type' => 'city_guide',
            'title' => $title,
            'content' => $content,
            'metadata' => [
                'generation_type' => 'city_guide',
                'city_id' => $city->id,
                'city_name' => $city->name,
                'country' => $city->country->name ?? 'Unknown',
            ],
            'seo_data' => [
                'meta_description' => "Complete digital nomad guide to {$city->name} including cost of living, internet, safety, and nomad-friendly amenities.",
                'keywords' => [
                    'digital nomad guide',
                    strtolower($city->name),
                    'remote work',
                    'cost of living',
                    'nomad guide',
                ],
            ],
            'tags' => ['city guide', 'digital nomad', 'remote work', strtolower($city->name)],
            'categories' => ['guides', 'destinations'],
        ]);
    }

    /**
     * Create content record.
     */
    private function createContent(array $data): AiGeneratedContent
    {
        return AiGeneratedContent::create(array_merge($data, [
            'status' => 'draft',
            'is_active' => true,
        ]));
    }

    /**
     * Build prompt for top cities blog post.
     */
    private function buildTopCitiesPrompt($cities, int $year): string
    {
        $citiesData = $cities->map(function ($city) {
            $countryName = $city->country?->name ?? 'Unknown';

            return "• {$city->name}, {$countryName}: ".
                   "Cost Index {$city->cost_of_living_index}, ".
                   "Internet {$city->internet_speed_mbps} Mbps, ".
                   "Safety {$city->safety_score}/10";
        })->join("\n");

        return "Write a comprehensive blog post titled 'Top 10 Digital Nomad Cities in {$year}' for a digital nomad community website.\n\n".
               "Use this data about the cities:\n{$citiesData}\n\n".
               "Requirements:\n".
               "1. Write in an engaging, informative tone\n".
               "2. Include specific details about each city\n".
               "3. Mention cost of living, internet quality, safety, and nomad-friendly amenities\n".
               "4. Include practical tips for nomads considering each destination\n".
               "5. Use SEO-friendly headings and structure\n".
               "6. Include a conclusion with recommendations\n".
               "7. Make it approximately 2000-2500 words\n".
               '8. Use markdown formatting for headings, lists, and emphasis';
    }

    /**
     * Build prompt for trending destinations post.
     */
    private function buildTrendingDestinationsPrompt($cities, $jobs): string
    {
        $citiesData = $cities->map(function ($city) {
            $countryName = $city->country?->name ?? 'Unknown';

            return "• {$city->name}, {$countryName}: Cost Index {$city->cost_of_living_index}, Internet {$city->internet_speed_mbps} Mbps";
        })->join("\n");

        $jobsData = $jobs->take(3)->map(function ($job) {
            return "• {$job->title} at {$job->company->name} ({$job->location})";
        })->join("\n");

        return "Write a blog post titled 'Where Digital Nomads Are Moving This Month' for a digital nomad community website.\n\n".
               "Featured destinations:\n{$citiesData}\n\n".
               "Recent job highlights:\n{$jobsData}\n\n".
               "Requirements:\n".
               "1. Analyze why these destinations are popular with nomads\n".
               "2. Include insights about remote work opportunities\n".
               "3. Mention practical considerations for nomads\n".
               "4. Use an engaging, data-driven tone\n".
               "5. Include actionable advice for nomads\n".
               "6. Make it approximately 1500-2000 words\n".
               '7. Use markdown formatting';
    }

    /**
     * Build prompt for newsletter.
     */
    private function buildNewsletterPrompt(array $stats, $jobs): string
    {
        $jobsData = $jobs->map(function ($job) {
            return "• {$job->title} at {$job->company->name} - {$job->location}";
        })->join("\n");

        return "Write a weekly newsletter for digital nomads titled 'Digital Nomad Weekly'.\n\n".
               "Weekly statistics:\n".
               "• New jobs posted: {$stats['new_jobs']}\n".
               "• New cities added: {$stats['new_cities']}\n".
               "• Community members: {$stats['new_members']}\n\n".
               "Featured job opportunities:\n{$jobsData}\n\n".
               "Requirements:\n".
               "1. Write in a friendly, community-focused tone\n".
               "2. Include a brief intro about the week's highlights\n".
               "3. Feature the job opportunities with brief descriptions\n".
               "4. Include community tips or insights\n".
               "5. End with an encouraging call-to-action\n".
               "6. Keep it concise but informative (800-1200 words)\n".
               '7. Use markdown formatting';
    }

    /**
     * Build prompt for community summary.
     */
    private function buildCommunitySummaryPrompt(array $discussions): string
    {
        $discussionsText = collect($discussions)->map(function ($discussion, $index) {
            return ($index + 1).". Topic: {$discussion['topic']}\n".
                   "   Participants: {$discussion['participants']}\n".
                   "   Key points: {$discussion['key_points']}";
        })->join("\n\n");

        return "Create a summary of community discussions for digital nomads.\n\n".
               "Discussion topics:\n{$discussionsText}\n\n".
               "Requirements:\n".
               "1. Summarize the key insights from each discussion\n".
               "2. Highlight actionable advice for nomads\n".
               "3. Identify common themes and trends\n".
               "4. Write in a clear, informative tone\n".
               "5. Keep it concise but comprehensive (600-1000 words)\n".
               '6. Use markdown formatting';
    }

    /**
     * Build prompt for city guide.
     */
    private function buildCityGuidePrompt(City $city): string
    {
        $countryName = $city->country?->name ?? 'Unknown'; // @phpstan-ignore-line

        return "Write a comprehensive digital nomad guide for {$city->name}, {$countryName}.\n\n".
               "City information:\n".
               "• Population: {$city->population}\n".
               "• Cost of Living Index: {$city->cost_of_living_index}\n".
               "• Internet Speed: {$city->internet_speed_mbps} Mbps\n".
               "• Safety Score: {$city->safety_score}/10\n".
               "• Climate: {$city->climate}\n".
               "• Description: {$city->description}\n\n".
               "Requirements:\n".
               "1. Create a detailed guide covering all aspects of living as a nomad\n".
               "2. Include sections on accommodation, coworking spaces, internet, safety, cost of living\n".
               "3. Mention visa requirements and practical tips\n".
               "4. Include recommendations for neighborhoods and areas\n".
               "5. Write in an informative, helpful tone\n".
               "6. Make it comprehensive (3000-4000 words)\n".
               "7. Use markdown formatting with clear headings\n".
               '8. Include practical tips and insider knowledge';
    }

    /**
     * Get weekly statistics.
     */
    private function getWeeklyStats(): array
    {
        return Cache::remember('weekly_stats_'.now()->format('Y-W'), 3600, function () {
            return [
                'new_jobs' => Job::where('created_at', '>=', now()->subWeek())->count(),
                'new_cities' => City::where('created_at', '>=', now()->subWeek())->count(),
                'new_members' => User::where('created_at', '>=', now()->subWeek())->count(),
                'total_jobs' => Job::where('is_active', true)->count(),
                'total_cities' => City::where('is_active', true)->count(),
                'total_members' => User::count(),
            ];
        });
    }

    /**
     * Generate fallback content when OpenAI is unavailable.
     */
    private function generateFallbackTopCitiesContent($cities, int $year): string
    {
        $content = "# Top 10 Digital Nomad Cities in {$year}\n\n";
        $content .= "Based on our analysis of cost of living, internet speed, safety, and nomad-friendly amenities, here are the top digital nomad destinations for {$year}:\n\n";

        foreach ($cities->take(10) as $index => $city) {
            $countryName = $city->country?->name ?? 'Unknown';
            $content .= '## '.($index + 1).". {$city->name}, {$countryName}\n\n";
            $content .= "**Cost of Living Index:** {$city->cost_of_living_index}\n";
            $content .= "**Internet Speed:** {$city->internet_speed_mbps} Mbps\n";
            $content .= "**Safety Score:** {$city->safety_score}/10\n\n";

            if ($city->description) {
                $content .= $city->description."\n\n";
            }

            $content .= "**Why it's great for nomads:** ";
            if ($city->cost_of_living_index < 50) {
                $content .= 'Budget-friendly with ';
            } elseif ($city->cost_of_living_index < 80) {
                $content .= 'Moderate cost of living with ';
            } else {
                $content .= 'Higher cost but ';
            }

            if ($city->internet_speed_mbps > 50) {
                $content .= 'excellent internet speeds';
            } else {
                $content .= 'decent internet connectivity';
            }

            if ($city->safety_score > 7) {
                $content .= ' and high safety ratings';
            }

            $content .= ".\n\n";
        }

        $content .= "## Conclusion\n\n";
        $content .= 'These cities offer the perfect combination of affordability, connectivity, and safety for digital nomads. '.
                   "Whether you're looking for budget-friendly destinations or premium locations with top-tier amenities, ".
                   "there's something for every type of nomad in {$year}.\n\n";
        $content .= 'Remember to consider your personal preferences, visa requirements, and work needs when choosing your next destination. '.
                   'Happy nomading! 🌍✈️';

        return $content;
    }

    private function generateFallbackNewsletterContent(array $stats, $jobs): string
    {
        $content = '# Digital Nomad Weekly - '.now()->format('F j, Y')."\n\n";
        $content .= "Welcome to this week's roundup of digital nomad news and opportunities!\n\n";

        $content .= "## This Week's Highlights\n\n";
        $content .= "• **New Jobs Posted:** {$stats['new_jobs']}\n";
        $content .= "• **New Cities Added:** {$stats['new_cities']}\n";
        $content .= "• **New Community Members:** {$stats['new_members']}\n\n";

        if ($jobs->count() > 0) {
            $content .= "## Featured Job Opportunities\n\n";
            foreach ($jobs->take(5) as $job) {
                $content .= "### {$job->title} at {$job->company->name}\n";
                $content .= "**Location:** {$job->location}\n";
                $content .= "**Type:** {$job->type} | **Remote:** {$job->remote_type}\n";
                if ($job->salary_min || $job->salary_max) {
                    $content .= '**Salary:** '.($job->formatted_salary ?? 'Competitive')."\n";
                }
                $content .= "\n";
            }
        }

        $content .= "## Community Tips\n\n";
        $content .= "This week's tip: Always research visa requirements and internet reliability before booking long-term stays. ".
                   "Connect with local nomad communities for insider tips and recommendations.\n\n";

        $content .= "## What's Next?\n\n";
        $content .= 'Stay tuned for more job opportunities, city guides, and community insights. '.
                   "Don't forget to update your profile and preferences to get personalized recommendations!\n\n";

        $content .= 'Happy nomading! 🌍✈️';

        return $content;
    }

    private function generateFallbackTrendingContent($cities, $jobs): string
    {
        $content = "# Where Digital Nomads Are Moving This Month\n\n";
        $content .= "Based on our analysis of job opportunities, community activity, and destination popularity, here are the trending destinations for digital nomads this month:\n\n";

        foreach ($cities->take(5) as $index => $city) {
            $countryName = $city->country?->name ?? 'Unknown';
            $content .= '## '.($index + 1).". {$city->name}, {$countryName}\n\n";
            $content .= "**Cost of Living Index:** {$city->cost_of_living_index}\n";
            $content .= "**Internet Speed:** {$city->internet_speed_mbps} Mbps\n";
            $content .= "**Safety Score:** {$city->safety_score}/10\n\n";

            if ($city->description) {
                $content .= $city->description."\n\n";
            }

            $content .= '**Why nomads are flocking here:** ';
            if ($city->cost_of_living_index < 50) {
                $content .= 'Affordable living costs make it perfect for budget-conscious nomads';
            } elseif ($city->cost_of_living_index < 80) {
                $content .= 'Great balance of affordability and modern amenities';
            } else {
                $content .= 'Premium destination with excellent infrastructure';
            }

            if ($city->internet_speed_mbps > 50) {
                $content .= ' with lightning-fast internet';
            }

            if ($city->safety_score > 7) {
                $content .= ' and high safety ratings';
            }

            $content .= ".\n\n";
        }

        if ($jobs->count() > 0) {
            $content .= "## Recent Job Opportunities\n\n";
            foreach ($jobs->take(3) as $job) {
                $content .= "### {$job->title} at {$job->company->name}\n";
                $content .= "**Location:** {$job->location}\n";
                $content .= "**Type:** {$job->type} | **Remote:** {$job->remote_type}\n\n";
            }
        }

        $content .= "## What's Driving the Trend?\n\n";
        $content .= "Several factors are contributing to the popularity of these destinations:\n\n";
        $content .= "• **Cost-effectiveness**: Budget-friendly options for long-term stays\n";
        $content .= "• **Digital infrastructure**: Reliable internet and coworking spaces\n";
        $content .= "• **Nomad-friendly communities**: Established expat and nomad networks\n";
        $content .= "• **Visa flexibility**: Easy entry and extended stay options\n\n";

        $content .= "## Tips for Nomads\n\n";
        $content .= "Before making your move, consider:\n\n";
        $content .= "• Research visa requirements and duration limits\n";
        $content .= "• Check internet reliability in your preferred neighborhoods\n";
        $content .= "• Connect with local nomad communities for insider tips\n";
        $content .= "• Plan your budget based on local cost of living\n\n";

        $content .= 'Happy nomading! 🌍✈️';

        return $content;
    }
}
