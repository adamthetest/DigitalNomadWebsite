<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RecommendationEngine;
use App\Services\SmartRecommendationEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecommendationController extends Controller
{
    protected SmartRecommendationEngineService $recommendationService;

    public function __construct(SmartRecommendationEngineService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Get personalized recommendations for current user.
     */
    public function getPersonalizedRecommendations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'nullable|string|in:cities,jobs,articles,mixed',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $entityType = $request->query('entity_type', 'mixed');
        $limit = $request->query('limit', 10);

        $recommendations = $this->recommendationService->getPersonalizedRecommendations(
            $userId,
            $entityType,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Get collaborative filtering recommendations.
     */
    public function getCollaborativeFilteringRecommendations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:cities,jobs,articles',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $entityType = $request->query('entity_type');
        $limit = $request->query('limit', 10);

        $recommendations = $this->recommendationService->getCollaborativeFilteringRecommendations(
            $userId,
            $entityType,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Get content-based filtering recommendations.
     */
    public function getContentBasedRecommendations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:cities,jobs,articles',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $entityType = $request->query('entity_type');
        $limit = $request->query('limit', 10);

        $recommendations = $this->recommendationService->getContentBasedRecommendations(
            $userId,
            $entityType,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Get hybrid recommendations.
     */
    public function getHybridRecommendations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:cities,jobs,articles',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $entityType = $request->query('entity_type');
        $limit = $request->query('limit', 10);

        $recommendations = $this->recommendationService->getHybridRecommendations(
            $userId,
            $entityType,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Train recommendation engine.
     */
    public function trainRecommendationEngine(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:cities,jobs,articles',
            'days' => 'nullable|integer|min:7|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $entityType = $request->query('entity_type');
        $days = $request->query('days', 30);

        $result = $this->recommendationService->trainRecommendationEngine($entityType, $days);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get recommendation engine status.
     */
    public function getRecommendationEngineStatus(Request $request): JsonResponse
    {
        $entityType = $request->query('entity_type');

        $query = RecommendationEngine::query();

        if ($entityType) {
            $query->where('target_entity', $entityType);
        }

        $engines = $query->get()->map(function ($engine) {
            return [
                'id' => $engine->id,
                'name' => $engine->name,
                'description' => $engine->description,
                'engine_type' => $engine->engine_type,
                'target_entity' => $engine->target_entity,
                'status' => $engine->status,
                'accuracy_score' => $engine->accuracy_score,
                'recommendation_count' => $engine->recommendation_count,
                'click_through_rate' => $engine->click_through_rate,
                'conversion_rate' => $engine->conversion_rate,
                'last_trained_at' => $engine->last_trained_at,
                'last_used_at' => $engine->last_used_at,
                'performance_score' => $engine->getPerformanceScore(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'engines' => $engines,
                'total' => $engines->count(),
            ],
        ]);
    }

    /**
     * Get recommendation statistics.
     */
    public function getRecommendationStatistics(): JsonResponse
    {
        $stats = [
            'total_engines' => RecommendationEngine::count(),
            'active_engines' => RecommendationEngine::active()->count(),
            'engines_by_type' => RecommendationEngine::selectRaw('engine_type, COUNT(*) as count')
                ->groupBy('engine_type')
                ->pluck('count', 'engine_type'),
            'engines_by_entity' => RecommendationEngine::selectRaw('target_entity, COUNT(*) as count')
                ->groupBy('target_entity')
                ->pluck('count', 'target_entity'),
            'avg_accuracy_score' => RecommendationEngine::whereNotNull('accuracy_score')->avg('accuracy_score'),
            'avg_click_through_rate' => RecommendationEngine::whereNotNull('click_through_rate')->avg('click_through_rate'),
            'avg_conversion_rate' => RecommendationEngine::whereNotNull('conversion_rate')->avg('conversion_rate'),
            'total_recommendations' => RecommendationEngine::sum('recommendation_count'),
            'engines_needing_retraining' => RecommendationEngine::where(function ($query) {
                $query->whereNull('last_trained_at')
                    ->orWhere('last_trained_at', '<', now()->subDays(30));
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get recommendation performance metrics.
     */
    public function getRecommendationPerformance(Request $request): JsonResponse
    {
        $entityType = $request->query('entity_type');
        $days = $request->query('days', 30);

        $query = RecommendationEngine::query();

        if ($entityType) {
            $query->where('target_entity', $entityType);
        }

        $engines = $query->get();

        $performance = $engines->map(function ($engine) {
            return [
                'engine_id' => $engine->id,
                'name' => $engine->name,
                'target_entity' => $engine->target_entity,
                'performance_score' => $engine->getPerformanceScore(),
                'is_performing_well' => $engine->isPerformingWell(),
                'needs_retraining' => $engine->needsRetraining(),
                'accuracy_score' => $engine->accuracy_score,
                'click_through_rate' => $engine->click_through_rate,
                'conversion_rate' => $engine->conversion_rate,
                'recommendation_count' => $engine->recommendation_count,
                'last_trained_at' => $engine->last_trained_at,
                'last_used_at' => $engine->last_used_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'period_days' => $days,
                'entity_type_filter' => $entityType,
                'performance' => $performance,
                'summary' => [
                    'total_engines' => $engines->count(),
                    'performing_well' => $engines->filter(fn ($e) => $e->isPerformingWell())->count(),
                    'needing_retraining' => $engines->filter(fn ($e) => $e->needsRetraining())->count(),
                    'avg_performance_score' => $engines->avg(fn ($e) => $e->getPerformanceScore()),
                ],
            ],
        ]);
    }

    /**
     * Update recommendation engine metrics.
     */
    public function updateRecommendationMetrics(Request $request, int $engineId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recommendations' => 'required|integer|min:0',
            'clicks' => 'nullable|integer|min:0',
            'conversions' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $engine = RecommendationEngine::find($engineId);
        if (! $engine) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation engine not found',
            ], 404);
        }

        $engine->updateMetrics(
            $request->recommendations,
            $request->clicks ?? 0,
            $request->conversions ?? 0
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation metrics updated successfully',
            'data' => [
                'engine_id' => $engineId,
                'updated_metrics' => [
                    'recommendation_count' => $engine->recommendation_count,
                    'click_through_rate' => $engine->click_through_rate,
                    'conversion_rate' => $engine->conversion_rate,
                ],
            ],
        ]);
    }

    /**
     * Get recommendation engine configuration.
     */
    public function getRecommendationEngineConfig(int $engineId): JsonResponse
    {
        $engine = RecommendationEngine::find($engineId);
        if (! $engine) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation engine not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'engine_id' => $engineId,
                'name' => $engine->name,
                'description' => $engine->description,
                'engine_type' => $engine->engine_type,
                'target_entity' => $engine->target_entity,
                'algorithm_config' => $engine->algorithm_config,
                'feature_weights' => $engine->feature_weights,
                'status' => $engine->status,
            ],
        ]);
    }

    /**
     * Update recommendation engine configuration.
     */
    public function updateRecommendationEngineConfig(Request $request, int $engineId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'algorithm_config' => 'nullable|array',
            'feature_weights' => 'nullable|array',
            'status' => 'nullable|string|in:training,active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $engine = RecommendationEngine::find($engineId);
        if (! $engine) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendation engine not found',
            ], 404);
        }

        $updateData = [];
        if ($request->has('algorithm_config')) {
            $updateData['algorithm_config'] = $request->algorithm_config;
        }
        if ($request->has('feature_weights')) {
            $updateData['feature_weights'] = $request->feature_weights;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        $engine->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Recommendation engine configuration updated successfully',
            'data' => [
                'engine_id' => $engineId,
                'updated_config' => $updateData,
            ],
        ]);
    }
}
