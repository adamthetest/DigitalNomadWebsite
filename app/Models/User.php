<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'tagline',
        'job_title',
        'company',
        'skills',
        'work_type',
        'availability',
        'location',
        'location_current',
        'location_next',
        'travel_timeline',
        'profile_image',
        'website',
        'twitter',
        'instagram',
        'linkedin',
        'github',
        'behance',
        'is_public',
        'id_verified',
        'premium_status',
        'last_active',
        'visibility',
        'location_precise',
        'show_social_links',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_public' => 'boolean',
            'id_verified' => 'boolean',
            'premium_status' => 'boolean',
            'last_active' => 'datetime',
            'location_precise' => 'boolean',
            'show_social_links' => 'boolean',
            'skills' => 'array',
            'travel_timeline' => 'array',
        ];
    }

    /**
     * Get the user's favorites.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the user's favorite cities.
     */
    public function favoriteCities(): HasMany
    {
        return $this->favorites()->where('category', 'city')->with('favoritable');
    }

    /**
     * Get the user's favorite articles.
     */
    public function favoriteArticles(): HasMany
    {
        return $this->favorites()->where('category', 'article')->with('favoritable');
    }

    /**
     * Get the user's favorite deals.
     */
    public function favoriteDeals(): HasMany
    {
        return $this->favorites()->where('category', 'deal')->with('favoritable');
    }

    /**
     * Get the user's job interactions.
     */
    public function jobInteractions(): HasMany
    {
        return $this->hasMany(JobUserInteraction::class);
    }

    /**
     * Get jobs saved by the user.
     */
    public function savedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_user_interactions')
                    ->wherePivot('status', 'saved')
                    ->withTimestamps();
    }

    /**
     * Get jobs applied by the user.
     */
    public function appliedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_user_interactions')
                    ->wherePivot('status', 'applied')
                    ->withTimestamps();
    }

    /**
     * Get the user's profile image URL.
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the user's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? 'Anonymous User';
    }

    /**
     * Get the user's social links.
     */
    public function getSocialLinksAttribute(): array
    {
        if (!$this->show_social_links) {
            return [];
        }
        
        return array_filter([
            'website' => $this->website,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'github' => $this->github,
            'behance' => $this->behance,
        ]);
    }

    /**
     * Check if user has a complete profile.
     */
    public function hasCompleteProfile(): bool
    {
        return !empty($this->bio) && !empty($this->location_current) && !empty($this->profile_image) && !empty($this->tagline);
    }

    /**
     * Get profile completion percentage.
     */
    public function getProfileCompletionAttribute(): int
    {
        $fields = ['bio', 'tagline', 'location_current', 'profile_image', 'job_title', 'skills', 'work_type'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    /**
     * Get the user's current location for display.
     */
    public function getCurrentLocationAttribute(): string
    {
        if (!$this->location_precise && $this->location_current) {
            // Return only country if location_precise is false
            $parts = explode(',', $this->location_current);
            return trim(end($parts)) ?? $this->location_current;
        }
        
        return $this->location_current ?? $this->location ?? 'Location not set';
    }

    /**
     * Get verification badges.
     */
    public function getVerificationBadgesAttribute(): array
    {
        $badges = [];
        
        if ($this->email_verified_at) {
            $badges[] = 'email_verified';
        }
        
        if ($this->id_verified) {
            $badges[] = 'id_verified';
        }
        
        if ($this->premium_status) {
            $badges[] = 'premium';
        }
        
        return $badges;
    }

    /**
     * Check if user is online (active within last 15 minutes).
     */
    public function isOnline(): bool
    {
        return $this->last_active && $this->last_active->isAfter(now()->subMinutes(15));
    }

    /**
     * Update last active timestamp.
     */
    public function updateLastActive(): void
    {
        $this->update(['last_active' => now()]);
    }

    /**
     * Add a city to travel timeline.
     */
    public function addToTravelTimeline(string $city, string $country, ?string $arrivedAt = null, ?string $leftAt = null): void
    {
        $timeline = $this->travel_timeline ?? [];
        
        $timeline[] = [
            'city' => $city,
            'country' => $country,
            'arrived_at' => $arrivedAt ?? now()->toDateString(),
            'left_at' => $leftAt,
        ];
        
        $this->update(['travel_timeline' => $timeline]);
    }

    /**
     * Scope for public profiles.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope for members-only profiles.
     */
    public function scopeMembers($query)
    {
        return $query->whereIn('visibility', ['public', 'members']);
    }

    /**
     * Scope for premium users.
     */
    public function scopePremium($query)
    {
        return $query->where('premium_status', true);
    }

    /**
     * Scope for verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for users by location.
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('location_current', 'like', "%{$location}%")
                    ->orWhere('location_next', 'like', "%{$location}%");
    }

    /**
     * Scope for users by skills.
     */
    public function scopeBySkills($query, array $skills)
    {
        return $query->where(function ($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('skills', $skill);
            }
        });
    }

    /**
     * Scope for users by work type.
     */
    public function scopeByWorkType($query, string $workType)
    {
        return $query->where('work_type', $workType);
    }
}
