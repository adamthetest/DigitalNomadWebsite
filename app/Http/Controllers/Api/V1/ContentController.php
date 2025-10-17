<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiGeneratedContent;
use App\Services\AiContentGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    protected AiContentGenerationService $contentService;

    public function __construct(AiContentGenerationService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Get all AI-generated content with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AiGeneratedContent::query();

        // Filter by content type
        if ($request->has('type')) {
            $query->where('content_type', $request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%'.$request->get('search').'%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 20), 100);
        $content = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $content->items(),
            'pagination' => [
                'current_page' => $content->currentPage(),
                'last_page' => $content->lastPage(),
                'per_page' => $content->perPage(),
                'total' => $content->total(),
            ],
        ]);
    }

    /**
     * Get a specific content item.
     */
    public function show(AiGeneratedContent $content): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $content->load('reviewer'),
        ]);
    }

    /**
     * Generate new content.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:newsletter,trending_destinations,top_cities,city_guide',
            'city_id' => 'required_if:type,city_guide|exists:cities,id',
            'year' => 'integer|min:2020|max:2030',
        ]);

        try {
            $content = match ($request->get('type')) {
                'newsletter' => $this->contentService->generateWeeklyNewsletter(),
                'trending_destinations' => $this->contentService->generateTrendingDestinationsPost(),
                'top_cities' => $this->contentService->generateTopCitiesBlogPost($request->get('year')),
                'city_guide' => $this->contentService->generateCityGuide(
                    \App\Models\City::findOrFail($request->get('city_id'))
                ),
            };

            if (! $content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate content',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Content generated successfully',
                'data' => $content,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating content: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update content status (review workflow).
     */
    public function updateStatus(Request $request, AiGeneratedContent $content): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $request->validate([
            'status' => 'required|string|in:draft,pending_review,approved,published,rejected',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $status = $request->get('status');
        $notes = $request->get('review_notes');

        switch ($status) {
            case 'approved':
                $content->approve($user, $notes);
                break;
            case 'rejected':
                if (empty($notes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Review notes are required when rejecting content',
                    ], 422);
                }
                $content->reject($user, $notes);
                break;
            case 'published':
                if ($content->status !== 'approved') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Content must be approved before publishing',
                    ], 422);
                }
                $content->markAsPublished();
                break;
            default:
                $content->update([
                    'status' => $status,
                    'review_notes' => $notes,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Content status updated successfully',
            'data' => $content->fresh(),
        ]);
    }

    /**
     * Update content details.
     */
    public function update(Request $request, AiGeneratedContent $content): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'categories' => 'nullable|array',
            'is_featured' => 'sometimes|boolean',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $content->update($request->only([
            'title',
            'content',
            'excerpt',
            'tags',
            'categories',
            'is_featured',
            'scheduled_at',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Content updated successfully',
            'data' => $content->fresh(),
        ]);
    }

    /**
     * Delete content.
     */
    public function destroy(AiGeneratedContent $content): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required',
            ], 403);
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully',
        ]);
    }

    /**
     * Get content statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_content' => AiGeneratedContent::count(),
            'published_content' => AiGeneratedContent::published()->count(),
            'draft_content' => AiGeneratedContent::draft()->count(),
            'pending_review' => AiGeneratedContent::pendingReview()->count(),
            'by_type' => AiGeneratedContent::selectRaw('content_type, COUNT(*) as count')
                ->groupBy('content_type')
                ->pluck('count', 'content_type'),
            'by_status' => AiGeneratedContent::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'this_month' => AiGeneratedContent::where('created_at', '>=', now()->startOfMonth())->count(),
            'this_week' => AiGeneratedContent::where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get published content for public consumption.
     */
    public function published(Request $request): JsonResponse
    {
        $query = AiGeneratedContent::published();

        // Filter by content type
        if ($request->has('type')) {
            $query->where('content_type', $request->get('type'));
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%'.$request->get('search').'%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 20), 100);
        $content = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $content->items(),
            'pagination' => [
                'current_page' => $content->currentPage(),
                'last_page' => $content->lastPage(),
                'per_page' => $content->perPage(),
                'total' => $content->total(),
            ],
        ]);
    }
}
