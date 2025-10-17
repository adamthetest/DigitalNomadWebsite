<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\DailyMetric;
use App\Models\Job;
use App\Models\User;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPredictiveAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 900; // 15 minutes

    /**
     * The analytics type to process.
     */
    public string $analyticsType;

    /**
     * The forecast period in days.
     */
    public int $forecastDays;

    /**
     * Create a new job instance.
     */
    public function __construct(string $analyticsType = 'all', int $forecastDays = 30)
    {
        $this->analyticsType = $analyticsType;
        $this->forecastDays = $forecastDays;
    }

    /**
     * Execute the job.
     */
    public function handle(PredictiveAnalyticsService $analyticsService): void
    {
        Log::info('Starting predictive analytics processing', [
            'analytics_type' => $this->analyticsType,
            'forecast_days' => $this->forecastDays,
        ]);

        try {
            // Collect daily metrics first
            $this->collectDailyMetrics();

            // Process analytics based on type
            switch ($this->analyticsType) {
                case 'cost_trends':
                    $this->processCostTrends($analyticsService);
                    break;
                case 'trending_cities':
                    $this->processTrendingCities($analyticsService);
                    break;
                case 'user_growth':
                    $this->processUserGrowth($analyticsService);
                    break;
                case 'all':
                    $this->processAllAnalytics($analyticsService);
                    break;
                default:
                    Log::warning("Unknown analytics type: {$this->analyticsType}");
            }

            Log::info('Predictive analytics processing completed successfully', [
                'analytics_type' => $this->analyticsType,
                'forecast_days' => $this->forecastDays,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing predictive analytics', [
                'analytics_type' => $this->analyticsType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Collect daily metrics from various sources.
     */
    private function collectDailyMetrics(): void
    {
        $today = now()->toDateString();

        // Collect city cost metrics
        $this->collectCityCostMetrics($today);

        // Collect traffic metrics
        $this->collectTrafficMetrics($today);

        // Collect user activity metrics
        $this->collectUserActivityMetrics($today);

        // Collect job posting metrics
        $this->collectJobMetrics($today);

        Log::info('Daily metrics collection completed', ['date' => $today]);
    }

    /**
     * Collect city cost metrics.
     */
    private function collectCityCostMetrics(string $date): void
    {
        $cities = City::where('is_active', true)->get();

        foreach ($cities as $city) {
            $costData = [
                'cost_of_living_index' => $city->cost_of_living_index,
                'cost_accommodation_monthly' => $city->cost_accommodation_monthly,
                'cost_food_monthly' => $city->cost_food_monthly,
                'cost_transport_monthly' => $city->cost_transport_monthly,
                'cost_coworking_monthly' => $city->cost_coworking_monthly,
            ];

            DailyMetric::storeCityCostMetrics($city->id, $date, $costData);
        }
    }

    /**
     * Collect traffic metrics.
     */
    private function collectTrafficMetrics(string $date): void
    {
        // In a real implementation, this would integrate with analytics services
        $trafficData = [
            'page_views' => rand(1000, 5000), // Placeholder
            'unique_visitors' => rand(500, 2000), // Placeholder
            'sessions' => rand(800, 3000), // Placeholder
            'bounce_rate' => rand(30, 70) / 100, // Placeholder
        ];

        DailyMetric::storeTrafficMetrics($date, $trafficData);
    }

    /**
     * Collect user activity metrics.
     */
    private function collectUserActivityMetrics(string $date): void
    {
        $userData = [
            'user_count' => User::count(),
            'new_users_today' => User::whereDate('created_at', $date)->count(),
            'active_users_today' => User::whereDate('last_active', $date)->count(),
            'premium_users' => User::where('premium_status', 'premium')->count(),
        ];

        DailyMetric::storeUserActivityMetrics($date, $userData);
    }

    /**
     * Collect job posting metrics.
     */
    private function collectJobMetrics(string $date): void
    {
        $jobData = [
            'total_jobs' => Job::where('is_active', true)->count(),
            'new_jobs_today' => Job::whereDate('created_at', $date)->count(),
            'remote_jobs' => Job::where('is_active', true)->where('remote_type', 'fully_remote')->count(),
            'featured_jobs' => Job::where('is_active', true)->where('featured', true)->count(),
        ];

        DailyMetric::storeJobMetrics($date, $jobData);
    }

    /**
     * Process cost trend predictions.
     */
    private function processCostTrends(PredictiveAnalyticsService $analyticsService): void
    {
        $predictions = $analyticsService->predictCostTrends(null, $this->forecastDays);

        Log::info('Cost trend predictions generated', [
            'predictions_count' => count($predictions),
        ]);
    }

    /**
     * Process trending cities predictions.
     */
    private function processTrendingCities(PredictiveAnalyticsService $analyticsService): void
    {
        $predictions = $analyticsService->predictTrendingCities($this->forecastDays);

        Log::info('Trending cities predictions generated', [
            'predictions_count' => count($predictions),
        ]);
    }

    /**
     * Process user growth predictions.
     */
    private function processUserGrowth(PredictiveAnalyticsService $analyticsService): void
    {
        $prediction = $analyticsService->predictUserGrowth($this->forecastDays);

        Log::info('User growth prediction generated', [
            'prediction_data' => $prediction['data'] ?? [],
        ]);
    }

    /**
     * Process all analytics types.
     */
    private function processAllAnalytics(PredictiveAnalyticsService $analyticsService): void
    {
        $this->processCostTrends($analyticsService);
        $this->processTrendingCities($analyticsService);
        $this->processUserGrowth($analyticsService);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessPredictiveAnalytics job failed', [
            'analytics_type' => $this->analyticsType,
            'forecast_days' => $this->forecastDays,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
