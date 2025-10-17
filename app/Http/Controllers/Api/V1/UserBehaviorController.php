<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserBehaviorAnalytic;
use App\Services\UserBehaviorAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserBehaviorController extends Controller
{
    protected UserBehaviorAnalysisService $behaviorAnalysisService;

    public function __construct(UserBehaviorAnalysisService $behaviorAnalysisService)
    {
        $this->behaviorAnalysisService = $behaviorAnalysisService;
    }

    /**
     * Track a user behavior event.
     */
    public function trackEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_type' => 'required|string|max:50',
            'entity_type' => 'nullable|string|max:50',
            'entity_id' => 'nullable|integer',
            'event_data' => 'nullable|array',
            'user_context' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $analytic = $this->behaviorAnalysisService->trackEvent(
            $request->event_type,
            auth()->id(),
            $request->session_id,
            $request->entity_type,
            $request->entity_id,
            $request->event_data ?? [],
            $request->user_context ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Event tracked successfully',
            'data' => [
                'analytic_id' => $analytic->id,
                'engagement_score' => $analytic->engagement_score,
            ],
        ]);
    }

    /**
     * Get user behavior analysis.
     */
    public function getUserBehaviorAnalysis(Request $request, int $userId): JsonResponse
    {
        $days = $request->query('days', 30);
        $days = max(1, min(365, $days)); // Limit between 1 and 365 days

        $analysis = $this->behaviorAnalysisService->analyzeUserBehavior($userId, $days);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    /**
     * Get user engagement score.
     */
    public function getUserEngagementScore(Request $request, int $userId): JsonResponse
    {
        $days = $request->query('days', 7);
        $days = max(1, min(30, $days)); // Limit between 1 and 30 days

        $score = $this->behaviorAnalysisService->getUserEngagementScore($userId, $days);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $userId,
                'engagement_score' => $score,
                'period_days' => $days,
            ],
        ]);
    }

    /**
     * Predict user churn probability.
     */
    public function predictChurnProbability(int $userId): JsonResponse
    {
        $prediction = $this->behaviorAnalysisService->predictChurnProbability($userId);

        return response()->json([
            'success' => true,
            'data' => $prediction,
        ]);
    }

    /**
     * Get user journey analysis.
     */
    public function getUserJourney(int $userId): JsonResponse
    {
        $journey = $this->behaviorAnalysisService->analyzeUserJourney($userId);

        return response()->json([
            'success' => true,
            'data' => $journey,
        ]);
    }

    /**
     * Get behavior analytics statistics.
     */
    public function getBehaviorStatistics(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $days = max(1, min(365, $days));

        $startDate = now()->subDays($days);
        $endDate = now();

        $stats = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => $days,
            ],
            'total_events' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)->count(),
            'unique_users' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)->distinct('user_id')->count(),
            'unique_sessions' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)->distinct('session_id')->count(),
            'event_types' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)
                ->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'event_type'),
            'entity_types' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)
                ->whereNotNull('entity_type')
                ->selectRaw('entity_type, COUNT(*) as count')
                ->groupBy('entity_type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'entity_type'),
            'avg_engagement_score' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)->avg('engagement_score'),
            'top_engaging_events' => UserBehaviorAnalytic::byDateRange($startDate, $endDate)
                ->selectRaw('event_type, AVG(engagement_score) as avg_score')
                ->groupBy('event_type')
                ->orderBy('avg_score', 'desc')
                ->limit(10)
                ->get()
                ->pluck('avg_score', 'event_type'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get user behavior trends.
     */
    public function getBehaviorTrends(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $days = max(1, min(365, $days));

        $trends = [];
        $startDate = now()->subDays($days);

        // Daily trends
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $nextDate = $date->copy()->addDay();

            $dailyStats = UserBehaviorAnalytic::byDateRange($date, $nextDate)
                ->selectRaw('COUNT(*) as events, COUNT(DISTINCT user_id) as users, AVG(engagement_score) as avg_engagement')
                ->first();

            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'events' => $dailyStats->events ?? 0,
                'users' => $dailyStats->users ?? 0,
                'avg_engagement' => round($dailyStats->avg_engagement ?? 0, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'period_days' => $days,
                'daily_trends' => $trends,
            ],
        ]);
    }

    /**
     * Get top performing content.
     */
    public function getTopPerformingContent(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $entityType = $request->query('entity_type');
        $limit = $request->query('limit', 20);

        $days = max(1, min(365, $days));
        $limit = max(1, min(100, $limit));

        $startDate = now()->subDays($days);
        $endDate = now();

        $query = UserBehaviorAnalytic::byDateRange($startDate, $endDate)
            ->whereNotNull('entity_type')
            ->whereNotNull('entity_id');

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        $topContent = $query
            ->selectRaw('entity_type, entity_id, COUNT(*) as interactions, AVG(engagement_score) as avg_engagement')
            ->groupBy('entity_type', 'entity_id')
            ->orderBy('interactions', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period_days' => $days,
                'entity_type_filter' => $entityType,
                'top_content' => $topContent->map(function ($item) {
                    return [
                        'entity_type' => $item->entity_type,
                        'entity_id' => $item->entity_id,
                        'interactions' => $item->interactions,
                        'avg_engagement' => round($item->avg_engagement, 2),
                    ];
                }),
            ],
        ]);
    }
}
