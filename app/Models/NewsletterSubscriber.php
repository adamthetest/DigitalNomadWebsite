<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'country_code',
        'interests',
        'status',
        'source',
        'utm_data',
        'last_email_sent',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'interests' => 'array',
        'utm_data' => 'array',
        'last_email_sent' => 'datetime',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function unsubscribe()
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}
