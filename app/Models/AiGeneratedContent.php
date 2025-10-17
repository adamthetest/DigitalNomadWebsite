<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiGeneratedContent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_generated_content';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'content_type',
        'title',
        'slug',
        'content',
        'excerpt',
        'metadata',
        'seo_data',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'scheduled_at',
        'published_at',
        'featured_image',
        'tags',
        'categories',
        'view_count',
        'engagement_score',
        'is_featured',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'seo_data' => 'array',
        'tags' => 'array',
        'categories' => 'array',
        'reviewed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
        });

        static::updating(function ($content) {
            if ($content->isDirty('title') && empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
        });
    }

    /**
     * Get the user who reviewed this content.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for published content.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('is_active', true)
            ->whereNotNull('published_at');
    }

    /**
     * Scope for draft content.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for pending review content.
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    /**
     * Scope for scheduled content.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    /**
     * Scope for content by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope for featured content.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the reading time estimate.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));

        return max(1, round($wordCount / 200)); // 200 words per minute
    }

    /**
     * Get the content excerpt or generate one.
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return Str::limit(strip_tags($this->content), 160);
    }

    /**
     * Get the SEO meta description.
     */
    public function getMetaDescriptionAttribute(): string
    {
        if (isset($this->seo_data['meta_description'])) {
            return $this->seo_data['meta_description'];
        }

        return $this->excerpt;
    }

    /**
     * Get the SEO keywords.
     */
    public function getMetaKeywordsAttribute(): array
    {
        return $this->seo_data['keywords'] ?? [];
    }

    /**
     * Check if content is ready for publishing.
     */
    public function isReadyForPublishing(): bool
    {
        return $this->status === 'approved' &&
               ! empty($this->title) &&
               ! empty($this->content) &&
               $this->is_active;
    }

    /**
     * Mark content as published.
     */
    public function markAsPublished(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Mark content as pending review.
     */
    public function markAsPendingReview(): void
    {
        $this->update(['status' => 'pending_review']);
    }

    /**
     * Approve content for publishing.
     */
    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Reject content.
     */
    public function reject(User $reviewer, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Update engagement score.
     */
    public function updateEngagementScore(int $score): void
    {
        $this->update(['engagement_score' => $score]);
    }
}
