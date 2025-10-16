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

        <!-- Quick Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Filters</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('cities.index', ['continent' => 'Europe']) }}" 
                   class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                    🇪🇺 Europe
                </a>
                <a href="{{ route('cities.index', ['continent' => 'Asia']) }}" 
                   class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                    🌏 Asia
                </a>
                <a href="{{ route('cities.index', ['continent' => 'North America']) }}" 
                   class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                    🌎 North America
                </a>
                <a href="{{ route('cities.index', ['featured' => 'true']) }}" 
                   class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm hover:bg-yellow-200 transition-colors">
                    ⭐ Featured Only
                </a>
                <a href="{{ route('cities.index', ['cost_max' => '1000']) }}" 
                   class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm hover:bg-green-200 transition-colors">
                    💰 Under $1000/month
                </a>
                <a href="{{ route('cities.index', ['internet_min' => '100']) }}" 
                   class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm hover:bg-purple-200 transition-colors">
                    🚀 Fast Internet (100+ Mbps)
                </a>
                <a href="{{ route('cities.index', ['safety_min' => '8']) }}" 
                   class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm hover:bg-red-200 transition-colors">
                    🛡️ Very Safe (8+)
                </a>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('cities.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Cities</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="City or country name..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               autocomplete="off">
                        
                        <!-- Search Suggestions Dropdown -->
                        <div id="search-suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                            <!-- Suggestions will be populated here -->
                        </div>
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

                    <!-- Temperature Range -->
                    <div>
                        <label for="temp_min" class="block text-sm font-medium text-gray-700 mb-2">Min Temperature (°C)</label>
                        <input type="number" 
                               id="temp_min" 
                               name="temp_min" 
                               value="{{ request('temp_min') }}"
                               placeholder="15"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="temp_max" class="block text-sm font-medium text-gray-700 mb-2">Max Temperature (°C)</label>
                        <input type="number" 
                               id="temp_max" 
                               name="temp_max" 
                               value="{{ request('temp_max') }}"
                               placeholder="35"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Continent Filter -->
                    <div>
                        <label for="continent" class="block text-sm font-medium text-gray-700 mb-2">Continent</label>
                        <select id="continent" 
                                name="continent" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Continents</option>
                            @foreach($continents as $continent)
                                <option value="{{ $continent }}" {{ request('continent') == $continent ? 'selected' : '' }}>
                                    {{ $continent }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Featured Filter -->
                    <div>
                        <label for="featured" class="block text-sm font-medium text-gray-700 mb-2">Featured Cities</label>
                        <select id="featured" 
                                name="featured" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Cities</option>
                            <option value="true" {{ request('featured') == 'true' ? 'selected' : '' }}>Featured Only</option>
                            <option value="false" {{ request('featured') == 'false' ? 'selected' : '' }}>Non-Featured Only</option>
                        </select>
                    </div>

                    <!-- Sort Options -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select id="sort" 
                                name="sort" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured First</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="cost_low" {{ request('sort') == 'cost_low' ? 'selected' : '' }}>Cost: Low to High</option>
                            <option value="cost_high" {{ request('sort') == 'cost_high' ? 'selected' : '' }}>Cost: High to Low</option>
                            <option value="internet" {{ request('sort') == 'internet' ? 'selected' : '' }}>Internet Speed</option>
                            <option value="safety" {{ request('sort') == 'safety' ? 'selected' : '' }}>Safety Score</option>
                            <option value="temperature" {{ request('sort') == 'temperature' ? 'selected' : '' }}>Temperature</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    
                    @if(request()->hasAny(['search', 'country', 'cost_min', 'cost_max', 'internet_min', 'safety_min', 'temp_min', 'temp_max', 'continent', 'featured', 'sort']))
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
                @if(request()->hasAny(['search', 'country', 'cost_min', 'cost_max', 'internet_min', 'safety_min', 'temp_min', 'temp_max', 'continent', 'featured', 'sort']))
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
document.addEventListener('DOMContentLoaded', async function() {
    // Only log in development
    if (window.location.hostname === 'localhost') {
        console.log('🗺️ Initializing cities map...');
    }
    
        // Initialize the map
        const map = SimpleMap.initializeMap('citiesMap', 20, 0, 2);
    
    if (!map) {
        console.error('❌ Failed to initialize cities map');
        return;
    }
    
    // Add markers for each city
    const markers = [];
    @foreach($cities as $city)
        @if($city->latitude && $city->longitude)
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
        @endif
    @endforeach
    
    // Fit map to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});

// Search Autocomplete Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const suggestionsDiv = document.getElementById('search-suggestions');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        // Debounce search requests
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('cities.search-suggestions') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(suggestions => {
                    displaySuggestions(suggestions);
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }, 300);
    });

    function displaySuggestions(suggestions) {
        if (suggestions.length === 0) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        suggestionsDiv.innerHTML = suggestions.map(city => `
            <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 onclick="selectSuggestion('${city.name}', '${city.country}')">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-semibold text-gray-900">${city.name}</div>
                        <div class="text-sm text-gray-600">${city.country}</div>
                    </div>
                    <div class="text-xs text-gray-500 text-right">
                        ${city.cost ? `$${city.cost}/mo` : ''}
                        ${city.internet ? ` • ${city.internet} Mbps` : ''}
                        ${city.safety ? ` • ${city.safety}/10 safety` : ''}
                    </div>
                </div>
            </div>
        `).join('');

        suggestionsDiv.classList.remove('hidden');
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
        }
    });

    // Hide suggestions on escape key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            suggestionsDiv.classList.add('hidden');
        }
    });
});

function selectSuggestion(cityName, countryName) {
    document.getElementById('search').value = cityName;
    document.getElementById('search-suggestions').classList.add('hidden');
    
    // Optionally submit the form automatically
    // document.querySelector('form').submit();
}
</script>

@endsection
