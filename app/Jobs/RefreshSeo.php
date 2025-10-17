<?php

namespace App\Jobs;

use App\Services\SeoAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshSeo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * The SEO task to perform.
     */
    public string $task;

    /**
     * Create a new job instance.
     */
    public function __construct(string $task = 'all')
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     */
    public function handle(SeoAutomationService $seoService): void
    {
        Log::info('Starting SEO refresh', [
            'task' => $this->task,
        ]);

        try {
            match ($this->task) {
                'sitemaps' => $this->generateSitemaps($seoService),
                'robots' => $this->generateRobots($seoService),
                'meta' => $this->updateMetaDescriptions($seoService),
                'cleanup' => $this->cleanupOldFiles($seoService),
                'all' => $this->performAllTasks($seoService),
                default => throw new \InvalidArgumentException("Unknown SEO task: {$this->task}"),
            };

        } catch (\Exception $e) {
            Log::error('SEO refresh failed', [
                'task' => $this->task,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate sitemaps.
     */
    private function generateSitemaps(SeoAutomationService $seoService): void
    {
        $results = $seoService->generateAllSitemaps();
        
        $totalUrls = array_sum(array_column($results, 'urls_count'));
        
        Log::info('Sitemaps generated successfully', [
            'total_urls' => $totalUrls,
            'results' => $results,
        ]);
    }

    /**
     * Generate robots.txt.
     */
    private function generateRobots(SeoAutomationService $seoService): void
    {
        $result = $seoService->generateRobotsTxt();
        
        Log::info('Robots.txt generated successfully', [
            'file_size' => $result['file_size'],
        ]);
    }

    /**
     * Update meta descriptions.
     */
    private function updateMetaDescriptions(SeoAutomationService $seoService): void
    {
        $results = $seoService->updateMetaDescriptions();
        
        $totalUpdated = array_sum($results);
        
        Log::info('Meta descriptions updated successfully', [
            'total_updated' => $totalUpdated,
            'results' => $results,
        ]);
    }

    /**
     * Cleanup old files.
     */
    private function cleanupOldFiles(SeoAutomationService $seoService): void
    {
        $deleted = $seoService->cleanupOldSitemaps();
        
        Log::info('Old sitemap files cleaned up', [
            'files_deleted' => $deleted,
        ]);
    }

    /**
     * Perform all SEO tasks.
     */
    private function performAllTasks(SeoAutomationService $seoService): void
    {
        // Generate sitemaps
        $this->generateSitemaps($seoService);
        
        // Generate robots.txt
        $this->generateRobots($seoService);
        
        // Update meta descriptions
        $this->updateMetaDescriptions($seoService);
        
        // Cleanup old files
        $this->cleanupOldFiles($seoService);
        
        Log::info('All SEO tasks completed successfully');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('RefreshSeo job failed', [
            'task' => $this->task,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}