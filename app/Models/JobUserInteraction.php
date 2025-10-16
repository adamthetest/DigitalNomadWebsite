<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobUserInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'status',
        'notes',
        'applied_at',
        'status_updated_at',
        'application_data',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'status_updated_at' => 'datetime',
            'application_data' => 'array',
        ];
    }

    /**
     * Get the user that owns the interaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job that owns the interaction.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Scope for saved jobs.
     */
    public function scopeSaved($query)
    {
        return $query->where('status', 'saved');
    }

    /**
     * Scope for applied jobs.
     */
    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    /**
     * Scope for rejected jobs.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for shortlisted jobs.
     */
    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }

    /**
     * Scope for interviewed jobs.
     */
    public function scopeInterviewed($query)
    {
        return $query->where('status', 'interviewed');
    }

    /**
     * Scope for offered jobs.
     */
    public function scopeOffered($query)
    {
        return $query->where('status', 'offered');
    }

    /**
     * Update status and timestamp.
     */
    public function updateStatus(string $status, array $data = []): void
    {
        $this->update([
            'status' => $status,
            'status_updated_at' => now(),
            'applied_at' => $status === 'applied' ? now() : $this->applied_at,
            ...$data,
        ]);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'saved' => 'Saved',
            'applied' => 'Applied',
            'rejected' => 'Rejected',
            'shortlisted' => 'Shortlisted',
            'interviewed' => 'Interviewed',
            'offered' => 'Offered',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'saved' => 'blue',
            'applied' => 'yellow',
            'rejected' => 'red',
            'shortlisted' => 'green',
            'interviewed' => 'purple',
            'offered' => 'emerald',
            default => 'gray',
        };
    }
}
