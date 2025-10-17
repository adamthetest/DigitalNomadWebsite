<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Job Model
 *
 * Represents a job posting in the digital nomad platform with company information,
 * salary details, remote work options, and application tracking.
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string|null $requirements
 * @property string|null $benefits
 * @property int $company_id
 * @property string $type
 * @property string $remote_type
 * @property int|null $salary_min
 * @property int|null $salary_max
 * @property string|null $salary_currency
 * @property string|null $salary_period
 * @property array|null $tags
 * @property string|null $timezone
 * @property bool $visa_support
 * @property string $source
 * @property string|null $source_url
 * @property string|null $apply_url
 * @property string|null $apply_email
 * @property bool $featured
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $views_count
 * @property int $applications_count
 * @property string|null $location
 * @property array|null $experience_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobUserInteraction> $interactions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $savedByUsers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $appliedByUsers
 * @property-read string $formatted_salary
 * @property-read string $type_label
 * @property-read string $remote_type_label
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Job active()
 * @method static \Illuminate\Database\Eloquent\Builder|Job published()
 * @method static \Illuminate\Database\Eloquent\Builder|Job notExpired()
 * @method static \Illuminate\Database\Eloquent\Builder|Job featured()
 * @method static \Illuminate\Database\Eloquent\Builder|Job byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder|Job byRemoteType(string $remoteType)
 * @method static \Illuminate\Database\Eloquent\Builder|Job bySalaryRange(int $min, ?int $max = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Job byTags(array $tags)
 * @method static \Illuminate\Database\Eloquent\Builder|Job visaFriendly()
 * @method static \Illuminate\Database\Eloquent\Builder|Job recent(int $days = 7)
 */
class Job extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'job_postings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
        // Phase 3: Job Matching fields
        'job_embedding',
        'skills_embedding',
        'company_embedding',
        'matching_metadata',
        'last_embedding_update',
        'ai_job_summary',
        'ai_skills_extracted',
        'ai_requirements_parsed',
        'ai_company_culture',
        'match_score_base',
        'match_factors',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
        return $this->expires_at?->isPast() ?? false;
    }

    /**
     * Check if job is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at?->isPast() ?? false;
    }

    /**
     * Get formatted salary range.
     */
    public function getFormattedSalaryAttribute(): string
    {
        if (! $this->salary_min && ! $this->salary_max) {
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
        return match ($this->type) {
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
        return match ($this->remote_type) {
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for published jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for non-expired jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for jobs by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for jobs by remote type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRemoteType($query, string $remoteType)
    {
        return $query->where('remote_type', $remoteType);
    }

    /**
     * Scope for jobs by salary range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySalaryRange($query, int $min, ?int $max = null)
    {
        return $query->where(function ($q) use ($min, $max) {
            // Job salary range intersects with search range
            $q->where(function ($subQ) use ($min, $max) {
                // Job's min salary is within search range
                $subQ->where('salary_min', '>=', $min);
                if ($max) {
                    $subQ->where('salary_min', '<=', $max);
                }
            })->orWhere(function ($subQ) use ($min, $max) {
                // Job's max salary is within search range AND job's min is not above search max
                $subQ->where('salary_max', '>=', $min);
                if ($max) {
                    $subQ->where('salary_max', '<=', $max);
                    $subQ->where('salary_min', '<=', $max);
                }
            });
        });
    }

    /**
     * Scope for jobs by tags.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
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
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisaFriendly($query)
    {
        return $query->where('visa_support', true);
    }

    /**
     * Scope for jobs posted recently.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get AI contexts for this job.
     */
    public function aiContexts(): MorphMany
    {
        return $this->morphMany(AiContext::class, 'context', 'context_model', 'context_id');
    }

    /**
     * Get the job matches for this job.
     */
    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }
}
