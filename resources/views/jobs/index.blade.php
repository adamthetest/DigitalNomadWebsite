@extends('layouts.app')

@section('title', 'Remote Jobs for Digital Nomads')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    💼 Remote Jobs for Digital Nomads
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Find your next remote opportunity from verified companies worldwide
                </p>
                
                <!-- Search Bar -->
                <form method="GET" action="{{ route('jobs.index') }}" class="max-w-2xl mx-auto">
                    <div class="flex gap-2">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search jobs, companies, or skills..." 
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    
                    <form method="GET" action="{{ route('jobs.index') }}" id="filter-form">
                        <!-- Job Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
                            <select name="type" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Types</option>
                                @foreach($jobTypes as $value => $label)
                                    <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Remote Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Remote Type</label>
                            <select name="remote_type" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Remote Types</option>
                                @foreach($remoteTypes as $value => $label)
                                    <option value="{{ $value }}" {{ request('remote_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Salary Range -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range (USD/year)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" 
                                       name="salary_min" 
                                       value="{{ request('salary_min') }}"
                                       placeholder="Min" 
                                       class="border border-gray-300 rounded-md px-3 py-2">
                                <input type="number" 
                                       name="salary_max" 
                                       value="{{ request('salary_max') }}"
                                       placeholder="Max" 
                                       class="border border-gray-300 rounded-md px-3 py-2">
                            </div>
                        </div>

                        <!-- Date Posted -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Posted</label>
                            <select name="date_posted" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Any Time</option>
                                <option value="24h" {{ request('date_posted') == '24h' ? 'selected' : '' }}>Last 24 hours</option>
                                <option value="7d" {{ request('date_posted') == '7d' ? 'selected' : '' }}>Last 7 days</option>
                                <option value="30d" {{ request('date_posted') == '30d' ? 'selected' : '' }}>Last 30 days</option>
                            </select>
                        </div>

                        <!-- Visa Support -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="visa_support" 
                                       value="1" 
                                       {{ request('visa_support') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Visa Support</span>
                            </label>
                        </div>

                        <!-- Preserve search query -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            Apply Filters
                        </button>
                        
                        <a href="{{ route('jobs.index') }}" class="block w-full text-center text-gray-600 py-2 px-4 rounded-md hover:text-gray-800 transition-colors mt-2">
                            Clear Filters
                        </a>
                    </form>
                </div>
            </div>

            <!-- Jobs List -->
            <div class="lg:w-3/4">
                <!-- Sort Options -->
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm text-gray-600">
                        Showing {{ $jobs->count() }} of {{ $jobs->total() }} jobs
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Sort by:</span>
                        <form method="GET" action="{{ route('jobs.index') }}" class="inline">
                            @foreach(request()->except('sort') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                                @foreach($sortOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('sort', 'newest') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Jobs Grid -->
                @if($jobs->count() > 0)
                    <div class="grid gap-6">
                        @foreach($jobs as $job)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-start gap-4">
                                        <img src="{{ $job->company->logo_url }}" 
                                             alt="{{ $job->company->name }}" 
                                             class="w-12 h-12 rounded-lg object-cover">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-1">
                                                <a href="{{ route('jobs.show', $job) }}" class="hover:text-blue-600">
                                                    {{ $job->title }}
                                                </a>
                                            </h3>
                                            <p class="text-gray-600 mb-2">
                                                <a href="{{ route('jobs.company', $job->company) }}" class="hover:text-blue-600">
                                                    {{ $job->company->name }}
                                                </a>
                                                @if($job->company->verified)
                                                    <span class="ml-2 text-green-600 text-sm">✓ Verified</span>
                                                @endif
                                            </p>
                                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                    {{ $job->type_label }}
                                                </span>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                    {{ $job->remote_type_label }}
                                                </span>
                                                @if($job->visa_support)
                                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                                        Visa Support
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($job->featured)
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium mb-2 block">
                                                ⭐ Featured
                                            </span>
                                        @endif
                                        <div class="text-sm text-gray-500">
                                            {{ $job->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                <p class="text-gray-700 mb-4 line-clamp-2">
                                    {{ Str::limit(strip_tags($job->description), 200) }}
                                </p>

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-4">
                                        @if($job->salary_min || $job->salary_max)
                                            <span class="text-lg font-semibold text-green-600">
                                                {{ $job->formatted_salary }}
                                            </span>
                                        @endif
                                        @if($job->tags)
                                            <div class="flex gap-1">
                                                @foreach(array_slice($job->tags, 0, 3) as $tag)
                                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                                                        {{ $tag }}
                                                    </span>
                                                @endforeach
                                                @if(count($job->tags) > 3)
                                                    <span class="text-gray-500 text-xs">+{{ count($job->tags) - 3 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        @auth
                                            <button onclick="toggleSave({{ $job->id }})" 
                                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 transition-colors">
                                                💾 Save
                                            </button>
                                        @endauth
                                        <a href="{{ route('jobs.show', $job) }}" 
                                           class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition-colors">
                                            View Job
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $jobs->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">🔍</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No jobs found</h3>
                        <p class="text-gray-600 mb-6">Try adjusting your search criteria or filters</p>
                        <a href="{{ route('jobs.index') }}" class="text-blue-600 hover:text-blue-800">
                            Clear all filters
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@auth
<script>
function toggleSave(jobId) {
    fetch(`/jobs/${jobId}/save`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.saved) {
            // Update button to show saved state
            event.target.textContent = '✓ Saved';
            event.target.classList.add('bg-green-100', 'text-green-800');
        } else {
            // Update button to show unsaved state
            event.target.textContent = '💾 Save';
            event.target.classList.remove('bg-green-100', 'text-green-800');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endauth
@endsection
