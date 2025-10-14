<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'reason',
        'banned_by',
        'banned_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who banned this IP.
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * Check if the ban is still active.
     */
    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if an IP is banned.
     */
    public static function isBanned(string $ipAddress): bool
    {
        return static::where('ip_address', $ipAddress)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Ban an IP address.
     */
    public static function banIp(string $ipAddress, ?string $reason = null, ?int $bannedBy = null, ?Carbon $expiresAt = null): self
    {
        return static::create([
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'banned_by' => $bannedBy,
            'banned_at' => now(),
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }

    /**
     * Unban an IP address.
     */
    public static function unbanIp(string $ipAddress): bool
    {
        return static::where('ip_address', $ipAddress)
            ->update(['is_active' => false]);
    }

    /**
     * Scope for active bans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for expired bans.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
