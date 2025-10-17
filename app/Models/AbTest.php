<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'test_type',
        'target_element',
        'variants',
        'traffic_allocation',
        'status',
        'success_metrics',
        'targeting_rules',
        'start_date',
        'end_date',
        'results',
        'winner_variant',
        'confidence_level',
    ];

    protected $casts = [
        'variants' => 'array',
        'traffic_allocation' => 'array',
        'success_metrics' => 'array',
        'targeting_rules' => 'array',
        'results' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'confidence_level' => 'decimal:2',
    ];

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by test type.
     */
    public function scopeByTestType($query, string $testType)
    {
        return $query->where('test_type', $testType);
    }

    /**
     * Scope to filter active tests.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter completed tests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if the test is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->start_date <= now() &&
               ($this->end_date === null || $this->end_date >= now());
    }

    /**
     * Check if the test is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' ||
               ($this->end_date !== null && $this->end_date < now());
    }

    /**
     * Get the traffic allocation for a specific variant.
     */
    public function getTrafficAllocationForVariant(string $variant): float
    {
        $allocation = $this->traffic_allocation ?? [];

        return $allocation[$variant] ?? 0.0;
    }

    /**
     * Get the winner variant if test is completed.
     */
    public function getWinner(): ?string
    {
        return $this->isCompleted() ? $this->winner_variant : null;
    }

    /**
     * Check if statistical significance is reached.
     */
    public function hasStatisticalSignificance(): bool
    {
        return $this->confidence_level !== null && $this->confidence_level >= 95.0;
    }

    /**
     * Calculate conversion rate for a variant.
     */
    public function getConversionRateForVariant(string $variant): float
    {
        $results = $this->results ?? [];
        $variantResults = $results[$variant] ?? [];

        $conversions = $variantResults['conversions'] ?? 0;
        $visitors = $variantResults['visitors'] ?? 0;

        return $visitors > 0 ? round(($conversions / $visitors) * 100, 2) : 0.0;
    }

    /**
     * Get the best performing variant.
     */
    public function getBestVariant(): ?string
    {
        if (! $this->results) {
            return null;
        }

        $bestVariant = null;
        $bestRate = 0.0;

        foreach ($this->results as $variant => $data) {
            $rate = $this->getConversionRateForVariant($variant);
            if ($rate > $bestRate) {
                $bestRate = $rate;
                $bestVariant = $variant;
            }
        }

        return $bestVariant;
    }

    /**
     * Start the A/B test.
     */
    public function start(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'start_date' => now(),
        ]);

        return true;
    }

    /**
     * Complete the A/B test.
     */
    public function complete(?string $winnerVariant = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $winner = $winnerVariant ?? $this->getBestVariant();

        $this->update([
            'status' => 'completed',
            'end_date' => now(),
            'winner_variant' => $winner,
        ]);

        return true;
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
}
