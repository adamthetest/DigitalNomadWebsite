<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CoworkingSpace;
use App\Models\CostItem;
use App\Models\Deal;
use App\Models\Article;
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

        $cities = $query->paginate(12);
        
        // Get countries for filter dropdown
        $countries = \App\Models\Country::orderBy('name')->get();

        return view('cities.index', compact('cities', 'countries'));
    }

    /**
     * Display the specified city.
     */
    public function show(City $city)
    {
        // Ensure city is active
        if (!$city->is_active) {
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
                      $city->cost_of_living_index + 200
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
