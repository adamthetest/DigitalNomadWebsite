<div>
    <div class="ai-city-insights-widget bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                AI City Insights
            </h3>
            <button wire:click="loadAiData" 
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>

        @if($loading)
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600">Generating AI insights...</span>
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
        @else
            <!-- AI Summary Section -->
            @if($aiSummary)
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">AI Summary</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        @if(is_array($aiSummary) && isset($aiSummary['text']))
                            <p class="text-gray-700 leading-relaxed">
                                {{ $showFullSummary ? $aiSummary['text'] : Str::limit($aiSummary['text'], 200) }}
                            </p>
                            @if(strlen($aiSummary['text']) > 200)
                                <button wire:click="toggleSummary" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2">
                                    {{ $showFullSummary ? 'Show Less' : 'Read More' }}
                                </button>
                            @endif
                        @else
                            <p class="text-gray-700 leading-relaxed">{{ $aiSummary }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- AI Tags/Insights Section -->
            @if($aiInsights && is_array($aiInsights))
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Key Insights</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($aiInsights as $tag)
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                @auth
                    <button wire:click="getCityRecommendations" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Get Similar Cities
                    </button>
                @else
                    <a href="{{ route('login') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Login for Recommendations
                    </a>
                @endauth
                
                <button onclick="openCityComparisonModal()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Compare Cities
                </button>
            </div>
        @endif
    </div>

    <!-- City Comparison Modal -->
    <div id="cityComparisonModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Compare Cities</h3>
                        <button onclick="closeCityComparisonModal()" 
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="comparisonContent" class="space-y-4">
                        <!-- Comparison content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations Modal -->
    <div id="recommendationsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Recommended Cities</h3>
                        <button onclick="closeRecommendationsModal()" 
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="recommendationsContent" class="space-y-4">
                        <!-- Recommendations content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Livewire event listeners
        document.addEventListener('livewire:init', () => {
            Livewire.on('showComparison', (data) => {
                document.getElementById('comparisonContent').innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-lg mb-3">${data.city1.name}, ${data.city1.country}</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Cost of Living:</strong> $${data.city1.data.cost_of_living_index}</p>
                                <p><strong>Internet Speed:</strong> ${data.city1.data.internet_speed_mbps} Mbps</p>
                                <p><strong>Safety Score:</strong> ${data.city1.data.safety_score}/10</p>
                                <p><strong>Climate:</strong> ${data.city1.data.climate}</p>
                            </div>
                        </div>
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-lg mb-3">${data.city2.name}, ${data.city2.country}</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Cost of Living:</strong> $${data.city2.data.cost_of_living_index}</p>
                                <p><strong>Internet Speed:</strong> ${data.city2.data.internet_speed_mbps} Mbps</p>
                                <p><strong>Safety Score:</strong> ${data.city2.data.safety_score}/10</p>
                                <p><strong>Climate:</strong> ${data.city2.data.climate}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h5 class="font-semibold text-blue-800 mb-2">AI Comparison</h5>
                        <p class="text-blue-700">${data.ai_comparison}</p>
                    </div>
                `;
                document.getElementById('cityComparisonModal').classList.remove('hidden');
            });

            Livewire.on('showRecommendations', (data) => {
                let recommendationsHtml = '<div class="space-y-4">';
                data.recommendations.forEach(city => {
                    recommendationsHtml += `
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-lg">${city.name}, ${city.country}</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2 text-sm">
                                        <div><strong>Cost:</strong> $${city.cost_of_living_index}</div>
                                        <div><strong>Internet:</strong> ${city.internet_speed_mbps} Mbps</div>
                                        <div><strong>Safety:</strong> ${city.safety_score}/10</div>
                                        <div><strong>Match:</strong> ${city.match_score}%</div>
                                    </div>
                                    ${city.ai_summary ? `<p class="mt-2 text-gray-600 text-sm">${city.ai_summary.text || city.ai_summary}</p>` : ''}
                                </div>
                                <button onclick="compareWithCity(${city.id})" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    Compare
                                </button>
                            </div>
                        </div>
                    `;
                });
                recommendationsHtml += '</div>';
                
                if (data.ai_insights) {
                    recommendationsHtml += `
                        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h5 class="font-semibold text-green-800 mb-2">AI Insights</h5>
                            <p class="text-green-700">${data.ai_insights}</p>
                        </div>
                    `;
                }
                
                document.getElementById('recommendationsContent').innerHTML = recommendationsHtml;
                document.getElementById('recommendationsModal').classList.remove('hidden');
            });
        });

        function openCityComparisonModal() {
            // This would open a city selection modal
            // For now, we'll show a placeholder
            alert('City comparison feature - select a city to compare with ' + @json($city->name));
        }

        function closeCityComparisonModal() {
            document.getElementById('cityComparisonModal').classList.add('hidden');
        }

        function closeRecommendationsModal() {
            document.getElementById('recommendationsModal').classList.add('hidden');
        }

        function compareWithCity(cityId) {
            @this.call('compareWithCity', cityId);
        }
    </script>
</div>