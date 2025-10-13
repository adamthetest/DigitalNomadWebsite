@extends('layouts.app')

@section('title', 'Cost Calculation Results - ' . $city->name)
@section('description', 'Your personalized cost breakdown for living in ' . $city->name . ', ' . $city->country->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Cost Calculation Results</h1>
            <p class="text-xl text-gray-600">
                Your personalized budget for <strong>{{ $city->name }}, {{ $city->country->name }}</strong>
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Results -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                    <!-- Total Cost -->
                    <div class="text-center mb-8">
                        <div class="text-4xl font-bold text-blue-600 mb-2">${{ number_format($totalCost, 2) }}</div>
                        <div class="text-lg text-gray-600">Total Monthly Cost</div>
                    </div>

                    <!-- Cost Breakdown -->
                    <div class="space-y-6">
                        @foreach($costBreakdown as $category => $items)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ ucfirst($category) }}</h3>
                                <div class="space-y-3">
                                    @foreach($items as $item)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-gray-700">{{ $item['name'] }}</span>
                                                @if($item['is_custom'])
                                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Custom</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if($item['is_custom'])
                                                    <span class="text-sm text-gray-500 line-through">${{ number_format($item['original_price'], 2) }}</span>
                                                @endif
                                                <span class="font-semibold text-gray-900">${{ number_format($item['price'], 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Budget Insights -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Insights</h3>
                    <div class="space-y-4">
                        @if($totalCost < 1000)
                            <div class="flex items-start space-x-3">
                                <div class="text-green-500 text-xl">💰</div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Budget-Friendly</h4>
                                    <p class="text-sm text-gray-600">This city offers excellent value for money, perfect for budget-conscious digital nomads.</p>
                                </div>
                            </div>
                        @elseif($totalCost < 2000)
                            <div class="flex items-start space-x-3">
                                <div class="text-blue-500 text-xl">⚖️</div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Moderate Cost</h4>
                                    <p class="text-sm text-gray-600">A balanced cost of living with good amenities and infrastructure.</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start space-x-3">
                                <div class="text-orange-500 text-xl">🏙️</div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Premium Location</h4>
                                    <p class="text-sm text-gray-600">Higher costs but with premium amenities, infrastructure, and opportunities.</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-start space-x-3">
                            <div class="text-purple-500 text-xl">💡</div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Pro Tip</h4>
                                <p class="text-sm text-gray-600">Add 20-30% buffer to your budget for unexpected expenses and emergencies.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('calculator.index', ['city_id' => $city->id]) }}" 
                           class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            Recalculate
                        </a>
                        <a href="{{ route('cities.show', $city) }}" 
                           class="block w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                            View City Details
                        </a>
                        <a href="{{ route('calculator.compare') }}" 
                           class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                            Compare Cities
                        </a>
                    </div>
                </div>

                <!-- Budget Breakdown -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Breakdown</h3>
                    <div class="space-y-3">
                        @foreach($costBreakdown as $category => $items)
                            @php
                                $categoryTotal = collect($items)->sum('price');
                                $percentage = $totalCost > 0 ? ($categoryTotal / $totalCost) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">{{ ucfirst($category) }}</span>
                                    <span class="text-sm font-semibold text-gray-900">${{ number_format($categoryTotal, 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ number_format($percentage, 1) }}%</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Related Cities -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Similar Cities</h3>
                    <div class="space-y-3">
                        @php
                            $similarCities = \App\Models\City::where('is_active', true)
                                ->where('id', '!=', $city->id)
                                ->where(function ($q) use ($city) {
                                    $q->where('country_id', $city->country_id)
                                      ->orWhereBetween('cost_of_living_index', [
                                          $city->cost_of_living_index - 200,
                                          $city->cost_of_living_index + 200
                                      ]);
                                })
                                ->limit(3)
                                ->get();
                        @endphp
                        
                        @foreach($similarCities as $similarCity)
                            <a href="{{ route('calculator.index', ['city_id' => $similarCity->id]) }}" 
                               class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $similarCity->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $similarCity->country->name }}</p>
                                    </div>
                                    <div class="text-right text-sm text-gray-500">
                                        <div>${{ $similarCity->cost_of_living_index ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
