<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'overall_score',
        'skills_score',
        'experience_score',
        'location_score',
        'salary_score',
        'culture_score',
        'matching_factors',
        'ai_insights',
        'match_reasoning',
        'user_viewed',
        'user_applied',
        'user_saved',
        'viewed_at',
        'applied_at',
        'saved_at',
        'recommendation_type',
        'recommendation_rank',
        'recommendation_context',
        'ai_application_tips',
        'ai_resume_suggestions',
        'ai_cover_letter_tips',
    ];

    protected $casts = [
        'matching_factors' => 'array',
        'ai_insights' => 'array',
        'recommendation_context' => 'array',
        'user_viewed' => 'boolean',
        'user_applied' => 'boolean',
        'user_saved' => 'boolean',
        'viewed_at' => 'datetime',
        'applied_at' => 'datetime',
        'saved_at' => 'datetime',
        'overall_score' => 'decimal:2',
        'skills_score' => 'decimal:2',
        'experience_score' => 'decimal:2',
        'location_score' => 'decimal:2',
        'salary_score' => 'decimal:2',
        'culture_score' => 'decimal:2',
    ];

    /**
     * Get the user that owns the job match.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job that owns the job match.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Scope to get matches for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get matches for a specific job.
     */
    public function scopeForJob($query, int $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    /**
     * Scope to get matches above a certain score.
     */
    public function scopeAboveScore($query, float $score)
    {
        return $query->where('overall_score', '>=', $score);
    }

    /**
     * Scope to get viewed matches.
     */
    public function scopeViewed($query)
    {
        return $query->where('user_viewed', true);
    }

    /**
     * Scope to get applied matches.
     */
    public function scopeApplied($query)
    {
        return $query->where('user_applied', true);
    }

    /**
     * Scope to get saved matches.
     */
    public function scopeSaved($query)
    {
        return $query->where('user_saved', true);
    }

    /**
     * Scope to get algorithmic recommendations.
     */
    public function scopeAlgorithmic($query)
    {
        return $query->where('recommendation_type', 'algorithmic');
    }

    /**
     * Scope to get AI recommendations.
     */
    public function scopeAiRecommended($query)
    {
        return $query->where('recommendation_type', 'ai');
    }

    /**
     * Mark the match as viewed by the user.
     */
    public function markAsViewed(): void
    {
        $this->update([
            'user_viewed' => true,
            'viewed_at' => now(),
        ]);
    }

    /**
     * Mark the match as applied by the user.
     */
    public function markAsApplied(): void
    {
        $this->update([
            'user_applied' => true,
            'applied_at' => now(),
        ]);
    }

    /**
     * Mark the match as saved by the user.
     */
    public function markAsSaved(): void
    {
        $this->update([
            'user_saved' => true,
            'saved_at' => now(),
        ]);
    }

    /**
     * Get the match score as a percentage.
     */
    public function getScorePercentageAttribute(): string
    {
        return number_format((float) $this->overall_score, 1).'%';
    }

    /**
     * Get the match quality level.
     */
    public function getQualityLevelAttribute(): string
    {
        if ($this->overall_score >= 90) {
            return 'Excellent';
        } elseif ($this->overall_score >= 80) {
            return 'Very Good';
        } elseif ($this->overall_score >= 70) {
            return 'Good';
        } elseif ($this->overall_score >= 60) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    /**
     * Get the match quality color for UI.
     */
    public function getQualityColorAttribute(): string
    {
        if ($this->overall_score >= 90) {
            return 'green';
        } elseif ($this->overall_score >= 80) {
            return 'blue';
        } elseif ($this->overall_score >= 70) {
            return 'yellow';
        } elseif ($this->overall_score >= 60) {
            return 'orange';
        } else {
            return 'red';
        }
    }

    /**
     * Get formatted matching factors.
     */
    public function getFormattedMatchingFactorsAttribute(): array
    {
        if (! $this->matching_factors) {
            return [];
        }

        $factors = [];
        foreach ($this->matching_factors as $factor => $value) {
            $factors[] = [
                'factor' => ucfirst(str_replace('_', ' ', $factor)),
                'value' => $value,
                'score' => $this->getAttribute($factor.'_score') ?? 0,
            ];
        }

        return $factors;
    }

    /**
     * Get AI insights summary.
     */
    public function getAiInsightsSummaryAttribute(): string
    {
        if (! $this->ai_insights || ! isset($this->ai_insights['insights'])) {
            return 'No AI insights available.';
        }

        $insights = $this->ai_insights['insights'];

        return is_string($insights) ? $insights : 'AI insights generated successfully.';
    }

    /**
     * Get application tips.
     */
    public function getApplicationTipsAttribute(): array
    {
        if (empty($this->ai_application_tips)) {
            return [];
        }

        return (array) $this->ai_application_tips;
    }

    /**
     * Get resume suggestions.
     */
    public function getResumeSuggestionsAttribute(): array
    {
        if (empty($this->ai_resume_suggestions)) {
            return [];
        }

        return (array) $this->ai_resume_suggestions;
    }

    /**
     * Get cover letter tips.
     */
    public function getCoverLetterTipsAttribute(): array
    {
        if (empty($this->ai_cover_letter_tips)) {
            return [];
        }

        return (array) $this->ai_cover_letter_tips;
    }
}
