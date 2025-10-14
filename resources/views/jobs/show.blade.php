@extends('layouts.app')

@section('title', $job->title . ' - ' . $job->company->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Job Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-6">
                    <img src="{{ $job->company->logo_url }}" 
                         alt="{{ $job->company->name }}" 
                         class="w-16 h-16 rounded-lg object-cover">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $job->title }}</h1>
                        <div class="flex items-center gap-4 mb-4">
                            <a href="{{ route('jobs.company', $job->company) }}" class="text-xl text-gray-600 hover:text-blue-600">
                                {{ $job->company->name }}
                            </a>
                            @if($job->company->verified)
                                <span class="text-green-600 text-sm font-medium">✓ Verified Company</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">
                                {{ $job->type_label }}
                            </span>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-medium">
                                {{ $job->remote_type_label }}
                            </span>
                            @if($job->visa_support)
                                <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full font-medium">
                                    Visa Support Available
                                </span>
                            @endif
                            @if($job->featured)
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-medium">
                                    ⭐ Featured Job
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    @if($job->salary_min || $job->salary_max)
                        <div class="text-2xl font-bold text-green-600 mb-2">
                            {{ $job->formatted_salary }}
                        </div>
                    @endif
                    <div class="text-sm text-gray-500 mb-4">
                        Posted {{ $job->created_at->diffForHumans() }}
                    </div>
                    <div class="flex gap-2">
                        @auth
                            <button onclick="toggleSave({{ $job->id }})" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                {{ $userInteraction && $userInteraction->status === 'saved' ? '✓ Saved' : '💾 Save Job' }}
                            </button>
                        @endauth
                        <a href="{{ $job->apply_url }}" 
                           target="_blank"
                           class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium">
                            Apply Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Job Description -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Job Description</h2>
                    <div class="prose max-w-none">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </div>

                <!-- Requirements -->
                @if($job->requirements)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Requirements</h2>
                        <div class="prose max-w-none">
                            {!! nl2br(e($job->requirements)) !!}
                        </div>
                    </div>
                @endif

                <!-- Benefits -->
                @if($job->benefits)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Benefits</h2>
                        <div class="prose max-w-none">
                            {!! nl2br(e($job->benefits)) !!}
                        </div>
                    </div>
                @endif

                <!-- Skills/Tags -->
                @if($job->tags)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Required Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->tags as $tag)
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Related Jobs -->
                @if($relatedJobs->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">More Jobs from {{ $job->company->name }}</h2>
                        <div class="space-y-4">
                            @foreach($relatedJobs as $relatedJob)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h3 class="font-semibold text-gray-900 mb-1">
                                        <a href="{{ route('jobs.show', $relatedJob) }}" class="hover:text-blue-600">
                                            {{ $relatedJob->title }}
                                        </a>
                                    </h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-600">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                            {{ $relatedJob->type_label }}
                                        </span>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                            {{ $relatedJob->remote_type_label }}
                                        </span>
                                        @if($relatedJob->salary_min || $relatedJob->salary_max)
                                            <span class="text-green-600 font-medium">
                                                {{ $relatedJob->formatted_salary }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Company Info -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">About {{ $job->company->name }}</h3>
                    @if($job->company->description)
                        <p class="text-gray-600 mb-4">{{ Str::limit($job->company->description, 200) }}</p>
                    @endif
                    <div class="space-y-2 text-sm">
                        @if($job->company->industry)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Industry:</span>
                                <span class="text-gray-900">{{ $job->company->industry }}</span>
                            </div>
                        @endif
                        @if($job->company->size)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Size:</span>
                                <span class="text-gray-900">{{ $job->company->size }} employees</span>
                            </div>
                        @endif
                        @if($job->company->headquarters)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Headquarters:</span>
                                <span class="text-gray-900">{{ $job->company->headquarters }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">Active Jobs:</span>
                            <span class="text-gray-900">{{ $job->company->job_count }}</span>
                        </div>
                    </div>
                    @if($job->company->website)
                        <a href="{{ $job->company->website }}" 
                           target="_blank"
                           class="block w-full text-center mt-4 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Visit Company Website
                        </a>
                    @endif
                </div>

                <!-- Job Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Views:</span>
                            <span class="font-medium">{{ number_format($job->views_count) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Applications:</span>
                            <span class="font-medium">{{ number_format($job->applications_count) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Posted:</span>
                            <span class="font-medium">{{ $job->created_at->format('M j, Y') }}</span>
                        </div>
                        @if($job->expires_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Expires:</span>
                                <span class="font-medium">{{ $job->expires_at->format('M j, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Apply Section -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Apply for this Job</h3>
                    <p class="text-gray-600 mb-4">Ready to apply? Click the button below to go to the application page.</p>
                    <a href="{{ $job->apply_url }}" 
                       target="_blank"
                       class="block w-full text-center px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium">
                        Apply Now
                    </a>
                    @if($job->apply_email)
                        <p class="text-sm text-gray-500 mt-2 text-center">
                            Or email: <a href="mailto:{{ $job->apply_email }}" class="text-blue-600">{{ $job->apply_email }}</a>
                        </p>
                    @endif
                </div>
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
            event.target.textContent = '✓ Saved';
            event.target.classList.add('bg-green-100', 'text-green-800');
        } else {
            event.target.textContent = '💾 Save Job';
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
