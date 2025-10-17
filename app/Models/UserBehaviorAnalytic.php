<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBehaviorAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'event_type',
        'entity_type',
        'entity_id',
        'event_data',
        'user_context',
        'page_url',
        'referrer',
        'user_agent',
        'ip_address',
        'engagement_score',
        'event_timestamp',
    ];

    protected $casts = [
        'event_data' => 'array',
        'user_context' => 'array',
        'engagement_score' => 'decimal:2',
        'event_timestamp' => 'datetime',
    ];

    /**
     * Get the user that owns the behavior analytic.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by session.
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by engagement score.
     */
    public function scopeByEngagementScore($query, float $minScore)
    {
        return $query->where('engagement_score', '>=', $minScore);
    }

    /**
     * Get the entity model based on entity_type and entity_id.
     */
    public function getEntityAttribute()
    {
        if (! $this->entity_type || ! $this->entity_id) {
            return null;
        }

        $modelClass = match ($this->entity_type) {
            'city' => City::class,
            'job' => Job::class,
            'article' => Article::class,
            'deal' => Deal::class,
            default => null,
        };

        return $modelClass ? $modelClass::find($this->entity_id) : null;
    }

    /**
     * Calculate engagement score based on event data.
     */
    public function calculateEngagementScore(): float
    {
        $baseScore = match ($this->event_type) {
            'page_view' => 1.0,
            'click' => 2.0,
            'search' => 3.0,
            'favorite' => 5.0,
            'apply' => 10.0,
            'share' => 8.0,
            'comment' => 6.0,
            default => 1.0,
        };

        // Apply multipliers based on user context
        $multiplier = 1.0;
        if ($this->user_context) {
            // Higher engagement for returning users
            if (isset($this->user_context['is_returning']) && $this->user_context['is_returning']) {
                $multiplier += 0.2;
            }

            // Higher engagement for users with complete profiles
            if (isset($this->user_context['profile_completion']) && $this->user_context['profile_completion'] > 0.8) {
                $multiplier += 0.3;
            }

            // Higher engagement for premium users
            if (isset($this->user_context['is_premium']) && $this->user_context['is_premium']) {
                $multiplier += 0.5;
            }
        }

        return round($baseScore * $multiplier, 2);
    }
}