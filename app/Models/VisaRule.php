<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaRule extends Model
{
    protected $fillable = [
        'country_id',
        'nationality',
        'visa_type',
        'stay_duration_days',
        'validity_days',
        'cost_usd',
        'requirements',
        'application_process',
        'official_website',
        'restrictions',
        'notes',
        'last_updated',
        'is_active',
    ];

    protected $casts = [
        'cost_usd' => 'decimal:2',
        'restrictions' => 'array',
        'last_updated' => 'date',
        'is_active' => 'boolean',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeByNationality($query, $nationality)
    {
        return $query->where('nationality', $nationality);
    }

    public function scopeVisaFree($query)
    {
        return $query->where('visa_type', 'visa_free');
    }

    public function scopeVisaOnArrival($query)
    {
        return $query->where('visa_type', 'visa_on_arrival');
    }
}
