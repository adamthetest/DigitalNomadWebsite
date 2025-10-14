<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'city_id',
        'category',
        'name',
        'description',
        'price_min',
        'price_max',
        'price_average',
        'currency',
        'unit',
        'price_range',
        'details',
        'notes',
        'last_updated',
        'is_active',
    ];

    protected $casts = [
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'price_average' => 'decimal:2',
        'details' => 'array',
        'last_updated' => 'date',
        'is_active' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriceRange($query, $range)
    {
        return $query->where('price_range', $range);
    }

    public function scopeBudget($query)
    {
        return $query->where('price_range', 'budget');
    }

    public function scopeMidRange($query)
    {
        return $query->where('price_range', 'mid_range');
    }

    public function scopeLuxury($query)
    {
        return $query->where('price_range', 'luxury');
    }
}
