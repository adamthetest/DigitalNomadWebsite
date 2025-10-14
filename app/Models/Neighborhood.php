<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Neighborhood extends Model
{
    use HasFactory;
    protected $fillable = [
        'city_id',
        'name',
        'slug',
        'description',
        'latitude',
        'longitude',
        'type',
        'cost_level',
        'safety_score',
        'internet_speed_mbps',
        'amenities',
        'transportation',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'amenities' => 'array',
        'transportation' => 'array',
        'is_active' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function coworkingSpaces(): HasMany
    {
        return $this->hasMany(CoworkingSpace::class);
    }

    public function activeCoworkingSpaces(): HasMany
    {
        return $this->hasMany(CoworkingSpace::class)->where('is_active', true);
    }
}
