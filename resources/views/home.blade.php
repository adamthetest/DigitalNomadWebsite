@extends('layouts.app')

@section('title', 'Digital Nomad Guide - Find Your Next Destination')
@section('description', 'Discover the best cities for digital nomads. Find coworking spaces, cost of living data, visa information, and more.')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Find Your Next Digital Nomad Destination
        </h1>
        <p class="text-xl md:text-2xl mb-8 text-blue-100">
            Discover cities with great internet, affordable living costs, and vibrant coworking communities
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('cities.index') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Explore Cities
            </a>
            <a href="{{ route('calculator.index') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                Cost Calculator
            </a>
        </div>
    </div>
</section>

<!-- Featured Cities -->
<section id="cities" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Destinations</h2>
            <p class="text-lg text-gray-600">Popular cities for digital nomads around the world</p>
        </div>
        
        @if($featuredCities->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredCities as $city)
            <div class="card hover:shadow-lg transition-shadow">
                <div class="aspect-w-16 aspect-h-9 mb-4">
                    <img src="{{ $city->images[0] ?? 'https://via.placeholder.com/400x225?text=' . urlencode($city->name) }}" 
                         alt="{{ $city->name }}" 
                         class="w-full h-48 object-cover rounded-lg">
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ $city->name }}, {{ $city->country->name }}</h3>
                <p class="text-gray-600 mb-4">{{ Str::limit($city->description, 120) }}</p>
                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                    <span>🌐 {{ $city->internet_speed_mbps ?? 'N/A' }} Mbps</span>
                    <span>💰 ${{ $city->cost_of_living_index ?? 'N/A' }}/month</span>
                    <span>🛡️ {{ $city->safety_score ?? 'N/A' }}/10</span>
                </div>
                <a href="{{ route('cities.show', $city) }}" class="btn-primary w-full text-center">Explore {{ $city->name }}</a>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <p class="text-gray-500">No featured cities available yet. Check back soon!</p>
        </div>
        @endif
    </div>
</section>

<!-- Latest Articles -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Latest Articles</h2>
            <p class="text-lg text-gray-600">Tips, guides, and insights for digital nomads</p>
        </div>
        
        @if($latestArticles->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($latestArticles as $article)
            <div class="card hover:shadow-lg transition-shadow">
                @if($article->featured_image)
                <img src="{{ $article->featured_image }}" 
                     alt="{{ $article->title }}" 
                     class="w-full h-48 object-cover rounded-lg mb-4">
                @endif
                <h3 class="text-xl font-semibold mb-2">{{ $article->title }}</h3>
                <p class="text-gray-600 mb-4">{!! Str::limit($article->parsed_excerpt, 100) !!}</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">{{ $article->published_at->format('M d, Y') }}</span>
                    <a href="#" class="text-blue-600 font-medium hover:text-blue-700">Read More →</a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <p class="text-gray-500">No articles available yet. Check back soon!</p>
        </div>
        @endif
    </div>
</section>

<!-- Featured Deals -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Deals</h2>
            <p class="text-lg text-gray-600">Exclusive offers for digital nomads</p>
        </div>
        
        @if($featuredDeals->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredDeals as $deal)
            <div class="card hover:shadow-lg transition-shadow border-l-4 border-blue-500">
                <div class="flex justify-between items-start mb-2">
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                        {{ $deal->category }}
                    </span>
                    @if($deal->discount_percentage)
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">
                        -{{ $deal->discount_percentage }}%
                    </span>
                    @endif
                </div>
                <h3 class="text-lg font-semibold mb-2">{{ $deal->title }}</h3>
                <p class="text-gray-600 mb-4">{{ Str::limit($deal->description, 80) }}</p>
                <div class="flex justify-between items-center mb-4">
                    @if($deal->original_price && $deal->discounted_price)
                    <div>
                        <span class="text-lg font-bold text-blue-600">${{ $deal->discounted_price }}</span>
                        <span class="text-sm text-gray-500 line-through ml-2">${{ $deal->original_price }}</span>
                    </div>
                    @elseif($deal->original_price)
                    <span class="text-lg font-bold text-blue-600">${{ $deal->original_price }}</span>
                    @endif
                    <span class="text-xs text-gray-500">{{ $deal->valid_until->format('M d') }}</span>
                </div>
                <a href="{{ $deal->deal_url }}" 
                   target="_blank" 
                   class="btn-primary w-full text-center"
                   onclick="trackDealClick({{ $deal->id }})">
                    Get Deal
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <p class="text-gray-500">No deals available at the moment. Check back soon!</p>
        </div>
        @endif
    </div>
</section>

<!-- Newsletter Signup -->
<section class="py-16 bg-blue-600 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
        <p class="text-xl text-blue-100 mb-8">
            Get weekly recommendations for your next digital nomad destination
        </p>
        <form class="max-w-md mx-auto flex gap-4">
            <input type="email" 
                   placeholder="Enter your email" 
                   class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
            <button type="submit" 
                    class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Subscribe
            </button>
        </form>
        <p class="text-sm text-blue-200 mt-4">
            Join 10,000+ digital nomads getting weekly destination recommendations
        </p>
    </div>
</section>

<script>
function trackDealClick(dealId) {
    // Track deal clicks for analytics
    fetch('/api/deals/' + dealId + '/click', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    }).catch(console.error);
}
</script>
@endsection
