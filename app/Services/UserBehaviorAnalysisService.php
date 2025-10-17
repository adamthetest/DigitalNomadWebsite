<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBehaviorAnalytic;
use Illuminate\Support\Str;

class UserBehaviorAnalysisService
{
    protected array $eventWeights = [
        'page_view' => 1.0,
        'click' => 2.0,
        'search' => 3.0,
        'favorite' => 5.0,
        'apply' => 10.0,
        'share' => 8.0,
        'comment' => 6.0,
        'download' => 4.0,
        'signup' => 15.0,
        'purchase' => 20.0,
    ];

    /**
     * Track a user behavior event.
     */
    public function trackEvent(
        string $eventType,
        ?int $userId = null,
        ?string $sessionId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        array $eventData = [],
        array $userContext = []
    ): UserBehaviorAnalytic {
        $analytic = UserBehaviorAnalytic::create([
            'user_id' => $userId,
            'session_id' => $sessionId ?? $this->generateSessionId(),
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'event_data' => $eventData,
            'user_context' => $this->enrichUserContext($userId, $userContext),
            'page_url' => request()->url(),
            'referrer' => request()->header('referer'),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'engagement_score' => $this->calculateEngagementScore($eventType, $userContext),
            'event_timestamp' => now(),
        ]);

        // Update user's last activity
        if ($userId) {
            User::where('id', $userId)->update(['last_active_at' => now()]);
        }

        return $analytic;
    }

    /**
     * Analyze user behavior patterns.
     */
    public function analyzeUserBehavior(int $userId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $events = UserBehaviorAnalytic::byUser($userId)
            ->byDateRange($startDate, $endDate)
            ->orderBy('event_timestamp')
            ->get();

        return [
            'user_id' => $userId,
            'analysis_period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => $days,
            ],
            'total_events' => $events->count(),
            'event_types' => $this->analyzeEventTypes($events),
            'engagement_score' => $this->calculateOverallEngagementScore($events),
            'behavior_patterns' => $this->identifyBehaviorPatterns($events),
            'preferences' => $this->extractUserPreferences($events),
            'session_analysis' => $this->analyzeSessions($events),
            'recommendations' => $this->generateBehaviorRecommendations($events),
        ];
    }

    /**
     * Get user engagement score.
     */
    public function getUserEngagementScore(int $userId, int $days = 7): float
    {
        $startDate = now()->subDays($days);

        $events = UserBehaviorAnalytic::byUser($userId)
            ->byDateRange($startDate, now())
            ->get();

        if ($events->isEmpty()) {
            return 0.0;
        }

        $totalScore = $events->sum('engagement_score');
        $maxPossibleScore = $events->count() * max($this->eventWeights);

        return round(($totalScore / $maxPossibleScore) * 100, 2);
    }

    /**
     * Predict user churn probability.
     */
    public function predictChurnProbability(int $userId): array
    {
        $recentEvents = UserBehaviorAnalytic::byUser($userId)
            ->byDateRange(now()->subDays(14), now())
            ->get();

        $engagementScore = $this->getUserEngagementScore($userId, 14);
        $daysSinceLastActivity = $this->getDaysSinceLastActivity($userId);
        $sessionFrequency = $this->getSessionFrequency($userId, 30);

        // Churn prediction algorithm
        $churnScore = 0;

        // Low engagement increases churn probability
        if ($engagementScore < 20) {
            $churnScore += 40;
        } elseif ($engagementScore < 50) {
            $churnScore += 20;
        }

        // Long inactivity increases churn probability
        if ($daysSinceLastActivity > 7) {
            $churnScore += 30;
        } elseif ($daysSinceLastActivity > 3) {
            $churnScore += 15;
        }

        // Low session frequency increases churn probability
        if ($sessionFrequency < 0.1) {
            $churnScore += 30;
        } elseif ($sessionFrequency < 0.3) {
            $churnScore += 15;
        }

        $churnProbability = min($churnScore, 100);

        return [
            'user_id' => $userId,
            'churn_probability' => $churnProbability,
            'risk_level' => $this->getRiskLevel($churnProbability),
            'factors' => [
                'engagement_score' => $engagementScore,
                'days_since_last_activity' => $daysSinceLastActivity,
                'session_frequency' => $sessionFrequency,
            ],
            'recommendations' => $this->getChurnPreventionRecommendations($churnProbability),
        ];
    }

    /**
     * Get user journey analysis.
     */
    public function analyzeUserJourney(int $userId): array
    {
        $events = UserBehaviorAnalytic::byUser($userId)
            ->orderBy('event_timestamp')
            ->get();

        $journey = [];
        $currentSession = null;

        foreach ($events as $event) {
            if ($currentSession !== $event->session_id) {
                if ($currentSession !== null) {
                    $journey[] = $this->analyzeSession($events->where('session_id', $currentSession));
                }
                $currentSession = $event->session_id;
            }
        }

        // Add the last session
        if ($currentSession !== null) {
            $journey[] = $this->analyzeSession($events->where('session_id', $currentSession));
        }

        return [
            'user_id' => $userId,
            'total_sessions' => count($journey),
            'journey' => $journey,
            'conversion_funnel' => $this->analyzeConversionFunnel($events),
            'drop_off_points' => $this->identifyDropOffPoints($events),
        ];
    }

    /**
     * Generate session ID.
     */
    private function generateSessionId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Enrich user context with additional data.
     */
    private function enrichUserContext(?int $userId, array $userContext): array
    {
        if (! $userId) {
            return $userContext;
        }

        $user = User::find($userId);
        if (! $user) {
            return $userContext;
        }

        return array_merge($userContext, [
            'is_returning' => $user->last_active_at && $user->last_active_at->diffInDays(now()) > 1,
            'profile_completion' => $this->calculateProfileCompletion($user),
            'is_premium' => $user->is_premium ?? false,
            'user_type' => $user->user_type ?? 'member',
            'location' => $user->current_location,
        ]);
    }

    /**
     * Calculate profile completion percentage.
     */
    private function calculateProfileCompletion(User $user): float
    {
        $requiredFields = ['first_name', 'last_name', 'email', 'bio', 'skills', 'work_type'];
        $completedFields = 0;

        foreach ($requiredFields as $field) {
            if (! empty($user->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($requiredFields)) * 100, 2);
    }

    /**
     * Calculate engagement score for an event.
     */
    private function calculateEngagementScore(string $eventType, array $userContext): float
    {
        $baseScore = $this->eventWeights[$eventType] ?? 1.0;
        $multiplier = 1.0;

        // Apply context multipliers
        if (isset($userContext['is_returning']) && $userContext['is_returning']) {
            $multiplier += 0.2;
        }

        if (isset($userContext['profile_completion']) && $userContext['profile_completion'] > 80) {
            $multiplier += 0.3;
        }

        if (isset($userContext['is_premium']) && $userContext['is_premium']) {
            $multiplier += 0.5;
        }

        return round($baseScore * $multiplier, 2);
    }

    /**
     * Analyze event types distribution.
     */
    private function analyzeEventTypes($events): array
    {
        $eventTypes = $events->groupBy('event_type');
        $analysis = [];

        foreach ($eventTypes as $type => $typeEvents) {
            $analysis[$type] = [
                'count' => $typeEvents->count(),
                'percentage' => round(($typeEvents->count() / $events->count()) * 100, 2),
                'avg_engagement_score' => round($typeEvents->avg('engagement_score'), 2),
            ];
        }

        return $analysis;
    }

    /**
     * Calculate overall engagement score.
     */
    private function calculateOverallEngagementScore($events): float
    {
        if ($events->isEmpty()) {
            return 0.0;
        }

        $totalScore = $events->sum('engagement_score');
        $maxPossibleScore = $events->count() * max($this->eventWeights);

        return round(($totalScore / $maxPossibleScore) * 100, 2);
    }

    /**
     * Identify behavior patterns.
     */
    private function identifyBehaviorPatterns($events): array
    {
        $patterns = [];

        // Peak activity hours
        $hourlyActivity = $events->groupBy(function ($event) {
            return $event->event_timestamp->hour;
        });

        $peakHours = $hourlyActivity->map(function ($hourEvents) {
            return $hourEvents->count();
        })->sortDesc()->take(3);

        $patterns['peak_hours'] = $peakHours->keys()->toArray();

        // Most active days
        $dailyActivity = $events->groupBy(function ($event) {
            return $event->event_timestamp->format('Y-m-d');
        });

        $patterns['most_active_days'] = $dailyActivity->map(function ($dayEvents) {
            return $dayEvents->count();
        })->sortDesc()->take(5)->keys()->toArray();

        // Entity preferences
        $entityPreferences = $events->whereNotNull('entity_type')
            ->groupBy('entity_type')
            ->map(function ($entityEvents) {
                return $entityEvents->count();
            })
            ->sortDesc();

        $patterns['entity_preferences'] = $entityPreferences->toArray();

        return $patterns;
    }

    /**
     * Extract user preferences from behavior.
     */
    private function extractUserPreferences($events): array
    {
        $preferences = [];

        // City preferences
        $cityEvents = $events->where('entity_type', 'city');
        if ($cityEvents->isNotEmpty()) {
            $preferences['cities'] = $cityEvents->pluck('entity_id')->unique()->toArray();
        }

        // Job preferences
        $jobEvents = $events->where('entity_type', 'job');
        if ($jobEvents->isNotEmpty()) {
            $preferences['jobs'] = $jobEvents->pluck('entity_id')->unique()->toArray();
        }

        // Content preferences
        $articleEvents = $events->where('entity_type', 'article');
        if ($articleEvents->isNotEmpty()) {
            $preferences['articles'] = $articleEvents->pluck('entity_id')->unique()->toArray();
        }

        return $preferences;
    }

    /**
     * Analyze user sessions.
     */
    private function analyzeSessions($events): array
    {
        $sessions = $events->groupBy('session_id');
        $sessionAnalysis = [];

        foreach ($sessions as $sessionId => $sessionEvents) {
            $sessionAnalysis[] = [
                'session_id' => $sessionId,
                'duration_minutes' => $this->calculateSessionDuration($sessionEvents),
                'event_count' => $sessionEvents->count(),
                'engagement_score' => round($sessionEvents->avg('engagement_score'), 2),
                'start_time' => $sessionEvents->min('event_timestamp'),
                'end_time' => $sessionEvents->max('event_timestamp'),
            ];
        }

        return [
            'total_sessions' => count($sessionAnalysis),
            'avg_session_duration' => round(collect($sessionAnalysis)->avg('duration_minutes'), 2),
            'avg_events_per_session' => round(collect($sessionAnalysis)->avg('event_count'), 2),
            'sessions' => $sessionAnalysis,
        ];
    }

    /**
     * Calculate session duration in minutes.
     */
    private function calculateSessionDuration($sessionEvents): float
    {
        $startTime = $sessionEvents->min('event_timestamp');
        $endTime = $sessionEvents->max('event_timestamp');

        return $startTime->diffInMinutes($endTime);
    }

    /**
     * Generate behavior-based recommendations.
     */
    private function generateBehaviorRecommendations($events): array
    {
        $recommendations = [];

        // Low engagement recommendation
        $avgEngagement = $events->avg('engagement_score');
        if ($avgEngagement < 3.0) {
            $recommendations[] = [
                'type' => 'engagement',
                'message' => 'Consider exploring more interactive features to increase engagement',
                'priority' => 'high',
            ];
        }

        // Session frequency recommendation
        $sessions = $events->groupBy('session_id');
        if ($sessions->count() < 3) {
            $recommendations[] = [
                'type' => 'frequency',
                'message' => 'Try to visit more regularly to discover new content',
                'priority' => 'medium',
            ];
        }

        return $recommendations;
    }

    /**
     * Get days since last activity.
     */
    private function getDaysSinceLastActivity(int $userId): int
    {
        $lastEvent = UserBehaviorAnalytic::byUser($userId)
            ->orderBy('event_timestamp', 'desc')
            ->first();

        return $lastEvent ? $lastEvent->event_timestamp->diffInDays(now()) : 999;
    }

    /**
     * Get session frequency (sessions per day).
     */
    private function getSessionFrequency(int $userId, int $days): float
    {
        $startDate = now()->subDays($days);
        $sessions = UserBehaviorAnalytic::byUser($userId)
            ->byDateRange($startDate, now())
            ->distinct('session_id')
            ->count();

        return round($sessions / $days, 3);
    }

    /**
     * Get risk level based on churn probability.
     */
    private function getRiskLevel(float $churnProbability): string
    {
        if ($churnProbability >= 70) {
            return 'high';
        } elseif ($churnProbability >= 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get churn prevention recommendations.
     */
    private function getChurnPreventionRecommendations(float $churnProbability): array
    {
        $recommendations = [];

        if ($churnProbability >= 70) {
            $recommendations[] = 'Send personalized re-engagement email';
            $recommendations[] = 'Offer exclusive content or discounts';
            $recommendations[] = 'Schedule follow-up call or survey';
        } elseif ($churnProbability >= 40) {
            $recommendations[] = 'Send weekly newsletter with personalized content';
            $recommendations[] = 'Invite to community events or webinars';
        } else {
            $recommendations[] = 'Continue regular engagement activities';
        }

        return $recommendations;
    }

    /**
     * Analyze individual session.
     */
    private function analyzeSession($sessionEvents): array
    {
        return [
            'session_id' => $sessionEvents->first()->session_id,
            'duration_minutes' => $this->calculateSessionDuration($sessionEvents),
            'event_count' => $sessionEvents->count(),
            'engagement_score' => round($sessionEvents->avg('engagement_score'), 2),
            'events' => $sessionEvents->map(function ($event) {
                return [
                    'type' => $event->event_type,
                    'entity_type' => $event->entity_type,
                    'entity_id' => $event->entity_id,
                    'timestamp' => $event->event_timestamp,
                    'engagement_score' => $event->engagement_score,
                ];
            })->toArray(),
        ];
    }

    /**
     * Analyze conversion funnel.
     */
    private function analyzeConversionFunnel($events): array
    {
        $funnelSteps = [
            'page_view' => $events->where('event_type', 'page_view')->count(),
            'click' => $events->where('event_type', 'click')->count(),
            'search' => $events->where('event_type', 'search')->count(),
            'favorite' => $events->where('event_type', 'favorite')->count(),
            'apply' => $events->where('event_type', 'apply')->count(),
        ];

        $conversionRates = [];
        $previousCount = $funnelSteps['page_view'];

        foreach ($funnelSteps as $step => $count) {
            if ($previousCount > 0) {
                $conversionRates[$step] = round(($count / $previousCount) * 100, 2);
            } else {
                $conversionRates[$step] = 0;
            }
            $previousCount = $count;
        }

        return [
            'steps' => $funnelSteps,
            'conversion_rates' => $conversionRates,
        ];
    }

    /**
     * Identify drop-off points in user journey.
     */
    private function identifyDropOffPoints($events): array
    {
        $dropOffPoints = [];

        $funnelSteps = ['page_view', 'click', 'search', 'favorite', 'apply'];
        $stepCounts = [];

        foreach ($funnelSteps as $step) {
            $stepCounts[$step] = $events->where('event_type', $step)->count();
        }

        $previousCount = $stepCounts['page_view'];
        foreach ($stepCounts as $step => $count) {
            if ($previousCount > 0) {
                $dropOffRate = round((($previousCount - $count) / $previousCount) * 100, 2);
                if ($dropOffRate > 50) {
                    $dropOffPoints[] = [
                        'step' => $step,
                        'drop_off_rate' => $dropOffRate,
                        'users_lost' => $previousCount - $count,
                    ];
                }
            }
            $previousCount = $count;
        }

        return $dropOffPoints;
    }
}
