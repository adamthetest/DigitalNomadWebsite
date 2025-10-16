@extends('layouts.app')

@section('title', 'My Favorites - Digital Nomad Guide')
@section('description', 'View and manage your favorite cities, articles, and deals.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">❤️ My Favorites</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Your saved cities, articles, and deals. Keep track of what interests you most.
            </p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('favorites.index', ['category' => 'all']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition-colors {{ $category === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All ({{ auth()->user()->favorites()->count() }})
                </a>
                <a href="{{ route('favorites.index', ['category' => 'city']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition-colors {{ $category === 'city' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Cities ({{ auth()->user()->favoriteCities()->count() }})
                </a>
                <a href="{{ route('favorites.index', ['category' => 'article']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition-colors {{ $category === 'article' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Articles ({{ auth()->user()->favoriteArticles()->count() }})
                </a>
                <a href="{{ route('favorites.index', ['category' => 'deal']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition-colors {{ $category === 'deal' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Deals ({{ auth()->user()->favoriteDeals()->count() }})
                </a>
            </div>
        </div>

        <!-- Favorites Grid -->
        @if($favorites->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                @foreach($favorites as $favorite)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        @if($favorite->category === 'city')
                            <!-- City Card -->
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="{{ $favorite->favoritable->images[0] ?? 'https://via.placeholder.com/400x225?text=' . urlencode($favorite->favoritable->name) }}" 
                                     alt="{{ $favorite->favoritable->name }}" 
                                     class="w-full h-48 object-cover">
                            </div>
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $favorite->favoritable->name }}</h3>
                                    <button onclick="toggleFavorite({{ $favorite->favoritable->id }}, 'App\\Models\\City', 'city')" 
                                            class="text-red-500 hover:text-red-700 transition-colors">
                                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-gray-600 mb-4">{{ Str::limit($favorite->favoritable->description, 100) }}</p>
                                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                    <span>🌐 {{ $favorite->favoritable->internet_speed_mbps ?? 'N/A' }} Mbps</span>
                                    <span>💰 ${{ $favorite->favoritable->cost_of_living_index ?? 'N/A' }}/month</span>
                                    <span>🛡️ {{ $favorite->favoritable->safety_score ?? 'N/A' }}/10</span>
                                </div>
                                @if($favorite->notes)
                                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-gray-700"><strong>Your notes:</strong> {{ $favorite->notes }}</p>
                                    </div>
                                @endif
                                <a href="{{ route('cities.show', $favorite->favoritable) }}" 
                                   class="btn-primary w-full text-center">Explore City</a>
                            </div>

                        @elseif($favorite->category === 'article')
                            <!-- Article Card -->
                            <div class="aspect-w-16 aspect-h-9">
                                @if($favorite->favoritable->featured_image)
                                    <img src="{{ $favorite->favoritable->featured_image }}" 
                                         alt="{{ $favorite->favoritable->title }}" 
                                         class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-500">📝</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $favorite->favoritable->title }}</h3>
                                    <button onclick="toggleFavorite({{ $favorite->favoritable->id }}, 'App\\Models\\Article', 'article')" 
                                            class="text-red-500 hover:text-red-700 transition-colors">
                                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-gray-600 mb-4">{!! Str::limit($favorite->favoritable->parsed_excerpt, 100) !!}</p>
                                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                    <span>{{ optional($favorite->favoritable->published_at)->format('M d, Y') ?? 'Unpublished' }}</span>
                                    @if($favorite->favoritable->author)
                                        <span>By {{ $favorite->favoritable->author }}</span>
                                    @endif
                                </div>
                                @if($favorite->notes)
                                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-gray-700"><strong>Your notes:</strong> {{ $favorite->notes }}</p>
                                    </div>
                                @endif
                                <a href="{{ route('articles.show', $favorite->favoritable) }}" 
                                   class="btn-primary w-full text-center">Read Article</a>
                            </div>

                        @elseif($favorite->category === 'deal')
                            <!-- Deal Card -->
                            <div class="aspect-w-16 aspect-h-9">
                                @if($favorite->favoritable->image)
                                    <img src="{{ $favorite->favoritable->image }}" 
                                         alt="{{ $favorite->favoritable->title }}" 
                                         class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-green-100 flex items-center justify-center">
                                        <span class="text-green-600 text-4xl">🎯</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $favorite->favoritable->title }}</h3>
                                    <button onclick="toggleFavorite({{ $favorite->favoritable->id }}, 'App\\Models\\Deal', 'deal')" 
                                            class="text-red-500 hover:text-red-700 transition-colors">
                                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-gray-600 mb-4">{{ Str::limit($favorite->favoritable->description, 100) }}</p>
                                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                    <span>{{ ucfirst($favorite->favoritable->category) }}</span>
                                    @if($favorite->favoritable->discount_percentage)
                                        <span class="font-semibold text-green-600">-{{ $favorite->favoritable->discount_percentage }}% OFF</span>
                                    @endif
                                </div>
                                @if($favorite->notes)
                                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-gray-700"><strong>Your notes:</strong> {{ $favorite->notes }}</p>
                                    </div>
                                @endif
                                <a href="{{ route('deals.show', $favorite->favoritable) }}" 
                                   class="btn-primary w-full text-center">View Deal</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $favorites->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">❤️</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No favorites yet</h3>
                <p class="text-gray-600 mb-4">
                    @if($category === 'all')
                        Start exploring and add cities, articles, or deals to your favorites!
                    @else
                        No {{ $category }}s in your favorites yet.
                    @endif
                </p>
                <div class="space-x-4">
                    <a href="{{ route('cities.index') }}" 
                       class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Explore Cities
                    </a>
                    <a href="{{ route('articles.index') }}" 
                       class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition-colors">
                        Read Articles
                    </a>
                    <a href="{{ route('deals.index') }}" 
                       class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                        View Deals
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
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
            // Reload the page to update the favorites list
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endsection
