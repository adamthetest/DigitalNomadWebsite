<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'favoritable_id',
        'favoritable_type',
        'category',
        'notes',
    ];

    protected $casts = [
        'notes' => 'array',
    ];

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favoritable model (city, article, deal, etc.).
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if a user has favorited a specific item.
     */
    public static function isFavorited($userId, $favoritableId, $favoritableType)
    {
        return static::where('user_id', $userId)
            ->where('favoritable_id', $favoritableId)
            ->where('favoritable_type', $favoritableType)
            ->exists();
    }

    /**
     * Toggle favorite status for an item.
     */
    public static function toggle($userId, $favoritableId, $favoritableType, $category = null, $notes = null)
    {
        $favorite = static::where('user_id', $userId)
            ->where('favoritable_id', $favoritableId)
            ->where('favoritable_type', $favoritableType)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return false; // Removed from favorites
        } else {
            static::create([
                'user_id' => $userId,
                'favoritable_id' => $favoritableId,
                'favoritable_type' => $favoritableType,
                'category' => $category,
                'notes' => $notes,
            ]);

            return true; // Added to favorites
        }
    }
}
