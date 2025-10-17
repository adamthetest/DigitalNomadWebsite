<?php

use App\Http\Controllers\Api\V1\AbTestingController;
use App\Http\Controllers\Api\V1\AiAdvisorController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\JobMatchingController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\UserBehaviorController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1 routes
Route::prefix('v1')->group(function () {

    // Cities API
    Route::prefix('cities')->group(function () {
        Route::get('/', [CityController::class, 'index']);
        Route::get('/recommendations', [CityController::class, 'recommendations']);
        Route::get('/{city}', [CityController::class, 'show']);
        Route::get('/{city}/ai-context', [CityController::class, 'aiContext']);
    });

    // Jobs API
    Route::prefix('jobs')->group(function () {
        Route::get('/', [JobController::class, 'index']);
        Route::get('/recommendations', [JobController::class, 'recommendations']);
        Route::get('/statistics', [JobController::class, 'statistics']);
        Route::get('/{job}', [JobController::class, 'show']);
        Route::get('/{job}/ai-context', [JobController::class, 'aiContext']);
    });

    // Users API
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/recommendations', [UserController::class, 'recommendations']);
        Route::get('/statistics', [UserController::class, 'statistics']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::get('/{user}/ai-context', [UserController::class, 'aiContext']);
    });

    // AI Advisor API (requires authentication) - Phase 2
    Route::prefix('ai-advisor')->middleware('auth:sanctum')->group(function () {
        Route::get('/city-recommendations', [AiAdvisorController::class, 'getCityRecommendations']);
        Route::get('/city/{city}/summary', [AiAdvisorController::class, 'getCitySummary']);
        Route::get('/city/{city}/insights', [AiAdvisorController::class, 'getCityInsights']);
        Route::post('/compare-cities', [AiAdvisorController::class, 'compareCities']);
    });

    // Job Matching API (requires authentication) - Phase 3
    Route::middleware('auth:sanctum')->prefix('job-matching')->group(function () {
        Route::get('/recommendations', [JobMatchingController::class, 'getRecommendations']);
        Route::get('/match-history', [JobMatchingController::class, 'getMatchHistory']);
        Route::post('/upload-resume', [JobMatchingController::class, 'uploadResume']);

        Route::get('/jobs/{job}/match-analysis', [JobMatchingController::class, 'getMatchAnalysis']);
        Route::post('/jobs/{job}/optimize-resume', [JobMatchingController::class, 'optimizeResume']);
        Route::get('/jobs/{job}/generate-cover-letter', [JobMatchingController::class, 'generateCoverLetter']);
        Route::get('/jobs/{job}/extract-skills', [JobMatchingController::class, 'extractSkills']);

        Route::post('/matches/{jobMatch}/viewed', [JobMatchingController::class, 'markAsViewed']);
        Route::post('/matches/{jobMatch}/applied', [JobMatchingController::class, 'markAsApplied']);
        Route::post('/matches/{jobMatch}/saved', [JobMatchingController::class, 'markAsSaved']);
    });

    // Content Management API (requires authentication) - Phase 4
    Route::middleware('auth:sanctum')->prefix('content')->group(function () {
        Route::get('/', [ContentController::class, 'index']);
        Route::get('/statistics', [ContentController::class, 'statistics']);
        Route::post('/generate', [ContentController::class, 'generate']);
        Route::get('/{content}', [ContentController::class, 'show']);
        Route::put('/{content}/status', [ContentController::class, 'updateStatus']);
        Route::put('/{content}', [ContentController::class, 'update']);
        Route::delete('/{content}', [ContentController::class, 'destroy']);
    });

    // Public Content API - Phase 4
    Route::prefix('content')->group(function () {
        Route::get('/published', [ContentController::class, 'published']);
    });

    // Analytics API (requires authentication) - Phase 5
    Route::middleware('auth:sanctum')->prefix('analytics')->group(function () {
        Route::get('/forecasted-metrics', [AnalyticsController::class, 'getForecastedMetrics']);
        Route::get('/cost-trend-predictions', [AnalyticsController::class, 'getCostTrendPredictions']);
        Route::get('/trending-cities-predictions', [AnalyticsController::class, 'getTrendingCitiesPredictions']);
        Route::get('/user-growth-predictions', [AnalyticsController::class, 'getUserGrowthPredictions']);
        Route::get('/daily-metrics', [AnalyticsController::class, 'getDailyMetrics']);
        Route::get('/performance-summary', [AnalyticsController::class, 'generatePerformanceSummary']);
        Route::get('/statistics', [AnalyticsController::class, 'getAnalyticsStatistics']);
        Route::post('/process', [AnalyticsController::class, 'triggerAnalyticsProcessing']);
        Route::get('/prediction-accuracy', [AnalyticsController::class, 'getPredictionAccuracy']);
    });

    // User Behavior Analysis API (requires authentication) - Phase 7
    Route::middleware('auth:sanctum')->prefix('behavior')->group(function () {
        Route::post('/track-event', [UserBehaviorController::class, 'trackEvent']);
        Route::get('/analysis/{userId}', [UserBehaviorController::class, 'getUserBehaviorAnalysis']);
        Route::get('/engagement-score/{userId}', [UserBehaviorController::class, 'getUserEngagementScore']);
        Route::get('/churn-prediction/{userId}', [UserBehaviorController::class, 'predictChurnProbability']);
        Route::get('/journey/{userId}', [UserBehaviorController::class, 'getUserJourney']);
        Route::get('/statistics', [UserBehaviorController::class, 'getBehaviorStatistics']);
        Route::get('/trends', [UserBehaviorController::class, 'getBehaviorTrends']);
        Route::get('/top-content', [UserBehaviorController::class, 'getTopPerformingContent']);
    });

    // A/B Testing API (requires authentication) - Phase 7
    Route::middleware('auth:sanctum')->prefix('ab-testing')->group(function () {
        Route::post('/tests', [AbTestingController::class, 'createTest']);
        Route::post('/tests/{testId}/start', [AbTestingController::class, 'startTest']);
        Route::get('/tests/{testId}/variant', [AbTestingController::class, 'getUserVariant']);
        Route::post('/tests/{testId}/conversion', [AbTestingController::class, 'trackConversion']);
        Route::post('/tests/{testId}/event', [AbTestingController::class, 'trackEvent']);
        Route::get('/tests/{testId}/results', [AbTestingController::class, 'getTestResults']);
        Route::post('/tests/{testId}/complete', [AbTestingController::class, 'completeTest']);
        Route::get('/tests', [AbTestingController::class, 'getAllTests']);
        Route::get('/tests/active', [AbTestingController::class, 'getActiveTestsForUser']);
        Route::post('/generate-variants', [AbTestingController::class, 'generateAiVariants']);
        Route::get('/statistics', [AbTestingController::class, 'getTestStatistics']);
    });

    // Recommendation Engine API (requires authentication) - Phase 7
    Route::middleware('auth:sanctum')->prefix('recommendations')->group(function () {
        Route::get('/personalized', [RecommendationController::class, 'getPersonalizedRecommendations']);
        Route::get('/collaborative', [RecommendationController::class, 'getCollaborativeFilteringRecommendations']);
        Route::get('/content-based', [RecommendationController::class, 'getContentBasedRecommendations']);
        Route::get('/hybrid', [RecommendationController::class, 'getHybridRecommendations']);
        Route::post('/train', [RecommendationController::class, 'trainRecommendationEngine']);
        Route::get('/engines', [RecommendationController::class, 'getRecommendationEngineStatus']);
        Route::get('/statistics', [RecommendationController::class, 'getRecommendationStatistics']);
        Route::get('/performance', [RecommendationController::class, 'getRecommendationPerformance']);
        Route::post('/engines/{engineId}/metrics', [RecommendationController::class, 'updateRecommendationMetrics']);
        Route::get('/engines/{engineId}/config', [RecommendationController::class, 'getRecommendationEngineConfig']);
        Route::put('/engines/{engineId}/config', [RecommendationController::class, 'updateRecommendationEngineConfig']);
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});
