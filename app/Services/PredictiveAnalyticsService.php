<?php

namespace App\Services;

use App\Models\City;
use App\Models\DailyMetric;
use App\Models\Job;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Predictive Analytics Service
 *
 * Handles predictive analytics and forecasting for cities, users, and platform metrics.
 * Provides trend analysis, cost predictions, and smart recommendations.
 */
class PredictiveAnalyticsService
{
    protected OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Predict cost-of-living trends for cities.
     */
    public function predictCostTrends($cityId = null, $days = 30): array
    {
        $cities = $cityId ? City::where('id', $cityId)->get() : City::where('is_active', true)->get();
        $predictions = [];

        foreach ($cities as $city) {
            $historicalData = DailyMetric::getCityMetrics($city->id, now()->subDays(90), now());
            $costMetrics = $historicalData->where('metric_type', 'city_cost');

            if ($costMetrics->count() < 7) {
                // Not enough data for prediction
                continue;
            }

            $prediction = $this->analyzeCostTrend($city, $costMetrics, $days);
            
            if ($prediction) {
                Prediction::storeCostTrendPrediction(
                    $city->id,
                    now()->addDays($days),
                    $prediction['data'],
                    $prediction['confidence'],
                    $prediction['factors']
                );
                
                $predictions[] = [
                    'city' => $city,
                    'prediction' => $prediction,
                ];
            }
        }

        return $predictions;
    }

    /**
     * Predict trending cities based on job and search data.
     */
    public function predictTrendingCities($days = 30): array
    {
        $cities = City::where('is_active', true)->get();
        $trendingPredictions = [];

        foreach ($cities as $city) {
            $trendScore = $this->calculateTrendScore($city, $days);
            
            if ($trendScore > 0.6) { // Only include cities with high trend potential
                $prediction = [
                    'data' => [
                        'trend_score' => $trendScore,
                        'expected_growth' => $this->calculateExpectedGrowth($city),
                        'key_factors' => $this->getTrendFactors($city),
                    ],
                    'confidence' => [
                        'overall' => $trendScore,
                        'job_market' => $this->getJobMarketConfidence($city),
                        'cost_attractiveness' => $this->getCostAttractivenessConfidence($city),
                    ],
                    'factors' => $this->getTrendFactors($city),
                ];

                Prediction::storeTrendingCityPrediction(
                    $city->id,
                    now()->addDays($days),
                    $prediction['data'],
                    $prediction['confidence'],
                    $prediction['factors']
                );

                $trendingPredictions[] = [
                    'city' => $city,
                    'prediction' => $prediction,
                ];
            }
        }

        // Sort by trend score
        usort($trendingPredictions, fn($a, $b) => $b['prediction']['data']['trend_score'] <=> $a['prediction']['data']['trend_score']);

        return array_slice($trendingPredictions, 0, 10); // Top 10 trending cities
    }

    /**
     * Predict user growth and engagement.
     */
    public function predictUserGrowth($days = 30): array
    {
        $historicalData = DailyMetric::getGlobalMetrics('user_activity', now()->subDays(90), now());
        
        if ($historicalData->count() < 7) {
            return [
                'data' => ['error' => 'Insufficient historical data'],
                'confidence' => ['overall' => 0.0],
                'factors' => [],
            ];
        }

        $prediction = $this->analyzeUserGrowthTrend($historicalData, $days);
        
        Prediction::storeUserGrowthPrediction(
            now()->addDays($days),
            $prediction['data'],
            $prediction['confidence'],
            $prediction['factors']
        );

        return $prediction;
    }

    /**
     * Generate AI-powered performance summary for admins.
     */
    public function generatePerformanceSummary(): array
    {
        $summaryData = $this->collectPerformanceData();
        
        $prompt = $this->buildPerformanceSummaryPrompt($summaryData);
        $aiSummary = $this->openAiService->generateContent($prompt, [
            'max_tokens' => 1500,
            'temperature' => 0.5,
        ]);

        if (!$aiSummary) {
            $aiSummary = $this->generateFallbackPerformanceSummary($summaryData);
        }

        return [
            'summary' => $aiSummary,
            'data' => $summaryData,
            'generated_at' => now(),
        ];
    }

    /**
     * Get forecasted metrics for admin dashboard.
     */
    public function getForecastedMetrics($days = 30): array
    {
        return Cache::remember("forecasted_metrics_{$days}", 3600, function () use ($days) {
            $userGrowth = $this->predictUserGrowth($days);
            $trendingCities = $this->predictTrendingCities($days);
            $costTrends = $this->predictCostTrends(null, $days);

            return [
                'user_growth' => $userGrowth,
                'trending_cities' => $trendingCities,
                'cost_trends' => $costTrends,
                'generated_at' => now(),
                'forecast_period' => $days,
            ];
        });
    }

    /**
     * Analyze cost trend for a city.
     */
    private function analyzeCostTrend(City $city, $costMetrics, int $days): ?array
    {
        $costData = $costMetrics->pluck('metrics')->toArray();
        
        if (count($costData) < 7) {
            return null;
        }

        // Simple trend analysis (in a real implementation, you'd use more sophisticated algorithms)
        $trend = $this->calculateLinearTrend($costData);
        $seasonality = $this->detectSeasonality($costData);
        
        $predictedCost = $city->cost_of_living_index + ($trend * $days);
        $confidence = $this->calculateTrendConfidence($costData);

        return [
            'data' => [
                'current_cost_index' => $city->cost_of_living_index,
                'predicted_cost_index' => max(0, $predictedCost),
                'trend_direction' => $trend > 0 ? 'increasing' : 'decreasing',
                'trend_magnitude' => abs($trend),
                'seasonality_factor' => $seasonality,
            ],
            'confidence' => [
                'overall' => $confidence,
                'trend' => $confidence,
                'seasonality' => $seasonality > 0.5 ? 0.8 : 0.3,
            ],
            'factors' => [
                'historical_data_points' => count($costData),
                'trend_strength' => abs($trend),
                'seasonality_detected' => $seasonality > 0.5,
            ],
        ];
    }

    /**
     * Calculate trend score for a city.
     */
    private function calculateTrendScore(City $city, int $days): float
    {
        $score = 0.0;

        // Job market factor (30%)
        $jobScore = $this->getJobMarketScore($city);
        $score += $jobScore * 0.3;

        // Cost attractiveness (25%)
        $costScore = $this->getCostAttractivenessScore($city);
        $score += $costScore * 0.25;

        // Internet quality (20%)
        $internetScore = $this->getInternetQualityScore($city);
        $score += $internetScore * 0.2;

        // Safety score (15%)
        $safetyScore = $city->safety_score / 10;
        $score += $safetyScore * 0.15;

        // Recent activity (10%)
        $activityScore = $this->getRecentActivityScore($city);
        $score += $activityScore * 0.1;

        return min(1.0, $score);
    }

    /**
     * Calculate expected growth for a city.
     */
    private function calculateExpectedGrowth(City $city): array
    {
        return [
            'user_interest' => $this->getUserInterestGrowth($city),
            'job_opportunities' => $this->getJobOpportunityGrowth($city),
            'cost_stability' => $this->getCostStabilityScore($city),
        ];
    }

    /**
     * Get trend factors for a city.
     */
    private function getTrendFactors(City $city): array
    {
        return [
            'job_market_growth' => $this->getJobMarketScore($city),
            'cost_competitiveness' => $this->getCostAttractivenessScore($city),
            'digital_infrastructure' => $this->getInternetQualityScore($city),
            'safety_rating' => $city->safety_score / 10,
            'nomad_amenities' => $this->getNomadAmenitiesScore($city),
        ];
    }

    /**
     * Analyze user growth trend.
     */
    private function analyzeUserGrowthTrend($historicalData, int $days): array
    {
        $userCounts = $historicalData->pluck('metrics.user_count')->filter()->toArray();
        
        if (count($userCounts) < 7) {
            return [
                'data' => ['error' => 'Insufficient data'],
                'confidence' => ['overall' => 0.0],
                'factors' => [],
            ];
        }

        $trend = $this->calculateLinearTrend($userCounts);
        $currentUsers = User::count();
        $predictedGrowth = $currentUsers + ($trend * $days);
        
        return [
            'data' => [
                'current_users' => $currentUsers,
                'predicted_users' => max(0, $predictedGrowth),
                'growth_rate' => $trend,
                'growth_percentage' => $currentUsers > 0 ? (($trend * $days) / $currentUsers) * 100 : 0,
            ],
            'confidence' => [
                'overall' => $this->calculateTrendConfidence($userCounts),
                'trend' => $this->calculateTrendConfidence($userCounts),
            ],
            'factors' => [
                'historical_data_points' => count($userCounts),
                'trend_strength' => abs($trend),
            ],
        ];
    }

    /**
     * Collect performance data for summary.
     */
    private function collectPerformanceData(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
                'active_this_week' => User::where('last_active', '>=', now()->subWeek())->count(),
            ],
            'cities' => [
                'total' => City::where('is_active', true)->count(),
                'featured' => City::where('is_featured', true)->count(),
            ],
            'jobs' => [
                'total' => Job::where('is_active', true)->count(),
                'new_this_week' => Job::where('created_at', '>=', now()->subWeek())->count(),
            ],
            'metrics' => [
                'avg_cost_index' => City::where('is_active', true)->avg('cost_of_living_index'),
                'avg_internet_speed' => City::where('is_active', true)->avg('internet_speed_mbps'),
                'avg_safety_score' => City::where('is_active', true)->avg('safety_score'),
            ],
        ];
    }

    /**
     * Build performance summary prompt.
     */
    private function buildPerformanceSummaryPrompt(array $data): string
    {
        return "Generate a comprehensive weekly performance summary for a digital nomad platform admin dashboard.\n\n" .
               "Platform Data:\n" .
               "• Total Users: {$data['users']['total']}\n" .
               "• New Users This Week: {$data['users']['new_this_week']}\n" .
               "• Active Users This Week: {$data['users']['active_this_week']}\n" .
               "• Total Cities: {$data['cities']['total']}\n" .
               "• Featured Cities: {$data['cities']['featured']}\n" .
               "• Total Jobs: {$data['jobs']['total']}\n" .
               "• New Jobs This Week: {$data['jobs']['new_this_week']}\n" .
               "• Average Cost Index: " . number_format($data['metrics']['avg_cost_index'], 1) . "\n" .
               "• Average Internet Speed: " . number_format($data['metrics']['avg_internet_speed'], 1) . " Mbps\n" .
               "• Average Safety Score: " . number_format($data['metrics']['avg_safety_score'], 1) . "/10\n\n" .
               "Requirements:\n" .
               "1. Provide key insights and trends\n" .
               "2. Highlight growth areas and opportunities\n" .
               "3. Identify potential concerns or areas for improvement\n" .
               "4. Include actionable recommendations\n" .
               "5. Use a professional, data-driven tone\n" .
               "6. Keep it concise but comprehensive (500-800 words)\n" .
               "7. Use markdown formatting";
    }

    /**
     * Generate fallback performance summary.
     */
    private function generateFallbackPerformanceSummary(array $data): string
    {
        $growthRate = $data['users']['total'] > 0 ? ($data['users']['new_this_week'] / $data['users']['total']) * 100 : 0;
        
        return "# Weekly Performance Summary\n\n" .
               "## Key Metrics\n\n" .
               "• **Total Users:** {$data['users']['total']} (+{$data['users']['new_this_week']} this week)\n" .
               "• **Active Users:** {$data['users']['active_this_week']} this week\n" .
               "• **Cities:** {$data['cities']['total']} total, {$data['cities']['featured']} featured\n" .
               "• **Jobs:** {$data['jobs']['total']} total (+{$data['jobs']['new_this_week']} this week)\n\n" .
               "## Platform Health\n\n" .
               "• **User Growth Rate:** " . number_format($growthRate, 1) . "%\n" .
               "• **Average Cost Index:** " . number_format($data['metrics']['avg_cost_index'], 1) . "\n" .
               "• **Average Internet Speed:** " . number_format($data['metrics']['avg_internet_speed'], 1) . " Mbps\n" .
               "• **Average Safety Score:** " . number_format($data['metrics']['avg_safety_score'], 1) . "/10\n\n" .
               "## Recommendations\n\n" .
               "• Continue monitoring user growth trends\n" .
               "• Focus on expanding job opportunities\n" .
               "• Maintain city data quality and accuracy\n" .
               "• Consider adding more featured destinations";
    }

    // Helper methods for trend analysis
    private function calculateLinearTrend(array $data): float
    {
        $n = count($data);
        if ($n < 2) return 0;
        
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $data[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }
        
        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
    }

    private function detectSeasonality(array $data): float
    {
        // Simple seasonality detection
        if (count($data) < 7) return 0;
        
        $variance = $this->calculateVariance($data);
        $mean = array_sum($data) / count($data);
        
        return $mean > 0 ? $variance / $mean : 0;
    }

    private function calculateVariance(array $data): float
    {
        $mean = array_sum($data) / count($data);
        $variance = 0;
        
        foreach ($data as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return $variance / count($data);
    }

    private function calculateTrendConfidence(array $data): float
    {
        $variance = $this->calculateVariance($data);
        $mean = array_sum($data) / count($data);
        
        if ($mean == 0) return 0;
        
        $coefficientOfVariation = sqrt($variance) / $mean;
        
        return max(0, min(1, 1 - $coefficientOfVariation));
    }

    // Scoring methods for city analysis
    private function getJobMarketScore(City $city): float
    {
        $jobCount = Job::where('location', 'like', '%' . $city->name . '%')->count();
        return min(1.0, $jobCount / 10); // Normalize to 0-1 scale
    }

    private function getCostAttractivenessScore(City $city): float
    {
        $costIndex = $city->cost_of_living_index;
        if ($costIndex <= 30) return 1.0;
        if ($costIndex <= 50) return 0.8;
        if ($costIndex <= 70) return 0.6;
        if ($costIndex <= 90) return 0.4;
        return 0.2;
    }

    private function getInternetQualityScore(City $city): float
    {
        $speed = $city->internet_speed_mbps;
        if ($speed >= 50) return 1.0;
        if ($speed >= 30) return 0.8;
        if ($speed >= 20) return 0.6;
        if ($speed >= 10) return 0.4;
        return 0.2;
    }

    private function getRecentActivityScore(City $city): float
    {
        // This would be based on recent user activity, searches, etc.
        return 0.5; // Placeholder
    }

    private function getUserInterestGrowth(City $city): float
    {
        return $this->getJobMarketScore($city) * 0.7 + $this->getCostAttractivenessScore($city) * 0.3;
    }

    private function getJobOpportunityGrowth(City $city): float
    {
        return $this->getJobMarketScore($city);
    }

    private function getCostStabilityScore(City $city): float
    {
        return 1.0 - ($city->cost_of_living_index / 100);
    }

    private function getNomadAmenitiesScore(City $city): float
    {
        $score = 0;
        if ($city->coworking_spaces_count > 5) $score += 0.3;
        if ($city->english_widely_spoken) $score += 0.2;
        if ($city->female_safe) $score += 0.2;
        if ($city->lgbtq_friendly) $score += 0.2;
        if ($city->visa_duration_days > 90) $score += 0.1;
        
        return min(1.0, $score);
    }

    private function getJobMarketConfidence(City $city): float
    {
        return $this->getJobMarketScore($city);
    }

    private function getCostAttractivenessConfidence(City $city): float
    {
        return $this->getCostAttractivenessScore($city);
    }
}
