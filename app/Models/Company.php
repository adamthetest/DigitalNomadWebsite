<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Company Model
 *
 * Represents a company in the digital nomad platform with job postings,
 * company information, and subscription details.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $logo
 * @property string|null $website
 * @property string|null $remote_policy
 * @property string|null $industry
 * @property string|null $size
 * @property string|null $headquarters
 * @property bool $verified
 * @property string $subscription_plan
 * @property array|null $benefits
 * @property array|null $tech_stack
 * @property string|null $contact_email
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Job> $jobs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Job> $activeJobs
 * @property-read string $logo_url
 * @property-read int $job_count
 * @property-read int $total_applications
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder|Company verified()
 * @method static \Illuminate\Database\Eloquent\Builder|Company active()
 * @method static \Illuminate\Database\Eloquent\Builder|Company premium()
 */
class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'remote_policy',
        'industry',
        'size',
        'headquarters',
        'verified',
        'subscription_plan',
        'benefits',
        'tech_stack',
        'contact_email',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'is_active' => 'boolean',
            'benefits' => 'array',
            'tech_stack' => 'array',
        ];
    }

    /**
     * Get the jobs for the company.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Get active jobs for the company.
     */
    public function activeJobs(): HasMany
    {
        return $this->hasMany(Job::class)->where('is_active', true);
    }

    /**
     * Get the company's logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/'.$this->logo);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the company's slug for URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });

        static::updating(function ($company) {
            if ($company->isDirty('name') && empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    /**
     * Scope for verified companies.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope for active companies.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for premium companies.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePremium($query)
    {
        return $query->whereIn('subscription_plan', ['premium', 'enterprise']);
    }

    /**
     * Get the company's job count.
     */
    public function getJobCountAttribute(): int
    {
        return $this->jobs()->where('is_active', true)->count();
    }

    /**
     * Get the company's total applications.
     */
    public function getTotalApplicationsAttribute(): int
    {
        return $this->jobs()->sum('applications_count');
    }
}
