<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.openai.com/v1';

    private int $timeout;

    private int $maxTokens;

    private float $temperature;

    private string $model;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        $this->timeout = config('openai.timeout', 30);
        $this->maxTokens = config('openai.max_tokens', 2000);
        $this->temperature = config('openai.temperature', 0.7);
        $this->model = config('openai.model', 'gpt-3.5-turbo');
    }

    /**
     * Generate AI content using OpenAI API
     */
    public function generateContent(string $prompt, array $options = []): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key not configured');

            return null;
        }

        $cacheKey = 'openai_'.md5($prompt.serialize($options));

        return Cache::remember($cacheKey, 3600, function () use ($prompt, $options) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->baseUrl.'/chat/completions', [
                        'model' => $options['model'] ?? $this->model,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                        'temperature' => $options['temperature'] ?? $this->temperature,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return $data['choices'][0]['message']['content'] ?? null;
                }

                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('OpenAI API exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return null;
            }
        });
    }

    /**
     * Generate city summary using AI
     */
    public function generateCitySummary(array $cityData): ?string
    {
        $prompt = $this->buildCitySummaryPrompt($cityData);

        return $this->generateContent($prompt);
    }

    /**
     * Generate city comparison using AI
     */
    public function generateCityComparison(array $city1Data, array $city2Data): ?string
    {
        $prompt = $this->buildCityComparisonPrompt($city1Data, $city2Data);

        return $this->generateContent($prompt);
    }

    /**
     * Generate city recommendations using AI
     */
    public function generateCityRecommendations(array $userPreferences, array $availableCities): ?string
    {
        $prompt = $this->buildCityRecommendationPrompt($userPreferences, $availableCities);

        return $this->generateContent($prompt);
    }

    /**
     * Generate city insights using AI
     */
    public function generateCityInsights(array $cityData): ?string
    {
        $prompt = $this->buildCityInsightsPrompt($cityData);

        return $this->generateContent($prompt);
    }

    /**
     * Build prompt for city summary
     */
    private function buildCitySummaryPrompt(array $cityData): string
    {
        $basePrompt = config('openai.default_prompts.city_summary');

        $cityInfo = "City: {$cityData['name']}, {$cityData['country']}\n";
        $cityInfo .= "Population: {$cityData['population']}\n";
        $cityInfo .= "Cost of Living Index: {$cityData['cost_of_living_index']}\n";
        $cityInfo .= "Internet Speed: {$cityData['internet_speed_mbps']} Mbps\n";
        $cityInfo .= "Safety Score: {$cityData['safety_score']}/10\n";
        $cityInfo .= "Climate: {$cityData['climate']}\n";

        if (isset($cityData['cost_accommodation_monthly'])) {
            $cityInfo .= "Monthly Accommodation Cost: \${$cityData['cost_accommodation_monthly']}\n";
        }

        if (isset($cityData['visa_options'])) {
            $cityInfo .= 'Visa Options: '.implode(', ', $cityData['visa_options'])."\n";
        }

        return $basePrompt."\n\nCity Information:\n".$cityInfo;
    }

    /**
     * Build prompt for city comparison
     */
    private function buildCityComparisonPrompt(array $city1Data, array $city2Data): string
    {
        $basePrompt = config('openai.default_prompts.city_comparison');

        $city1Info = $this->formatCityForComparison($city1Data);
        $city2Info = $this->formatCityForComparison($city2Data);

        return $basePrompt."\n\nCity 1:\n".$city1Info."\n\nCity 2:\n".$city2Info;
    }

    /**
     * Build prompt for city recommendations
     */
    private function buildCityRecommendationPrompt(array $userPreferences, array $availableCities): string
    {
        $basePrompt = config('openai.default_prompts.city_recommendation');

        $preferences = "User Preferences:\n";
        $preferences .= "Budget: \${$userPreferences['budget_min']} - \${$userPreferences['budget_max']}\n";
        $preferences .= 'Preferred Climate: '.implode(', ', $userPreferences['preferred_climates'] ?? [])."\n";
        $preferences .= "Internet Requirements: {$userPreferences['min_internet_speed']} Mbps minimum\n";
        $preferences .= "Safety Requirements: {$userPreferences['min_safety_score']}/10 minimum\n";

        $citiesList = "Available Cities:\n";
        foreach ($availableCities as $city) {
            $citiesList .= "- {$city['name']}, {$city['country']} (Cost: \${$city['cost_of_living_index']}, Internet: {$city['internet_speed_mbps']} Mbps, Safety: {$city['safety_score']}/10)\n";
        }

        return $basePrompt."\n\n".$preferences."\n".$citiesList;
    }

    /**
     * Build prompt for city insights
     */
    private function buildCityInsightsPrompt(array $cityData): string
    {
        $basePrompt = config('openai.default_prompts.city_insights');

        $cityInfo = $this->formatCityForComparison($cityData);

        return $basePrompt."\n\nCity Information:\n".$cityInfo;
    }

    /**
     * Format city data for comparison/insights
     */
    private function formatCityForComparison(array $cityData): string
    {
        $info = "Name: {$cityData['name']}, {$cityData['country']}\n";
        $info .= "Population: {$cityData['population']}\n";
        $info .= "Cost of Living Index: {$cityData['cost_of_living_index']}\n";
        $info .= "Internet Speed: {$cityData['internet_speed_mbps']} Mbps\n";
        $info .= "Safety Score: {$cityData['safety_score']}/10\n";
        $info .= "Climate: {$cityData['climate']}\n";

        if (isset($cityData['cost_accommodation_monthly'])) {
            $info .= "Monthly Accommodation: \${$cityData['cost_accommodation_monthly']}\n";
        }

        if (isset($cityData['cost_food_monthly'])) {
            $info .= "Monthly Food: \${$cityData['cost_food_monthly']}\n";
        }

        if (isset($cityData['visa_options'])) {
            $info .= 'Visa Options: '.implode(', ', $cityData['visa_options'])."\n";
        }

        if (isset($cityData['coworking_spaces_count'])) {
            $info .= "Coworking Spaces: {$cityData['coworking_spaces_count']}\n";
        }

        return $info;
    }

    /**
     * Check if OpenAI is properly configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        return config('openai.models', []);
    }

    /**
     * Clear cache for specific prompt
     */
    public function clearCache(string $prompt, array $options = []): void
    {
        $cacheKey = 'openai_'.md5($prompt.serialize($options));
        Cache::forget($cacheKey);
    }
}
