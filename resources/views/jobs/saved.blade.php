@extends('layouts.app')

@section('title', 'My Saved Jobs')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">💾 My Saved Jobs</h1>
                <p class="text-gray-600">Jobs you've saved for later review</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($savedJobs->count() > 0)
            <div class="grid gap-6">
                @foreach($savedJobs as $job)
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
                                    Saved {{ $job->pivot->created_at->diffForHumans() }}
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
                                <button onclick="removeSaved({{ $job->id }})" 
                                        class="px-4 py-2 border border-red-300 text-red-600 rounded-md hover:bg-red-50 transition-colors">
                                    Remove
                                </button>
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
                {{ $savedJobs->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">💾</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No saved jobs yet</h3>
                <p class="text-gray-600 mb-6">Start exploring jobs and save the ones you're interested in</p>
                <a href="{{ route('jobs.index') }}" class="text-blue-600 hover:text-blue-800">
                    Browse Jobs
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function removeSaved(jobId) {
    if (confirm('Are you sure you want to remove this job from your saved list?')) {
        fetch(`/jobs/${jobId}/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (!data.saved) {
                // Remove the job card from the page
                event.target.closest('.bg-white').remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>
@endsection
