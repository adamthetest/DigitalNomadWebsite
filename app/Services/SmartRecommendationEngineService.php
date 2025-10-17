<?php

namespace App\Services;

use App\Models\City;
use App\Models\Job;
use App\Models\RecommendationEngine;
use App\Models\User;
use App\Models\UserBehaviorAnalytic;
use Illuminate\Support\Facades\Log;

class SmartRecommendationEngineService
{
    protected UserBehaviorAnalysisService $behaviorAnalysisService;

    public function __construct(UserBehaviorAnalysisService $behaviorAnalysisService)
    {
        $this->behaviorAnalysisService = $behaviorAnalysisService;
    }

    /**
     * Get personalized recommendations for a user.
     */
    public function getPersonalizedRecommendations(int $userId, string $entityType = 'mixed', int $limit = 10): array
    {
        $user = User::find($userId);
        if (! $user) {
            return [];
        }

        $userProfile = $this->buildUserProfile($userId);
        $recommendations = [];

        switch ($entityType) {
            case 'cities':
                $recommendations = $this->recommendCities($userProfile, $limit);
                break;
            case 'jobs':
                $recommendations = $this->recommendJobs($userProfile, $limit);
                break;
            case 'articles':
                $recommendations = $this->recommendArticles($userProfile, $limit);
                break;
            default:
                $recommendations = $this->recommendMixedContent($userProfile, $limit);
        }

        // Update recommendation engine metrics
        $this->updateRecommendationMetrics($entityType, count($recommendations));

        return [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'recommendations' => $recommendations,
            'algorithm_used' => $this->getActiveAlgorithm($entityType),
            'confidence_score' => $this->calculateConfidenceScore($recommendations, $userProfile),
            'generated_at' => now(),
        ];
    }

    /**
     * Train recommendation engine with user behavior data.
     */
    public function trainRecommendationEngine(string $entityType, int $days = 30): array
    {
        $engine = RecommendationEngine::where('target_entity', $entityType)
            ->where('status', '!=', 'inactive')
            ->first();

        if (! $engine) {
            $engine = $this->createRecommendationEngine($entityType);
        }

        $trainingData = $this->collectTrainingData($entityType, $days);
        $model = $this->buildRecommendationModel($trainingData, $engine->engine_type);
        $accuracy = $this->evaluateModel($model, $trainingData);

        $engine->update([
            'training_data' => $model,
            'accuracy_score' => $accuracy,
            'last_trained_at' => now(),
            'status' => 'active',
        ]);

        Log::info('Recommendation engine trained', [
            'entity_type' => $entityType,
            'accuracy' => $accuracy,
            'training_samples' => count($trainingData),
        ]);

        return [
            'entity_type' => $entityType,
            'accuracy_score' => $accuracy,
            'training_samples' => count($trainingData),
            'model_updated' => true,
        ];
    }

    /**
     * Get collaborative filtering recommendations.
     */
    public function getCollaborativeFilteringRecommendations(int $userId, string $entityType, int $limit = 10): array
    {
        $userProfile = $this->buildUserProfile($userId);
        $similarUsers = $this->findSimilarUsers($userProfile, $entityType);

        if (empty($similarUsers)) {
            return [];
        }

        $recommendations = $this->getRecommendationsFromSimilarUsers($similarUsers, $userId, $entityType, $limit);

        return [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'recommendations' => $recommendations,
            'algorithm' => 'collaborative_filtering',
            'similar_users_count' => count($similarUsers),
            'generated_at' => now(),
        ];
    }

    /**
     * Get content-based filtering recommendations.
     */
    public function getContentBasedRecommendations(int $userId, string $entityType, int $limit = 10): array
    {
        $userProfile = $this->buildUserProfile($userId);
        $userPreferences = $this->extractUserPreferences($userProfile);

        $recommendations = match ($entityType) {
            'cities' => $this->getContentBasedCityRecommendations($userPreferences, $limit),
            'jobs' => $this->getContentBasedJobRecommendations($userPreferences, $limit),
            'articles' => $this->getContentBasedArticleRecommendations($userPreferences, $limit),
            default => [],
        };

        return [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'recommendations' => $recommendations,
            'algorithm' => 'content_based',
            'preferences_used' => array_keys($userPreferences),
            'generated_at' => now(),
        ];
    }

    /**
     * Get hybrid recommendations combining multiple algorithms.
     */
    public function getHybridRecommendations(int $userId, string $entityType, int $limit = 10): array
    {
        $collaborativeRecs = $this->getCollaborativeFilteringRecommendations($userId, $entityType, $limit);
        $contentBasedRecs = $this->getContentBasedRecommendations($userId, $entityType, $limit);

        $hybridRecommendations = $this->combineRecommendations(
            $collaborativeRecs['recommendations'] ?? [],
            $contentBasedRecs['recommendations'] ?? [],
            $limit
        );

        return [
            'user_id' => $userId,
            'entity_type' => $entityType,
            'recommendations' => $hybridRecommendations,
            'algorithm' => 'hybrid',
            'components' => [
                'collaborative_filtering' => count($collaborativeRecs['recommendations'] ?? []),
                'content_based' => count($contentBasedRecs['recommendations'] ?? []),
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Build comprehensive user profile.
     */
    private function buildUserProfile(int $userId): array
    {
        $user = User::find($userId);
        if (! $user) {
            return [];
        }

        $behaviorAnalysis = $this->behaviorAnalysisService->analyzeUserBehavior($userId, 30);
        $recentEvents = UserBehaviorAnalytic::byUser($userId)
            ->where('event_timestamp', '>=', now()->subDays(7))
            ->get();

        return [
            'user_id' => $userId,
            'demographics' => [
                'age_range' => $user->age_range ?? null,
                'location' => $user->current_location,
                'work_type' => $user->work_type,
                'experience_level' => $user->experience_level ?? null,
            ],
            'preferences' => [
                'skills' => is_array($user->skills ?? null) ? $user->skills : explode(',', (string) ($user->skills ?? '')),
                'interests' => is_array($user->interests ?? null) ? $user->interests : explode(',', (string) ($user->interests ?? '')),
                'budget_min' => $user->budget_min ?? null,
                'budget_max' => $user->budget_max ?? null,
                'preferred_climate' => $user->preferred_climate ?? null,
                'salary_expectation_min' => $user->salary_expectation_min ?? null,
            ],
            'behavior' => [
                'engagement_score' => $behaviorAnalysis['engagement_score'] ?? 0,
                'event_types' => $behaviorAnalysis['event_types'] ?? [],
                'preferences' => $behaviorAnalysis['preferences'] ?? [],
                'session_frequency' => $this->calculateSessionFrequency($recentEvents),
                'peak_hours' => $behaviorAnalysis['behavior_patterns']['peak_hours'] ?? [],
            ],
            'interactions' => [
                'cities_viewed' => $recentEvents->where('entity_type', 'city')->pluck('entity_id')->unique()->toArray(),
                'jobs_applied' => $recentEvents->where('event_type', 'apply')->pluck('entity_id')->unique()->toArray(),
                'articles_read' => $recentEvents->where('entity_type', 'article')->pluck('entity_id')->unique()->toArray(),
            ],
        ];
    }

    /**
     * Recommend cities based on user profile.
     */
    private function recommendCities(array $userProfile, int $limit): array
    {
        $query = City::where('is_active', true);

        // Apply budget constraints
        if (isset($userProfile['preferences']['budget_min']) && $userProfile['preferences']['budget_min']) {
            $query->where('cost_of_living_index', '>=', $userProfile['preferences']['budget_min']);
        }
        if (isset($userProfile['preferences']['budget_max']) && $userProfile['preferences']['budget_max']) {
            $query->where('cost_of_living_index', '<=', $userProfile['preferences']['budget_max']);
        }

        // Apply climate preferences
        if (isset($userProfile['preferences']['preferred_climate']) && $userProfile['preferences']['preferred_climate']) {
            $query->where('description', 'like', '%'.$userProfile['preferences']['preferred_climate'].'%');
        }

        $cities = $query->limit($limit * 2)->get();

        return $cities->map(function ($city) use ($userProfile) {
            $score = $this->calculateCityRecommendationScore($city, $userProfile);

            return [
                'id' => $city->id,
                'name' => $city->name,
                'country' => $city->country->name,
                'cost_of_living_index' => $city->cost_of_living_index,
                'internet_speed_mbps' => $city->internet_speed_mbps,
                'safety_score' => $city->safety_score,
                'recommendation_score' => $score,
                'match_reasons' => $this->getCityMatchReasons($city, $userProfile),
            ];
        })->sortByDesc('recommendation_score')->take($limit)->values()->toArray();
    }

    /**
     * Recommend jobs based on user profile.
     */
    private function recommendJobs(array $userProfile, int $limit): array
    {
        $query = Job::active()->published()->notExpired();

        // Apply skills matching
        if (isset($userProfile['preferences']['skills']) && ! empty($userProfile['preferences']['skills'])) {
            $skills = $userProfile['preferences']['skills'];
            $query->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhere('tags', 'like', '%'.trim($skill).'%');
                }
            });
        }

        // Apply work type preferences
        if (isset($userProfile['preferences']['work_type']) && $userProfile['preferences']['work_type']) {
            $query->where('type', $userProfile['preferences']['work_type']);
        }

        // Apply salary expectations
        if (isset($userProfile['preferences']['salary_expectation_min']) && $userProfile['preferences']['salary_expectation_min']) {
            $query->where('salary_min', '>=', $userProfile['preferences']['salary_expectation_min']);
        }

        $jobs = $query->limit($limit * 2)->get();

        return $jobs->map(function ($job) use ($userProfile) {
            $score = $this->calculateJobRecommendationScore($job, $userProfile);

            return [
                'id' => $job->id,
                'title' => $job->title,
                'company' => $job->company->name,
                'location' => $job->location,
                'remote_type' => $job->remote_type,
                'salary_min' => $job->salary_min,
                'salary_max' => $job->salary_max,
                'tags' => $job->tags,
                'recommendation_score' => $score,
                'match_reasons' => $this->getJobMatchReasons($job, $userProfile),
            ];
        })->sortByDesc('recommendation_score')->take($limit)->values()->toArray();
    }

    /**
     * Recommend articles based on user profile.
     */
    private function recommendArticles(array $userProfile, int $limit): array
    {
        $query = Article::published();

        // Apply interest matching
        if (isset($userProfile['preferences']['interests']) && ! empty($userProfile['preferences']['interests'])) {
            $interests = $userProfile['preferences']['interests'];
            $query->where(function ($q) use ($interests) {
                foreach ($interests as $interest) {
                    $q->orWhere('title', 'like', '%'.trim($interest).'%')
                        ->orWhere('content', 'like', '%'.trim($interest).'%');
                }
            });
        }

        $articles = $query->limit($limit * 2)->get();

        return $articles->map(function ($article) use ($userProfile) {
            $score = $this->calculateArticleRecommendationScore($article, $userProfile);

            return [
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'published_at' => $article->published_at,
                'recommendation_score' => $score,
                'match_reasons' => $this->getArticleMatchReasons($article, $userProfile),
            ];
        })->sortByDesc('recommendation_score')->take($limit)->values()->toArray();
    }

    /**
     * Recommend mixed content types.
     */
    private function recommendMixedContent(array $userProfile, int $limit): array
    {
        $cityLimit = (int) ceil($limit / 3);
        $jobLimit = (int) ceil($limit / 3);
        $articleLimit = $limit - $cityLimit - $jobLimit;

        $cities = $this->recommendCities($userProfile, $cityLimit);
        $jobs = $this->recommendJobs($userProfile, $jobLimit);
        $articles = $this->recommendArticles($userProfile, $articleLimit);

        $mixedContent = array_merge($cities, $jobs, $articles);
        usort($mixedContent, function ($a, $b) {
            return $b['recommendation_score'] <=> $a['recommendation_score'];
        });

        return array_slice($mixedContent, 0, $limit);
    }

    /**
     * Find similar users based on behavior patterns.
     */
    private function findSimilarUsers(array $userProfile, string $entityType): array
    {
        $userInteractions = $userProfile['interactions'][$entityType.'_viewed'] ?? [];
        if (empty($userInteractions)) {
            return [];
        }

        // Find users who have interacted with similar entities
        $similarUsers = UserBehaviorAnalytic::where('entity_type', $entityType)
            ->whereIn('entity_id', $userInteractions)
            ->where('user_id', '!=', $userProfile['user_id'])
            ->selectRaw('user_id, COUNT(*) as common_interactions')
            ->groupBy('user_id')
            ->having('common_interactions', '>', 0)
            ->orderBy('common_interactions', 'desc')
            ->limit(50)
            ->get();

        return $similarUsers->pluck('user_id')->toArray();
    }

    /**
     * Get recommendations from similar users.
     */
    private function getRecommendationsFromSimilarUsers(array $similarUserIds, int $userId, string $entityType, int $limit): array
    {
        $excludeIds = UserBehaviorAnalytic::byUser($userId)
            ->byEntityType($entityType)
            ->pluck('entity_id')
            ->unique()
            ->toArray();

        $recommendations = UserBehaviorAnalytic::whereIn('user_id', $similarUserIds)
            ->where('entity_type', $entityType)
            ->whereNotIn('entity_id', $excludeIds)
            ->selectRaw('entity_id, COUNT(*) as recommendation_score')
            ->groupBy('entity_id')
            ->orderBy('recommendation_score', 'desc')
            ->limit($limit)
            ->get();

        return $recommendations->map(function ($rec) use ($entityType) {
            $entity = $this->getEntityById($rec->entity_id, $entityType);

            return [
                'id' => $rec->entity_id,
                'recommendation_score' => $rec->recommendation_score ?? 0,
                'entity_data' => $entity,
            ];
        })->toArray();
    }

    /**
     * Get content-based city recommendations.
     */
    private function getContentBasedCityRecommendations(array $userPreferences, int $limit): array
    {
        $query = City::where('is_active', true);

        if (isset($userPreferences['budget_min'])) {
            $query->where('cost_of_living_index', '>=', $userPreferences['budget_min']);
        }
        if (isset($userPreferences['budget_max'])) {
            $query->where('cost_of_living_index', '<=', $userPreferences['budget_max']);
        }
        if (isset($userPreferences['preferred_climate'])) {
            $query->where('description', 'like', '%'.$userPreferences['preferred_climate'].'%');
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Get content-based job recommendations.
     */
    private function getContentBasedJobRecommendations(array $userPreferences, int $limit): array
    {
        $query = Job::active()->published()->notExpired();

        if (isset($userPreferences['skills']) && ! empty($userPreferences['skills'])) {
            $query->where(function ($q) use ($userPreferences) {
                foreach ($userPreferences['skills'] as $skill) {
                    $q->orWhere('tags', 'like', '%'.trim($skill).'%');
                }
            });
        }

        if (isset($userPreferences['work_type'])) {
            $query->where('type', $userPreferences['work_type']);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Get content-based article recommendations.
     */
    private function getContentBasedArticleRecommendations(array $userPreferences, int $limit): array
    {
        $query = Article::published();

        if (isset($userPreferences['interests']) && ! empty($userPreferences['interests'])) {
            $query->where(function ($q) use ($userPreferences) {
                foreach ($userPreferences['interests'] as $interest) {
                    $q->orWhere('title', 'like', '%'.trim($interest).'%');
                }
            });
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Combine recommendations from different algorithms.
     */
    private function combineRecommendations(array $collaborativeRecs, array $contentBasedRecs, int $limit): array
    {
        $combined = [];
        $weights = ['collaborative' => 0.6, 'content_based' => 0.4];

        // Add collaborative filtering recommendations
        foreach ($collaborativeRecs as $rec) {
            $combined[$rec['id']] = [
                'id' => $rec['id'],
                'collaborative_score' => $rec['recommendation_score'] ?? 0,
                'content_based_score' => 0,
                'combined_score' => ($rec['recommendation_score'] ?? 0) * $weights['collaborative'],
            ];
        }

        // Add content-based recommendations
        foreach ($contentBasedRecs as $rec) {
            $id = $rec['id'];
            if (isset($combined[$id])) {
                $combined[$id]['content_based_score'] = 50; // Default score
                $combined[$id]['combined_score'] += 50 * $weights['content_based'];
            } else {
                $combined[$id] = [
                    'id' => $id,
                    'collaborative_score' => 0,
                    'content_based_score' => 50,
                    'combined_score' => 50 * $weights['content_based'],
                ];
            }
        }

        // Sort by combined score and return top recommendations
        usort($combined, function ($a, $b) {
            return $b['combined_score'] <=> $a['combined_score'];
        });

        return array_slice($combined, 0, $limit);
    }

    /**
     * Calculate city recommendation score.
     */
    private function calculateCityRecommendationScore(City $city, array $userProfile): float
    {
        $score = 0;

        // Budget match (40% weight)
        if (isset($userProfile['preferences']['budget_min']) && isset($userProfile['preferences']['budget_max'])) {
            $min = $userProfile['preferences']['budget_min'];
            $max = $userProfile['preferences']['budget_max'];
            if ($city->cost_of_living_index >= $min && $city->cost_of_living_index <= $max) {
                $score += 40;
            }
        }

        // Climate match (25% weight)
        if (isset($userProfile['preferences']['preferred_climate'])) {
            $climate = $userProfile['preferences']['preferred_climate'];
            if (str_contains(strtolower($city->description ?? ''), strtolower($climate))) {
                $score += 25;
            }
        }

        // Internet speed (20% weight)
        if ($city->internet_speed_mbps >= 50) {
            $score += 20;
        } elseif ($city->internet_speed_mbps >= 25) {
            $score += 10;
        }

        // Safety (15% weight)
        if ($city->safety_score >= 8) {
            $score += 15;
        } elseif ($city->safety_score >= 6) {
            $score += 10;
        }

        return min($score, 100);
    }

    /**
     * Calculate job recommendation score.
     */
    private function calculateJobRecommendationScore(Job $job, array $userProfile): float
    {
        $score = 0;

        // Skills match (50% weight)
        if (isset($userProfile['preferences']['skills']) && ! empty($userProfile['preferences']['skills'])) {
            $userSkills = $userProfile['preferences']['skills'];
            $jobTags = $job->tags ?? [];
            $skillMatches = count(array_intersect($userSkills, $jobTags));
            $score += min($skillMatches * 25, 50);
        }

        // Work type match (25% weight)
        if (isset($userProfile['preferences']['work_type'])) {
            if ($job->type === $userProfile['preferences']['work_type']) {
                $score += 25;
            }
        }

        // Salary match (25% weight)
        if (isset($userProfile['preferences']['salary_expectation_min'])) {
            if ($job->salary_min && $job->salary_min >= $userProfile['preferences']['salary_expectation_min']) {
                $score += 25;
            }
        }

        return min($score, 100);
    }

    /**
     * Calculate article recommendation score.
     */
    private function calculateArticleRecommendationScore($article, array $userProfile): float
    {
        $score = 0;

        // Interest match (60% weight)
        if (isset($userProfile['preferences']['interests']) && ! empty($userProfile['preferences']['interests'])) {
            $interests = $userProfile['preferences']['interests'];
            foreach ($interests as $interest) {
                if (str_contains(strtolower($article->title), strtolower($interest))) {
                    $score += 30;
                }
                if (str_contains(strtolower($article->content), strtolower($interest))) {
                    $score += 30;
                }
            }
        }

        // Recency (40% weight)
        $daysSincePublished = $article->published_at->diffInDays(now());
        if ($daysSincePublished <= 7) {
            $score += 40;
        } elseif ($daysSincePublished <= 30) {
            $score += 20;
        }

        return min($score, 100);
    }

    /**
     * Get city match reasons.
     */
    private function getCityMatchReasons(City $city, array $userProfile): array
    {
        $reasons = [];

        if (isset($userProfile['preferences']['budget_min']) && isset($userProfile['preferences']['budget_max'])) {
            $min = $userProfile['preferences']['budget_min'];
            $max = $userProfile['preferences']['budget_max'];
            if ($city->cost_of_living_index >= $min && $city->cost_of_living_index <= $max) {
                $reasons[] = 'Matches your budget range';
            }
        }

        if (isset($userProfile['preferences']['preferred_climate'])) {
            $climate = $userProfile['preferences']['preferred_climate'];
            if (str_contains(strtolower($city->description ?? ''), strtolower($climate))) {
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
    private function getJobMatchReasons(Job $job, array $userProfile): array
    {
        $reasons = [];

        if (isset($userProfile['preferences']['skills']) && ! empty($userProfile['preferences']['skills'])) {
            $userSkills = $userProfile['preferences']['skills'];
            $jobTags = $job->tags ?? [];
            $skillMatches = array_intersect($userSkills, $jobTags);
            if (! empty($skillMatches)) {
                $reasons[] = 'Matches your skills: '.implode(', ', $skillMatches);
            }
        }

        if (isset($userProfile['preferences']['work_type'])) {
            if ($job->type === $userProfile['preferences']['work_type']) {
                $reasons[] = 'Matches your work type preference';
            }
        }

        if ($job->remote_type === 'Full Remote') {
            $reasons[] = 'Fully remote position';
        }

        return $reasons;
    }

    /**
     * Get article match reasons.
     */
    private function getArticleMatchReasons($article, array $userProfile): array
    {
        $reasons = [];

        if (isset($userProfile['preferences']['interests']) && ! empty($userProfile['preferences']['interests'])) {
            $interests = $userProfile['preferences']['interests'];
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
     * Extract user preferences from profile.
     */
    private function extractUserPreferences(array $userProfile): array
    {
        return $userProfile['preferences'] ?? [];
    }

    /**
     * Calculate session frequency.
     */
    private function calculateSessionFrequency($events): float
    {
        $sessions = $events->pluck('session_id')->unique()->count();
        $days = 7; // Last 7 days

        return round($sessions / $days, 3);
    }

    /**
     * Get entity by ID and type.
     */
    private function getEntityById(int $entityId, string $entityType)
    {
        return match ($entityType) {
            'cities' => City::find($entityId),
            'jobs' => Job::find($entityId),
            'articles' => Article::find($entityId),
            default => null,
        };
    }

    /**
     * Create recommendation engine.
     */
    private function createRecommendationEngine(string $entityType): RecommendationEngine
    {
        return RecommendationEngine::create([
            'name' => ucfirst($entityType).' Recommendation Engine',
            'description' => 'AI-powered recommendation engine for '.$entityType,
            'engine_type' => 'hybrid',
            'target_entity' => $entityType,
            'algorithm_config' => [
                'collaborative_weight' => 0.6,
                'content_based_weight' => 0.4,
                'min_interactions' => 5,
            ],
            'status' => 'training',
        ]);
    }

    /**
     * Collect training data for recommendation engine.
     */
    private function collectTrainingData(string $entityType, int $days): array
    {
        $startDate = now()->subDays($days);

        return UserBehaviorAnalytic::byEntityType($entityType)
            ->byDateRange($startDate, now())
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($userEvents) {
                return [
                    'user_id' => $userEvents->first()->user_id,
                    'interactions' => $userEvents->pluck('entity_id')->toArray(),
                    'engagement_scores' => $userEvents->pluck('engagement_score')->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Build recommendation model.
     */
    private function buildRecommendationModel(array $trainingData, string $engineType): array
    {
        // Simplified model building - in practice, you'd use more sophisticated ML algorithms
        $model = [
            'engine_type' => $engineType,
            'training_samples' => count($trainingData),
            'feature_weights' => [
                'engagement_score' => 0.3,
                'recency' => 0.2,
                'frequency' => 0.3,
                'diversity' => 0.2,
            ],
            'model_params' => [
                'min_interactions' => 5,
                'similarity_threshold' => 0.7,
            ],
        ];

        return $model;
    }

    /**
     * Evaluate model accuracy.
     */
    private function evaluateModel(array $model, array $trainingData): float
    {
        // Simplified accuracy calculation
        $totalSamples = count($trainingData);
        $accuratePredictions = 0;

        foreach ($trainingData as $sample) {
            // Simulate prediction accuracy based on interaction patterns
            $interactionCount = count($sample['interactions']);
            if ($interactionCount >= $model['model_params']['min_interactions']) {
                $accuratePredictions++;
            }
        }

        return $totalSamples > 0 ? round(($accuratePredictions / $totalSamples) * 100, 2) : 0.0;
    }

    /**
     * Get active algorithm for entity type.
     */
    private function getActiveAlgorithm(string $entityType): string
    {
        $engine = RecommendationEngine::where('target_entity', $entityType)
            ->where('status', 'active')
            ->first();

        return $engine ? $engine->engine_type : 'hybrid';
    }

    /**
     * Calculate confidence score for recommendations.
     */
    private function calculateConfidenceScore(array $recommendations, array $userProfile): float
    {
        if (empty($recommendations)) {
            return 0.0;
        }

        $avgScore = array_sum(array_column($recommendations, 'recommendation_score')) / count($recommendations);
        $profileCompleteness = $this->calculateProfileCompleteness($userProfile);

        return round(($avgScore * $profileCompleteness) / 100, 2);
    }

    /**
     * Calculate profile completeness.
     */
    private function calculateProfileCompleteness(array $userProfile): float
    {
        $requiredFields = ['skills', 'interests', 'budget_min', 'work_type'];
        $completedFields = 0;

        foreach ($requiredFields as $field) {
            if (! empty($userProfile['preferences'][$field])) {
                $completedFields++;
            }
        }

        return ($completedFields / count($requiredFields)) * 100;
    }

    /**
     * Update recommendation metrics.
     */
    private function updateRecommendationMetrics(string $entityType, int $recommendationCount): void
    {
        $engine = RecommendationEngine::where('target_entity', $entityType)->first();
        if ($engine) {
            $engine->updateMetrics($recommendationCount);
        }
    }
}
