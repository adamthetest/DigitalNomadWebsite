<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeJobs;
use App\Services\JobScrapingService;
use Illuminate\Console\Command;

class ScrapeJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:scrape 
                            {source? : Specific source to scrape (remoteok, weworkremotely)}
                            {--queue : Dispatch to queue instead of running immediately}
                            {--stats : Show scraping statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape jobs from external sources (RemoteOK, We Work Remotely)';

    /**
     * Execute the console command.
     */
    public function handle(JobScrapingService $scrapingService): int
    {
        $source = $this->argument('source');
        $useQueue = $this->option('queue');
        $showStats = $this->option('stats');

        if ($showStats) {
            $this->showScrapingStats($scrapingService);

            return 0;
        }

        $this->info('Starting job scraping...');

        try {
            if ($useQueue) {
                ScrapeJobs::dispatch($source);
                $this->info('📊 Job scraping job dispatched to queue');
            } else {
                $this->scrapeJobsImmediately($scrapingService, $source);
            }

            $this->info('✅ Job scraping completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error scraping jobs: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Scrape jobs immediately.
     */
    private function scrapeJobsImmediately(JobScrapingService $scrapingService, ?string $source): void
    {
        if ($source) {
            $this->info("🔍 Scraping jobs from {$source}...");
            $results = $scrapingService->scrapeSource($source);

            $this->displayResults($source, $results);
        } else {
            $this->info('🔍 Scraping jobs from all sources...');
            $results = $scrapingService->scrapeAllSources();

            $this->displayAllResults($results);
        }
    }

    /**
     * Display scraping results for a single source.
     */
    private function displayResults(string $source, array $results): void
    {
        $this->info("📊 Results for {$source}:");
        $this->line("• Total processed: {$results['count']}");
        $this->line("• New jobs: {$results['new']}");
        $this->line("• Updated jobs: {$results['updated']}");
        $this->line("• Skipped jobs: {$results['skipped']}");

        if (isset($results['error'])) {
            $this->warn("• Error: {$results['error']}");
        }
    }

    /**
     * Display scraping results for all sources.
     */
    private function displayAllResults(array $results): void
    {
        $totalJobs = array_sum(array_column($results, 'count'));
        $totalNew = array_sum(array_column($results, 'new'));
        $totalUpdated = array_sum(array_column($results, 'updated'));
        $totalSkipped = array_sum(array_column($results, 'skipped'));

        $this->info('📊 Overall Results:');
        $this->line("• Total processed: {$totalJobs}");
        $this->line("• New jobs: {$totalNew}");
        $this->line("• Updated jobs: {$totalUpdated}");
        $this->line("• Skipped jobs: {$totalSkipped}");

        $this->newLine();
        $this->info('📊 Results by Source:');

        foreach ($results as $source => $sourceResults) {
            $this->line("• {$source}: {$sourceResults['count']} jobs");
        }
    }

    /**
     * Show scraping statistics.
     */
    private function showScrapingStats(JobScrapingService $scrapingService): void
    {
        $stats = $scrapingService->getScrapingStats();

        $this->info('📊 Job Scraping Statistics:');
        $this->line("• Total scraped jobs: {$stats['total_scraped_jobs']}");
        $this->line("• Active scraped jobs: {$stats['active_scraped_jobs']}");
        $this->line("• Recent scraped jobs (last week): {$stats['recent_scraped_jobs']}");

        $this->newLine();
        $this->info('🔧 Available Sources:');

        foreach ($stats['sources_enabled'] as $sourceName => $config) {
            $status = $config['enabled'] ? '✅ Enabled' : '❌ Disabled';
            $this->line("• {$sourceName}: {$status}");
        }
    }
}
