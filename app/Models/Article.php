<?php

namespace App\Models;

use App\Services\MarkdownService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'city_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'images',
        'type',
        'status',
        'tags',
        'meta_data',
        'view_count',
        'like_count',
        'is_featured',
        'is_pinned',
        'published_at',
        'author',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'meta_data' => 'array',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Get the parsed HTML content from Markdown.
     */
    public function getParsedContentAttribute(): string
    {
        $markdownService = app(MarkdownService::class);

        return $markdownService->parse($this->content);
    }

    /**
     * Get the parsed HTML excerpt from Markdown.
     */
    public function getParsedExcerptAttribute(): string
    {
        if (empty($this->excerpt)) {
            return '';
        }

        $markdownService = app(MarkdownService::class);

        return $markdownService->parseInline($this->excerpt);
    }
}
