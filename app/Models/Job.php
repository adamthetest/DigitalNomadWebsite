<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Job extends Model
{
    protected $table = 'job_postings';
    
    protected $fillable = [
        'title',
        'description',
        'requirements',
        'benefits',
        'company_id',
        'type',
        'remote_type',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_period',
        'tags',
        'timezone',
        'visa_support',
        'source',
        'source_url',
        'apply_url',
        'apply_email',
        'featured',
        'is_active',
        'expires_at',
        'published_at',
        'views_count',
        'applications_count',
        'location',
        'experience_level',
    ];

    protected function casts(): array
    {
        return [
            'visa_support' => 'boolean',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'tags' => 'array',
            'experience_level' => 'array',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
        ];
    }

    /**
     * Get the company that owns the job.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the job user interactions.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(JobUserInteraction::class);
    }

    /**
     * Get users who saved this job.
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_user_interactions')
                    ->wherePivot('status', 'saved')
                    ->withTimestamps();
    }

    /**
     * Get users who applied to this job.
     */
    public function appliedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_user_interactions')
                    ->wherePivot('status', 'applied')
                    ->withTimestamps();
    }

    /**
     * Check if job is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if job is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at->isPast();
    }

    /**
     * Get formatted salary range.
     */
    public function getFormattedSalaryAttribute(): string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return 'Salary not specified';
        }

        $currency = $this->salary_currency;
        $period = $this->salary_period === 'yearly' ? '/year' : ($this->salary_period === 'monthly' ? '/month' : '/hour');

        if ($this->salary_min && $this->salary_max) {
            return "{$currency} {$this->salary_min} - {$this->salary_max}{$period}";
        } elseif ($this->salary_min) {
            return "{$currency} {$this->salary_min}+{$period}";
        } else {
            return "{$currency} {$this->salary_max}{$period}";
        }
    }

    /**
     * Get job type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get remote type label.
     */
    public function getRemoteTypeLabelAttribute(): string
    {
        return match($this->remote_type) {
            'fully-remote' => 'Fully Remote',
            'hybrid' => 'Hybrid',
            'timezone-limited' => 'Timezone Limited',
            'onsite' => 'On-site',
            default => ucfirst($this->remote_type),
        };
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment applications count.
     */
    public function incrementApplications(): void
    {
        $this->increment('applications_count');
    }

    /**
     * Scope for active jobs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for published jobs.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for non-expired jobs.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for featured jobs.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for jobs by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for jobs by remote type.
     */
    public function scopeByRemoteType($query, string $remoteType)
    {
        return $query->where('remote_type', $remoteType);
    }

    /**
     * Scope for jobs by salary range.
     */
    public function scopeBySalaryRange($query, int $min, int $max = null)
    {
        return $query->where(function ($q) use ($min, $max) {
            $q->where(function ($subQ) use ($min, $max) {
                $subQ->where('salary_min', '>=', $min);
                if ($max) {
                    $subQ->where('salary_max', '<=', $max);
                }
            })->orWhere(function ($subQ) use ($min, $max) {
                $subQ->where('salary_max', '>=', $min);
                if ($max) {
                    $subQ->where('salary_max', '<=', $max);
                }
            });
        });
    }

    /**
     * Scope for jobs by tags.
     */
    public function scopeByTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope for visa-friendly jobs.
     */
    public function scopeVisaFriendly($query)
    {
        return $query->where('visa_support', true);
    }

    /**
     * Scope for jobs posted recently.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
