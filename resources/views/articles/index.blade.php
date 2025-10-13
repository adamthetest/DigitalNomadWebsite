@extends('layouts.app')

@section('title', 'Articles - Digital Nomad Guide')
@section('description', 'Read the latest tips, guides, and insights for digital nomads. Learn about destinations, lifestyle, and remote work strategies.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Digital Nomad Articles</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Tips, guides, and insights from experienced digital nomads. Learn about destinations, lifestyle, and remote work strategies.
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('articles.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Articles</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search articles..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- City Filter -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <select id="city" 
                                name="city" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select id="type" 
                                name="type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    
                    @if(request()->hasAny(['search', 'city', 'type']))
                        <a href="{{ route('articles.index') }}" 
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
                Showing {{ $articles->count() }} of {{ $articles->total() }} articles
                @if(request()->hasAny(['search', 'city', 'type']))
                    matching your criteria
                @endif
            </p>
        </div>

        <!-- Articles Grid -->
        @if($articles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                @foreach($articles as $article)
                    <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Article Image -->
                        @if($article->featured_image)
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="{{ $article->featured_image }}" 
                                     alt="{{ $article->title }}" 
                                     class="w-full h-48 object-cover">
                            </div>
                        @endif

                        <!-- Article Content -->
                        <div class="p-6">
                            <!-- Category and Date -->
                            <div class="flex justify-between items-center mb-3">
                                @if($article->category)
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                        {{ ucfirst($article->category) }}
                                    </span>
                                @endif
                                <time class="text-sm text-gray-500">
                                    {{ $article->published_at->format('M d, Y') }}
                                </time>
                            </div>

                            <!-- Title -->
                            <h2 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2">
                                <a href="{{ route('articles.show', $article) }}" class="hover:text-blue-600">
                                    {{ $article->title }}
                                </a>
                            </h2>

                            <!-- Excerpt -->
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {!! $article->parsed_excerpt !!}
                            </p>

                            <!-- Author and Location -->
                            <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                <div>
                                    @if($article->city)
                                        <span>📍 {{ $article->city->name }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($article->author)
                                        <span>By {{ $article->author }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Read More -->
                            <a href="{{ route('articles.show', $article) }}" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                                Read More
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📝</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No articles found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse all articles.</p>
                <a href="{{ route('articles.index') }}" 
                   class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    View All Articles
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
