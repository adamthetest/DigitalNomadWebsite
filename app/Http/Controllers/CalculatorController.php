<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CostItem;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    /**
     * Show the cost calculator form.
     */
    public function index(Request $request)
    {
        $cities = City::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCity = null;
        $costBreakdown = [];
        $totalCost = 0;

        if ($request->filled('city_id')) {
            $selectedCity = City::find($request->city_id);
            
            if ($selectedCity) {
                $costItems = CostItem::where('city_id', $selectedCity->id)
                    ->orderBy('category')
                    ->orderBy('name')
                    ->get();

                // Group by category
                $costBreakdown = $costItems->groupBy('category');
                
                // Calculate total
                $totalCost = $costItems->sum('price');
            }
        }

        return view('calculator.index', compact('cities', 'selectedCity', 'costBreakdown', 'totalCost'));
    }

    /**
     * Calculate costs for a specific city with custom inputs.
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'custom_costs' => 'array',
            'custom_costs.*' => 'numeric|min:0'
        ]);

        $city = City::find($request->city_id);
        $costItems = CostItem::where('city_id', $city->id)->get();
        
        $costBreakdown = [];
        $totalCost = 0;

        foreach ($costItems as $item) {
            $customPrice = $request->input("custom_costs.{$item->id}", $item->price);
            
            $costBreakdown[$item->category][] = [
                'name' => $item->name,
                'price' => $customPrice,
                'original_price' => $item->price,
                'is_custom' => $customPrice != $item->price
            ];
            
            $totalCost += $customPrice;
        }

        return view('calculator.result', compact('city', 'costBreakdown', 'totalCost'));
    }

    /**
     * Compare costs between multiple cities.
     */
    public function compare(Request $request)
    {
        $request->validate([
            'cities' => 'required|array|min:2|max:4',
            'cities.*' => 'exists:cities,id'
        ]);

        $cities = City::whereIn('id', $request->cities)->get();
        $comparison = [];

        foreach ($cities as $city) {
            $costItems = CostItem::where('city_id', $city->id)->get();
            $totalCost = $costItems->sum('price');
            
            $comparison[] = [
                'city' => $city,
                'total_cost' => $totalCost,
                'cost_items' => $costItems->groupBy('category')
            ];
        }

        // Sort by total cost
        usort($comparison, function ($a, $b) {
            return $a['total_cost'] <=> $b['total_cost'];
        });

        return view('calculator.compare', compact('comparison'));
    }
}
