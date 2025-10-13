@extends('layouts.app')

@section('title', 'Deals - Digital Nomad Guide')
@section('description', 'Exclusive deals and discounts for digital nomads. Save money on accommodation, coworking spaces, flights, and more.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Exclusive Deals</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Save money on accommodation, coworking spaces, flights, and more. Exclusive discounts for digital nomads.
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('deals.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Deals</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search deals..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category" 
                                name="category" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Discount Filter -->
                    <div>
                        <label for="discount_min" class="block text-sm font-medium text-gray-700 mb-2">Min Discount (%)</label>
                        <input type="number" 
                               id="discount_min" 
                               name="discount_min" 
                               value="{{ request('discount_min') }}"
                               placeholder="10"
                               min="0"
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    
                    @if(request()->hasAny(['search', 'category', 'discount_min']))
                        <a href="{{ route('deals.index') }}" 
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
                Showing {{ $deals->count() }} of {{ $deals->total() }} active deals
                @if(request()->hasAny(['search', 'category', 'discount_min']))
                    matching your criteria
                @endif
            </p>
        </div>

        <!-- Deals Grid -->
        @if($deals->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                @foreach($deals as $deal)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Deal Image -->
                        @if($deal->image_url)
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="{{ $deal->image_url }}" 
                                     alt="{{ $deal->title }}" 
                                     class="w-full h-48 object-cover">
                            </div>
                        @endif

                        <!-- Deal Content -->
                        <div class="p-6">
                            <!-- Category and Discount -->
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                    {{ ucfirst($deal->category) }}
                                </span>
                                @if($deal->discount_percentage)
                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">
                                        -{{ $deal->discount_percentage }}% OFF
                                    </span>
                                @endif
                            </div>

                            <!-- Featured Badge -->
                            @if($deal->is_featured)
                                <div class="mb-3">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded">
                                        ⭐ Featured Deal
                                    </span>
                                </div>
                            @endif

                            <!-- Title -->
                            <h3 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2">
                                <a href="{{ route('deals.show', $deal) }}" class="hover:text-blue-600">
                                    {{ $deal->title }}
                                </a>
                            </h3>

                            <!-- Description -->
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ $deal->description }}
                            </p>

                            <!-- Location -->
                            @if($deal->city)
                                <div class="flex items-center text-sm text-gray-500 mb-4">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $deal->city->name }}, {{ $deal->city->country->name }}
                                </div>
                            @endif

                            <!-- Price -->
                            <div class="flex justify-between items-center mb-4">
                                @if($deal->original_price && $deal->discounted_price)
                                    <div>
                                        <span class="text-2xl font-bold text-green-600">${{ $deal->discounted_price }}</span>
                                        <span class="text-sm text-gray-500 line-through ml-2">${{ $deal->original_price }}</span>
                                    </div>
                                @elseif($deal->original_price)
                                    <span class="text-2xl font-bold text-green-600">${{ $deal->original_price }}</span>
                                @endif
                                
                                <div class="text-right text-sm text-gray-500">
                                    <div>Valid until</div>
                                    <div class="font-semibold">{{ $deal->valid_until->format('M d, Y') }}</div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('deals.show', $deal) }}" 
                               class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                                View Deal
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $deals->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">🎯</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No deals found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search criteria or check back soon for new deals.</p>
                <a href="{{ route('deals.index') }}" 
                   class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                    View All Deals
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
