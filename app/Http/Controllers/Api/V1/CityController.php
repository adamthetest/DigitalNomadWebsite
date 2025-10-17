<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of cities with AI-ready data.
     */
    public function index(Request $request): JsonResponse
    {
        $query = City::with(['country', 'costItems', 'coworkingSpaces'])
            ->where('is_active', true);

        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Filter by featured cities
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Filter by budget range
        if ($request->has('budget_min') || $request->has('budget_max')) {
            $query->where(function ($q) use ($request) {
                if ($request->has('budget_min')) {
                    $q->where('cost_of_living_index', '>=', $request->budget_min);
                }
                if ($request->has('budget_max')) {
                    $q->where('cost_of_living_index', '<=', $request->budget_max);
                }
            });
        }

        // Filter by internet speed
        if ($request->has('min_internet_speed')) {
            $query->where('internet_speed_mbps', '>=', $request->min_internet_speed);
        }

        // Filter by safety score
        if ($request->has('min_safety_score')) {
            $query->where('safety_score', '>=', $request->min_safety_score);
        }

        // Filter by climate
        if ($request->has('climate')) {
            $query->where('climate', 'like', '%'.$request->climate.'%');
        }

        // Filter by visa options
        if ($request->has('visa_type')) {
            $query->whereJsonContains('visa_options', $request->visa_type);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'cost':
                $query->orderBy('cost_of_living_index', $sortOrder);
                break;
            case 'internet':
                $query->orderBy('internet_speed_mbps', $sortOrder);
                break;
            case 'safety':
                $query->orderBy('safety_score', $sortOrder);
                break;
            case 'population':
                $query->orderBy('population', $sortOrder);
                break;
            default:
                $query->orderBy('name', $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $cities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $cities->items(),
            'pagination' => [
                'current_page' => $cities->currentPage(),
                'last_page' => $cities->lastPage(),
                'per_page' => $cities->perPage(),
                'total' => $cities->total(),
                'from' => $cities->firstItem(),
                'to' => $cities->lastItem(),
            ],
        ]);
    }

    /**
     * Display the specified city with AI-ready data.
     */
    public function show(City $city): JsonResponse
    {
        $city->load([
            'country',
            'costItems',
            'coworkingSpaces',
            'neighborhoods',
            'articles' => function ($query) {
                $query->where('status', 'published');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $city,
        ]);
    }

    /**
     * Get AI context data for a city.
     */
    public function aiContext(City $city): JsonResponse
    {
        $aiContext = $city->aiContexts()->latest()->first();

        if (! $aiContext) {
            return response()->json([
                'success' => false,
                'message' => 'No AI context data available for this city',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'city_id' => $city->id,
                'city_name' => $city->name,
                'ai_summary' => $aiContext->ai_summary,
                'ai_tags' => $aiContext->ai_tags,
                'ai_insights' => $aiContext->ai_insights,
                'last_updated' => $aiContext->last_ai_update,
                'model_version' => $aiContext->ai_model_version,
            ],
        ]);
    }

    /**
     * Get cities suitable for a specific user profile.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $request->validate([
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'min_internet_speed' => 'nullable|integer|min:1',
            'min_safety_score' => 'nullable|integer|min:1|max:10',
            'preferred_climates' => 'nullable|array',
            'visa_type' => 'nullable|string',
            'work_requirements' => 'nullable|array',
        ]);

        $query = City::with(['country'])
            ->where('is_active', true);

        // Apply user preferences
        if ($request->has('budget_min') || $request->has('budget_max')) {
            $query->where(function ($q) use ($request) {
                if ($request->has('budget_min')) {
                    $q->where('cost_of_living_index', '>=', $request->budget_min);
                }
                if ($request->has('budget_max')) {
                    $q->where('cost_of_living_index', '<=', $request->budget_max);
                }
            });
        }

        if ($request->has('min_internet_speed')) {
            $query->where('internet_speed_mbps', '>=', $request->min_internet_speed);
        }

        if ($request->has('min_safety_score')) {
            $query->where('safety_score', '>=', $request->min_safety_score);
        }

        if ($request->has('preferred_climates')) {
            $query->whereIn('climate', $request->preferred_climates);
        }

        if ($request->has('visa_type')) {
            $query->whereJsonContains('visa_options', $request->visa_type);
        }

        // Work requirements
        if ($request->has('work_requirements')) {
            $requirements = $request->work_requirements;

            if (in_array('coworking', $requirements)) {
                $query->where('coworking_spaces_count', '>', 0);
            }

            if (in_array('english', $requirements)) {
                $query->where('english_widely_spoken', true);
            }

            if (in_array('fiber', $requirements)) {
                $query->where('fiber_available', true);
            }
        }

        $cities = $query->orderBy('cost_of_living_index', 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities,
            'recommendations_count' => $cities->count(),
        ]);
    }
}
