<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoworkingSpace extends Model
{
    use HasFactory;
    protected $fillable = [
        'city_id',
        'neighborhood_id',
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'website',
        'phone',
        'email',
        'type',
        'wifi_speed_mbps',
        'wifi_reliability',
        'noise_level',
        'seating_capacity',
        'has_power_outlets',
        'has_air_conditioning',
        'has_kitchen',
        'has_meeting_rooms',
        'has_printing',
        'is_24_hours',
        'daily_rate',
        'monthly_rate',
        'currency',
        'amenities',
        'images',
        'rating',
        'notes',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'daily_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'amenities' => 'array',
        'images' => 'array',
        'has_power_outlets' => 'boolean',
        'has_air_conditioning' => 'boolean',
        'has_kitchen' => 'boolean',
        'has_meeting_rooms' => 'boolean',
        'has_printing' => 'boolean',
        'is_24_hours' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithGoodWifi($query)
    {
        return $query->where('wifi_speed_mbps', '>=', 50);
    }

    public function scopeQuiet($query)
    {
        return $query->where('noise_level', 'quiet');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
