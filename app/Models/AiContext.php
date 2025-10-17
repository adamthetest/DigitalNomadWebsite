<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AI Context Model
 *
 * Stores AI-generated data and context for various models in the system.
 * This centralizes AI data storage and enables efficient AI-powered features.
 *
 * @property int $id
 * @property string $context_type
 * @property int $context_id
 * @property string $context_model
 * @property array $context_data
 * @property array|null $ai_embeddings
 * @property array|null $ai_summary
 * @property array|null $ai_tags
 * @property array|null $ai_insights
 * @property string|null $ai_model_version
 * @property \Illuminate\Support\Carbon|null $last_ai_update
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model $context
 */
class AiContext extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'context_type',
        'context_id',
        'context_model',
        'context_data',
        'ai_embeddings',
        'ai_summary',
        'ai_tags',
        'ai_insights',
        'ai_model_version',
        'last_ai_update',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context_data' => 'array',
        'ai_embeddings' => 'array',
        'ai_summary' => 'array',
        'ai_tags' => 'array',
        'ai_insights' => 'array',
        'last_ai_update' => 'datetime',
    ];

    /**
     * Get the related context model.
     */
    public function context(): MorphTo
    {
        return $this->morphTo('context', 'context_model', 'context_id');
    }

    /**
     * Scope for specific context type.
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('context_type', $type);
    }

    /**
     * Scope for specific model.
     */
    public function scopeForModel($query, string $model)
    {
        return $query->where('context_model', $model);
    }

    /**
     * Scope for recently updated AI data.
     */
    public function scopeRecentlyUpdated($query, int $days = 7)
    {
        return $query->where('last_ai_update', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific AI model version.
     */
    public function scopeForModelVersion($query, string $version)
    {
        return $query->where('ai_model_version', $version);
    }

    /**
     * Update AI data and timestamp.
     */
    public function updateAiData(array $data, ?string $modelVersion = null): void
    {
        $this->update([
            'ai_summary' => $data['summary'] ?? null,
            'ai_tags' => $data['tags'] ?? null,
            'ai_insights' => $data['insights'] ?? null,
            'ai_embeddings' => $data['embeddings'] ?? null,
            'ai_model_version' => $modelVersion,
            'last_ai_update' => now(),
        ]);
    }

    /**
     * Get AI summary text.
     */
    public function getSummaryTextAttribute(): ?string
    {
        return $this->ai_summary['text'] ?? null;
    }

    /**
     * Get AI tags as array.
     */
    public function getTagsArrayAttribute(): array
    {
        return $this->ai_tags ?? [];
    }

    /**
     * Get AI insights as array.
     */
    public function getInsightsArrayAttribute(): array
    {
        return $this->ai_insights ?? [];
    }

    /**
     * Check if AI data is recent.
     */
    public function isAiDataRecent(int $days = 7): bool
    {
        return $this->last_ai_update && $this->last_ai_update->isAfter(now()->subDays($days));
    }

    /**
     * Get context data for AI processing.
     */
    public function getContextForAi(): array
    {
        return array_merge(
            $this->context_data ?? [],
            [
                'context_type' => $this->context_type,
                'context_id' => $this->context_id,
                'last_updated' => $this->updated_at->toISOString(),
            ]
        );
    }
}
