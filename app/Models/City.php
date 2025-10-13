<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class City extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'latitude',
        'longitude',
        'description',
        'overview',
        'population',
        'climate',
        'internet_speed_mbps',
        'safety_score',
        'cost_of_living_index',
        'best_time_to_visit',
        'highlights',
        'images',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'cost_of_living_index' => 'decimal:2',
        'highlights' => 'array',
        'images' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = Str::slug($city->name);
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class);
    }

    public function coworkingSpaces(): HasMany
    {
        return $this->hasMany(CoworkingSpace::class);
    }

    public function costItems(): HasMany
    {
        return $this->hasMany(CostItem::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function activeNeighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class)->where('is_active', true);
    }

    public function activeCoworkingSpaces(): HasMany
    {
        return $this->hasMany(CoworkingSpace::class)->where('is_active', true);
    }

    public function publishedArticles(): HasMany
    {
        return $this->hasMany(Article::class)->where('status', 'published');
    }
}
