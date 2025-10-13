<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'event_type',
        'severity',
        'message',
        'metadata',
        'user_id',
        'url',
        'method',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user associated with this log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a security event.
     */
    public static function logEvent(
        string $eventType,
        string $message,
        string $severity = 'info',
        string $ipAddress = null,
        string $userAgent = null,
        int $userId = null,
        string $url = null,
        string $method = null,
        array $metadata = []
    ): self {
        return static::create([
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'event_type' => $eventType,
            'severity' => $severity,
            'message' => $message,
            'metadata' => $metadata,
            'user_id' => $userId,
            'url' => $url ?? request()->url(),
            'method' => $method ?? request()->method(),
        ]);
    }

    /**
     * Log a failed login attempt.
     */
    public static function logFailedLogin(string $email, string $ipAddress = null): self
    {
        return static::logEvent(
            'failed_login',
            "Failed login attempt for email: {$email}",
            'warning',
            $ipAddress,
            null,
            null,
            null,
            null,
            ['email' => $email]
        );
    }

    /**
     * Log a successful login.
     */
    public static function logSuccessfulLogin(User $user, string $ipAddress = null): self
    {
        return static::logEvent(
            'successful_login',
            "Successful login for user: {$user->name} ({$user->email})",
            'info',
            $ipAddress,
            null,
            $user->id
        );
    }

    /**
     * Log banned IP access attempt.
     */
    public static function logBannedAccess(string $ipAddress = null): self
    {
        return static::logEvent(
            'banned_access',
            "Access attempt from banned IP: " . ($ipAddress ?? request()->ip()),
            'critical',
            $ipAddress
        );
    }

    /**
     * Log admin access.
     */
    public static function logAdminAccess(User $user, string $ipAddress = null): self
    {
        return static::logEvent(
            'admin_access',
            "Admin panel access by: {$user->name}",
            'info',
            $ipAddress,
            null,
            $user->id
        );
    }

    /**
     * Log IP ban action.
     */
    public static function logIpBan(string $ipAddress, string $reason, User $bannedBy): self
    {
        return static::logEvent(
            'ip_banned',
            "IP {$ipAddress} banned by {$bannedBy->name}. Reason: {$reason}",
            'warning',
            $ipAddress,
            null,
            $bannedBy->id,
            null,
            null,
            ['banned_ip' => $ipAddress, 'reason' => $reason]
        );
    }

    /**
     * Log IP unban action.
     */
    public static function logIpUnban(string $ipAddress, User $unbannedBy): self
    {
        return static::logEvent(
            'ip_unbanned',
            "IP {$ipAddress} unbanned by {$unbannedBy->name}",
            'info',
            $ipAddress,
            null,
            $unbannedBy->id,
            null,
            null,
            ['unbanned_ip' => $ipAddress]
        );
    }

    /**
     * Scope for specific event types.
     */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for specific severity levels.
     */
    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for specific IP addresses.
     */
    public function scopeIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}