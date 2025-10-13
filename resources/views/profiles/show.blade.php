@extends('layouts.app')

@section('title', $user->display_name . ' - Digital Nomad Profile')
@section('description', $user->bio ? Str::limit($user->bio, 160) : 'View ' . $user->display_name . '\'s digital nomad profile')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
                <!-- Profile Image -->
                <div class="flex-shrink-0">
                    <img src="{{ $user->profile_image_url }}" 
                         alt="{{ $user->display_name }}"
                         class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                </div>

                <!-- Profile Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->display_name }}</h1>
                        @if($user->is_public)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Public Profile
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Private Profile
                            </span>
                        @endif
                    </div>

                    @if($user->location)
                        <div class="flex items-center text-gray-600 mb-3">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $user->location }}</span>
                        </div>
                    @endif

                    @if($user->bio)
                        <p class="text-gray-700 text-lg leading-relaxed">{{ $user->bio }}</p>
                    @endif

                    <!-- Social Links -->
                    @if($user->social_links)
                        <div class="flex items-center space-x-4 mt-4">
                            @if($user->website)
                                <a href="{{ $user->website }}" target="_blank" rel="noopener" 
                                   class="text-gray-600 hover:text-blue-600 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($user->twitter)
                                <a href="https://twitter.com/{{ $user->twitter }}" target="_blank" rel="noopener" 
                                   class="text-gray-600 hover:text-blue-400 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($user->instagram)
                                <a href="https://instagram.com/{{ $user->instagram }}" target="_blank" rel="noopener" 
                                   class="text-gray-600 hover:text-pink-500 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.014 5.367 18.647.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.297-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.807.875 1.297 2.026 1.297 3.323s-.49 2.448-1.297 3.323c-.875.807-2.026 1.297-3.323 1.297zm7.83-9.281c-.49 0-.98-.49-.98-.98s.49-.98.98-.98.98.49.98.98-.49.98-.98.98z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($user->linkedin)
                                <a href="https://linkedin.com/in/{{ $user->linkedin }}" target="_blank" rel="noopener" 
                                   class="text-gray-600 hover:text-blue-600 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($user->github)
                                <a href="https://github.com/{{ $user->github }}" target="_blank" rel="noopener" 
                                   class="text-gray-600 hover:text-gray-900 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Profile Actions -->
                @auth
                    @if(Auth::id() === $user->id)
                        <div class="flex-shrink-0">
                            <a href="{{ route('profile.edit') }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Edit Profile
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <!-- Profile Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $favoriteCities->count() }}</div>
                <div class="text-gray-600">Favorite Cities</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $favoriteArticles->count() }}</div>
                <div class="text-gray-600">Saved Articles</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $favoriteDeals->count() }}</div>
                <div class="text-gray-600">Favorite Deals</div>
            </div>
        </div>

        <!-- Favorites Sections -->
        @if($favoriteCities->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">🏙️ Favorite Cities</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($favoriteCities as $favorite)
                        @php $city = $favorite->favoritable; @endphp
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $city->name }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ $city->country->name }}</p>
                            @if($city->cost_of_living_index)
                                <p class="text-sm text-green-600 font-medium">${{ $city->cost_of_living_index }}/month</p>
                            @endif
                            <a href="{{ route('cities.show', $city) }}" 
                               class="inline-block mt-2 text-blue-600 hover:text-blue-700 text-sm">
                                View City →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($favoriteArticles->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">📝 Saved Articles</h2>
                <div class="space-y-4">
                    @foreach($favoriteArticles as $favorite)
                        @php $article = $favorite->favoritable; @endphp
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $article->title }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($article->parsed_excerpt, 120) }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ $article->published_at->format('M j, Y') }}</span>
                                <a href="{{ route('articles.show', $article) }}" 
                                   class="text-blue-600 hover:text-blue-700 text-sm">
                                    Read Article →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($favoriteDeals->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">💰 Favorite Deals</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($favoriteDeals as $favorite)
                        @php $deal = $favorite->favoritable; @endphp
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $deal->title }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($deal->description, 100) }}</p>
                            @if($deal->discount_percentage)
                                <span class="inline-block bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    {{ $deal->discount_percentage }}% OFF
                                </span>
                            @endif
                            <a href="{{ route('deals.show', $deal) }}" 
                               class="inline-block mt-2 text-blue-600 hover:text-blue-700 text-sm">
                                View Deal →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($favoriteCities->count() === 0 && $favoriteArticles->count() === 0 && $favoriteDeals->count() === 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <div class="text-gray-400 text-6xl mb-4">📝</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No favorites yet</h3>
                <p class="text-gray-600 mb-4">Start exploring cities, articles, and deals to build your collection!</p>
                <a href="{{ route('cities.index') }}" 
                   class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Explore Cities
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
