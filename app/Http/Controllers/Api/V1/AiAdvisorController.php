<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiAdvisorController extends Controller
{
    private OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Get AI-powered city recommendations for a user
     */
    public function getCityRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                ], 401);
            }

            // Get user preferences
            $userPreferences = $this->getUserPreferences($user);
            
            // Get available cities based on preferences
            $cities = $this->getFilteredCities($userPreferences);
            
            if ($cities->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cities match your preferences',
                ], 404);
            }

            // Generate AI recommendations
            $aiRecommendations = $this->openAiService->generateCityRecommendations(
                $userPreferences,
                $cities->toArray()
            );

            // Get top 5 cities based on scoring
            $topCities = $this->scoreCities($cities, $userPreferences)->take(5);

            return response()->json([
                'success' => true,
                'data' => [
                    'recommendations' => $topCities->map(function ($city) {
                        return [
                            'id' => $city->id,
                            'name' => $city->name,
                            'country' => $city->country->name,
                            'cost_of_living_index' => $city->cost_of_living_index,
                            'internet_speed_mbps' => $city->internet_speed_mbps,
                            'safety_score' => $city->safety_score,
                            'climate' => $city->climate,
                            'ai_summary' => $city->ai_summary,
                            'ai_tags' => $city->ai_tags,
                            'match_score' => $city->match_score ?? 0,
                        ];
                    }),
                    'ai_insights' => $aiRecommendations,
                    'user_preferences' => $userPreferences,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Advisor city recommendations error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations',
            ], 500);
        }
    }

    /**
     * Get AI-powered city summary
     */
    public function getCitySummary(City $city): JsonResponse
    {
        try {
            // Check if we have cached AI summary
            if ($city->ai_summary && $city->ai_data_updated_at && $city->ai_data_updated_at->isAfter(now()->subDays(7))) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'city_id' => $city->id,
                        'city_name' => $city->name,
                        'ai_summary' => $city->ai_summary,
                        'ai_tags' => $city->ai_tags,
                        'last_updated' => $city->ai_data_updated_at,
                        'cached' => true,
                    ],
                ]);
            }

            // Generate new AI summary
            $cityData = $this->prepareCityData($city);
            $aiSummary = $this->openAiService->generateCitySummary($cityData);

            if (!$aiSummary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate AI summary',
                ], 500);
            }

            // Update city with AI summary
            $city->update([
                'ai_summary' => ['text' => $aiSummary],
                'ai_data_updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'city_id' => $city->id,
                    'city_name' => $city->name,
                    'ai_summary' => ['text' => $aiSummary],
                    'ai_tags' => $city->ai_tags,
                    'last_updated' => now(),
                    'cached' => false,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Advisor city summary error', [
                'city_id' => $city->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate city summary',
            ], 500);
        }
    }

    /**
     * Compare two cities with AI insights
     */
    public function compareCities(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'city1_id' => 'required|exists:cities,id',
                'city2_id' => 'required|exists:cities,id',
            ]);

            $city1 = City::with('country')->findOrFail($request->city1_id);
            $city2 = City::with('country')->findOrFail($request->city2_id);

            $city1Data = $this->prepareCityData($city1);
            $city2Data = $this->prepareCityData($city2);

            $aiComparison = $this->openAiService->generateCityComparison($city1Data, $city2Data);

            return response()->json([
                'success' => true,
                'data' => [
                    'city1' => [
                        'id' => $city1->id,
                        'name' => $city1->name,
                        'country' => $city1->country->name,
                        'data' => $city1Data,
                    ],
                    'city2' => [
                        'id' => $city2->id,
                        'name' => $city2->name,
                        'country' => $city2->country->name,
                        'data' => $city2Data,
                    ],
                    'ai_comparison' => $aiComparison,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Advisor city comparison error', [
                'city1_id' => $request->city1_id ?? null,
                'city2_id' => $request->city2_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to compare cities',
            ], 500);
        }
    }

    /**
     * Get AI insights for a specific city
     */
    public function getCityInsights(City $city): JsonResponse
    {
        try {
            $cityData = $this->prepareCityData($city);
            $aiInsights = $this->openAiService->generateCityInsights($cityData);

            return response()->json([
                'success' => true,
                'data' => [
                    'city_id' => $city->id,
                    'city_name' => $city->name,
                    'ai_insights' => $aiInsights,
                    'city_data' => $cityData,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AI Advisor city insights error', [
                'city_id' => $city->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate city insights',
            ], 500);
        }
    }

    /**
     * Get user preferences for AI recommendations
     */
    private function getUserPreferences(User $user): array
    {
        return [
            'budget_min' => $user->budget_monthly_min ?? 1000,
            'budget_max' => $user->budget_monthly_max ?? 5000,
            'preferred_climates' => $user->preferred_climates ?? ['temperate'],
            'min_internet_speed' => $user->min_internet_speed_mbps ?? 25,
            'min_safety_score' => 7, // Default safety requirement
            'visa_flexible' => $user->visa_flexible ?? false,
            'pet_friendly' => $user->pet_friendly_needed ?? false,
            'family_friendly' => $user->family_friendly_needed ?? false,
            'work_type' => $user->work_type ?? 'remote',
            'experience_level' => $user->experience_years ?? 3,
        ];
    }

    /**
     * Get filtered cities based on user preferences
     */
    private function getFilteredCities(array $preferences)
    {
        $query = City::with('country')->where('is_active', true);

        // Budget filter
        if (isset($preferences['budget_max'])) {
            $query->where('cost_of_living_index', '<=', $preferences['budget_max']);
        }

        // Internet speed filter
        if (isset($preferences['min_internet_speed'])) {
            $query->where('internet_speed_mbps', '>=', $preferences['min_internet_speed']);
        }

        // Safety score filter
        if (isset($preferences['min_safety_score'])) {
            $query->where('safety_score', '>=', $preferences['min_safety_score']);
        }

        return $query->get();
    }

    /**
     * Score cities based on user preferences
     */
    private function scoreCities($cities, array $preferences)
    {
        return $cities->map(function ($city) use ($preferences) {
            $score = 0;

            // Cost scoring (lower is better)
            if ($city->cost_of_living_index <= $preferences['budget_max']) {
                $costScore = max(0, 100 - ($city->cost_of_living_index / $preferences['budget_max']) * 100);
                $score += $costScore * 0.3;
            }

            // Internet scoring
            if ($city->internet_speed_mbps >= $preferences['min_internet_speed']) {
                $internetScore = min(100, ($city->internet_speed_mbps / 100) * 100);
                $score += $internetScore * 0.25;
            }

            // Safety scoring
            $safetyScore = ($city->safety_score / 10) * 100;
            $score += $safetyScore * 0.25;

            // Climate scoring (if user has preferences)
            if (isset($preferences['preferred_climates']) && !empty($preferences['preferred_climates'])) {
                $climateScore = $this->calculateClimateScore($city->climate, $preferences['preferred_climates']);
                $score += $climateScore * 0.2;
            }

            $city->match_score = round($score, 2);
            return $city;
        })->sortByDesc('match_score');
    }

    /**
     * Calculate climate compatibility score
     */
    private function calculateClimateScore(string $cityClimate, array $preferredClimates): float
    {
        $climateMap = [
            'tropical' => ['tropical', 'humid', 'warm'],
            'temperate' => ['temperate', 'mild', 'moderate'],
            'continental' => ['continental', 'cold', 'cool'],
            'arid' => ['arid', 'dry', 'desert'],
            'mediterranean' => ['mediterranean', 'warm', 'mild'],
        ];

        $cityClimateLower = strtolower($cityClimate);
        
        foreach ($preferredClimates as $preferred) {
            $preferredLower = strtolower($preferred);
            if (isset($climateMap[$preferredLower])) {
                foreach ($climateMap[$preferredLower] as $climateType) {
                    if (strpos($cityClimateLower, $climateType) !== false) {
                        return 100;
                    }
                }
            }
        }

        return 50; // Default score if no match
    }

    /**
     * Prepare city data for AI processing
     */
    private function prepareCityData(City $city): array
    {
        return [
            'name' => $city->name,
            'country' => $city->country->name,
            'population' => $city->population,
            'cost_of_living_index' => $city->cost_of_living_index,
            'internet_speed_mbps' => $city->internet_speed_mbps,
            'safety_score' => $city->safety_score,
            'climate' => $city->climate,
            'cost_accommodation_monthly' => $city->cost_accommodation_monthly,
            'cost_food_monthly' => $city->cost_food_monthly,
            'cost_transport_monthly' => $city->cost_transport_monthly,
            'cost_coworking_monthly' => $city->cost_coworking_monthly,
            'visa_options' => $city->visa_options,
            'visa_duration_days' => $city->visa_duration_days,
            'visa_extensions_possible' => $city->visa_extensions_possible,
            'coworking_spaces_count' => $city->coworking_spaces_count,
            'cafes_with_wifi_count' => $city->cafes_with_wifi_count,
            'english_widely_spoken' => $city->english_widely_spoken,
            'female_safe' => $city->female_safe,
            'lgbtq_friendly' => $city->lgbtq_friendly,
            'avg_temperature_celsius' => $city->avg_temperature_celsius,
            'avg_humidity_percent' => $city->avg_humidity_percent,
            'rainy_days_per_year' => $city->rainy_days_per_year,
        ];
    }
}
