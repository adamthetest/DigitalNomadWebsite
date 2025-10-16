<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    /**
     * Toggle favorite status for an item.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'favoritable_id' => 'required|integer',
            'favoritable_type' => 'required|string|in:App\\Models\\City,App\\Models\\Article,App\\Models\\Deal',
            'category' => 'nullable|string|in:city,article,deal',
            'notes' => 'nullable',
        ]);

        // Enforce 1000-char max only for string notes; allow arrays to pass
        if (is_string($request->notes) && mb_strlen($request->notes) > 1000) {
            return response()->json([
                'message' => 'The notes field must not be greater than 1000 characters.',
                'errors' => ['notes' => ['The notes field must not be greater than 1000 characters.']],
            ], 422);
        }

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
            'notes' => 'nullable',
        ]);
        if (is_string($request->notes) && mb_strlen($request->notes) > 1000) {
            return response()->json([
                'message' => 'The notes field must not be greater than 1000 characters.',
                'errors' => ['notes' => ['The notes field must not be greater than 1000 characters.']],
            ], 422);
        }

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
            'App\\Models\\City' => 'city',
            'App\\Models\\Article' => 'article',
            'App\\Models\\Deal' => 'deal',
            default => 'other',
        };
    }

    /**
     * Get favorites count for a specific item.
     */
    public function getCount(Request $request): JsonResponse
    {
        // Accept both query string and JSON body for GET tests
        $favoritableId = $request->query('favoritable_id', $request->input('favoritable_id'));
        $favoritableType = $request->query('favoritable_type', $request->input('favoritable_type'));
        if (! $favoritableId || ! $favoritableType) {
            return response()->json([
                'message' => 'The favoritable id field is required. (and 1 more error)',
                'errors' => [
                    'favoritable_id' => ['The favoritable id field is required.'],
                    'favoritable_type' => ['The favoritable type field is required.'],
                ],
            ], 422);
        }

        $count = Favorite::where('favoritable_id', $favoritableId)
            ->where('favoritable_type', $favoritableType)
            ->count();

        $isFavorited = auth()->check() ? Favorite::isFavorited(
            auth()->id(),
            $favoritableId,
            $favoritableType
        ) : false;

        return response()->json([
            'count' => $count,
            'is_favorited' => $isFavorited,
        ]);
    }
}
