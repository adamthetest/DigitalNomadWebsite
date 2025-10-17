@extends('layouts.app')

@section('title', $city->name . ', ' . $city->country->name . ' - Digital Nomad Guide')
@section('description', Str::limit($city->description, 160))

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative h-96 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative h-full flex items-center justify-center">
            <div class="text-center text-white px-4">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">{{ $city->name }}</h1>
                <p class="text-xl md:text-2xl text-blue-100">{{ $city->country->name }}</p>
                @if($city->is_featured)
                    <span class="inline-block bg-yellow-400 text-yellow-900 px-4 py-2 rounded-full text-sm font-semibold mt-4">
                        ⭐ Featured Destination
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Favorite Button -->
        @auth
            <div class="mb-8 text-center">
                <button id="favorite-btn" 
                        onclick="toggleFavorite({{ $city->id }}, 'App\\Models\\City', 'city')"
                        class="inline-flex items-center px-6 py-3 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors font-semibold">
                    <svg id="favorite-icon" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <span id="favorite-text">Add to Favorites</span>
                </button>
            </div>
        @endauth

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl mb-2">🌐</div>
                <div class="text-2xl font-bold text-gray-900">{{ $city->internet_speed_mbps ?? 'N/A' }}</div>
                <div class="text-gray-600">Mbps Internet</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl mb-2">💰</div>
                <div class="text-2xl font-bold text-gray-900">${{ $city->cost_of_living_index ?? 'N/A' }}</div>
                <div class="text-gray-600">Monthly Cost</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl mb-2">🛡️</div>
                <div class="text-2xl font-bold text-gray-900">{{ $city->safety_score ?? 'N/A' }}</div>
                <div class="text-gray-600">Safety Score</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl mb-2">🌡️</div>
                <div class="text-2xl font-bold text-gray-900">{{ $city->average_temperature ?? 'N/A' }}°C</div>
                <div class="text-gray-600">Avg Temperature</div>
            </div>
        </div>

        <!-- City Map -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">📍 Location</h2>
            <div id="cityMap" style="height: 400px; width: 100%;" class="rounded-lg"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About {{ $city->name }}</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($city->description)) !!}
                    </div>
                </div>

                <!-- AI City Insights Widget -->
                @livewire('ai-city-insights-widget', ['city' => $city])

                <!-- Cost of Living -->
                @if($costItems->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Cost of Living</h2>
                        <div class="space-y-4">
                            @foreach($costItems->groupBy('category') as $category => $items)
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ ucfirst($category) }}</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($items as $item)
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-gray-700">{{ $item->name }}</span>
                                                <span class="font-semibold text-gray-900">${{ $item->price }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Coworking Spaces -->
                @if($coworkingSpaces->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Coworking Spaces</h2>
                        <div class="space-y-4">
                            @foreach($coworkingSpaces as $space)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $space->name }}</h3>
                                        @if($space->is_featured)
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                                Featured
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-600 mb-3">{{ Str::limit($space->description, 150) }}</p>
                                    <div class="flex justify-between items-center text-sm text-gray-500">
                                        <span>📍 {{ $space->address }}</span>
                                        @if($space->price_per_month)
                                            <span class="font-semibold text-gray-900">${{ $space->price_per_month }}/month</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Related Articles -->
                @if($articles->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Latest Articles</h2>
                        <div class="space-y-4">
                            @foreach($articles as $article)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $article->title }}</h3>
                                    <p class="text-gray-600 mb-3">{!! Str::limit($article->parsed_excerpt, 120) !!}</p>
                                    <div class="flex justify-between items-center text-sm text-gray-500">
                                        <span>{{ optional($article->published_at)->format('M d, Y') ?? 'Unpublished' }}</span>
                                        <a href="{{ route('articles.show', $article) }}" class="text-blue-600 hover:text-blue-700 font-medium">Read More →</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="#calculator" class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            Calculate Costs
                        </a>
                        <a href="#deals" class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                            View Deals
                        </a>
                        @auth
                            <button class="block w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                                Add to Favorites
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="block w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                                Login to Save
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Deals -->
                @if($deals->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Exclusive Deals</h3>
                        <div class="space-y-4">
                            @foreach($deals as $deal)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                            {{ $deal->category }}
                                        </span>
                                        @if($deal->discount_percentage)
                                            <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">
                                                -{{ $deal->discount_percentage }}%
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">{{ $deal->title }}</h4>
                                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($deal->description, 80) }}</p>
                                    <div class="flex justify-between items-center">
                                        @if($deal->original_price && $deal->discounted_price)
                                            <div>
                                                <span class="font-bold text-green-600">${{ $deal->discounted_price }}</span>
                                                <span class="text-sm text-gray-500 line-through ml-2">${{ $deal->original_price }}</span>
                                            </div>
                                        @elseif($deal->original_price)
                                            <span class="font-bold text-green-600">${{ $deal->original_price }}</span>
                                        @endif
                                        <a href="{{ $deal->deal_url }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                            Get Deal →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Similar Cities -->
                @if($similarCities->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Similar Cities</h3>
                        <div class="space-y-3">
                            @foreach($similarCities as $similarCity)
                                <a href="{{ route('cities.show', $similarCity) }}" 
                                   class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $similarCity->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $similarCity->country->name }}</p>
                                        </div>
                                        <div class="text-right text-sm text-gray-500">
                                            <div>${{ $similarCity->cost_of_living_index ?? 'N/A' }}</div>
                                            <div>{{ $similarCity->internet_speed_mbps ?? 'N/A' }} Mbps</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    // Only log in development
    if (window.location.hostname === 'localhost') {
        console.log('🗺️ Initializing city map...');
    }
    
        // Check if city has coordinates
        @if($city->latitude && $city->longitude)
            // Initialize the map
            const map = SimpleMap.initializeMap('cityMap', {{ $city->latitude }}, {{ $city->longitude }}, 12);
        
        if (!map) {
            console.error('❌ Failed to initialize city map');
            return;
        }
        
        // Add a marker for the city
        const cityMarker = L.marker([{{ $city->latitude }}, {{ $city->longitude }}])
            .addTo(map)
            .bindPopup(`
                <div class="text-center">
                    <h3 class="font-bold text-lg">{{ $city->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $city->country->name }}</p>
                    @if($city->description)
                        <p class="text-sm mt-2">{{ Str::limit($city->description, 100) }}</p>
                    @endif
                </div>
            `);
        
        // Add markers for coworking spaces if they exist
        @if($coworkingSpaces->count() > 0)
            @foreach($coworkingSpaces as $space)
                @if($space->latitude && $space->longitude)
                    L.marker([{{ $space->latitude }}, {{ $space->longitude }}], {
                        icon: L.divIcon({
                            className: 'coworking-marker',
                        html: '<div style="background-color: #3B82F6; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(map).bindPopup(`
                    <div class="text-center">
                        <h4 class="font-bold">{{ $space->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $space->address }}</p>
                        @if($space->price_per_month)
                            <p class="text-sm font-semibold text-green-600">${{ $space->price_per_month }}/month</p>
                        @endif
                    </div>
                `);
            @endif
        @endforeach
    @endif
    
        // Fit map to show all markers
        if (map.getBounds().isValid()) {
            map.fitBounds(map.getBounds(), { padding: [20, 20] });
        }
    @else
            // Show error message if no coordinates
            SimpleMap.showMapError('cityMap', 'City coordinates not available');
    @endif
});

function toggleFavorite(favoritableId, favoritableType, category) {
    fetch('{{ route("favorites.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            favoritable_id: favoritableId,
            favoritable_type: favoritableType,
            category: category,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('favorite-btn');
            const icon = document.getElementById('favorite-icon');
            const text = document.getElementById('favorite-text');
            
            if (data.is_favorited) {
                btn.className = btn.className.replace('bg-red-100 text-red-600 hover:bg-red-200', 'bg-red-500 text-white hover:bg-red-600');
                text.textContent = 'Remove from Favorites';
            } else {
                btn.className = btn.className.replace('bg-red-500 text-white hover:bg-red-600', 'bg-red-100 text-red-600 hover:bg-red-200');
                text.textContent = 'Add to Favorites';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

@endsection
