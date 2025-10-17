<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI Service
 *
 * Handles all interactions with OpenAI API for AI-powered features
 * including city insights, job matching, resume optimization, and content generation.
 */
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
     * Check if OpenAI is properly configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
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
     * Generate job matching insights for a user-job pair
     */
    public function generateJobMatchingInsights(array $userProfile, array $jobData): array
    {
        if (! $this->isConfigured()) {
            return $this->getFallbackJobInsights();
        }

        $cacheKey = 'job_insights_'.md5(json_encode($userProfile).json_encode($jobData));

        return Cache::remember($cacheKey, 3600, function () use ($userProfile, $jobData) {
            try {
                $prompt = $this->buildJobMatchingPrompt($userProfile, $jobData);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an AI job matching expert. Analyze user profiles and job requirements to provide detailed matching insights, scores, and recommendations.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';

                    return $this->parseJobMatchingResponse($content);
                }

                Log::warning('OpenAI API request failed', ['response' => $response->body()]);

                return $this->getFallbackJobInsights();

            } catch (\Exception $e) {
                Log::error('OpenAI API error', ['error' => $e->getMessage()]);

                return $this->getFallbackJobInsights();
            }
        });
    }

    /**
     * Optimize resume for a specific job
     */
    public function optimizeResumeForJob(string $resumeContent, array $jobData): array
    {
        if (! $this->isConfigured()) {
            return $this->getFallbackResumeOptimization();
        }

        $cacheKey = 'resume_opt_'.md5($resumeContent.json_encode($jobData));

        return Cache::remember($cacheKey, 3600, function () use ($resumeContent, $jobData) {
            try {
                $prompt = $this->buildResumeOptimizationPrompt($resumeContent, $jobData);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert resume writer and career coach. Optimize resumes to match specific job requirements while maintaining authenticity and highlighting relevant experience.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => $this->maxTokens,
                    'temperature' => 0.3, // Lower temperature for more consistent results
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';

                    return $this->parseResumeOptimizationResponse($content);
                }

                Log::warning('OpenAI resume optimization failed', ['response' => $response->body()]);

                return $this->getFallbackResumeOptimization();

            } catch (\Exception $e) {
                Log::error('OpenAI resume optimization error', ['error' => $e->getMessage()]);

                return $this->getFallbackResumeOptimization();
            }
        });
    }

    /**
     * Generate a personalized cover letter
     */
    public function generateCoverLetter(array $userProfile, array $jobData): array
    {
        if (! $this->isConfigured()) {
            return $this->getFallbackCoverLetter();
        }

        $cacheKey = 'cover_letter_'.md5(json_encode($userProfile).json_encode($jobData));

        return Cache::remember($cacheKey, 3600, function () use ($userProfile, $jobData) {
            try {
                $prompt = $this->buildCoverLetterPrompt($userProfile, $jobData);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert cover letter writer. Create personalized, professional cover letters that highlight relevant experience and demonstrate genuine interest in the position.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => $this->maxTokens,
                    'temperature' => 0.5,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';

                    return $this->parseCoverLetterResponse($content);
                }

                Log::warning('OpenAI cover letter generation failed', ['response' => $response->body()]);

                return $this->getFallbackCoverLetter();

            } catch (\Exception $e) {
                Log::error('OpenAI cover letter generation error', ['error' => $e->getMessage()]);

                return $this->getFallbackCoverLetter();
            }
        });
    }

    /**
     * Extract skills from job description
     */
    public function extractSkillsFromJob(string $jobDescription): array
    {
        if (! $this->isConfigured()) {
            return $this->getFallbackSkills();
        }

        $cacheKey = 'skills_extract_'.md5($jobDescription);

        return Cache::remember($cacheKey, 3600, function () use ($jobDescription) {
            try {
                $prompt = "Extract all technical skills, soft skills, and requirements from this job description. Return as a JSON array:\n\n".$jobDescription;

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert at analyzing job descriptions and extracting skills, requirements, and qualifications. Return only valid JSON arrays.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    $skills = json_decode($content, true);

                    return is_array($skills) ? $skills : $this->getFallbackSkills();
                }

                return $this->getFallbackSkills();

            } catch (\Exception $e) {
                Log::error('OpenAI skills extraction error', ['error' => $e->getMessage()]);

                return $this->getFallbackSkills();
            }
        });
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
     * Build job matching prompt
     */
    private function buildJobMatchingPrompt(array $userProfile, array $jobData): string
    {
        return "Analyze this job match and provide detailed insights:\n\n".
               "USER PROFILE:\n".json_encode($userProfile, JSON_PRETTY_PRINT)."\n\n".
               "JOB DATA:\n".json_encode($jobData, JSON_PRETTY_PRINT)."\n\n".
               "Provide:\n".
               "1. Overall match score (0-100)\n".
               "2. Skills compatibility score\n".
               "3. Experience level match\n".
               "4. Location/timezone compatibility\n".
               "5. Salary expectation alignment\n".
               "6. Company culture fit\n".
               "7. Key strengths for this role\n".
               "8. Areas for improvement\n".
               "9. Application tips\n".
               "10. Resume optimization suggestions\n\n".
               'Format as JSON with detailed explanations.';
    }

    /**
     * Build resume optimization prompt
     */
    private function buildResumeOptimizationPrompt(string $resumeContent, array $jobData): string
    {
        return "Optimize this resume for the following job:\n\n".
               "RESUME:\n".$resumeContent."\n\n".
               "JOB REQUIREMENTS:\n".json_encode($jobData, JSON_PRETTY_PRINT)."\n\n".
               "Provide:\n".
               "1. Optimized resume content\n".
               "2. Key changes made\n".
               "3. Skills to highlight\n".
               "4. Experience to emphasize\n".
               "5. Formatting suggestions\n".
               "6. Keywords to include\n\n".
               'Format as JSON with the optimized resume and detailed explanations.';
    }

    /**
     * Build cover letter prompt
     */
    private function buildCoverLetterPrompt(array $userProfile, array $jobData): string
    {
        return "Generate a personalized cover letter:\n\n".
               "USER PROFILE:\n".json_encode($userProfile, JSON_PRETTY_PRINT)."\n\n".
               "JOB DATA:\n".json_encode($jobData, JSON_PRETTY_PRINT)."\n\n".
               "Create a professional cover letter that:\n".
               "1. Shows genuine interest in the role\n".
               "2. Highlights relevant experience\n".
               "3. Demonstrates understanding of the company\n".
               "4. Includes specific examples\n".
               "5. Maintains professional tone\n\n".
               'Format as JSON with the cover letter and key talking points.';
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
     * Parse job matching response
     */
    private function parseJobMatchingResponse(string $content): array
    {
        try {
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse job matching response as JSON', ['content' => $content]);
        }

        return [
            'overall_score' => 75,
            'skills_score' => 70,
            'experience_score' => 80,
            'location_score' => 85,
            'salary_score' => 75,
            'culture_score' => 80,
            'insights' => $content,
            'strengths' => ['Relevant experience', 'Good skill match'],
            'improvements' => ['Consider additional certifications'],
            'tips' => ['Highlight your most relevant projects'],
        ];
    }

    /**
     * Parse resume optimization response
     */
    private function parseResumeOptimizationResponse(string $content): array
    {
        try {
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse resume optimization response as JSON', ['content' => $content]);
        }

        return [
            'optimized_resume' => $content,
            'changes_made' => ['Improved formatting', 'Added relevant keywords'],
            'skills_to_highlight' => ['Technical skills', 'Leadership experience'],
            'keywords' => ['Remote work', 'Agile development'],
        ];
    }

    /**
     * Parse cover letter response
     */
    private function parseCoverLetterResponse(string $content): array
    {
        try {
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse cover letter response as JSON', ['content' => $content]);
        }

        return [
            'cover_letter' => $content,
            'key_points' => ['Relevant experience', 'Company interest', 'Value proposition'],
        ];
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

    /**
     * Fallback responses when OpenAI is not available
     */
    private function getFallbackJobInsights(): array
    {
        return [
            'overall_score' => 70,
            'skills_score' => 65,
            'experience_score' => 75,
            'location_score' => 80,
            'salary_score' => 70,
            'culture_score' => 75,
            'insights' => 'Basic job matching analysis based on profile data.',
            'strengths' => ['Relevant background', 'Good experience level'],
            'improvements' => ['Consider skill development'],
            'tips' => ['Tailor your application to highlight relevant experience'],
        ];
    }

    private function getFallbackResumeOptimization(): array
    {
        return [
            'optimized_resume' => 'Resume optimization temporarily unavailable. Please try again later.',
            'changes_made' => ['Basic formatting improvements'],
            'skills_to_highlight' => ['Technical skills', 'Relevant experience'],
            'keywords' => ['Remote work', 'Digital nomad'],
        ];
    }

    private function getFallbackCoverLetter(): array
    {
        return [
            'cover_letter' => 'Cover letter generation temporarily unavailable. Please try again later.',
            'key_points' => ['Relevant experience', 'Company interest'],
        ];
    }

    private function getFallbackSkills(): array
    {
        return ['Remote work', 'Communication', 'Problem solving'];
    }
}