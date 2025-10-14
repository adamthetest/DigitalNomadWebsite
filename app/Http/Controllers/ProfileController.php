<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(User $user)
    {
        // Check profile visibility
        if ($user->visibility === 'hidden' && Auth::id() !== $user->id) {
            abort(403, 'This profile is hidden.');
        }
        
        if ($user->visibility === 'members' && !Auth::check() && Auth::id() !== $user->id) {
            abort(403, 'This profile is only visible to members.');
        }

        // Update last active if viewing own profile
        if (Auth::id() === $user->id) {
            $user->updateLastActive();
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
            'tagline' => 'nullable|string|max:160',
            'bio' => 'nullable|string|max:1000',
            'job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'skills' => 'nullable|array|max:10',
            'skills.*' => 'string|max:50',
            'work_type' => 'nullable|in:freelancer,employee,entrepreneur',
            'availability' => 'nullable|string|max:255',
            'location_current' => 'nullable|string|max:255',
            'location_next' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'github' => 'nullable|url|max:255',
            'behance' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|max:255',
            'visibility' => 'required|in:public,members,hidden',
            'location_precise' => 'boolean',
            'show_social_links' => 'boolean',
            'is_public' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name', 'tagline', 'bio', 'job_title', 'company', 'skills', 'work_type', 'availability',
            'location_current', 'location_next', 'website', 'twitter', 'instagram', 'linkedin', 
            'github', 'behance', 'timezone', 'visibility', 'location_precise', 'show_social_links', 'is_public'
        ]);

        // Convert string values to boolean
        $data['location_precise'] = (bool) $data['location_precise'];
        $data['show_social_links'] = (bool) $data['show_social_links'];
        $data['is_public'] = (bool) $data['is_public'];

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
        $query = User::members()->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('location_current', 'like', "%{$search}%")
                  ->orWhere('location_next', 'like', "%{$search}%");
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->byLocation($request->location);
        }

        // Filter by skills
        if ($request->filled('skills')) {
            $skills = is_array($request->skills) ? $request->skills : explode(',', $request->skills);
            $query->bySkills($skills);
        }

        // Filter by work type
        if ($request->filled('work_type')) {
            $query->byWorkType($request->work_type);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            $query->verified();
        }

        // Filter by premium status
        if ($request->filled('premium')) {
            $query->premium();
        }

        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'online':
                $query->orderBy('last_active', 'desc');
                break;
            case 'active':
                $query->orderBy('last_active', 'desc');
                break;
            case 'premium':
                $query->orderBy('premium_status', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'verified':
                $query->orderBy('email_verified_at', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $users = $query->paginate(12)->withQueryString();

        // Get filter options for the form
        $workTypes = ['freelancer' => 'Freelancer', 'employee' => 'Remote Employee', 'entrepreneur' => 'Entrepreneur'];
        $sortOptions = [
            'newest' => 'Newest Profiles',
            'active' => 'Recently Active',
            'premium' => 'Premium Members',
            'verified' => 'Verified Members',
        ];

        return view('profiles.index', compact('users', 'workTypes', 'sortOptions'));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        // Debug: Log everything
        \Log::info('Password change request - DEBUG', [
            'user_id' => $user ? $user->id : 'null',
            'email' => $user ? $user->email : 'null',
            'request_method' => $request->method(),
            'request_data' => $request->only(['current_password', 'password', 'password_confirmation']),
            'all_request_data' => $request->all(),
            'has_current_password' => $request->has('current_password'),
            'current_password_value' => $request->input('current_password'),
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'is_form' => $request->is('*')
        ]);

        if (!$user) {
            \Log::error('No authenticated user for password change');
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to change your password.'
            ], 401);
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            \Log::warning('Password change failed - incorrect current password', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return response()->json([
                'success' => false,
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        \Log::info('Password changed successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
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

    /**
     * Display the discover page for finding nomads.
     */
    public function discover(Request $request)
    {
        $query = User::public()->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('location_current', 'like', "%{$search}%")
                  ->orWhere('location_next', 'like', "%{$search}%");
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->byLocation($request->location);
        }

        // Filter by skills
        if ($request->filled('skills')) {
            $skills = is_array($request->skills) ? $request->skills : explode(',', $request->skills);
            $query->bySkills($skills);
        }

        // Filter by work type
        if ($request->filled('work_type')) {
            $query->byWorkType($request->work_type);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            $query->verified();
        }

        // Filter by premium status
        if ($request->filled('premium')) {
            $query->premium();
        }

        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'online':
                $query->orderBy('last_active', 'desc');
                break;
            case 'active':
                $query->orderBy('last_active', 'desc');
                break;
            case 'premium':
                $query->orderBy('premium_status', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'verified':
                $query->orderBy('email_verified_at', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $users = $query->paginate(12)->withQueryString();

        // Get filter options for the form
        $workTypes = ['freelancer' => 'Freelancer', 'employee' => 'Remote Employee', 'entrepreneur' => 'Entrepreneur'];
        $sortOptions = [
            'newest' => 'Newest Profiles',
            'active' => 'Recently Active',
            'premium' => 'Premium Members',
            'verified' => 'Verified Members',
        ];

        return view('profiles.discover', compact('users', 'workTypes', 'sortOptions'));
    }
}