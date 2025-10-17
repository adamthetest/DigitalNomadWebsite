<?php

namespace App\Services;

use App\Models\Article;
use App\Models\City;
use App\Models\Job;
use App\Models\User;
use App\Models\UserBehaviorAnalytic;

class AdvancedContentCurationService
{
    protected UserBehaviorAnalysisService $behaviorAnalysisService;

    public function __construct(UserBehaviorAnalysisService $behaviorAnalysisService)
    {
        $this->behaviorAnalysisService = $behaviorAnalysisService;
    }

    /**
     * Curate personalized content for a user.
     */
    public function curatePersonalizedContent(int $userId, string $contentType = 'mixed', int $limit = 10): array
    {
        $user = User::find($userId);
        if (! $user) {
            return [];
        }

        $userPreferences = $this->behaviorAnalysisService->analyzeUserBehavior($userId, 30);
        $recommendations = [];

        switch ($contentType) {
            case 'cities':
                $recommendations = $this->curateCities($user, $userPreferences, $limit);
                break;
            case 'jobs':
                $recommendations = $this->curateJobs($user, $userPreferences, $limit);
                break;
            case 'articles':
                $recommendations = $this->curateArticles($user, $userPreferences, $limit);
                break;
            default:
                $recommendations = $this->curateMixedContent($user, $userPreferences, $limit);
        }

        return [
            'user_id' => $userId,
            'content_type' => $contentType,
            'recommendations' => $recommendations,
            'personalization_score' => $this->calculatePersonalizationScore($recommendations, $userPreferences),
            'generated_at' => now(),
        ];
    }

    /**
     * Generate trending content based on user behavior patterns.
     */
    public function generateTrendingContent(int $hours = 24, int $limit = 20): array
    {
        $startTime = now()->subHours($hours);
        $endTime = now();

        // Get trending entities based on recent activity
        $trendingCities = $this->getTrendingCities($startTime, $endTime, $limit);
        $trendingJobs = $this->getTrendingJobs($startTime, $endTime, $limit);
        $trendingArticles = $this->getTrendingArticles($startTime, $endTime, $limit);

        return [
            'period' => [
                'start' => $startTime,
                'end' => $endTime,
                'hours' => $hours,
            ],
            'trending_cities' => $trendingCities,
            'trending_jobs' => $trendingJobs,
            'trending_articles' => $trendingArticles,
            'generated_at' => now(),
        ];
    }

    /**
     * Create dynamic content collections based on user segments.
     */
    public function createDynamicCollections(array $userSegments = []): array
    {
        $collections = [];

        // Default segments if none provided
        if (empty($userSegments)) {
            $userSegments = [
                'new_users' => 'Users who joined in the last 7 days',
                'active_users' => 'Users with high engagement scores',
                'premium_users' => 'Users with premium subscriptions',
                'job_seekers' => 'Users who frequently view jobs',
                'city_explorers' => 'Users who frequently view cities',
            ];
        }

        foreach ($userSegments as $segment => $description) {
            $collections[$segment] = $this->createCollectionForSegment($segment, $description);
        }

        return [
            'collections' => $collections,
            'generated_at' => now(),
        ];
    }

    /**
     * Optimize content for SEO using AI insights.
     */
    public function optimizeContentForSEO(int $contentId, string $contentType): array
    {
        $content = $this->getContentById($contentId, $contentType);
        if (! $content) {
            return ['error' => 'Content not found'];
        }

        $seoOptimizations = [
            'title_optimization' => $this->optimizeTitle($content),
            'meta_description' => $this->generateMetaDescription($content),
            'keyword_suggestions' => $this->suggestKeywords($content),
            'content_structure' => $this->analyzeContentStructure($content),
            'internal_linking' => $this->suggestInternalLinks($content),
            'readability_score' => $this->calculateReadabilityScore($content),
        ];

        return [
            'content_id' => $contentId,
            'content_type' => $contentType,
            'optimizations' => $seoOptimizations,
            'overall_score' => $this->calculateOverallSEOScore($seoOptimizations),
            'generated_at' => now(),
        ];
    }

    /**
     * Curate cities based on user preferences.
     */
    private function curateCities(User $user, array $userPreferences, int $limit): array
    {
        $query = City::active();

        // Apply user preferences
        if (isset($userPreferences['preferences']['cities'])) {
            $preferredCityIds = $userPreferences['preferences']['cities'];
            $query->whereIn('id', $preferredCityIds);
        }

        // Apply budget constraints
        if ($user->budget_min || $user->budget_max) {
            if ($user->budget_min) {
                $query->where('cost_of_living_index', '>=', $user->budget_min);
            }
            if ($user->budget_max) {
                $query->where('cost_of_living_index', '<=', $user->budget_max);
            }
        }

        // Apply climate preferences
        if ($user->preferred_climate) {
            $query->where('climate_description', 'like', '%'.$user->preferred_climate.'%');
        }

        // Apply work type preferences
        if ($user->work_type) {
            $query->where('description', 'like', '%'.$user->work_type.'%');
        }

        $cities = $query->limit($limit)->get();

        return $cities->map(function ($city) use ($userPreferences) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'country' => $city->country->name,
                'cost_of_living_index' => $city->cost_of_living_index,
                'internet_speed_mbps' => $city->internet_speed_mbps,
                'safety_score' => $city->safety_score,
                'personalization_score' => $this->calculateCityPersonalizationScore($city, $userPreferences),
                'match_reasons' => $this->getCityMatchReasons($city, $userPreferences),
            ];
        })->toArray();
    }

    /**
     * Curate jobs based on user preferences.
     */
    private function curateJobs(User $user, array $userPreferences, int $limit): array
    {
        $query = Job::active()->published()->notExpired();

        // Apply skills matching
        if ($user->skills) {
            $skills = is_array($user->skills) ? $user->skills : explode(',', $user->skills);
            foreach ($skills as $skill) {
                $query->orWhere('tags', 'like', '%'.trim($skill).'%');
            }
        }

        // Apply work type preferences
        if ($user->work_type) {
            $query->where('type', $user->work_type);
        }

        // Apply salary preferences
        if ($user->salary_expectation_min) {
            $query->where('salary_min', '>=', $user->salary_expectation_min);
        }

        $jobs = $query->limit($limit)->get();

        return $jobs->map(function ($job) use ($userPreferences) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company' => $job->company->name,
                'location' => $job->location,
                'remote_type' => $job->remote_type,
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'tags' => $job->tags,
                'personalization_score' => $this->calculateJobPersonalizationScore($job, $userPreferences),
                'match_reasons' => $this->getJobMatchReasons($job, $userPreferences),
            ];
        })->toArray();
    }

    /**
     * Curate articles based on user preferences.
     */
    private function curateArticles(User $user, array $userPreferences, int $limit): array
    {
        $query = Article::published();

        // Apply user interests
        if ($user->interests) {
            $interests = is_array($user->interests) ? $user->interests : explode(',', $user->interests);
            foreach ($interests as $interest) {
                $query->orWhere('title', 'like', '%'.trim($interest).'%')
                    ->orWhere('content', 'like', '%'.trim($interest).'%');
            }
        }

        $articles = $query->limit($limit)->get();

        return $articles->map(function ($article) use ($userPreferences) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'published_at' => $article->published_at,
                'personalization_score' => $this->calculateArticlePersonalizationScore($article, $userPreferences),
                'match_reasons' => $this->getArticleMatchReasons($article, $userPreferences),
            ];
        })->toArray();
    }

    /**
     * Curate mixed content types.
     */
    private function curateMixedContent(User $user, array $userPreferences, int $limit): array
    {
        $cities = $this->curateCities($user, $userPreferences, ceil($limit / 3));
        $jobs = $this->curateJobs($user, $userPreferences, ceil($limit / 3));
        $articles = $this->curateArticles($user, $userPreferences, ceil($limit / 3));

        // Mix and rank by personalization score
        $mixedContent = array_merge($cities, $jobs, $articles);
        usort($mixedContent, function ($a, $b) {
            return $b['personalization_score'] <=> $a['personalization_score'];
        });

        return array_slice($mixedContent, 0, $limit);
    }

    /**
     * Get trending cities based on recent activity.
     */
    private function getTrendingCities($startTime, $endTime, int $limit): array
    {
        $trendingCityIds = UserBehaviorAnalytic::byEntityType('city')
            ->byDateRange($startTime, $endTime)
            ->selectRaw('entity_id, COUNT(*) as activity_count')
            ->groupBy('entity_id')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->pluck('entity_id');

        $cities = City::whereIn('id', $trendingCityIds)->get();

        return $cities->map(function ($city) use ($trendingCityIds) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'country' => $city->country->name,
                'trend_score' => $trendingCityIds->search($city->id) + 1,
                'cost_of_living_index' => $city->cost_of_living_index,
                'internet_speed_mbps' => $city->internet_speed_mbps,
                'safety_score' => $city->safety_score,
            ];
        })->toArray();
    }

    /**
     * Get trending jobs based on recent activity.
     */
    private function getTrendingJobs($startTime, $endTime, int $limit): array
    {
        $trendingJobIds = UserBehaviorAnalytic::byEntityType('job')
            ->byDateRange($startTime, $endTime)
            ->selectRaw('entity_id, COUNT(*) as activity_count')
            ->groupBy('entity_id')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->pluck('entity_id');

        $jobs = Job::whereIn('id', $trendingJobIds)->with('company')->get();

        return $jobs->map(function ($job) use ($trendingJobIds) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company' => $job->company->name,
                'location' => $job->location,
                'trend_score' => $trendingJobIds->search($job->id) + 1,
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'tags' => $job->tags,
            ];
        })->toArray();
    }

    /**
     * Get trending articles based on recent activity.
     */
    private function getTrendingArticles($startTime, $endTime, int $limit): array
    {
        $trendingArticleIds = UserBehaviorAnalytic::byEntityType('article')
            ->byDateRange($startTime, $endTime)
            ->selectRaw('entity_id, COUNT(*) as activity_count')
            ->groupBy('entity_id')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->pluck('entity_id');

        $articles = Article::whereIn('id', $trendingArticleIds)->get();

        return $articles->map(function ($article) use ($trendingArticleIds) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'trend_score' => $trendingArticleIds->search($article->id) + 1,
                'published_at' => $article->published_at,
            ];
        })->toArray();
    }

    /**
     * Create collection for specific user segment.
     */
    private function createCollectionForSegment(string $segment, string $description): array
    {
        $users = $this->getUsersForSegment($segment);
        $content = [];

        foreach ($users as $user) {
            $userPreferences = $this->behaviorAnalysisService->analyzeUserBehavior($user->id, 30);
            $userContent = $this->curatePersonalizedContent($user->id, 'mixed', 5);
            $content[] = [
                'user_id' => $user->id,
                'content' => $userContent['recommendations'] ?? [],
            ];
        }

        return [
            'segment' => $segment,
            'description' => $description,
            'user_count' => count($users),
            'content' => $content,
        ];
    }

    /**
     * Get users for specific segment.
     */
    private function getUsersForSegment(string $segment)
    {
        return match ($segment) {
            'new_users' => User::where('created_at', '>=', now()->subDays(7))->get(),
            'active_users' => User::where('last_active_at', '>=', now()->subDays(3))->get(),
            'premium_users' => User::where('is_premium', true)->get(),
            'job_seekers' => User::whereHas('behaviorAnalytics', function ($query) {
                $query->where('event_type', 'apply')->where('event_timestamp', '>=', now()->subDays(30));
            })->get(),
            'city_explorers' => User::whereHas('behaviorAnalytics', function ($query) {
                $query->where('entity_type', 'city')->where('event_timestamp', '>=', now()->subDays(30));
            })->get(),
            default => collect(),
        };
    }

    /**
     * Calculate personalization score for recommendations.
     */
    private function calculatePersonalizationScore(array $recommendations, array $userPreferences): float
    {
        if (empty($recommendations)) {
            return 0.0;
        }

        $totalScore = array_sum(array_column($recommendations, 'personalization_score'));
        $maxScore = count($recommendations) * 100;

        return round(($totalScore / $maxScore) * 100, 2);
    }

    /**
     * Calculate city personalization score.
     */
    private function calculateCityPersonalizationScore(City $city, array $userPreferences): float
    {
        $score = 0;

        // Budget match
        if (isset($userPreferences['preferences']['budget'])) {
            $budget = $userPreferences['preferences']['budget'];
            if ($city->cost_of_living_index >= $budget['min'] && $city->cost_of_living_index <= $budget['max']) {
                $score += 30;
            }
        }

        // Climate match
        if (isset($userPreferences['preferences']['climate'])) {
            $climate = $userPreferences['preferences']['climate'];
            if (str_contains(strtolower($city->climate_description), strtolower($climate))) {
                $score += 25;
            }
        }

        // Internet speed match
        if (isset($userPreferences['preferences']['internet_speed'])) {
            $requiredSpeed = $userPreferences['preferences']['internet_speed'];
            if ($city->internet_speed_mbps >= $requiredSpeed) {
                $score += 20;
            }
        }

        // Safety match
        if (isset($userPreferences['preferences']['safety'])) {
            $requiredSafety = $userPreferences['preferences']['safety'];
            if ($city->safety_score >= $requiredSafety) {
                $score += 15;
            }
        }

        // Recent activity boost
        if (isset($userPreferences['preferences']['cities']) && in_array($city->id, $userPreferences['preferences']['cities'])) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Calculate job personalization score.
     */
    private function calculateJobPersonalizationScore(Job $job, array $userPreferences): float
    {
        $score = 0;

        // Skills match
        if (isset($userPreferences['preferences']['skills'])) {
            $userSkills = $userPreferences['preferences']['skills'];
            $jobTags = $job->tags ?? [];
            $skillMatches = count(array_intersect($userSkills, $jobTags));
            $score += min($skillMatches * 20, 60);
        }

        // Salary match
        if (isset($userPreferences['preferences']['salary'])) {
            $expectedSalary = $userPreferences['preferences']['salary'];
            if ($job->salary_min && $job->salary_min >= $expectedSalary) {
                $score += 25;
            }
        }

        // Work type match
        if (isset($userPreferences['preferences']['work_type'])) {
            $workType = $userPreferences['preferences']['work_type'];
            if ($job->type === $workType) {
                $score += 15;
            }
        }

        return min($score, 100);
    }

    /**
     * Calculate article personalization score.
     */
    private function calculateArticlePersonalizationScore(Article $article, array $userPreferences): float
    {
        $score = 0;

        // Interest match
        if (isset($userPreferences['preferences']['interests'])) {
            $interests = $userPreferences['preferences']['interests'];
            foreach ($interests as $interest) {
                if (str_contains(strtolower($article->title), strtolower($interest)) ||
                    str_contains(strtolower($article->content), strtolower($interest))) {
                    $score += 30;
                }
            }
        }

        // Recent activity boost
        if (isset($userPreferences['preferences']['articles']) && in_array($article->id, $userPreferences['preferences']['articles'])) {
            $score += 20;
        }

        // Recency boost
        $daysSincePublished = $article->published_at->diffInDays(now());
        if ($daysSincePublished <= 7) {
            $score += 15;
        } elseif ($daysSincePublished <= 30) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Get city match reasons.
     */
    private function getCityMatchReasons(City $city, array $userPreferences): array
    {
        $reasons = [];

        if (isset($userPreferences['preferences']['budget'])) {
            $budget = $userPreferences['preferences']['budget'];
            if ($city->cost_of_living_index >= $budget['min'] && $city->cost_of_living_index <= $budget['max']) {
                $reasons[] = 'Matches your budget range';
            }
        }

        if (isset($userPreferences['preferences']['climate'])) {
            $climate = $userPreferences['preferences']['climate'];
            if (str_contains(strtolower($city->climate_description), strtolower($climate))) {
                $reasons[] = 'Matches your preferred climate';
            }
        }

        if ($city->internet_speed_mbps >= 50) {
            $reasons[] = 'High-speed internet available';
        }

        if ($city->safety_score >= 8) {
            $reasons[] = 'High safety rating';
        }

        return $reasons;
    }

    /**
     * Get job match reasons.
     */
    private function getJobMatchReasons(Job $job, array $userPreferences): array
    {
        $reasons = [];

        if (isset($userPreferences['preferences']['skills'])) {
            $userSkills = $userPreferences['preferences']['skills'];
            $jobTags = $job->tags ?? [];
            $skillMatches = array_intersect($userSkills, $jobTags);
            if (! empty($skillMatches)) {
                $reasons[] = 'Matches your skills: '.implode(', ', $skillMatches);
            }
        }

        if ($job->remote_type === 'Full Remote') {
            $reasons[] = 'Fully remote position';
        }

        if ($job->salary_min && $job->salary_min >= 50000) {
            $reasons[] = 'Competitive salary';
        }

        return $reasons;
    }

    /**
     * Get article match reasons.
     */
    private function getArticleMatchReasons(Article $article, array $userPreferences): array
    {
        $reasons = [];

        if (isset($userPreferences['preferences']['interests'])) {
            $interests = $userPreferences['preferences']['interests'];
            foreach ($interests as $interest) {
                if (str_contains(strtolower($article->title), strtolower($interest))) {
                    $reasons[] = 'Matches your interest in '.$interest;
                    break;
                }
            }
        }

        $daysSincePublished = $article->published_at->diffInDays(now());
        if ($daysSincePublished <= 7) {
            $reasons[] = 'Recently published';
        }

        return $reasons;
    }

    /**
     * Get content by ID and type.
     */
    private function getContentById(int $contentId, string $contentType)
    {
        return match ($contentType) {
            'city' => City::find($contentId),
            'job' => Job::find($contentId),
            'article' => Article::find($contentId),
            default => null,
        };
    }

    /**
     * Optimize title for SEO.
     */
    private function optimizeTitle($content): array
    {
        $title = $content->title ?? $content->name ?? '';
        $wordCount = str_word_count($title);
        $characterCount = strlen($title);

        return [
            'current_title' => $title,
            'word_count' => $wordCount,
            'character_count' => $characterCount,
            'recommendations' => [
                'optimal_word_count' => $wordCount >= 5 && $wordCount <= 10 ? 'Good' : 'Consider 5-10 words',
                'optimal_character_count' => $characterCount >= 30 && $characterCount <= 60 ? 'Good' : 'Consider 30-60 characters',
                'includes_keywords' => $this->checkKeywordsInTitle($title),
            ],
        ];
    }

    /**
     * Generate meta description.
     */
    private function generateMetaDescription($content): string
    {
        $description = $content->description ?? $content->excerpt ?? $content->overview ?? '';
        $description = strip_tags($description);
        $description = Str::limit($description, 160);

        return $description;
    }

    /**
     * Suggest keywords for content.
     */
    private function suggestKeywords($content): array
    {
        $title = $content->title ?? $content->name ?? '';
        $description = $content->description ?? $content->excerpt ?? '';

        $text = $title.' '.$description;
        $words = str_word_count(strtolower($text), 1);
        $wordFreq = array_count_values($words);

        // Remove common words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];
        $wordFreq = array_diff_key($wordFreq, array_flip($stopWords));

        arsort($wordFreq);

        return array_slice(array_keys($wordFreq), 0, 10);
    }

    /**
     * Analyze content structure.
     */
    private function analyzeContentStructure($content): array
    {
        $contentText = $content->content ?? $content->description ?? '';
        $wordCount = str_word_count($contentText);
        $paragraphCount = substr_count($contentText, '<p>') + substr_count($contentText, "\n\n");

        return [
            'word_count' => $wordCount,
            'paragraph_count' => $paragraphCount,
            'avg_words_per_paragraph' => $paragraphCount > 0 ? round($wordCount / $paragraphCount, 2) : 0,
            'readability_score' => $this->calculateReadabilityScore($content),
        ];
    }

    /**
     * Suggest internal links.
     */
    private function suggestInternalLinks($content): array
    {
        // This would typically use AI to suggest relevant internal links
        // For now, return a basic implementation
        return [
            'suggested_links' => [],
            'link_opportunities' => 'Consider adding links to related cities, jobs, or articles',
        ];
    }

    /**
     * Calculate readability score.
     */
    private function calculateReadabilityScore($content): float
    {
        $contentText = $content->content ?? $content->description ?? '';
        $contentText = strip_tags($contentText);

        $sentences = preg_split('/[.!?]+/', $contentText);
        $words = str_word_count($contentText);
        $syllables = $this->countSyllables($contentText);

        if (count($sentences) === 0 || $words === 0) {
            return 0;
        }

        $avgWordsPerSentence = $words / count($sentences);
        $avgSyllablesPerWord = $syllables / $words;

        $score = 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);

        return round(max(0, min(100, $score)), 2);
    }

    /**
     * Count syllables in text.
     */
    private function countSyllables(string $text): int
    {
        $text = strtolower($text);
        $syllables = 0;
        $words = explode(' ', $text);

        foreach ($words as $word) {
            $syllables += $this->countSyllablesInWord($word);
        }

        return $syllables;
    }

    /**
     * Count syllables in a single word.
     */
    private function countSyllablesInWord(string $word): int
    {
        $word = preg_replace('/[^a-z]/', '', $word);
        $syllables = 0;
        $vowels = 'aeiouy';
        $prevChar = '';

        for ($i = 0; $i < strlen($word); $i++) {
            $char = $word[$i];
            if (strpos($vowels, $char) !== false && strpos($vowels, $prevChar) === false) {
                $syllables++;
            }
            $prevChar = $char;
        }

        // Handle silent 'e'
        if (substr($word, -1) === 'e' && $syllables > 1) {
            $syllables--;
        }

        return max(1, $syllables);
    }

    /**
     * Check if title includes important keywords.
     */
    private function checkKeywordsInTitle(string $title): bool
    {
        $keywords = ['digital nomad', 'remote work', 'travel', 'city', 'job', 'nomad'];
        $title = strtolower($title);

        foreach ($keywords as $keyword) {
            if (str_contains($title, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate overall SEO score.
     */
    private function calculateOverallSEOScore(array $optimizations): float
    {
        $scores = [];

        // Title optimization score
        $titleRecs = $optimizations['title_optimization']['recommendations'];
        $titleScore = 0;
        if ($titleRecs['optimal_word_count'] === 'Good') {
            $titleScore += 25;
        }
        if ($titleRecs['optimal_character_count'] === 'Good') {
            $titleScore += 25;
        }
        if ($titleRecs['includes_keywords']) {
            $titleScore += 25;
        }
        $scores[] = $titleScore;

        // Meta description score
        $metaDesc = $optimizations['meta_description'];
        $metaScore = 0;
        if (strlen($metaDesc) >= 120 && strlen($metaDesc) <= 160) {
            $metaScore += 25;
        }
        if (strlen($metaDesc) > 0) {
            $metaScore += 25;
        }
        $scores[] = $metaScore;

        // Content structure score
        $structure = $optimizations['content_structure'];
        $structureScore = 0;
        if ($structure['word_count'] >= 300) {
            $structureScore += 25;
        }
        if ($structure['readability_score'] >= 60) {
            $structureScore += 25;
        }
        $scores[] = $structureScore;

        return round(array_sum($scores) / count($scores), 2);
    }
}
