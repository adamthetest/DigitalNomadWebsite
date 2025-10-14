<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;
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
     * Boot the model.
     */
    protected static function boot()
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
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope for active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for premium companies.
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
