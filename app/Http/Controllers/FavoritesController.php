<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoritesController extends Controller
{
    /**
     * Toggle favorite status for an item.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|string|in:App\Models\City,App\Models\Article,App\Models\Deal',
            'category' => 'nullable|string|in:city,article,deal',
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        $favoritableId = $request->favoritable_id;
        $favoritableType = $request->favoritable_type;
        $category = $request->category ?? $this->getCategoryFromType($favoritableType);
        $notes = $request->notes;

        $isFavorited = Favorite::toggle($userId, $favoritableId, $favoritableType, $category, $notes);

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? 'Added to favorites' : 'Removed from favorites',
        ]);
    }

    /**
     * Get user's favorites by category.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $category = $request->get('category', 'all');

        $favorites = $user->favorites()
            ->when($category !== 'all', function ($query) use ($category) {
                return $query->where('category', $category);
            })
            ->with('favoritable')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('favorites.index', compact('favorites', 'category'));
    }

    /**
     * Remove a favorite item.
     */
    public function destroy(Request $request, Favorite $favorite): JsonResponse
    {
        // Ensure the favorite belongs to the authenticated user
        if ($favorite->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites',
        ]);
    }

    /**
     * Update notes for a favorite item.
     */
    public function updateNotes(Request $request, Favorite $favorite): JsonResponse
    {
        // Ensure the favorite belongs to the authenticated user
        if ($favorite->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $favorite->update(['notes' => $request->notes]);

        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully',
        ]);
    }

    /**
     * Get category from model type.
     */
    private function getCategoryFromType(string $type): string
    {
        return match ($type) {
            'App\Models\City' => 'city',
            'App\Models\Article' => 'article',
            'App\Models\Deal' => 'deal',
            default => 'other',
        };
    }

    /**
     * Get favorites count for a specific item.
     */
    public function getCount(Request $request): JsonResponse
    {
        $request->validate([
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|string',
        ]);

        $count = Favorite::where('favoritable_id', $request->favoritable_id)
            ->where('favoritable_type', $request->favoritable_type)
            ->count();

        $isFavorited = auth()->check() ? Favorite::isFavorited(
            auth()->id(),
            $request->favoritable_id,
            $request->favoritable_type
        ) : false;

        return response()->json([
            'count' => $count,
            'is_favorited' => $isFavorited,
        ]);
    }
}
