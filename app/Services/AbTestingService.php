<?php

namespace App\Services;

use App\Models\AbTest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AbTestingService
{
    protected UserBehaviorAnalysisService $behaviorAnalysisService;

    public function __construct(UserBehaviorAnalysisService $behaviorAnalysisService)
    {
        $this->behaviorAnalysisService = $behaviorAnalysisService;
    }

    /**
     * Create a new A/B test.
     */
    public function createTest(array $testData): AbTest
    {
        $test = AbTest::create([
            'name' => $testData['name'],
            'description' => $testData['description'] ?? null,
            'test_type' => $testData['test_type'],
            'target_element' => $testData['target_element'],
            'variants' => $testData['variants'],
            'traffic_allocation' => $testData['traffic_allocation'] ?? $this->generateEqualTrafficAllocation($testData['variants']),
            'status' => 'draft',
            'success_metrics' => $testData['success_metrics'] ?? ['conversion_rate', 'engagement_score'],
            'targeting_rules' => $testData['targeting_rules'] ?? null,
        ]);

        Log::info('A/B test created', ['test_id' => $test->id, 'name' => $test->name]);

        return $test;
    }

    /**
     * Start an A/B test.
     */
    public function startTest(int $testId): bool
    {
        $test = AbTest::find($testId);
        if (! $test) {
            return false;
        }

        if ($test->start()) {
            Log::info('A/B test started', ['test_id' => $testId]);

            return true;
        }

        return false;
    }

    /**
     * Get variant for a user in an A/B test.
     */
    public function getVariantForUser(int $testId, int $userId): ?string
    {
        $test = AbTest::find($testId);
        if (! $test || ! $test->isActive()) {
            return null;
        }

        // Check if user matches targeting rules
        if (! $this->userMatchesTargetingRules($test, $userId)) {
            return null;
        }

        // Use consistent hashing to ensure same user gets same variant
        $hash = crc32($userId.$testId);
        $bucket = $hash % 100;

        $cumulativeAllocation = 0;
        foreach ($test->traffic_allocation as $variant => $percentage) {
            $cumulativeAllocation += $percentage;
            if ($bucket < $cumulativeAllocation) {
                return $variant;
            }
        }

        // Fallback to first variant
        return array_key_first($test->traffic_allocation);
    }

    /**
     * Track a conversion for an A/B test.
     */
    public function trackConversion(int $testId, int $userId, string $variant, array $conversionData = []): void
    {
        $test = AbTest::find($testId);
        if (! $test || ! $test->isActive()) {
            return;
        }

        // Verify user is actually in this variant
        $userVariant = $this->getVariantForUser($testId, $userId);
        if ($userVariant !== $variant) {
            Log::warning('Conversion tracked for wrong variant', [
                'test_id' => $testId,
                'user_id' => $userId,
                'expected_variant' => $userVariant,
                'tracked_variant' => $variant,
            ]);

            return;
        }

        // Update test results
        $this->updateTestResults($test, $variant, 'conversion', $conversionData);

        Log::info('A/B test conversion tracked', [
            'test_id' => $testId,
            'user_id' => $userId,
            'variant' => $variant,
        ]);
    }

    /**
     * Track an event for an A/B test.
     */
    public function trackEvent(int $testId, int $userId, string $variant, string $eventType, array $eventData = []): void
    {
        $test = AbTest::find($testId);
        if (! $test || ! $test->isActive()) {
            return;
        }

        // Verify user is actually in this variant
        $userVariant = $this->getVariantForUser($testId, $userId);
        if ($userVariant !== $variant) {
            return;
        }

        // Update test results
        $this->updateTestResults($test, $variant, $eventType, $eventData);

        // Also track in behavior analytics
        $this->behaviorAnalysisService->trackEvent(
            $eventType,
            $userId,
            null,
            'ab_test',
            $testId,
            array_merge($eventData, ['variant' => $variant, 'test_id' => $testId])
        );
    }

    /**
     * Analyze A/B test results.
     */
    public function analyzeTestResults(int $testId): array
    {
        $test = AbTest::find($testId);
        if (! $test) {
            return ['error' => 'Test not found'];
        }

        $results = $test->results ?? [];
        if (empty($results)) {
            return ['error' => 'No results available'];
        }

        $analysis = [
            'test_id' => $testId,
            'test_name' => $test->name,
            'status' => $test->status,
            'variants' => [],
            'statistical_significance' => $this->calculateStatisticalSignificance($results),
            'recommendation' => $this->getTestRecommendation($test, $results),
        ];

        foreach ($results as $variant => $data) {
            $analysis['variants'][$variant] = [
                'visitors' => $data['visitors'] ?? 0,
                'conversions' => $data['conversions'] ?? 0,
                'conversion_rate' => $this->calculateConversionRate($data),
                'engagement_score' => $data['avg_engagement_score'] ?? 0,
                'events' => $data['events'] ?? [],
            ];
        }

        return $analysis;
    }

    /**
     * Complete an A/B test and determine winner.
     */
    public function completeTest(int $testId, bool $forceComplete = false): array
    {
        $test = AbTest::find($testId);
        if (! $test) {
            return ['error' => 'Test not found'];
        }

        if ($test->status !== 'active') {
            return ['error' => 'Test is not active'];
        }

        $results = $test->results ?? [];
        if (empty($results)) {
            return ['error' => 'No results to analyze'];
        }

        // Check if test should be completed
        if (! $forceComplete && ! $this->shouldCompleteTest($test, $results)) {
            return ['message' => 'Test should continue running', 'recommendation' => 'continue'];
        }

        // Determine winner
        $winner = $this->determineWinner($results);
        $confidence = $this->calculateStatisticalSignificance($results);

        // Complete the test
        $test->complete($winner);
        $test->update(['confidence_level' => $confidence]);

        Log::info('A/B test completed', [
            'test_id' => $testId,
            'winner' => $winner,
            'confidence' => $confidence,
        ]);

        return [
            'test_id' => $testId,
            'winner' => $winner,
            'confidence_level' => $confidence,
            'message' => 'Test completed successfully',
        ];
    }

    /**
     * Get active tests for a user.
     */
    public function getActiveTestsForUser(int $userId): array
    {
        $activeTests = AbTest::active()->get();
        $userTests = [];

        foreach ($activeTests as $test) {
            if ($this->userMatchesTargetingRules($test, $userId)) {
                $variant = $this->getVariantForUser($test->id, $userId);
                if ($variant) {
                    $userTests[] = [
                        'test_id' => $test->id,
                        'test_name' => $test->name,
                        'test_type' => $test->test_type,
                        'target_element' => $test->target_element,
                        'variant' => $variant,
                        'variant_data' => $test->variants[$variant] ?? null,
                    ];
                }
            }
        }

        return $userTests;
    }

    /**
     * Generate AI-powered test variants.
     */
    public function generateAiVariants(string $testType, string $targetElement, array $baseContent): array
    {
        $variants = ['control' => $baseContent];

        switch ($testType) {
            case 'content':
                $variants['variant_a'] = $this->generateContentVariant($baseContent, 'shorter');
                $variants['variant_b'] = $this->generateContentVariant($baseContent, 'longer');
                break;

            case 'layout':
                $variants['variant_a'] = $this->generateLayoutVariant($baseContent, 'compact');
                $variants['variant_b'] = $this->generateLayoutVariant($baseContent, 'spacious');
                break;

            case 'cta':
                $variants['variant_a'] = $this->generateCtaVariant($baseContent, 'urgent');
                $variants['variant_b'] = $this->generateCtaVariant($baseContent, 'friendly');
                break;

            default:
                $variants['variant_a'] = $this->generateGenericVariant($baseContent);
        }

        return $variants;
    }

    /**
     * Generate equal traffic allocation for variants.
     */
    private function generateEqualTrafficAllocation(array $variants): array
    {
        $variantCount = count($variants);
        $percentagePerVariant = 100 / $variantCount;
        $allocation = [];

        foreach (array_keys($variants) as $variant) {
            $allocation[$variant] = round($percentagePerVariant, 2);
        }

        // Adjust for rounding errors
        $total = array_sum($allocation);
        if ($total !== 100) {
            $firstVariant = array_key_first($allocation);
            $allocation[$firstVariant] += (100 - $total);
        }

        return $allocation;
    }

    /**
     * Check if user matches targeting rules.
     */
    private function userMatchesTargetingRules(AbTest $test, int $userId): bool
    {
        $rules = $test->targeting_rules;
        if (! $rules) {
            return true; // No targeting rules means all users
        }

        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        // Check user type
        if (isset($rules['user_types']) && ! in_array($user->user_type ?? 'member', $rules['user_types'])) {
            return false;
        }

        // Check premium status
        if (isset($rules['premium_only']) && $rules['premium_only'] && ! ($user->is_premium ?? false)) {
            return false;
        }

        // Check location
        if (isset($rules['locations']) && ! in_array($user->current_location, $rules['locations'])) {
            return false;
        }

        // Check registration date
        if (isset($rules['registration_date_range'])) {
            $range = $rules['registration_date_range'];
            if ($user->created_at < $range['start'] || $user->created_at > $range['end']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update test results for a variant.
     */
    private function updateTestResults(AbTest $test, string $variant, string $eventType, array $eventData): void
    {
        $results = $test->results ?? [];
        $variantResults = $results[$variant] ?? [
            'visitors' => 0,
            'conversions' => 0,
            'events' => [],
            'avg_engagement_score' => 0,
        ];

        // Update visitor count (only once per user)
        if ($eventType === 'page_view' && ! isset($eventData['visitor_counted'])) {
            $variantResults['visitors']++;
            $eventData['visitor_counted'] = true;
        }

        // Update conversion count
        if ($eventType === 'conversion') {
            $variantResults['conversions']++;
        }

        // Update events
        if (! isset($variantResults['events'][$eventType])) {
            $variantResults['events'][$eventType] = 0;
        }
        $variantResults['events'][$eventType]++;

        // Update engagement score
        if (isset($eventData['engagement_score'])) {
            $currentAvg = $variantResults['avg_engagement_score'];
            $totalEvents = array_sum($variantResults['events']);
            $variantResults['avg_engagement_score'] = (($currentAvg * ($totalEvents - 1)) + $eventData['engagement_score']) / $totalEvents;
        }

        $results[$variant] = $variantResults;
        $test->update(['results' => $results]);
    }

    /**
     * Calculate conversion rate for variant data.
     */
    private function calculateConversionRate(array $data): float
    {
        $visitors = $data['visitors'] ?? 0;
        $conversions = $data['conversions'] ?? 0;

        return $visitors > 0 ? round(($conversions / $visitors) * 100, 2) : 0.0;
    }

    /**
     * Calculate statistical significance.
     */
    private function calculateStatisticalSignificance(array $results): float
    {
        if (count($results) < 2) {
            return 0.0;
        }

        $variants = array_keys($results);
        $control = $variants[0];
        $testVariant = $variants[1];

        $controlData = $results[$control];
        $testData = $results[$testVariant];

        $controlVisitors = $controlData['visitors'] ?? 0;
        $controlConversions = $controlData['conversions'] ?? 0;
        $testVisitors = $testData['visitors'] ?? 0;
        $testConversions = $testData['conversions'] ?? 0;

        if ($controlVisitors === 0 || $testVisitors === 0) {
            return 0.0;
        }

        // Calculate conversion rates
        $controlRate = $controlConversions / $controlVisitors;
        $testRate = $testConversions / $testVisitors;

        // Calculate standard error
        $controlSE = sqrt(($controlRate * (1 - $controlRate)) / $controlVisitors);
        $testSE = sqrt(($testRate * (1 - $testRate)) / $testVisitors);

        // Calculate z-score
        $seDiff = sqrt(pow($controlSE, 2) + pow($testSE, 2));
        $zScore = abs($testRate - $controlRate) / $seDiff;

        // Convert z-score to confidence level
        $confidence = $this->zScoreToConfidence($zScore);

        return round($confidence, 2);
    }

    /**
     * Convert z-score to confidence level.
     */
    private function zScoreToConfidence(float $zScore): float
    {
        // Simplified conversion - in practice, you'd use proper statistical tables
        if ($zScore >= 2.58) {
            return 99.0;
        } elseif ($zScore >= 1.96) {
            return 95.0;
        } elseif ($zScore >= 1.65) {
            return 90.0;
        } elseif ($zScore >= 1.28) {
            return 80.0;
        } else {
            return 50.0;
        }
    }

    /**
     * Determine if test should be completed.
     */
    private function shouldCompleteTest(AbTest $test, array $results): bool
    {
        // Check if test has been running for minimum duration
        $minDuration = $test->algorithm_config['min_duration_days'] ?? 7;
        if ($test->start_date && $test->start_date->diffInDays(now()) < $minDuration) {
            return false;
        }

        // Check if we have enough visitors
        $minVisitors = $test->algorithm_config['min_visitors'] ?? 1000;
        $totalVisitors = array_sum(array_column($results, 'visitors'));
        if ($totalVisitors < $minVisitors) {
            return false;
        }

        // Check statistical significance
        $confidence = $this->calculateStatisticalSignificance($results);
        $minConfidence = $test->algorithm_config['min_confidence'] ?? 95.0;
        if ($confidence >= $minConfidence) {
            return true;
        }

        // Check if test has been running for maximum duration
        $maxDuration = $test->algorithm_config['max_duration_days'] ?? 30;
        if ($test->start_date && $test->start_date->diffInDays(now()) >= $maxDuration) {
            return true;
        }

        return false;
    }

    /**
     * Determine the winning variant.
     */
    private function determineWinner(array $results): ?string
    {
        $bestVariant = null;
        $bestRate = 0.0;

        foreach ($results as $variant => $data) {
            $rate = $this->calculateConversionRate($data);
            if ($rate > $bestRate) {
                $bestRate = $rate;
                $bestVariant = $variant;
            }
        }

        return $bestVariant;
    }

    /**
     * Get test recommendation.
     */
    private function getTestRecommendation(AbTest $test, array $results): string
    {
        $confidence = $this->calculateStatisticalSignificance($results);

        if ($confidence >= 95) {
            $winner = $this->determineWinner($results);

            return "Test is statistically significant. Winner: {$winner}";
        } elseif ($confidence >= 80) {
            return 'Test is approaching significance. Consider running longer.';
        } else {
            return 'Test needs more data. Continue running.';
        }
    }

    /**
     * Generate content variant.
     */
    private function generateContentVariant(array $baseContent, string $style): array
    {
        $variant = $baseContent;

        switch ($style) {
            case 'shorter':
                $variant['title'] = $this->shortenText($baseContent['title'] ?? '');
                $variant['description'] = $this->shortenText($baseContent['description'] ?? '');
                break;

            case 'longer':
                $variant['title'] = $this->lengthenText($baseContent['title'] ?? '');
                $variant['description'] = $this->lengthenText($baseContent['description'] ?? '');
                break;
        }

        return $variant;
    }

    /**
     * Generate layout variant.
     */
    private function generateLayoutVariant(array $baseContent, string $style): array
    {
        $variant = $baseContent;

        switch ($style) {
            case 'compact':
                $variant['layout'] = 'compact';
                $variant['spacing'] = 'tight';
                break;

            case 'spacious':
                $variant['layout'] = 'spacious';
                $variant['spacing'] = 'loose';
                break;
        }

        return $variant;
    }

    /**
     * Generate CTA variant.
     */
    private function generateCtaVariant(array $baseContent, string $style): array
    {
        $variant = $baseContent;

        switch ($style) {
            case 'urgent':
                $variant['cta_text'] = 'Get Started Now!';
                $variant['cta_color'] = 'red';
                break;

            case 'friendly':
                $variant['cta_text'] = 'Join Our Community';
                $variant['cta_color'] = 'blue';
                break;
        }

        return $variant;
    }

    /**
     * Generate generic variant.
     */
    private function generateGenericVariant(array $baseContent): array
    {
        $variant = $baseContent;
        $variant['variant_type'] = 'generic';

        return $variant;
    }

    /**
     * Shorten text.
     */
    private function shortenText(string $text): string
    {
        $words = explode(' ', $text);

        return implode(' ', array_slice($words, 0, (int) ceil(count($words) * 0.7)));
    }

    /**
     * Lengthen text.
     */
    private function lengthenText(string $text): string
    {
        // Add descriptive words
        $enhancers = ['amazing', 'incredible', 'fantastic', 'wonderful', 'outstanding'];
        $words = explode(' ', $text);
        $enhancer = $enhancers[array_rand($enhancers)];

        return $enhancer.' '.$text;
    }
}
