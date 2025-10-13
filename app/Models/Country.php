<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'currency_code',
        'currency_symbol',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function visaRules(): HasMany
    {
        return $this->hasMany(VisaRule::class);
    }

    public function activeCities(): HasMany
    {
        return $this->hasMany(City::class)->where('is_active', true);
    }
}
