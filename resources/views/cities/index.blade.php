@extends('layouts.app')

@section('title', 'Cities - Digital Nomad Guide')
@section('description', 'Discover the best cities for digital nomads around the world. Compare costs, internet speeds, safety scores, and more.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Digital Nomad Cities</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Discover amazing destinations around the world. Find cities with great internet, affordable living costs, and vibrant communities.
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('cities.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Cities</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="City or country name..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Country Filter -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <select id="country" 
                                name="country" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ request('country') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cost Range -->
                    <div>
                        <label for="cost_min" class="block text-sm font-medium text-gray-700 mb-2">Min Cost ($/month)</label>
                        <input type="number" 
                               id="cost_min" 
                               name="cost_min" 
                               value="{{ request('cost_min') }}"
                               placeholder="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="cost_max" class="block text-sm font-medium text-gray-700 mb-2">Max Cost ($/month)</label>
                        <input type="number" 
                               id="cost_max" 
                               name="cost_max" 
                               value="{{ request('cost_max') }}"
                               placeholder="3000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Internet Speed -->
                    <div>
                        <label for="internet_min" class="block text-sm font-medium text-gray-700 mb-2">Min Internet Speed (Mbps)</label>
                        <input type="number" 
                               id="internet_min" 
                               name="internet_min" 
                               value="{{ request('internet_min') }}"
                               placeholder="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Safety Score -->
                    <div>
                        <label for="safety_min" class="block text-sm font-medium text-gray-700 mb-2">Min Safety Score (1-10)</label>
                        <input type="number" 
                               id="safety_min" 
                               name="safety_min" 
                               value="{{ request('safety_min') }}"
                               min="1" 
                               max="10"
                               placeholder="7"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    
                    @if(request()->hasAny(['search', 'country', 'cost_min', 'cost_max', 'internet_min', 'safety_min']))
                        <a href="{{ route('cities.index') }}" 
                           class="text-gray-600 hover:text-gray-800">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <div class="mb-6">
            <p class="text-gray-600">
                Showing {{ $cities->count() }} of {{ $cities->total() }} cities
                @if(request()->hasAny(['search', 'country', 'cost_min', 'cost_max', 'internet_min', 'safety_min']))
                    matching your criteria
                @endif
            </p>
        </div>

        <!-- Cities Map -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🗺️ All Cities Map</h2>
            <div id="citiesMap" style="height: 500px; width: 100%;" class="rounded-lg"></div>
        </div>

        <!-- Cities Grid -->
        @if($cities->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                @foreach($cities as $city)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- City Image -->
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ $city->images[0] ?? 'https://via.placeholder.com/400x225?text=' . urlencode($city->name) }}" 
                                 alt="{{ $city->name }}" 
                                 class="w-full h-48 object-cover">
                        </div>

                        <!-- City Info -->
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $city->name }}</h3>
                                @if($city->is_featured)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded">
                                        Featured
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-gray-600 mb-4">{{ $city->country->name }}</p>
                            
                            <p class="text-gray-700 mb-4">{{ Str::limit($city->description, 120) }}</p>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-4 mb-4 text-sm">
                                <div class="text-center">
                                    <div class="text-gray-500">Internet</div>
                                    <div class="font-semibold">{{ $city->internet_speed_mbps ?? 'N/A' }} Mbps</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-gray-500">Cost</div>
                                    <div class="font-semibold">${{ $city->cost_of_living_index ?? 'N/A' }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-gray-500">Safety</div>
                                    <div class="font-semibold">{{ $city->safety_score ?? 'N/A' }}/10</div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('cities.show', $city) }}" 
                               class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                Explore {{ $city->name }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $cities->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">🌍</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No cities found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse all cities.</p>
                <a href="{{ route('cities.index') }}" 
                   class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    View All Cities
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    const map = L.map('citiesMap').setView([20, 0], 2);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add markers for each city
    const markers = [];
    @foreach($cities as $city)
        const marker{{ $city->id }} = L.marker([{{ $city->latitude }}, {{ $city->longitude }}])
            .addTo(map)
            .bindPopup(`
                <div class="text-center">
                    <h3 class="font-bold text-lg">{{ $city->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $city->country->name }}</p>
                    <div class="mt-2 space-y-1">
                        @if($city->cost_of_living_index)
                            <p class="text-sm"><span class="font-semibold">Cost:</span> ${{ $city->cost_of_living_index }}/month</p>
                        @endif
                        @if($city->internet_speed)
                            <p class="text-sm"><span class="font-semibold">Internet:</span> {{ $city->internet_speed }} Mbps</p>
                        @endif
                        @if($city->safety_score)
                            <p class="text-sm"><span class="font-semibold">Safety:</span> {{ $city->safety_score }}/10</p>
                        @endif
                    </div>
                    <a href="{{ route('cities.show', $city) }}" 
                       class="inline-block mt-2 bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                        View Details
                    </a>
                </div>
            `);
        markers.push(marker{{ $city->id }});
    @endforeach
    
    // Fit map to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});
</script>

@endsection
