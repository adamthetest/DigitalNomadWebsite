<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Job;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:scrape {--limit=10 : Number of jobs to scrape per source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape remote jobs from external APIs and job boards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Starting job scraping process (limit: {$limit} per source)...");

        $totalScraped = 0;

        // Scrape from RemoteOK API
        $totalScraped += $this->scrapeRemoteOK($limit);

        // Scrape from WeWorkRemotely RSS
        $totalScraped += $this->scrapeWeWorkRemotely($limit);

        $this->info("Job scraping completed. Total jobs processed: {$totalScraped}");
    }

    /**
     * Scrape jobs from RemoteOK API
     */
    private function scrapeRemoteOK(int $limit): int
    {
        $this->info("Scraping from RemoteOK...");
        
        try {
            $response = Http::timeout(30)->get('https://remoteok.io/api');
            
            if (!$response->successful()) {
                $this->error("Failed to fetch from RemoteOK: HTTP {$response->status()}");
                return 0;
            }

            $jobs = $response->json();
            $scraped = 0;

            foreach (array_slice($jobs, 1, $limit) as $jobData) { // Skip first element (metadata)
                if ($this->processRemoteOKJob($jobData)) {
                    $scraped++;
                }
            }

            $this->info("RemoteOK: {$scraped} jobs processed");
            return $scraped;

        } catch (\Exception $e) {
            $this->error("Error scraping RemoteOK: " . $e->getMessage());
            Log::error("RemoteOK scraping error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Process a single RemoteOK job
     */
    private function processRemoteOKJob(array $jobData): bool
    {
        try {
            // Check if job already exists
            $existingJob = Job::where('source', 'scraped')
                ->where('source_url', $jobData['url'] ?? '')
                ->first();

            if ($existingJob) {
                return false; // Skip duplicate
            }

            // Create or find company
            $company = $this->findOrCreateCompany($jobData['company'] ?? 'Unknown Company');

            // Create job
            Job::create([
                'title' => $jobData['position'] ?? 'Remote Position',
                'description' => $this->cleanDescription($jobData['description'] ?? ''),
                'company_id' => $company->id,
                'type' => $this->mapJobType($jobData['tags'] ?? []),
                'remote_type' => 'fully-remote',
                'salary_min' => $this->extractSalary($jobData['salary_min'] ?? null),
                'salary_max' => $this->extractSalary($jobData['salary_max'] ?? null),
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => $jobData['tags'] ?? [],
                'timezone' => 'Any',
                'visa_support' => false,
                'source' => 'scraped',
                'source_url' => $jobData['url'] ?? '',
                'apply_url' => $jobData['url'] ?? '',
                'featured' => false,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Error processing RemoteOK job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Scrape jobs from WeWorkRemotely RSS
     */
    private function scrapeWeWorkRemotely(int $limit): int
    {
        $this->info("Scraping from WeWorkRemotely...");
        
        try {
            $response = Http::timeout(30)->get('https://weworkremotely.com/categories/remote-programming-jobs.rss');
            
            if (!$response->successful()) {
                $this->error("Failed to fetch from WeWorkRemotely: HTTP {$response->status()}");
                return 0;
            }

            $xml = simplexml_load_string($response->body());
            $scraped = 0;

            $items = (array) $xml->channel->item;
            if (isset($items[0])) {
                // Single item case
                $items = [$items];
            }

            foreach (array_slice($items, 0, $limit) as $item) {
                if ($this->processWeWorkRemotelyJob($item)) {
                    $scraped++;
                }
            }

            $this->info("WeWorkRemotely: {$scraped} jobs processed");
            return $scraped;

        } catch (\Exception $e) {
            $this->error("Error scraping WeWorkRemotely: " . $e->getMessage());
            Log::error("WeWorkRemotely scraping error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Process a single WeWorkRemotely job
     */
    private function processWeWorkRemotelyJob($item): bool
    {
        try {
            $title = is_array($item) ? $item['title'] : (string) $item->title;
            $description = is_array($item) ? $item['description'] : (string) $item->description;
            $link = is_array($item) ? $item['link'] : (string) $item->link;

            // Check if job already exists
            $existingJob = Job::where('source', 'scraped')
                ->where('source_url', $link)
                ->first();

            if ($existingJob) {
                return false; // Skip duplicate
            }

            // Extract company name from title (usually format: "Company Name: Job Title")
            $companyName = $this->extractCompanyFromTitle($title);

            // Create or find company
            $company = $this->findOrCreateCompany($companyName);

            // Create job
            Job::create([
                'title' => $title,
                'description' => $this->cleanDescription($description),
                'company_id' => $company->id,
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => $this->extractTagsFromDescription($description),
                'timezone' => 'Any',
                'visa_support' => false,
                'source' => 'scraped',
                'source_url' => $link,
                'apply_url' => $link,
                'featured' => false,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Error processing WeWorkRemotely job: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find or create a company
     */
    private function findOrCreateCompany(string $name): Company
    {
        $company = Company::where('name', $name)->first();
        
        if (!$company) {
            $company = Company::create([
                'name' => $name,
                'slug' => \Str::slug($name),
                'verified' => false,
                'subscription_plan' => 'basic',
                'is_active' => true,
            ]);
        }

        return $company;
    }

    /**
     * Clean job description
     */
    private function cleanDescription(string $description): string
    {
        // Remove HTML tags and clean up
        $description = strip_tags($description);
        $description = html_entity_decode($description);
        $description = preg_replace('/\s+/', ' ', $description);
        
        return trim($description);
    }

    /**
     * Map job type from tags
     */
    private function mapJobType(array $tags): string
    {
        $tagString = strtolower(implode(' ', $tags));
        
        if (str_contains($tagString, 'part-time') || str_contains($tagString, 'part time')) {
            return 'part-time';
        }
        
        if (str_contains($tagString, 'contract')) {
            return 'contract';
        }
        
        if (str_contains($tagString, 'freelance')) {
            return 'freelance';
        }
        
        return 'full-time';
    }

    /**
     * Extract salary from string
     */
    private function extractSalary(?string $salary): ?int
    {
        if (!$salary) {
            return null;
        }

        // Extract numbers from salary string
        preg_match('/[\d,]+/', $salary, $matches);
        
        if (!empty($matches)) {
            return (int) str_replace(',', '', $matches[0]);
        }

        return null;
    }

    /**
     * Extract company name from job title
     */
    private function extractCompanyFromTitle(string $title): string
    {
        // Common format: "Company Name: Job Title"
        if (str_contains($title, ':')) {
            return trim(explode(':', $title)[0]);
        }

        return 'Unknown Company';
    }

    /**
     * Extract tags from job description
     */
    private function extractTagsFromDescription(string $description): array
    {
        $commonTags = [
            'JavaScript', 'Python', 'React', 'Node.js', 'PHP', 'Java', 'Ruby',
            'AWS', 'Docker', 'Kubernetes', 'PostgreSQL', 'MySQL', 'MongoDB',
            'Frontend', 'Backend', 'Full Stack', 'DevOps', 'UI/UX', 'Design'
        ];

        $foundTags = [];
        $description = strtolower($description);

        foreach ($commonTags as $tag) {
            if (str_contains($description, strtolower($tag))) {
                $foundTags[] = $tag;
            }
        }

        return array_slice($foundTags, 0, 5); // Limit to 5 tags
    }
}
