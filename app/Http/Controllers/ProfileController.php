<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(User $user)
    {
        // Check if profile is public or if user is viewing their own profile
        if (!$user->is_public && Auth::id() !== $user->id) {
            abort(403, 'This profile is private.');
        }

        $user->load('favorites.favoritable');
        
        // Get user's favorites grouped by category
        $favoriteCities = $user->favoriteCities()->get();
        $favoriteArticles = $user->favoriteArticles()->get();
        $favoriteDeals = $user->favoriteDeals()->get();

        return view('profiles.show', compact('user', 'favoriteCities', 'favoriteArticles', 'favoriteDeals'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profiles.edit', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'twitter' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'github' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:255',
            'is_public' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name', 'bio', 'location', 'website', 'twitter', 
            'instagram', 'linkedin', 'github', 'timezone', 'is_public'
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $image = $request->file('profile_image');
            $filename = 'profiles/' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public', $filename);
            $data['profile_image'] = $filename;
        }

        $user->update($data);

        return redirect()->route('profile.show', $user)
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Display a listing of public profiles.
     */
    public function index(Request $request)
    {
        $query = User::where('is_public', true)
            ->whereNotNull('bio')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        $users = $query->paginate(12);

        return view('profiles.index', compact('users'));
    }

    /**
     * Delete user's profile image.
     */
    public function deleteImage()
    {
        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->update(['profile_image' => null]);
        }

        return response()->json(['success' => true]);
    }
}