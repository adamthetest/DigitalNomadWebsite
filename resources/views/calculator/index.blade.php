@extends('layouts.app')

@section('title', 'Cost Calculator - Digital Nomad Guide')
@section('description', 'Calculate your monthly living costs in different cities around the world. Compare expenses and plan your digital nomad budget.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Cost Calculator</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Calculate your monthly living costs in different cities. Compare expenses and plan your digital nomad budget.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Calculator Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Select a City</h2>
                    
                    <form method="GET" action="{{ route('calculator.index') }}" class="space-y-6">
                        <div>
                            <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">Choose Destination</label>
                            <select id="city_id" 
                                    name="city_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="this.form.submit()">
                                <option value="">Select a city...</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ $selectedCity && $selectedCity->id == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}, {{ $city->country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if($selectedCity)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Cost Breakdown for {{ $selectedCity->name }}
                            </h3>
                            
                            @if(count($costBreakdown) > 0)
                                <form method="POST" action="{{ route('calculator.calculate') }}" class="space-y-6">
                                    @csrf
                                    <input type="hidden" name="city_id" value="{{ $selectedCity->id }}">
                                    
                                    @foreach($costBreakdown as $category => $items)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <h4 class="text-lg font-semibold text-gray-800 mb-3">{{ ucfirst($category) }}</h4>
                                            <div class="space-y-3">
                                                @foreach($items as $item)
                                                    <div class="flex justify-between items-center">
                                                        <label for="cost_{{ $item->id }}" class="text-gray-700">
                                                            {{ $item->name }}
                                                        </label>
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-gray-500 text-sm">$</span>
                                                            <input type="number" 
                                                                   id="cost_{{ $item->id }}" 
                                                                   name="custom_costs[{{ $item->id }}]" 
                                                                   value="{{ $item->price }}" 
                                                                   min="0" 
                                                                   step="0.01"
                                                                   class="w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                                        <div class="text-lg font-semibold text-gray-900">
                                            Total Monthly Cost: ${{ number_format($totalCost, 2) }}
                                        </div>
                                        <button type="submit" 
                                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                            Calculate with Custom Values
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    <p>No cost data available for {{ $selectedCity->name }} yet.</p>
                                    <p class="text-sm mt-2">Check back soon or explore other cities!</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Tips -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Tips</h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li>• Adjust costs based on your lifestyle preferences</li>
                        <li>• Consider seasonal variations in prices</li>
                        <li>• Factor in visa and insurance costs</li>
                        <li>• Include emergency fund in your budget</li>
                        <li>• Compare multiple cities before deciding</li>
                    </ul>
                </div>

                <!-- Compare Cities -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Compare Cities</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Want to compare costs between multiple cities?
                    </p>
                    <a href="{{ route('calculator.compare') }}" 
                       class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                        Compare Cities
                    </a>
                </div>

                <!-- Popular Cities -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Cities</h3>
                    <div class="space-y-3">
                        @foreach($cities->where('is_featured', true)->take(5) as $city)
                            <a href="{{ route('calculator.index', ['city_id' => $city->id]) }}" 
                               class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $city->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $city->country->name }}</p>
                                    </div>
                                    <div class="text-right text-sm text-gray-500">
                                        <div>${{ $city->cost_of_living_index ?? 'N/A' }}</div>
                                        <div>{{ $city->internet_speed_mbps ?? 'N/A' }} Mbps</div>
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
