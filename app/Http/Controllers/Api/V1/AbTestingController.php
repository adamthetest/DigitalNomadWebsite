<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AbTest;
use App\Services\AbTestingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbTestingController extends Controller
{
    protected AbTestingService $abTestingService;

    public function __construct(AbTestingService $abTestingService)
    {
        $this->abTestingService = $abTestingService;
    }

    /**
     * Create a new A/B test.
     */
    public function createTest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'test_type' => 'required|string|in:content,layout,cta,feature',
            'target_element' => 'required|string|max:255',
            'variants' => 'required|array|min:2',
            'variants.*' => 'required|array',
            'traffic_allocation' => 'nullable|array',
            'success_metrics' => 'nullable|array',
            'targeting_rules' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $test = $this->abTestingService->createTest($request->all());

        return response()->json([
            'success' => true,
            'message' => 'A/B test created successfully',
            'data' => [
                'test_id' => $test->id,
                'name' => $test->name,
                'status' => $test->status,
            ],
        ], 201);
    }

    /**
     * Start an A/B test.
     */
    public function startTest(int $testId): JsonResponse
    {
        $success = $this->abTestingService->startTest($testId);

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start A/B test',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'A/B test started successfully',
        ]);
    }

    /**
     * Get variant for current user.
     */
    public function getUserVariant(int $testId): JsonResponse
    {
        $userId = auth()->id();
        $variant = $this->abTestingService->getVariantForUser($testId, $userId);

        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'No variant available for this test',
            ], 404);
        }

        $test = AbTest::find($testId);
        $variantData = $test->variants[$variant] ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'test_id' => $testId,
                'variant' => $variant,
                'variant_data' => $variantData,
            ],
        ]);
    }

    /**
     * Track conversion for A/B test.
     */
    public function trackConversion(Request $request, int $testId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'variant' => 'required|string',
            'conversion_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $this->abTestingService->trackConversion(
            $testId,
            $userId,
            $request->variant,
            $request->conversion_data ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversion tracked successfully',
        ]);
    }

    /**
     * Track event for A/B test.
     */
    public function trackEvent(Request $request, int $testId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'variant' => 'required|string',
            'event_type' => 'required|string|max:50',
            'event_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();
        $this->abTestingService->trackEvent(
            $testId,
            $userId,
            $request->variant,
            $request->event_type,
            $request->event_data ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Event tracked successfully',
        ]);
    }

    /**
     * Get A/B test results.
     */
    public function getTestResults(int $testId): JsonResponse
    {
        $results = $this->abTestingService->analyzeTestResults($testId);

        if (isset($results['error'])) {
            return response()->json([
                'success' => false,
                'message' => $results['error'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Complete A/B test.
     */
    public function completeTest(Request $request, int $testId): JsonResponse
    {
        $forceComplete = $request->query('force', false);
        $result = $this->abTestingService->completeTest($testId, $forceComplete);

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get active tests for current user.
     */
    public function getActiveTestsForUser(): JsonResponse
    {
        $userId = auth()->id();
        $tests = $this->abTestingService->getActiveTestsForUser($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $userId,
                'active_tests' => $tests,
            ],
        ]);
    }

    /**
     * Generate AI-powered test variants.
     */
    public function generateAiVariants(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'test_type' => 'required|string|in:content,layout,cta,feature',
            'target_element' => 'required|string|max:255',
            'base_content' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $variants = $this->abTestingService->generateAiVariants(
            $request->test_type,
            $request->target_element,
            $request->base_content
        );

        return response()->json([
            'success' => true,
            'data' => [
                'test_type' => $request->test_type,
                'target_element' => $request->target_element,
                'variants' => $variants,
            ],
        ]);
    }

    /**
     * Get all A/B tests.
     */
    public function getAllTests(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $testType = $request->query('test_type');
        $limit = $request->query('limit', 20);

        $query = AbTest::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($testType) {
            $query->where('test_type', $testType);
        }

        $tests = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($test) {
                return [
                    'id' => $test->id,
                    'name' => $test->name,
                    'description' => $test->description,
                    'test_type' => $test->test_type,
                    'target_element' => $test->target_element,
                    'status' => $test->status,
                    'start_date' => $test->start_date,
                    'end_date' => $test->end_date,
                    'winner_variant' => $test->winner_variant,
                    'confidence_level' => $test->confidence_level,
                    'created_at' => $test->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'tests' => $tests,
                'total' => $tests->count(),
            ],
        ]);
    }

    /**
     * Get A/B test statistics.
     */
    public function getTestStatistics(): JsonResponse
    {
        $stats = [
            'total_tests' => AbTest::count(),
            'active_tests' => AbTest::active()->count(),
            'completed_tests' => AbTest::completed()->count(),
            'draft_tests' => AbTest::where('status', 'draft')->count(),
            'tests_by_type' => AbTest::selectRaw('test_type, COUNT(*) as count')
                ->groupBy('test_type')
                ->pluck('count', 'test_type'),
            'avg_confidence_level' => AbTest::whereNotNull('confidence_level')->avg('confidence_level'),
            'recent_tests' => AbTest::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
