<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendationEngine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'engine_type',
        'target_entity',
        'algorithm_config',
        'feature_weights',
        'training_data',
        'accuracy_score',
        'recommendation_count',
        'click_through_rate',
        'conversion_rate',
        'status',
        'last_trained_at',
        'last_used_at',
    ];

    protected $casts = [
        'algorithm_config' => 'array',
        'feature_weights' => 'array',
        'training_data' => 'array',
        'accuracy_score' => 'decimal:2',
        'click_through_rate' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'last_trained_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Scope to filter by engine type.
     */
    public function scopeByEngineType($query, string $engineType)
    {
        return $query->where('engine_type', $engineType);
    }

    /**
     * Scope to filter by target entity.
     */
    public function scopeByTargetEntity($query, string $targetEntity)
    {
        return $query->where('target_entity', $targetEntity);
    }

    /**
     * Scope to filter active engines.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the engine is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the engine needs retraining.
     */
    public function needsRetraining(int $daysThreshold = 30): bool
    {
        if (! $this->last_trained_at) {
            return true;
        }

        return $this->last_trained_at->diffInDays(now()) > $daysThreshold;
    }

    /**
     * Get the feature weight for a specific feature.
     */
    public function getFeatureWeight(string $feature): float
    {
        $weights = $this->feature_weights ?? [];
        return $weights[$feature] ?? 0.0;
    }

    /**
     * Update the engine's performance metrics.
     */
    public function updateMetrics(int $recommendations = 1, int $clicks = 0, int $conversions = 0): void
    {
        $this->recommendation_count += $recommendations;

        if ($recommendations > 0) {
            $newCtr = $this->click_through_rate ?? 0;
            $newCtr = (($newCtr * ($this->recommendation_count - $recommendations)) + $clicks) / $this->recommendation_count;
            $this->click_through_rate = round($newCtr, 2);
        }

        if ($clicks > 0) {
            $newCvr = $this->conversion_rate ?? 0;
            $newCvr = (($newCvr * ($this->recommendation_count - $recommendations)) + $conversions) / $this->recommendation_count;
            $this->conversion_rate = round($newCvr, 2);
        }

        $this->last_used_at = now();
        $this->save();
    }

    /**
     * Mark the engine as trained.
     */
    public function markAsTrained(float $accuracyScore = null): void
    {
        $this->update([
            'status' => 'active',
            'last_trained_at' => now(),
            'accuracy_score' => $accuracyScore ?? $this->accuracy_score,
        ]);
    }

    /**
     * Get the algorithm configuration for a specific key.
     */
    public function getAlgorithmConfig(string $key, $default = null)
    {
        $config = $this->algorithm_config ?? [];
        return $config[$key] ?? $default;
    }

    /**
     * Set the algorithm configuration for a specific key.
     */
    public function setAlgorithmConfig(string $key, $value): void
    {
        $config = $this->algorithm_config ?? [];
        $config[$key] = $value;
        $this->algorithm_config = $config;
    }

    /**
     * Get performance score based on multiple metrics.
     */
    public function getPerformanceScore(): float
    {
        $ctr = $this->click_through_rate ?? 0;
        $cvr = $this->conversion_rate ?? 0;
        $accuracy = $this->accuracy_score ?? 0;

        // Weighted performance score
        $score = ($ctr * 0.3) + ($cvr * 0.5) + ($accuracy * 0.2);

        return round($score, 2);
    }

    /**
     * Check if the engine is performing well.
     */
    public function isPerformingWell(float $threshold = 0.7): bool
    {
        return $this->getPerformanceScore() >= $threshold;
    }
}