@extends('layouts.app')

@section('title', 'Edit Profile - Digital Nomad Guide')
@section('description', 'Update your digital nomad profile with bio, location, and social links')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Your Profile</h1>
            <p class="text-gray-600">Share your digital nomad journey with the community</p>
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Profile Image -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 mb-4">Profile Image</label>
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <img id="profile-image-preview" 
                                 src="{{ $user->profile_image_url }}" 
                                 alt="Profile preview"
                                 class="w-20 h-20 rounded-full object-cover border-4 border-gray-200">
                        </div>
                        <div class="flex-1">
                            <input type="file" 
                                   id="profile_image" 
                                   name="profile_image" 
                                   accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                            @if($user->profile_image)
                                <button type="button" 
                                        id="delete-image-btn"
                                        class="mt-2 text-red-600 hover:text-red-700 text-sm">
                                    Remove current image
                                </button>
                            @endif
                        </div>
                    </div>
                    @error('profile_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tagline" class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                        <input type="text" 
                               id="tagline" 
                               name="tagline" 
                               value="{{ old('tagline', $user->tagline) }}"
                               placeholder="e.g., Full-stack developer & travel enthusiast"
                               maxlength="160"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Short description (max 160 characters)</p>
                        @error('tagline')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Bio -->
                <div class="mb-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="bio" 
                              name="bio" 
                              rows="4"
                              placeholder="Tell us about your digital nomad journey, interests, and experiences..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('bio', $user->bio) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Share your story with the community (max 1000 characters)</p>
                    @error('bio')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Professional Details -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="job_title" class="block text-sm font-medium text-gray-700 mb-2">Job Title</label>
                            <input type="text" 
                                   id="job_title" 
                                   name="job_title" 
                                   value="{{ old('job_title', $user->job_title) }}"
                                   placeholder="e.g., Senior Developer, UX Designer"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('job_title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                            <input type="text" 
                                   id="company" 
                                   name="company" 
                                   value="{{ old('company', $user->company) }}"
                                   placeholder="e.g., Remote Company Inc."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('company')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="work_type" class="block text-sm font-medium text-gray-700 mb-2">Work Type</label>
                            <select id="work_type" 
                                    name="work_type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select work type</option>
                                <option value="freelancer" {{ old('work_type', $user->work_type) === 'freelancer' ? 'selected' : '' }}>Freelancer</option>
                                <option value="employee" {{ old('work_type', $user->work_type) === 'employee' ? 'selected' : '' }}>Remote Employee</option>
                                <option value="entrepreneur" {{ old('work_type', $user->work_type) === 'entrepreneur' ? 'selected' : '' }}>Entrepreneur</option>
                            </select>
                            @error('work_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="availability" class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                            <input type="text" 
                                   id="availability" 
                                   name="availability" 
                                   value="{{ old('availability', $user->availability) }}"
                                   placeholder="e.g., Available for projects, Open to opportunities"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('availability')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="mt-4">
                        <label for="skills" class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
                        <input type="text" 
                               id="skills" 
                               name="skills" 
                               value="{{ old('skills', is_array($user->skills) ? implode(', ', $user->skills) : '') }}"
                               placeholder="JavaScript, React, Design, Marketing..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple skills with commas (max 10 skills)</p>
                        @error('skills')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Nomad Lifestyle -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Nomad Lifestyle</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="location_current" class="block text-sm font-medium text-gray-700 mb-2">Current Location</label>
                            <input type="text" 
                                   id="location_current" 
                                   name="location_current" 
                                   value="{{ old('location_current', $user->location_current) }}"
                                   placeholder="e.g., Bangkok, Thailand"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('location_current')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location_next" class="block text-sm font-medium text-gray-700 mb-2">Next Destination</label>
                            <input type="text" 
                                   id="location_next" 
                                   name="location_next" 
                                   value="{{ old('location_next', $user->location_next) }}"
                                   placeholder="e.g., Lisbon, Portugal"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('location_next')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Social Links</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                            <input type="url" 
                                   id="website" 
                                   name="website" 
                                   value="{{ old('website', $user->website) }}"
                                   placeholder="https://yourwebsite.com"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('website')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    @
                                </span>
                                <input type="text" 
                                       id="twitter" 
                                       name="twitter" 
                                       value="{{ old('twitter', $user->twitter) }}"
                                       placeholder="username"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @error('twitter')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    @
                                </span>
                                <input type="text" 
                                       id="instagram" 
                                       name="instagram" 
                                       value="{{ old('instagram', $user->instagram) }}"
                                       placeholder="username"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @error('instagram')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="linkedin" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    in/
                                </span>
                                <input type="text" 
                                       id="linkedin" 
                                       name="linkedin" 
                                       value="{{ old('linkedin', $user->linkedin) }}"
                                       placeholder="username"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @error('linkedin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="github" class="block text-sm font-medium text-gray-700 mb-2">GitHub</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    github.com/
                                </span>
                                <input type="text" 
                                       id="github" 
                                       name="github" 
                                       value="{{ old('github', $user->github) }}"
                                       placeholder="username"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @error('github')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="behance" class="block text-sm font-medium text-gray-700 mb-2">Behance</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    behance.net/
                                </span>
                                <input type="text" 
                                       id="behance" 
                                       name="behance" 
                                       value="{{ old('behance', $user->behance) }}"
                                       placeholder="username"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            @error('behance')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                            <select id="timezone" 
                                    name="timezone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select your timezone</option>
                                <option value="UTC" {{ old('timezone', $user->timezone) === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ old('timezone', $user->timezone) === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                <option value="America/Los_Angeles" {{ old('timezone', $user->timezone) === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                <option value="Europe/London" {{ old('timezone', $user->timezone) === 'Europe/London' ? 'selected' : '' }}>London</option>
                                <option value="Europe/Paris" {{ old('timezone', $user->timezone) === 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                <option value="Asia/Tokyo" {{ old('timezone', $user->timezone) === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                <option value="Asia/Bangkok" {{ old('timezone', $user->timezone) === 'Asia/Bangkok' ? 'selected' : '' }}>Bangkok</option>
                                <option value="Australia/Sydney" {{ old('timezone', $user->timezone) === 'Australia/Sydney' ? 'selected' : '' }}>Sydney</option>
                            </select>
                            @error('timezone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Privacy Settings -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Privacy Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="visibility" class="block text-sm font-medium text-gray-700 mb-2">Profile Visibility</label>
                            <select id="visibility" 
                                    name="visibility"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="public" {{ old('visibility', $user->visibility) === 'public' ? 'selected' : '' }}>Public - Visible to everyone</option>
                                <option value="members" {{ old('visibility', $user->visibility) === 'members' ? 'selected' : '' }}>Members Only - Visible to registered users</option>
                                <option value="hidden" {{ old('visibility', $user->visibility) === 'hidden' ? 'selected' : '' }}>Hidden - Only visible to you</option>
                            </select>
                            @error('visibility')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="location_precise" 
                                       name="location_precise" 
                                       value="1"
                                       {{ old('location_precise', $user->location_precise) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="location_precise" class="ml-2 text-sm text-gray-700">
                                    Show precise location (city level)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="show_social_links" 
                                       name="show_social_links" 
                                       value="1"
                                       {{ old('show_social_links', $user->show_social_links) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="show_social_links" class="ml-2 text-sm text-gray-700">
                                    Show social links on profile
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <!-- Hidden input to ensure false value is sent when checkbox is unchecked -->
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" 
                                   id="is_public" 
                                   name="is_public" 
                                   value="1"
                                   {{ old('is_public', $user->is_public) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_public" class="ml-2 text-sm text-gray-700">
                                Make my profile public (visible to other users)
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">When public, other users can view your profile and favorites</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('profile.show', $user) }}" 
                       class="text-gray-600 hover:text-gray-800">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('profile_image');
    const imagePreview = document.getElementById('profile-image-preview');
    const deleteBtn = document.getElementById('delete-image-btn');

    // Handle image preview
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle image deletion
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove your profile image?')) {
                fetch('{{ route("profile.delete-image") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        imagePreview.src = 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF';
                        imageInput.value = '';
                        deleteBtn.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    }
});
</script>
@endsection
