<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyMetric;
use App\Models\Prediction;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected PredictiveAnalyticsService $analyticsService;

    public function __construct(PredictiveAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get forecasted metrics for admin dashboard.
     */
    public function getForecastedMetrics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $days = min($request->get('days', 30), 90); // Max 90 days
        $forecastedMetrics = $this->analyticsService->getForecastedMetrics($days);

        return response()->json([
            'success' => true,
            'data' => $forecastedMetrics,
        ]);
    }

    /**
     * Get cost trend predictions for cities.
     */
    public function getCostTrendPredictions(Request $request): JsonResponse
    {
        $cityId = $request->get('city_id');
        $days = min($request->get('days', 30), 90);

        $predictions = Prediction::byType('cost_trend')
            ->when($cityId, function ($query) use ($cityId) {
                return $query->forEntity('city', $cityId);
            })
            ->where('prediction_date', '>', now())
            ->orderBy('prediction_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Get trending cities predictions.
     */
    public function getTrendingCitiesPredictions(Request $request): JsonResponse
    {
        $days = min($request->get('days', 30), 90);
        $limit = min($request->get('limit', 10), 20);

        $predictions = Prediction::byType('trending_city')
            ->forEntity('city')
            ->where('prediction_date', '>', now())
            ->orderBy('prediction_data->trend_score', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Get user growth predictions.
     */
    public function getUserGrowthPredictions(Request $request): JsonResponse
    {
        $days = min($request->get('days', 30), 90);

        $predictions = Prediction::byType('user_growth')
            ->whereNull('entity_id')
            ->where('prediction_date', '>', now())
            ->orderBy('prediction_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Get daily metrics for analysis.
     */
    public function getDailyMetrics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $request->validate([
            'metric_type' => 'required|string|in:city_cost,traffic,user_activity,job_postings',
            'entity_type' => 'nullable|string|in:city,global,user,job',
            'entity_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = DailyMetric::byType($request->get('metric_type'));

        if ($request->has('entity_type')) {
            $query->forEntity($request->get('entity_type'), $request->get('entity_id'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        } else {
            // Default to last 30 days
            $query->dateRange(now()->subDays(30), now());
        }

        $metrics = $query->orderBy('date')->get();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Generate performance summary.
     */
    public function generatePerformanceSummary(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $summary = $this->analyticsService->generatePerformanceSummary();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get analytics statistics.
     */
    public function getAnalyticsStatistics(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $stats = [
            'daily_metrics' => [
                'total_records' => DailyMetric::count(),
                'by_type' => DailyMetric::selectRaw('metric_type, COUNT(*) as count')
                    ->groupBy('metric_type')
                    ->pluck('count', 'metric_type'),
                'date_range' => [
                    'earliest' => DailyMetric::min('date'),
                    'latest' => DailyMetric::max('date'),
                ],
            ],
            'predictions' => [
                'total_predictions' => Prediction::count(),
                'by_type' => Prediction::selectRaw('prediction_type, COUNT(*) as count')
                    ->groupBy('prediction_type')
                    ->pluck('count', 'prediction_type'),
                'future_predictions' => Prediction::future()->count(),
                'recent_predictions' => Prediction::recent()->count(),
            ],
            'data_quality' => [
                'cities_with_metrics' => DailyMetric::where('entity_type', 'city')
                    ->distinct('entity_id')
                    ->count('entity_id'),
                'avg_confidence_score' => Prediction::avg('confidence_scores->overall'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Trigger analytics processing.
     */
    public function triggerAnalyticsProcessing(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $request->validate([
            'type' => 'required|string|in:all,cost_trends,trending_cities,user_growth',
            'days' => 'nullable|integer|min:1|max:90',
            'queue' => 'nullable|boolean',
        ]);

        $type = $request->get('type');
        $days = $request->get('days', 30);
        $useQueue = $request->boolean('queue', true);

        try {
            if ($useQueue) {
                \App\Jobs\ProcessPredictiveAnalytics::dispatch($type, $days);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Analytics processing job dispatched to queue',
                    'data' => [
                        'type' => $type,
                        'days' => $days,
                        'queued' => true,
                    ],
                ]);
            } else {
                // Process immediately
                $forecastedMetrics = $this->analyticsService->getForecastedMetrics($days);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Analytics processed immediately',
                    'data' => $forecastedMetrics,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing analytics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get prediction accuracy metrics.
     */
    public function getPredictionAccuracy(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $accuracy = [
            'overall_accuracy' => Prediction::avg('confidence_scores->overall'),
            'by_type' => Prediction::selectRaw('prediction_type, AVG(JSON_EXTRACT(confidence_scores, "$.overall")) as avg_confidence')
                ->groupBy('prediction_type')
                ->pluck('avg_confidence', 'prediction_type'),
            'recent_accuracy' => Prediction::recent()->avg('confidence_scores->overall'),
            'high_confidence_predictions' => Prediction::whereRaw('JSON_EXTRACT(confidence_scores, "$.overall") > 0.8')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $accuracy,
        ]);
    }
}