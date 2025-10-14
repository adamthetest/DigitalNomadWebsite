@extends('layouts.app')

@section('title', 'Discover Digital Nomads - Digital Nomad Guide')
@section('description', 'Discover and connect with digital nomads from around the world. Find freelancers, remote workers, and entrepreneurs.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🌍 Discover Digital Nomads</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Find and connect with digital nomads worldwide. Discover freelancers, remote workers, and entrepreneurs in your area or with similar skills.
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('profiles.discover') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Name, skills, or location..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Location Filter -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" 
                               id="location" 
                               name="location" 
                               value="{{ request('location') }}"
                               placeholder="Current or next destination..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Work Type Filter -->
                    <div>
                        <label for="work_type" class="block text-sm font-medium text-gray-700 mb-2">Work Type</label>
                        <select id="work_type" 
                                name="work_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            @foreach($workTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('work_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Options -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select id="sort" 
                                name="sort" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($sortOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('sort') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="skills" class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
                        <input type="text" 
                               id="skills" 
                               name="skills" 
                               value="{{ request('skills') }}"
                               placeholder="JavaScript, Design, Marketing..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple skills with commas</p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="verified" 
                                   name="verified" 
                                   value="1"
                                   {{ request('verified') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="verified" class="ml-2 text-sm text-gray-700">Verified Only</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="premium" 
                                   name="premium" 
                                   value="1"
                                   {{ request('premium') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="premium" class="ml-2 text-sm text-gray-700">Premium Members</label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        🔍 Search Nomads
                    </button>
                    
                    @if(request()->hasAny(['search', 'location', 'work_type', 'sort', 'skills', 'verified', 'premium']))
                        <a href="{{ route('profiles.discover') }}" 
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
                Showing {{ $users->count() }} of {{ $users->total() }} nomads
                @if(request()->hasAny(['search', 'location', 'work_type', 'sort', 'skills', 'verified', 'premium']))
                    matching your criteria
                @endif
            </p>
        </div>

        <!-- Profiles Grid -->
        @if($users->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                @foreach($users as $user)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Profile Header -->
                        <div class="p-6">
                            <div class="flex items-center space-x-4 mb-4">
                                <img src="{{ $user->profile_image_url }}" 
                                     alt="{{ $user->display_name }}"
                                     class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $user->display_name }}</h3>
                                        @if($user->isOnline())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                🟢 Online
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Verification Badges -->
                                    <div class="flex items-center space-x-1 mb-2">
                                        @foreach($user->verification_badges as $badge)
                                            @if($badge === 'email_verified')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✓
                                                </span>
                                            @elseif($badge === 'id_verified')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    🛡️
                                                </span>
                                            @elseif($badge === 'premium')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    ⭐
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if($user->current_location)
                                        <div class="flex items-center text-gray-600 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="truncate">{{ $user->current_location }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Tagline -->
                            @if($user->tagline)
                                <p class="text-gray-700 text-sm font-medium mb-3">{{ $user->tagline }}</p>
                            @endif

                            <!-- Job Title & Company -->
                            @if($user->job_title || $user->company)
                                <div class="flex items-center text-gray-600 text-sm mb-3">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                    </svg>
                                    <span>
                                        @if($user->job_title && $user->company)
                                            {{ $user->job_title }} at {{ $user->company }}
                                        @elseif($user->job_title)
                                            {{ $user->job_title }}
                                        @elseif($user->company)
                                            {{ $user->company }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            <!-- Work Type -->
                            @if($user->work_type)
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        @if($user->work_type === 'freelancer')
                                            💼 Freelancer
                                        @elseif($user->work_type === 'employee')
                                            🏢 Remote Employee
                                        @elseif($user->work_type === 'entrepreneur')
                                            🚀 Entrepreneur
                                        @endif
                                    </span>
                                </div>
                            @endif

                            <!-- Skills -->
                            @if($user->skills && count($user->skills) > 0)
                                <div class="mb-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($user->skills, 0, 5) as $skill)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                        @if(count($user->skills) > 5)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                +{{ count($user->skills) - 5 }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Next Destination -->
                            @if($user->location_next)
                                <div class="flex items-center text-gray-600 text-sm mb-4">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <span>Next: {{ $user->location_next }}</span>
                                </div>
                            @endif

                            <!-- Social Links -->
                            @if($user->social_links)
                                <div class="flex items-center space-x-3 mb-4">
                                    @if($user->website)
                                        <a href="{{ $user->website }}" target="_blank" rel="noopener" 
                                           class="text-gray-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($user->linkedin)
                                        <a href="{{ $user->linkedin }}" target="_blank" rel="noopener" 
                                           class="text-gray-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($user->github)
                                        <a href="{{ $user->github }}" target="_blank" rel="noopener" 
                                           class="text-gray-400 hover:text-gray-900 transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <!-- Profile Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>{{ $user->favorites()->count() }} favorites</span>
                                <span>Joined {{ $user->created_at->format('M Y') }}</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a href="{{ route('profile.show', $user) }}" 
                                   class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    View Profile
                                </a>
                                <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    💬 Connect
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">🔍</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No nomads found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse all profiles.</p>
                <a href="{{ route('profiles.discover') }}" 
                   class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    View All Nomads
                </a>
            </div>
        @endif

        <!-- Call to Action -->
        @auth
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-8 text-center mt-12">
                <h2 class="text-2xl font-bold mb-4">Join the Nomad Community</h2>
                <p class="text-blue-100 mb-6">Create your profile and connect with fellow digital nomads worldwide.</p>
                <a href="{{ route('profile.edit') }}" 
                   class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Create Your Profile
                </a>
            </div>
        @else
            <div class="bg-gray-100 rounded-lg p-8 text-center mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Ready to Connect?</h2>
                <p class="text-gray-600 mb-6">Sign up to create your profile and join the digital nomad community.</p>
                <a href="{{ route('register') }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Sign Up Now
                </a>
            </div>
        @endauth
    </div>
</div>
@endsection
