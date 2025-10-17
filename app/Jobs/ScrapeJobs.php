<?php

namespace App\Jobs;

use App\Services\JobScrapingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The source to scrape from (null for all sources).
     */
    public ?string $source;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $source = null)
    {
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(JobScrapingService $scrapingService): void
    {
        Log::info('Starting job scraping', [
            'source' => $this->source ?? 'all',
        ]);

        try {
            if ($this->source) {
                // Scrape from specific source
                $results = $scrapingService->scrapeSource($this->source);

                Log::info("Job scraping completed for source: {$this->source}", [
                    'source' => $this->source,
                    'results' => $results,
                ]);
            } else {
                // Scrape from all sources
                $results = $scrapingService->scrapeAllSources();

                $totalJobs = array_sum(array_column($results, 'count'));
                $totalNew = array_sum(array_column($results, 'new'));
                $totalUpdated = array_sum(array_column($results, 'updated'));

                Log::info('Job scraping completed for all sources', [
                    'total_jobs' => $totalJobs,
                    'total_new' => $totalNew,
                    'total_updated' => $totalUpdated,
                    'results' => $results,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Job scraping failed', [
                'source' => $this->source ?? 'all',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ScrapeJobs job failed', [
            'source' => $this->source ?? 'all',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
