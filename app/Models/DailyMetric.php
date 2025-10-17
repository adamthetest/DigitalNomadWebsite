<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DailyMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'metric_type',
        'entity_type',
        'entity_id',
        'metrics',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'metrics' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the entity that owns the metric.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Scope for specific metric type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope for specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
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
     * Get metrics for a specific city.
     */
    public static function getCityMetrics($cityId, $startDate = null, $endDate = null)
    {
        $query = static::forEntity('city', $cityId);
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return $query->orderBy('date')->get();
    }

    /**
     * Get global metrics.
     */
    public static function getGlobalMetrics($metricType, $startDate = null, $endDate = null)
    {
        $query = static::byType($metricType)->whereNull('entity_id');
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return $query->orderBy('date')->get();
    }

    /**
     * Store city cost metrics.
     */
    public static function storeCityCostMetrics($cityId, $date, array $costData)
    {
        return static::updateOrCreate(
            [
                'date' => $date,
                'metric_type' => 'city_cost',
                'entity_type' => 'city',
                'entity_id' => $cityId,
            ],
            [
                'metrics' => $costData,
                'metadata' => [
                    'source' => 'manual_entry',
                    'currency' => 'USD',
                ],
            ]
        );
    }

    /**
     * Store traffic metrics.
     */
    public static function storeTrafficMetrics($date, array $trafficData)
    {
        return static::updateOrCreate(
            [
                'date' => $date,
                'metric_type' => 'traffic',
                'entity_type' => 'global',
                'entity_id' => null,
            ],
            [
                'metrics' => $trafficData,
                'metadata' => [
                    'source' => 'analytics',
                ],
            ]
        );
    }

    /**
     * Store user activity metrics.
     */
    public static function storeUserActivityMetrics($date, array $activityData)
    {
        return static::updateOrCreate(
            [
                'date' => $date,
                'metric_type' => 'user_activity',
                'entity_type' => 'global',
                'entity_id' => null,
            ],
            [
                'metrics' => $activityData,
                'metadata' => [
                    'source' => 'application_logs',
                ],
            ]
        );
    }

    /**
     * Store job posting metrics.
     */
    public static function storeJobMetrics($date, array $jobData)
    {
        return static::updateOrCreate(
            [
                'date' => $date,
                'metric_type' => 'job_postings',
                'entity_type' => 'global',
                'entity_id' => null,
            ],
            [
                'metrics' => $jobData,
                'metadata' => [
                    'source' => 'job_scraping',
                ],
            ]
        );
    }

    /**
     * Get trend data for analysis.
     */
    public static function getTrendData($metricType, $entityType = null, $entityId = null, $days = 30)
    {
        $query = static::byType($metricType)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date');

        if ($entityType) {
            $query->forEntity($entityType, $entityId);
        }

        return $query->get();
    }
}