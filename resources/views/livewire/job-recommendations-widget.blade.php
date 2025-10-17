<div>
    <div class="job-recommendations-widget bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                </svg>
                AI Job Recommendations
            </h3>
            <button wire:click="loadRecommendations"
                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>

        @if($loading)
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <span class="ml-3 text-gray-600">Finding your perfect matches...</span>
            </div>
        @elseif($error)
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @elseif(empty($recommendations))
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                </svg>
                <h4 class="text-lg font-medium text-gray-900 mb-2">No job recommendations yet</h4>
                <p class="text-gray-600 mb-4">Complete your profile to get personalized job recommendations.</p>
                <a href="{{ route('profile.edit') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Complete Profile
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($recommendations as $recommendation)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $recommendation['job']->title }}</h4>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($recommendation['quality_color'] === 'green') bg-green-100 text-green-800
                                        @elseif($recommendation['quality_color'] === 'blue') bg-blue-100 text-blue-800
                                        @elseif($recommendation['quality_color'] === 'yellow') bg-yellow-100 text-yellow-800
                                        @elseif($recommendation['quality_color'] === 'orange') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $recommendation['quality_level'] }} Match
                                    </span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 mb-2">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    {{ $recommendation['job']->company->name ?? 'Unknown Company' }}
                                    <span class="mx-2">•</span>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $recommendation['job']->location ?? 'Remote' }}
                                </div>
                                <p class="text-sm text-gray-700 line-clamp-2">{{ Str::limit($recommendation['job']->description, 150) }}</p>
                            </div>
                            <div class="text-right ml-4">
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($recommendation['score'], 1) }}%</div>
                                <div class="text-xs text-gray-500">Match Score</div>
                            </div>
                        </div>

                        <!-- AI Insights -->
                        @if($recommendation['ai_insights'] && isset($recommendation['ai_insights']['insights']))
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-3">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    <div>
                                        <h5 class="text-sm font-medium text-blue-800 mb-1">AI Insight</h5>
                                        <p class="text-sm text-blue-700">{{ $recommendation['ai_insights']['insights'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $recommendation['job']->apply_url ?? '#' }}" 
                               target="_blank"
                               class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                Apply Now
                            </a>
                            <button wire:click="optimizeResume({{ $recommendation['job']->id }})"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                Optimize Resume
                            </button>
                            <button wire:click="generateCoverLetter({{ $recommendation['job']->id }})"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                Cover Letter
                            </button>
                            <button wire:click="markAsSaved({{ $recommendation['job_match_id'] }})"
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                Save Job
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('jobs.index') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                    View All Jobs →
                </a>
            </div>
        @endif
    </div>

    <!-- Resume Optimization Modal -->
    @if($showResumeOptimization)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">Resume Optimization</h3>
                            <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        @if($selectedJob)
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <h4 class="font-medium text-blue-800">{{ $selectedJob->title }}</h4>
                                <p class="text-sm text-blue-700">{{ $selectedJob->company->name ?? 'Unknown Company' }}</p>
                            </div>
                        @endif

                        @if($optimizationLoading)
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="ml-3 text-gray-600">Optimizing your resume...</span>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-800 mb-2">Original Resume</h4>
                                    <div class="border rounded-md p-3 bg-gray-50 max-h-96 overflow-y-auto">
                                        <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $resumeContent }}</pre>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-800 mb-2">Optimized Resume</h4>
                                    <div class="border rounded-md p-3 bg-green-50 max-h-96 overflow-y-auto">
                                        <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $optimizedResume }}</pre>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Cover Letter Modal -->
    @if($showCoverLetter)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">AI Cover Letter</h3>
                            <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        @if($selectedJob)
                            <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-md">
                                <h4 class="font-medium text-purple-800">{{ $selectedJob->title }}</h4>
                                <p class="text-sm text-purple-700">{{ $selectedJob->company->name ?? 'Unknown Company' }}</p>
                            </div>
                        @endif

                        @if($coverLetterLoading)
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                                <span class="ml-3 text-gray-600">Generating cover letter...</span>
                            </div>
                        @else
                            <div class="border rounded-md p-4 bg-gray-50">
                                <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ $coverLetter }}</pre>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button onclick="copyToClipboard('{{ addslashes($coverLetter) }}')" 
                                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                                    Copy to Clipboard
                                </button>
                                <button onclick="downloadCoverLetter()" 
                                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                                    Download
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Cover letter copied to clipboard!');
            });
        }

        function downloadCoverLetter() {
            const content = `{{ addslashes($coverLetter) }}`;
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'cover-letter.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
</div>