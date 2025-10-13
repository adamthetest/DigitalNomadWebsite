<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    protected $fillable = [
        'affiliate_link_id',
        'title',
        'slug',
        'description',
        'deal_url',
        'provider',
        'category',
        'original_price',
        'discounted_price',
        'discount_percentage',
        'currency',
        'promo_code',
        'valid_from',
        'valid_until',
        'terms_conditions',
        'image',
        'is_featured',
        'is_active',
        'click_count',
        'conversion_count',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'terms_conditions' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function affiliateLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('valid_until', '<=', now()->addDays($days));
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
