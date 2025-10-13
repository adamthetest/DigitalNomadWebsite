<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateLink extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'original_url',
        'affiliate_url',
        'affiliate_provider',
        'category',
        'commission_type',
        'commission_rate',
        'currency',
        'tracking_params',
        'is_featured',
        'is_active',
        'click_count',
        'conversion_count',
        'total_commission',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'tracking_params' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function incrementClickCount()
    {
        $this->increment('click_count');
    }

    public function incrementConversionCount()
    {
        $this->increment('conversion_count');
    }
}
