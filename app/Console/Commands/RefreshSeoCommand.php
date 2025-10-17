<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSeo;
use App\Services\SeoAutomationService;
use Illuminate\Console\Command;

class RefreshSeoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:refresh 
                            {task=all : SEO task to perform (sitemaps, robots, meta, cleanup, all)}
                            {--queue : Dispatch to queue instead of running immediately}
                            {--stats : Show SEO statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh SEO data including sitemaps, robots.txt, and meta descriptions';

    /**
     * Execute the console command.
     */
    public function handle(SeoAutomationService $seoService): int
    {
        $task = $this->argument('task');
        $useQueue = $this->option('queue');
        $showStats = $this->option('stats');

        if ($showStats) {
            $this->showSeoStats($seoService);

            return 0;
        }

        $this->info("Starting SEO refresh for task: {$task}");

        try {
            if ($useQueue) {
                RefreshSeo::dispatch($task);
                $this->info('🔍 SEO refresh job dispatched to queue');
            } else {
                $this->refreshSeoImmediately($seoService, $task);
            }

            $this->info('✅ SEO refresh completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error refreshing SEO: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Refresh SEO immediately.
     */
    private function refreshSeoImmediately(SeoAutomationService $seoService, string $task): void
    {
        match ($task) {
            'sitemaps' => $this->generateSitemaps($seoService),
            'robots' => $this->generateRobots($seoService),
            'meta' => $this->updateMetaDescriptions($seoService),
            'cleanup' => $this->cleanupOldFiles($seoService),
            'all' => $this->performAllTasks($seoService),
            default => throw new \InvalidArgumentException("Unknown SEO task: {$task}"),
        };
    }

    /**
     * Generate sitemaps.
     */
    private function generateSitemaps(SeoAutomationService $seoService): void
    {
        $this->info('🗺️ Generating sitemaps...');

        $results = $seoService->generateAllSitemaps();

        $totalUrls = array_sum(array_column($results, 'urls_count'));

        $this->info('📊 Sitemaps generated successfully:');
        $this->line("• Total URLs: {$totalUrls}");

        foreach ($results as $type => $result) {
            if ($result['success'] ?? false) {
                $this->line("• {$type}: {$result['urls_count']} URLs, {$result['file_size']} bytes");
            } else {
                $this->warn("• {$type}: Failed - {$result['error']}");
            }
        }
    }

    /**
     * Generate robots.txt.
     */
    private function generateRobots(SeoAutomationService $seoService): void
    {
        $this->info('🤖 Generating robots.txt...');

        $result = $seoService->generateRobotsTxt();

        $this->info('📊 Robots.txt generated successfully:');
        $this->line("• File size: {$result['file_size']} bytes");
        $this->line("• Location: {$result['filepath']}");
    }

    /**
     * Update meta descriptions.
     */
    private function updateMetaDescriptions(SeoAutomationService $seoService): void
    {
        $this->info('📝 Updating meta descriptions...');

        $results = $seoService->updateMetaDescriptions();

        $totalUpdated = array_sum($results);

        $this->info('📊 Meta descriptions updated successfully:');
        $this->line("• Cities updated: {$results['cities_updated']}");
        $this->line("• Jobs updated: {$results['jobs_updated']}");
        $this->line("• Articles updated: {$results['articles_updated']}");
        $this->line("• Total updated: {$totalUpdated}");
    }

    /**
     * Cleanup old files.
     */
    private function cleanupOldFiles(SeoAutomationService $seoService): void
    {
        $this->info('🧹 Cleaning up old sitemap files...');

        $deleted = $seoService->cleanupOldSitemaps();

        $this->info('📊 Old files cleaned up:');
        $this->line("• Files deleted: {$deleted}");
    }

    /**
     * Perform all SEO tasks.
     */
    private function performAllTasks(SeoAutomationService $seoService): void
    {
        $this->info('🔄 Performing all SEO tasks...');

        // Generate sitemaps
        $this->generateSitemaps($seoService);
        $this->newLine();

        // Generate robots.txt
        $this->generateRobots($seoService);
        $this->newLine();

        // Update meta descriptions
        $this->updateMetaDescriptions($seoService);
        $this->newLine();

        // Cleanup old files
        $this->cleanupOldFiles($seoService);

        $this->info('✅ All SEO tasks completed successfully!');
    }

    /**
     * Show SEO statistics.
     */
    private function showSeoStats(SeoAutomationService $seoService): void
    {
        $stats = $seoService->getSeoStats();

        $this->info('📊 SEO Statistics:');
        $this->line("• Sitemap files: {$stats['sitemap_files']}");
        $this->line('• Total sitemap size: '.number_format($stats['total_sitemap_size']).' bytes');
        $this->line("• Cities without meta: {$stats['cities_without_meta']}");
        $this->line("• Jobs without meta: {$stats['jobs_without_meta']}");
        $this->line("• Articles without meta: {$stats['articles_without_meta']}");

        if ($stats['last_generated']) {
            $this->line('• Last generated: '.date('Y-m-d H:i:s', $stats['last_generated']));
        } else {
            $this->line('• Last generated: Never');
        }
    }
}
