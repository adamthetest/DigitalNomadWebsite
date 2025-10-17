<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Job Scraping Service
 *
 * Handles automated job scraping from various sources like RemoteOK, We Work Remotely, etc.
 * Reduces manual job posting work by 60-70%.
 */
class JobScrapingService
{
    protected array $sources = [
        'remoteok' => [
            'url' => 'https://remoteok.io/api',
            'enabled' => true,
        ],
        'weworkremotely' => [
            'url' => 'https://weworkremotely.com/categories/remote-programming-jobs.rss',
            'enabled' => true,
        ],
    ];

    protected int $maxJobsPerSource = 50;

    protected int $timeout = 30;

    /**
     * Scrape jobs from all enabled sources.
     */
    public function scrapeAllSources(): array
    {
        $results = [];

        foreach ($this->sources as $sourceName => $config) {
            if (! $config['enabled']) {
                continue;
            }

            try {
                $jobs = $this->scrapeSource($sourceName);
                $results[$sourceName] = $jobs;

                Log::info("Scraped {$jobs['count']} jobs from {$sourceName}", [
                    'source' => $sourceName,
                    'new_jobs' => $jobs['new'],
                    'updated_jobs' => $jobs['updated'],
                    'skipped_jobs' => $jobs['skipped'],
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to scrape jobs from {$sourceName}", [
                    'source' => $sourceName,
                    'error' => $e->getMessage(),
                ]);

                $results[$sourceName] = [
                    'count' => 0,
                    'new' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Scrape jobs from a specific source.
     */
    public function scrapeSource(string $sourceName): array
    {
        if (! isset($this->sources[$sourceName])) {
            throw new \InvalidArgumentException("Unknown source: {$sourceName}");
        }

        $config = $this->sources[$sourceName];

        return match ($sourceName) {
            'remoteok' => $this->scrapeRemoteOK($config['url']),
            'weworkremotely' => $this->scrapeWeWorkRemotely($config['url']),
            default => throw new \InvalidArgumentException("Unsupported source: {$sourceName}"),
        };
    }

    /**
     * Scrape jobs from RemoteOK API.
     */
    private function scrapeRemoteOK(string $url): array
    {
        $response = Http::timeout($this->timeout)->get($url);

        if (! $response->successful()) {
            throw new \Exception("Failed to fetch RemoteOK data: HTTP {$response->status()}");
        }

        $data = $response->json();

        if (! is_array($data) || empty($data)) {
            return ['count' => 0, 'new' => 0, 'updated' => 0, 'skipped' => 0];
        }

        // Remove the first element which is usually metadata
        $jobs = array_slice($data, 1, $this->maxJobsPerSource);

        $results = ['count' => 0, 'new' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($jobs as $jobData) {
            if (! $this->isValidRemoteOKJob($jobData)) {
                $results['skipped']++;

                continue;
            }

            $job = $this->processRemoteOKJob($jobData);
            $results['count']++;
            if ($job->wasRecentlyCreated) {
                $results['new']++;
            } else {
                $results['updated']++;
            }
        }

        return $results;
    }

    /**
     * Scrape jobs from We Work Remotely RSS feed.
     */
    private function scrapeWeWorkRemotely(string $url): array
    {
        $response = Http::timeout($this->timeout)->get($url);

        if (! $response->successful()) {
            throw new \Exception("Failed to fetch We Work Remotely data: HTTP {$response->status()}");
        }

        $xml = simplexml_load_string($response->body());

        if (! $xml || ! isset($xml->channel->item)) {
            return ['count' => 0, 'new' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $items = array_slice(iterator_to_array($xml->channel->item), 0, $this->maxJobsPerSource);

        $results = ['count' => 0, 'new' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($items as $item) {
            $jobData = $this->parseWWRItem($item);

            if (! $this->isValidWWRJob($jobData)) {
                $results['skipped']++;

                continue;
            }

            $job = $this->processWWRJob($jobData);
            $results['count']++;
            if ($job->wasRecentlyCreated) {
                $results['new']++;
            } else {
                $results['updated']++;
            }
        }

        return $results;
    }

    /**
     * Process a RemoteOK job and create/update database record.
     */
    private function processRemoteOKJob(array $jobData): Job
    {
        $company = $this->findOrCreateCompany($jobData['company'], $jobData['company_logo'] ?? null);

        $jobAttributes = [
            'title' => $jobData['position'],
            'description' => $this->cleanDescription($jobData['description'] ?? ''),
            'company_id' => $company->id,
            'location' => $jobData['location'] ?? 'Remote',
            'type' => $this->mapJobType($jobData['tags'] ?? []),
            'remote_type' => 'fully_remote',
            'salary_min' => $this->extractSalaryMin($jobData),
            'salary_max' => $this->extractSalaryMax($jobData),
            'salary_currency' => 'USD',
            'salary_period' => 'yearly',
            'tags' => $this->processTags($jobData['tags'] ?? []),
            'external_url' => $jobData['url'] ?? null,
            'external_id' => 'remoteok_'.($jobData['id'] ?? Str::random(10)),
            'source' => 'remoteok',
            'is_active' => true,
            'published' => true,
            'featured' => false,
            'visa_support' => $this->hasVisaSupport($jobData),
        ];

        return Job::updateOrCreate(
            ['external_id' => $jobAttributes['external_id']],
            $jobAttributes
        );
    }

    /**
     * Process a We Work Remotely job and create/update database record.
     */
    private function processWWRJob(array $jobData): Job
    {
        $company = $this->findOrCreateCompany($jobData['company'], null);

        $jobAttributes = [
            'title' => $jobData['title'],
            'description' => $this->cleanDescription($jobData['description']),
            'company_id' => $company->id,
            'location' => $jobData['location'] ?? 'Remote',
            'type' => $this->mapJobType($jobData['tags'] ?? []),
            'remote_type' => 'fully_remote',
            'salary_min' => $this->extractSalaryMin($jobData),
            'salary_max' => $this->extractSalaryMax($jobData),
            'salary_currency' => 'USD',
            'salary_period' => 'yearly',
            'tags' => $this->processTags($jobData['tags'] ?? []),
            'external_url' => $jobData['url'],
            'external_id' => 'wwr_'.Str::slug($jobData['title'].'_'.$jobData['company']),
            'source' => 'weworkremotely',
            'is_active' => true,
            'published' => true,
            'featured' => false,
            'visa_support' => $this->hasVisaSupport($jobData),
        ];

        return Job::updateOrCreate(
            ['external_id' => $jobAttributes['external_id']],
            $jobAttributes
        );
    }

    /**
     * Find or create a company.
     */
    private function findOrCreateCompany(string $name, ?string $logoUrl = null): Company
    {
        $company = Company::where('name', $name)->first();

        if (! $company) {
            $company = Company::create([
                'name' => $name,
                'logo_url' => $logoUrl,
                'website' => null,
                'description' => null,
                'size' => 'unknown',
                'industry' => 'technology',
                'is_active' => true,
            ]);
        } elseif ($logoUrl && ! $company->logo_url) {
            $company->update(['logo_url' => $logoUrl]);
        }

        return $company;
    }

    /**
     * Parse We Work Remotely RSS item.
     */
    private function parseWWRItem(\SimpleXMLElement $item): array
    {
        $description = strip_tags((string) $item->description);
        $title = (string) $item->title;

        // Extract company name from title (usually in format "Job Title at Company")
        $company = 'Unknown Company';
        if (preg_match('/at\s+(.+)$/', $title, $matches)) {
            $company = trim($matches[1]);
            $title = trim(str_replace(" at {$company}", '', $title));
        }

        return [
            'title' => $title,
            'description' => $description,
            'company' => $company,
            'url' => (string) $item->link,
            'location' => 'Remote',
            'tags' => [],
        ];
    }

    /**
     * Validate RemoteOK job data.
     */
    private function isValidRemoteOKJob(array $jobData): bool
    {
        return ! empty($jobData['position']) &&
               ! empty($jobData['company']) &&
               ! empty($jobData['url']);
    }

    /**
     * Validate We Work Remotely job data.
     */
    private function isValidWWRJob(array $jobData): bool
    {
        return ! empty($jobData['title']) &&
               ! empty($jobData['company']) &&
               ! empty($jobData['url']);
    }

    /**
     * Clean job description.
     */
    private function cleanDescription(string $description): string
    {
        // Remove HTML tags and clean up whitespace
        $description = strip_tags($description);
        $description = preg_replace('/\s+/', ' ', $description);

        return trim($description);
    }

    /**
     * Map job tags to job type.
     */
    private function mapJobType(array $tags): string
    {
        $tagString = strtolower(implode(' ', $tags));

        if (str_contains($tagString, 'full-time') || str_contains($tagString, 'fulltime')) {
            return 'full_time';
        }

        if (str_contains($tagString, 'part-time') || str_contains($tagString, 'parttime')) {
            return 'part_time';
        }

        if (str_contains($tagString, 'contract') || str_contains($tagString, 'freelance')) {
            return 'contract';
        }

        return 'full_time'; // Default
    }

    /**
     * Process and clean tags.
     */
    private function processTags(array $tags): array
    {
        $processedTags = [];

        foreach ($tags as $tag) {
            $cleanTag = strtolower(trim($tag));
            if (! empty($cleanTag) && ! in_array($cleanTag, ['full-time', 'part-time', 'contract'])) {
                $processedTags[] = $cleanTag;
            }
        }

        return array_unique(array_slice($processedTags, 0, 10)); // Max 10 tags
    }

    /**
     * Extract minimum salary from job data.
     */
    private function extractSalaryMin(array $jobData): ?int
    {
        $description = strtolower($jobData['description'] ?? '');

        // Look for salary ranges like "$50k-80k", "$50,000-80,000"
        if (preg_match('/\$(\d+(?:,\d{3})*(?:k)?)\s*[-–]\s*\$(\d+(?:,\d{3})*(?:k)?)/', $description, $matches)) {
            return $this->parseSalary($matches[1]);
        }

        // Look for minimum salary indicators
        if (preg_match('/starting at \$(\d+(?:,\d{3})*(?:k)?)/', $description, $matches)) {
            return $this->parseSalary($matches[1]);
        }

        return null;
    }

    /**
     * Extract maximum salary from job data.
     */
    private function extractSalaryMax(array $jobData): ?int
    {
        $description = strtolower($jobData['description'] ?? '');

        // Look for salary ranges like "$50k-80k", "$50,000-80,000"
        if (preg_match('/\$(\d+(?:,\d{3})*(?:k)?)\s*[-–]\s*\$(\d+(?:,\d{3})*(?:k)?)/', $description, $matches)) {
            return $this->parseSalary($matches[2]);
        }

        return null;
    }

    /**
     * Parse salary string to integer.
     */
    private function parseSalary(string $salary): int
    {
        $salary = str_replace(',', '', $salary);

        if (str_ends_with($salary, 'k')) {
            return (int) str_replace('k', '', $salary) * 1000;
        }

        return (int) $salary;
    }

    /**
     * Check if job offers visa support.
     */
    private function hasVisaSupport(array $jobData): bool
    {
        $description = strtolower($jobData['description'] ?? '');
        $tags = array_map('strtolower', $jobData['tags'] ?? []);

        $visaKeywords = ['visa', 'sponsor', 'sponsorship', 'relocation', 'immigration'];

        foreach ($visaKeywords as $keyword) {
            if (str_contains($description, $keyword) || in_array($keyword, $tags)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get scraping statistics.
     */
    public function getScrapingStats(): array
    {
        $totalJobs = Job::whereIn('source', ['remoteok', 'weworkremotely'])->count();
        $activeJobs = Job::whereIn('source', ['remoteok', 'weworkremotely'])
            ->where('is_active', true)
            ->count();

        $recentJobs = Job::whereIn('source', ['remoteok', 'weworkremotely'])
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return [
            'total_scraped_jobs' => $totalJobs,
            'active_scraped_jobs' => $activeJobs,
            'recent_scraped_jobs' => $recentJobs,
            'sources_enabled' => array_filter($this->sources, fn ($config) => $config['enabled']),
        ];
    }

    /**
     * Enable or disable a scraping source.
     */
    public function toggleSource(string $sourceName, bool $enabled): bool
    {
        if (! isset($this->sources[$sourceName])) {
            return false;
        }

        $this->sources[$sourceName]['enabled'] = $enabled;

        return true;
    }

    /**
     * Get available scraping sources.
     */
    public function getSources(): array
    {
        return $this->sources;
    }
}
