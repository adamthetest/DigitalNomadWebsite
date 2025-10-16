<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CoworkingSpace;
use Illuminate\Http\Request;

class CoworkingSpaceController extends Controller
{
    /**
     * Display a listing of coworking spaces.
     */
    public function index(Request $request)
    {
        $coworkingSpaces = CoworkingSpace::where('is_active', true)->paginate(12);
        $cities = City::where('is_active', true)->orderBy('name')->get();
        $types = [];
        $noiseLevels = [];

        return view('coworking-spaces.index', compact('coworkingSpaces', 'cities', 'types', 'noiseLevels'));
    }

    /**
     * Display the specified coworking space.
     */
    public function show(CoworkingSpace $coworkingSpace)
    {
        // Ensure coworking space is active
        if (! $coworkingSpace->is_active) {
            abort(404);
        }

        $coworkingSpace->load('city.country', 'neighborhood');

        // Get related coworking spaces in the same city
        $relatedSpaces = CoworkingSpace::where('is_active', true)
            ->where('id', '!=', $coworkingSpace->id)
            ->where('city_id', $coworkingSpace->city_id)
            ->orderBy('is_verified', 'desc')
            ->orderBy('rating', 'desc')
            ->limit(4)
            ->get();

        // Get latest coworking spaces
        $latestSpaces = CoworkingSpace::where('is_active', true)
            ->where('id', '!=', $coworkingSpace->id)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return view('coworking-spaces.show', compact('coworkingSpace', 'relatedSpaces', 'latestSpaces'));
    }

    /**
     * Get coworking spaces for a specific city.
     * @return \Illuminate\View\View
     */
    public function byCity(City $city)
    {
        $coworkingSpaces = CoworkingSpace::where('is_active', true)
            ->where('city_id', $city->id)
            ->with('neighborhood')
            ->orderBy('is_verified', 'desc')
            ->orderBy('rating', 'desc')
            ->get();

        return view('coworking-spaces.city', compact('coworkingSpaces', 'city'));
    }
}
