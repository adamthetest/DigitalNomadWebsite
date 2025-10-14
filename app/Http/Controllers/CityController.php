<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\City;
use App\Models\CostItem;
use App\Models\CoworkingSpace;
use App\Models\Deal;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of cities.
     */
    public function index(Request $request)
    {
        $query = City::with('country')
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('name');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('country', function ($countryQuery) use ($search) {
                        $countryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('id', $request->country);
            });
        }

        // Filter by cost range
        if ($request->filled('cost_min')) {
            $query->where('cost_of_living_index', '>=', $request->cost_min);
        }
        if ($request->filled('cost_max')) {
            $query->where('cost_of_living_index', '<=', $request->cost_max);
        }

        // Filter by internet speed
        if ($request->filled('internet_min')) {
            $query->where('internet_speed_mbps', '>=', $request->internet_min);
        }

        // Filter by safety score
        if ($request->filled('safety_min')) {
            $query->where('safety_score', '>=', $request->safety_min);
        }

        // Filter by temperature range
        if ($request->filled('temp_min')) {
            $query->where('average_temperature', '>=', $request->temp_min);
        }
        if ($request->filled('temp_max')) {
            $query->where('average_temperature', '<=', $request->temp_max);
        }

        // Filter by featured status
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'true');
        }

        // Filter by continent/region
        if ($request->filled('continent')) {
            $query->whereHas('country', function ($q) use ($request) {
                $q->where('continent', $request->continent);
            });
        }

        // Sort options
        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'cost_low':
                $query->orderBy('cost_of_living_index', 'asc');
                break;
            case 'cost_high':
                $query->orderBy('cost_of_living_index', 'desc');
                break;
            case 'internet':
                $query->orderBy('internet_speed_mbps', 'desc');
                break;
            case 'safety':
                $query->orderBy('safety_score', 'desc');
                break;
            case 'temperature':
                $query->orderBy('average_temperature', 'desc');
                break;
            case 'featured':
            default:
                $query->orderBy('is_featured', 'desc')->orderBy('name');
                break;
        }

        $cities = $query->paginate(12);

        // Get countries for filter dropdown
        $countries = \App\Models\Country::orderBy('name')->get();

        // Get continents for filter dropdown
        $continents = \App\Models\Country::distinct()
            ->pluck('continent')
            ->filter()
            ->sort()
            ->values();

        return view('cities.index', compact('cities', 'countries', 'continents'));
    }

    /**
     * Get city search suggestions for autocomplete.
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cities = City::with('country')
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('country', function ($countryQuery) use ($query) {
                        $countryQuery->where('name', 'like', "%{$query}%");
                    });
            })
            ->limit(10)
            ->get();

        $suggestions = $cities->map(function ($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'country' => $city->country->name,
                'url' => route('cities.show', $city),
                'cost' => $city->cost_of_living_index,
                'internet' => $city->internet_speed_mbps,
                'safety' => $city->safety_score,
            ];
        });

        return response()->json($suggestions);
    }

    /**
     * Display the specified city.
     */
    public function show(City $city)
    {
        // Ensure city is active
        if (! $city->is_active) {
            abort(404);
        }

        $city->load('country', 'neighborhoods');

        // Get related data
        $coworkingSpaces = CoworkingSpace::where('city_id', $city->id)
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->get();

        $costItems = CostItem::where('city_id', $city->id)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $deals = Deal::where('city_id', $city->id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $articles = Article::where('city_id', $city->id)
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // Get similar cities (same country or similar cost range)
        $similarCities = City::where('is_active', true)
            ->where('id', '!=', $city->id)
            ->where(function ($q) use ($city) {
                $q->where('country_id', $city->country_id)
                    ->orWhereBetween('cost_of_living_index', [
                        $city->cost_of_living_index - 200,
                        $city->cost_of_living_index + 200,
                    ]);
            })
            ->limit(4)
            ->get();

        return view('cities.show', compact(
            'city',
            'coworkingSpaces',
            'costItems',
            'deals',
            'articles',
            'similarCities'
        ));
    }
}
