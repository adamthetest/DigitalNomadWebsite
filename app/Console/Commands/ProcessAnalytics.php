<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPredictiveAnalytics;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Console\Command;

class ProcessAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:process 
                            {type : Type of analytics to process (all, cost_trends, trending_cities, user_growth)}
                            {--days=30 : Forecast period in days}
                            {--queue : Dispatch to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process predictive analytics and generate forecasts';

    /**
     * Execute the console command.
     */
    public function handle(PredictiveAnalyticsService $analyticsService): int
    {
        $type = $this->argument('type');
        $days = (int) $this->option('days');
        $useQueue = $this->option('queue');

        $this->info("Processing {$type} analytics with {$days} day forecast...");

        try {
            if ($useQueue) {
                ProcessPredictiveAnalytics::dispatch($type, $days);
                $this->info('📊 Analytics processing job dispatched to queue');
            } else {
                $this->processAnalyticsImmediately($analyticsService, $type, $days);
            }

            $this->info('✅ Analytics processing completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error processing analytics: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Process analytics immediately.
     */
    private function processAnalyticsImmediately(PredictiveAnalyticsService $analyticsService, string $type, int $days): void
    {
        switch ($type) {
            case 'cost_trends':
                $this->processCostTrends($analyticsService, $days);
                break;

            case 'trending_cities':
                $this->processTrendingCities($analyticsService, $days);
                break;

            case 'user_growth':
                $this->processUserGrowth($analyticsService, $days);
                break;

            case 'all':
                $this->processAllAnalytics($analyticsService, $days);
                break;

            default:
                $this->error("Unknown analytics type: {$type}");
                $this->line('Available types: all, cost_trends, trending_cities, user_growth');

                return;
        }
    }

    /**
     * Process cost trend predictions.
     */
    private function processCostTrends(PredictiveAnalyticsService $analyticsService, int $days): void
    {
        $this->info('📈 Processing cost trend predictions...');

        $predictions = $analyticsService->predictCostTrends(null, $days);

        $this->info('Generated cost trend predictions for '.count($predictions).' cities');

        foreach ($predictions as $prediction) {
            $city = $prediction['city'];
            $data = $prediction['prediction']['data'];

            $this->line("• {$city->name}: {$data['trend_direction']} trend (confidence: ".
                      number_format($prediction['prediction']['confidence']['overall'] * 100, 1).'%)');
        }
    }

    /**
     * Process trending cities predictions.
     */
    private function processTrendingCities(PredictiveAnalyticsService $analyticsService, int $days): void
    {
        $this->info('🏙️ Processing trending cities predictions...');

        $predictions = $analyticsService->predictTrendingCities($days);

        $this->info('Generated trending cities predictions for '.count($predictions).' cities');

        foreach ($predictions as $index => $prediction) {
            $city = $prediction['city'];
            $data = $prediction['prediction']['data'];

            $this->line(($index + 1).". {$city->name}: Trend score ".
                      number_format($data['trend_score'] * 100, 1).'%');
        }
    }

    /**
     * Process user growth predictions.
     */
    private function processUserGrowth(PredictiveAnalyticsService $analyticsService, int $days): void
    {
        $this->info('👥 Processing user growth predictions...');

        $prediction = $analyticsService->predictUserGrowth($days);

        if (isset($prediction['data']['error'])) {
            $this->warn('Insufficient data for user growth prediction');

            return;
        }

        $data = $prediction['data'];

        $this->info('User growth prediction generated:');
        $this->line("• Current users: {$data['current_users']}");
        $this->line("• Predicted users: {$data['predicted_users']}");
        $this->line('• Growth rate: '.number_format($data['growth_rate'], 2).' users/day');
        $this->line('• Growth percentage: '.number_format($data['growth_percentage'], 1).'%');
    }

    /**
     * Process all analytics types.
     */
    private function processAllAnalytics(PredictiveAnalyticsService $analyticsService, int $days): void
    {
        $this->info('🔄 Processing all analytics types...');

        $this->processCostTrends($analyticsService, $days);
        $this->newLine();

        $this->processTrendingCities($analyticsService, $days);
        $this->newLine();

        $this->processUserGrowth($analyticsService, $days);
        $this->newLine();

        $this->info('📊 Generating performance summary...');
        $summary = $analyticsService->generatePerformanceSummary();

        $this->line('Performance summary generated:');
        $this->line("• Generated at: {$summary['generated_at']}");
        $this->line('• Summary length: '.strlen($summary['summary']).' characters');
    }
}
