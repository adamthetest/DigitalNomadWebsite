<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Display a listing of jobs with AI-ready data.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Job::with(['company'])
            ->active()
            ->published()
            ->notExpired();

        // Filter by job type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by remote type
        if ($request->has('remote_type')) {
            $query->byRemoteType($request->remote_type);
        }

        // Filter by salary range
        if ($request->has('salary_min') || $request->has('salary_max')) {
            $query->bySalaryRange(
                $request->get('salary_min', 0),
                $request->get('salary_max')
            );
        }

        // Filter by tags/skills
        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : [$request->tags];
            $query->byTags($tags);
        }

        // Filter by visa support
        if ($request->has('visa_support')) {
            if ($request->boolean('visa_support')) {
                $query->visaFriendly();
            }
        }

        // Filter by experience level
        if ($request->has('experience_level')) {
            $levels = is_array($request->experience_level) ? $request->experience_level : [$request->experience_level];
            $query->where(function ($q) use ($levels) {
                foreach ($levels as $level) {
                    $q->orWhereJsonContains('experience_level', $level);
                }
            });
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter by timezone
        if ($request->has('timezone')) {
            $query->where('timezone', $request->timezone);
        }

        // Search by title or description
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('requirements', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by featured jobs
        if ($request->has('featured')) {
            $query->featured();
        }

        // Filter by recent jobs
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'salary':
                $query->orderBy('salary_max', $sortOrder);
                break;
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'company':
                $query->orderBy('company_id', $sortOrder);
                break;
            case 'featured':
                $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'from' => $jobs->firstItem(),
                'to' => $jobs->lastItem(),
            ],
        ]);
    }

    /**
     * Display the specified job with AI-ready data.
     */
    public function show(Job $job): JsonResponse
    {
        $job->load(['company']);

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Get AI context data for a job.
     */
    public function aiContext(Job $job): JsonResponse
    {
        $aiContext = $job->aiContexts()->latest()->first();

        if (!$aiContext) {
            return response()->json([
                'success' => false,
                'message' => 'No AI context data available for this job',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'ai_summary' => $aiContext->ai_summary,
                'ai_tags' => $aiContext->ai_tags,
                'ai_insights' => $aiContext->ai_insights,
                'last_updated' => $aiContext->last_ai_update,
                'model_version' => $aiContext->ai_model_version,
            ],
        ]);
    }

    /**
     * Get job recommendations for a specific user profile.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $request->validate([
            'skills' => 'nullable|array',
            'experience_level' => 'nullable|array',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'remote_type' => 'nullable|string',
            'visa_support' => 'nullable|boolean',
            'timezone' => 'nullable|string',
        ]);

        $query = Job::with(['company'])
            ->active()
            ->published()
            ->notExpired();

        // Match skills
        if ($request->has('skills')) {
            $query->byTags($request->skills);
        }

        // Match experience level
        if ($request->has('experience_level')) {
            $query->where(function ($q) use ($request) {
                foreach ($request->experience_level as $level) {
                    $q->orWhereJsonContains('experience_level', $level);
                }
            });
        }

        // Salary range
        if ($request->has('salary_min') || $request->has('salary_max')) {
            $query->bySalaryRange(
                $request->get('salary_min', 0),
                $request->get('salary_max')
            );
        }

        // Remote type preference
        if ($request->has('remote_type')) {
            $query->byRemoteType($request->remote_type);
        }

        // Visa support
        if ($request->has('visa_support') && $request->boolean('visa_support')) {
            $query->visaFriendly();
        }

        // Timezone preference
        if ($request->has('timezone')) {
            $query->where(function ($q) use ($request) {
                $q->where('timezone', $request->timezone)
                  ->orWhereNull('timezone')
                  ->orWhere('remote_type', 'fully-remote');
            });
        }

        $jobs = $query->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
            'recommendations_count' => $jobs->count(),
        ]);
    }

    /**
     * Get job statistics for AI analysis.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_jobs' => Job::active()->count(),
            'jobs_by_type' => Job::active()->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'jobs_by_remote_type' => Job::active()->selectRaw('remote_type, COUNT(*) as count')
                ->groupBy('remote_type')
                ->pluck('count', 'remote_type'),
            'visa_support_percentage' => round(
                Job::active()->where('visa_support', true)->count() / 
                Job::active()->count() * 100, 2
            ),
            'average_salary' => Job::active()
                ->whereNotNull('salary_max')
                ->avg('salary_max'),
            'recent_jobs_count' => Job::recent(7)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}