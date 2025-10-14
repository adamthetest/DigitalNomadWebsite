@extends('layouts.app')

@section('title', $company->name . ' - Company Profile')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Company Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-start gap-6">
                <img src="{{ $company->logo_url }}" 
                     alt="{{ $company->name }}" 
                     class="w-20 h-20 rounded-lg object-cover">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $company->name }}</h1>
                    @if($company->verified)
                        <span class="text-green-600 text-sm font-medium mb-4 block">✓ Verified Company</span>
                    @endif
                    @if($company->description)
                        <p class="text-gray-600 mb-4">{{ $company->description }}</p>
                    @endif
                    <div class="flex items-center gap-6 text-sm text-gray-500">
                        @if($company->industry)
                            <span>Industry: <span class="text-gray-900 font-medium">{{ $company->industry }}</span></span>
                        @endif
                        @if($company->size)
                            <span>Size: <span class="text-gray-900 font-medium">{{ $company->size }} employees</span></span>
                        @endif
                        @if($company->headquarters)
                            <span>HQ: <span class="text-gray-900 font-medium">{{ $company->headquarters }}</span></span>
                        @endif
                        <span>Jobs: <span class="text-gray-900 font-medium">{{ $jobs->total() }}</span></span>
                    </div>
                </div>
                <div class="text-right">
                    @if($company->website)
                        <a href="{{ $company->website }}" 
                           target="_blank"
                           class="block px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors mb-2">
                            Visit Website
                        </a>
                    @endif
                    <div class="text-sm text-gray-500">
                        {{ $company->subscription_plan }} plan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Company Description -->
                @if($company->description)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">About {{ $company->name }}</h2>
                        <div class="prose max-w-none">
                            {!! nl2br(e($company->description)) !!}
                        </div>
                    </div>
                @endif

                <!-- Remote Policy -->
                @if($company->remote_policy)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Remote Work Policy</h2>
                        <div class="prose max-w-none">
                            {!! nl2br(e($company->remote_policy)) !!}
                        </div>
                    </div>
                @endif

                <!-- Company Benefits -->
                @if($company->benefits)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Company Benefits</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($company->benefits as $benefit)
                                <div class="flex items-center gap-2">
                                    <span class="text-green-500">✓</span>
                                    <span class="text-gray-700">{{ $benefit }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Tech Stack -->
                @if($company->tech_stack)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Technologies Used</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($company->tech_stack as $tech)
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    {{ $tech }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Open Jobs -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Open Positions ({{ $jobs->total() }})</h2>
                    @if($jobs->count() > 0)
                        <div class="space-y-4">
                            @foreach($jobs as $job)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 mb-1">
                                                <a href="{{ route('jobs.show', $job) }}" class="hover:text-blue-600">
                                                    {{ $job->title }}
                                                </a>
                                            </h3>
                                            <div class="flex items-center gap-4 text-sm text-gray-600 mb-2">
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
                                                @if($job->featured)
                                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                                                        ⭐ Featured
                                                    </span>
                                                @endif
                                            </div>
                                            @if($job->tags)
                                                <div class="flex gap-1 mb-2">
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
                                        <div class="text-right">
                                            @if($job->salary_min || $job->salary_max)
                                                <div class="text-green-600 font-medium mb-2">
                                                    {{ $job->formatted_salary }}
                                                </div>
                                            @endif
                                            <div class="text-sm text-gray-500">
                                                {{ $job->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $jobs->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-4xl mb-2">💼</div>
                            <p class="text-gray-600">No open positions at the moment</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Company Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Company Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Jobs:</span>
                            <span class="font-medium">{{ $jobs->total() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Applications:</span>
                            <span class="font-medium">{{ number_format($company->total_applications) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Plan:</span>
                            <span class="font-medium capitalize">{{ $company->subscription_plan }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Member Since:</span>
                            <span class="font-medium">{{ $company->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                @if($company->contact_email)
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact</h3>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">Email:</span>
                                <a href="mailto:{{ $company->contact_email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $company->contact_email }}
                                </a>
                            </div>
                            @if($company->website)
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500">Website:</span>
                                    <a href="{{ $company->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        {{ parse_url($company->website, PHP_URL_HOST) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Follow Company -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Follow This Company</h3>
                    <p class="text-gray-600 mb-4">Get notified when {{ $company->name }} posts new jobs.</p>
                    @auth
                        <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Follow Company
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Login to Follow
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
