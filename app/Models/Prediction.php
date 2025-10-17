<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Prediction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'prediction_type',
        'entity_type',
        'entity_id',
        'prediction_date',
        'prediction_data',
        'confidence_scores',
        'factors',
        'model_version',
        'generated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prediction_date' => 'date',
        'prediction_data' => 'array',
        'confidence_scores' => 'array',
        'factors' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Get the entity that this prediction is for.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Scope for specific prediction type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('prediction_type', $type);
    }

    /**
     * Scope for specific entity.
     */
    public function scopeForEntity($query, string $entityType, $entityId = null)
    {
        $query = $query->where('entity_type', $entityType);
        
        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }
        
        return $query;
    }

    /**
     * Scope for future predictions.
     */
    public function scopeFuture($query)
    {
        return $query->where('prediction_date', '>', now());
    }

    /**
     * Scope for recent predictions.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    /**
     * Store cost trend prediction for a city.
     */
    public static function storeCostTrendPrediction($cityId, $predictionDate, array $predictionData, array $confidenceScores = [], array $factors = [])
    {
        return static::updateOrCreate(
            [
                'prediction_type' => 'cost_trend',
                'entity_type' => 'city',
                'entity_id' => $cityId,
                'prediction_date' => $predictionDate,
            ],
            [
                'prediction_data' => $predictionData,
                'confidence_scores' => $confidenceScores,
                'factors' => $factors,
                'model_version' => '1.0',
                'generated_at' => now(),
            ]
        );
    }

    /**
     * Store trending city prediction.
     */
    public static function storeTrendingCityPrediction($cityId, $predictionDate, array $predictionData, array $confidenceScores = [], array $factors = [])
    {
        return static::updateOrCreate(
            [
                'prediction_type' => 'trending_city',
                'entity_type' => 'city',
                'entity_id' => $cityId,
                'prediction_date' => $predictionDate,
            ],
            [
                'prediction_data' => $predictionData,
                'confidence_scores' => $confidenceScores,
                'factors' => $factors,
                'model_version' => '1.0',
                'generated_at' => now(),
            ]
        );
    }

    /**
     * Store user growth prediction.
     */
    public static function storeUserGrowthPrediction($predictionDate, array $predictionData, array $confidenceScores = [], array $factors = [])
    {
        return static::updateOrCreate(
            [
                'prediction_type' => 'user_growth',
                'entity_type' => 'global',
                'entity_id' => null,
                'prediction_date' => $predictionDate,
            ],
            [
                'prediction_data' => $predictionData,
                'confidence_scores' => $confidenceScores,
                'factors' => $factors,
                'model_version' => '1.0',
                'generated_at' => now(),
            ]
        );
    }

    /**
     * Store engagement prediction.
     */
    public static function storeEngagementPrediction($predictionDate, array $predictionData, array $confidenceScores = [], array $factors = [])
    {
        return static::updateOrCreate(
            [
                'prediction_type' => 'engagement',
                'entity_type' => 'global',
                'entity_id' => null,
                'prediction_date' => $predictionDate,
            ],
            [
                'prediction_data' => $predictionData,
                'confidence_scores' => $confidenceScores,
                'factors' => $factors,
                'model_version' => '1.0',
                'generated_at' => now(),
            ]
        );
    }

    /**
     * Get predictions for a specific city.
     */
    public static function getCityPredictions($cityId, $predictionType = null)
    {
        $query = static::forEntity('city', $cityId);
        
        if ($predictionType) {
            $query->byType($predictionType);
        }
        
        return $query->orderBy('prediction_date')->get();
    }

    /**
     * Get global predictions.
     */
    public static function getGlobalPredictions($predictionType = null)
    {
        $query = static::whereNull('entity_id');
        
        if ($predictionType) {
            $query->byType($predictionType);
        }
        
        return $query->orderBy('prediction_date')->get();
    }

    /**
     * Get latest predictions.
     */
    public static function getLatestPredictions($predictionType, $entityType = null, $entityId = null)
    {
        $query = static::byType($predictionType)->recent();
        
        if ($entityType) {
            $query->forEntity($entityType, $entityId);
        }
        
        return $query->orderBy('prediction_date')->get();
    }

    /**
     * Get prediction accuracy score.
     */
    public function getAccuracyScore(): float
    {
        $confidenceScores = $this->confidence_scores ?? [];
        
        if (empty($confidenceScores)) {
            return 0.0;
        }
        
        return array_sum($confidenceScores) / count($confidenceScores);
    }

    /**
     * Check if prediction is still valid.
     */
    public function isValid(): bool
    {
        return $this->generated_at->diffInDays(now()) <= 7; // Valid for 7 days
    }
}