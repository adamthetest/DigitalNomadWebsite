<?php

namespace App\Console\Commands;

use App\Jobs\ValidateAffiliateLinks;
use App\Services\AffiliateLinkValidationService;
use Illuminate\Console\Command;

class ValidateAffiliateLinksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:validate 
                            {category? : Specific category to validate}
                            {--queue : Dispatch to queue instead of running immediately}
                            {--stats : Show validation statistics}
                            {--report : Generate validation report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate affiliate links to ensure they are working and generating revenue';

    /**
     * Execute the console command.
     */
    public function handle(AffiliateLinkValidationService $validationService): int
    {
        $category = $this->argument('category');
        $useQueue = $this->option('queue');
        $showStats = $this->option('stats');
        $generateReport = $this->option('report');

        if ($showStats) {
            $this->showValidationStats($validationService);

            return 0;
        }

        if ($generateReport) {
            $this->generateValidationReport($validationService);

            return 0;
        }

        $this->info('Starting affiliate link validation...');

        try {
            if ($useQueue) {
                ValidateAffiliateLinks::dispatch($category);
                $this->info('🔗 Affiliate link validation job dispatched to queue');
            } else {
                $this->validateLinksImmediately($validationService, $category);
            }

            $this->info('✅ Affiliate link validation completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error validating affiliate links: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Validate links immediately.
     */
    private function validateLinksImmediately(AffiliateLinkValidationService $validationService, ?string $category): void
    {
        if ($category) {
            $this->info("🔍 Validating affiliate links for category: {$category}...");
            $results = $validationService->validateLinksByCategory($category);

            $this->displayCategoryResults($category, $results);
        } else {
            $this->info('🔍 Validating all affiliate links...');
            $results = $validationService->validateAllLinks();

            $this->displayAllResults($results);
        }
    }

    /**
     * Display validation results for a category.
     */
    private function displayCategoryResults(string $category, array $results): void
    {
        $this->info("📊 Results for {$category}:");
        $this->line("• Total links: {$results['total']}");
        $this->line("• Valid links: {$results['valid']}");
        $this->line("• Invalid links: {$results['invalid']}");
        $this->line("• Errors: {$results['errors']}");

        $successRate = $results['total'] > 0 ? round(($results['valid'] / $results['total']) * 100, 1) : 0;
        $this->line("• Success rate: {$successRate}%");
    }

    /**
     * Display validation results for all links.
     */
    private function displayAllResults(array $results): void
    {
        $this->info('📊 Overall Results:');
        $this->line("• Total links: {$results['total']}");
        $this->line("• Valid links: {$results['valid']}");
        $this->line("• Invalid links: {$results['invalid']}");
        $this->line("• Errors: {$results['errors']}");

        $successRate = $results['total'] > 0 ? round(($results['valid'] / $results['total']) * 100, 1) : 0;
        $this->line("• Success rate: {$successRate}%");

        $this->newLine();
        $this->info('📊 Detailed Results:');

        foreach ($results['details'] as $detail) {
            $status = match ($detail['status']) {
                'valid' => '✅',
                'invalid' => '❌',
                'error' => '⚠️',
                default => '❓',
            };

            $this->line("• {$status} {$detail['url']}");

            if (isset($detail['status_code'])) {
                $this->line("  Status: {$detail['status_code']}");
            }

            if (isset($detail['response_time_ms'])) {
                $this->line("  Response time: {$detail['response_time_ms']}ms");
            }

            if (isset($detail['error'])) {
                $this->line("  Error: {$detail['error']}");
            }
        }
    }

    /**
     * Show validation statistics.
     */
    private function showValidationStats(AffiliateLinkValidationService $validationService): void
    {
        $stats = $validationService->getValidationStats();

        $this->info('📊 Affiliate Link Validation Statistics:');
        $this->line("• Total links: {$stats['total_links']}");
        $this->line("• Active links: {$stats['active_links']}");
        $this->line("• Valid links: {$stats['valid_links']}");
        $this->line("• Invalid links: {$stats['invalid_links']}");
        $this->line("• Recently checked: {$stats['recently_checked']}");
        $this->line("• Never checked: {$stats['never_checked']}");
        $this->line("• Average response time: {$stats['avg_response_time_ms']}ms");
        $this->line("• Validation rate: {$stats['validation_rate']}%");
    }

    /**
     * Generate validation report.
     */
    private function generateValidationReport(AffiliateLinkValidationService $validationService): void
    {
        $this->info('📋 Generating validation report...');

        $report = $validationService->generateValidationReport();

        $this->info('📊 Validation Report Generated:');
        $this->line("• Generated at: {$report['generated_at']}");
        $this->line("• Total links: {$report['statistics']['total_links']}");
        $this->line("• Valid links: {$report['statistics']['valid_links']}");
        $this->line("• Invalid links: {$report['statistics']['invalid_links']}");
        $this->line("• Validation rate: {$report['statistics']['validation_rate']}%");

        $this->newLine();
        $this->info('💡 Recommendations:');

        foreach ($report['recommendations'] as $recommendation) {
            $this->line("• {$recommendation}");
        }
    }
}
