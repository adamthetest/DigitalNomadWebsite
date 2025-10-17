<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users with AI-ready data.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by public profiles only for non-admin users
        if (!auth()->user() || !auth()->user()->is_admin) {
            $query->public();
        }

        // Filter by location
        if ($request->has('location')) {
            $query->byLocation($request->location);
        }

        // Filter by skills
        if ($request->has('skills')) {
            $skills = is_array($request->skills) ? $request->skills : [$request->skills];
            $query->bySkills($skills);
        }

        // Filter by work type
        if ($request->has('work_type')) {
            $query->byWorkType($request->work_type);
        }

        // Filter by premium status
        if ($request->has('premium')) {
            $query->premium();
        }

        // Filter by verified users
        if ($request->has('verified')) {
            $query->verified();
        }

        // Filter by experience level
        if ($request->has('experience_years')) {
            $query->where('experience_years', '>=', $request->experience_years);
        }

        // Filter by budget range
        if ($request->has('budget_min') || $request->has('budget_max')) {
            $query->where(function ($q) use ($request) {
                if ($request->has('budget_min')) {
                    $q->where('budget_monthly_min', '>=', $request->budget_min);
                }
                if ($request->has('budget_max')) {
                    $q->where('budget_monthly_max', '<=', $request->budget_max);
                }
            });
        }

        // Filter by preferred climates
        if ($request->has('preferred_climates')) {
            $query->where(function ($q) use ($request) {
                foreach ($request->preferred_climates as $climate) {
                    $q->orWhereJsonContains('preferred_climates', $climate);
                }
            });
        }

        // Search by name, bio, or job title
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('bio', 'like', '%' . $searchTerm . '%')
                  ->orWhere('job_title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('tagline', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'last_active':
                $query->orderBy('last_active', $sortOrder);
                break;
            case 'experience':
                $query->orderBy('experience_years', $sortOrder);
                break;
            case 'premium':
                $query->orderBy('premium_status', 'desc')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $users = $query->paginate($perPage);

        // Remove sensitive data for non-admin users
        if (!auth()->user() || !auth()->user()->is_admin) {
            $users->getCollection()->transform(function ($user) {
                return $this->sanitizeUserData($user);
            });
        }

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    /**
     * Display the specified user with AI-ready data.
     */
    public function show(User $user): JsonResponse
    {
        // Check if user can view this profile
        if (!$this->canViewUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not accessible',
            ], 403);
        }

        $userData = $this->sanitizeUserData($user);

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    /**
     * Get AI context data for a user.
     */
    public function aiContext(User $user): JsonResponse
    {
        // Only allow users to access their own AI context or admin users
        if (!auth()->user() || (auth()->id() !== $user->id && !auth()->user()->is_admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $aiContext = $user->aiContexts()->latest()->first();

        if (!$aiContext) {
            return response()->json([
                'success' => false,
                'message' => 'No AI context data available for this user',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'ai_summary' => $aiContext->ai_summary,
                'ai_tags' => $aiContext->ai_tags,
                'ai_insights' => $aiContext->ai_insights,
                'last_updated' => $aiContext->last_ai_update,
                'model_version' => $aiContext->ai_model_version,
            ],
        ]);
    }

    /**
     * Get user recommendations based on AI analysis.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'recommendation_type' => 'nullable|in:cities,jobs,users',
        ]);

        $user = User::findOrFail($request->user_id);

        // Only allow users to get their own recommendations or admin users
        if (!auth()->user() || (auth()->id() !== $user->id && !auth()->user()->is_admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $recommendations = [];

        switch ($request->get('recommendation_type', 'cities')) {
            case 'cities':
                $recommendations = $this->getCityRecommendations($user);
                break;
            case 'jobs':
                $recommendations = $this->getJobRecommendations($user);
                break;
            case 'users':
                $recommendations = $this->getUserRecommendations($user);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $recommendations,
            'recommendation_type' => $request->get('recommendation_type', 'cities'),
        ]);
    }

    /**
     * Get user statistics for AI analysis.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'public_profiles' => User::public()->count(),
            'premium_users' => User::premium()->count(),
            'verified_users' => User::verified()->count(),
            'users_by_work_type' => User::selectRaw('work_type, COUNT(*) as count')
                ->whereNotNull('work_type')
                ->groupBy('work_type')
                ->pluck('count', 'work_type'),
            'users_by_experience' => User::selectRaw('
                CASE 
                    WHEN experience_years < 2 THEN "entry"
                    WHEN experience_years < 5 THEN "mid"
                    WHEN experience_years < 10 THEN "senior"
                    ELSE "expert"
                END as level, 
                COUNT(*) as count
            ')
                ->whereNotNull('experience_years')
                ->groupBy('level')
                ->pluck('count', 'level'),
            'ai_consent_percentage' => round(
                User::where('ai_data_collection_consent', true)->count() / 
                User::count() * 100, 2
            ),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Check if current user can view the specified user's profile.
     */
    private function canViewUser(User $user): bool
    {
        // Admin users can view all profiles
        if (auth()->user() && auth()->user()->is_admin) {
            return true;
        }

        // Users can always view their own profile
        if (auth()->user() && auth()->id() === $user->id) {
            return true;
        }

        // Check visibility settings
        return $user->visibility === 'public' || 
               ($user->visibility === 'members' && auth()->check());
    }

    /**
     * Sanitize user data for public consumption.
     */
    private function sanitizeUserData(User $user): array
    {
        $data = $user->toArray();
        
        // Remove sensitive fields
        unset($data['email'], $data['password'], $data['remember_token']);
        
        // Remove AI-specific sensitive data unless user is admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            unset($data['ai_preferences_vector'], $data['data_sharing_preferences']);
        }

        return $data;
    }

    /**
     * Get city recommendations for a user.
     */
    private function getCityRecommendations(User $user): array
    {
        // This would integrate with AI service to get personalized city recommendations
        // For now, return basic recommendations based on user preferences
        return [
            'message' => 'City recommendations would be generated by AI based on user profile',
            'user_preferences' => [
                'budget_range' => [$user->budget_monthly_min, $user->budget_monthly_max],
                'preferred_climates' => $user->preferred_climates,
                'work_requirements' => [
                    'min_internet_speed' => $user->min_internet_speed_mbps,
                    'requires_stable_internet' => $user->requires_stable_internet,
                ],
            ],
        ];
    }

    /**
     * Get job recommendations for a user.
     */
    private function getJobRecommendations(User $user): array
    {
        return [
            'message' => 'Job recommendations would be generated by AI based on user skills and preferences',
            'user_skills' => $user->technical_skills,
            'experience_level' => $user->experience_years,
        ];
    }

    /**
     * Get user recommendations for networking.
     */
    private function getUserRecommendations(User $user): array
    {
        return [
            'message' => 'User recommendations would be generated by AI for networking opportunities',
            'user_profile' => [
                'skills' => $user->technical_skills,
                'location' => $user->location_current,
                'work_type' => $user->work_type,
            ],
        ];
    }
}