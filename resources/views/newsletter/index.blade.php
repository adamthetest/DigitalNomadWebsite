@extends('layouts.app')

@section('title', 'Newsletter Subscription - Digital Nomad Guide')
@section('description', 'Subscribe to our newsletter for the latest digital nomad tips, destination guides, and exclusive deals.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">📧 Stay Updated</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Get weekly insights, destination guides, and exclusive deals delivered to your inbox. 
                Join thousands of digital nomads who trust our recommendations.
            </p>
        </div>

        <!-- Newsletter Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-6">
                @csrf
                
                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name') }}"
                               placeholder="Your first name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name') }}"
                               placeholder="Your last name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="your.email@example.com"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Country -->
                <div>
                    <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                    <select id="country_code" 
                            name="country_code" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select your country</option>
                        <option value="US" {{ old('country_code') == 'US' ? 'selected' : '' }}>United States</option>
                        <option value="CA" {{ old('country_code') == 'CA' ? 'selected' : '' }}>Canada</option>
                        <option value="GB" {{ old('country_code') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="AU" {{ old('country_code') == 'AU' ? 'selected' : '' }}>Australia</option>
                        <option value="DE" {{ old('country_code') == 'DE' ? 'selected' : '' }}>Germany</option>
                        <option value="FR" {{ old('country_code') == 'FR' ? 'selected' : '' }}>France</option>
                        <option value="ES" {{ old('country_code') == 'ES' ? 'selected' : '' }}>Spain</option>
                        <option value="IT" {{ old('country_code') == 'IT' ? 'selected' : '' }}>Italy</option>
                        <option value="NL" {{ old('country_code') == 'NL' ? 'selected' : '' }}>Netherlands</option>
                        <option value="SE" {{ old('country_code') == 'SE' ? 'selected' : '' }}>Sweden</option>
                        <option value="NO" {{ old('country_code') == 'NO' ? 'selected' : '' }}>Norway</option>
                        <option value="DK" {{ old('country_code') == 'DK' ? 'selected' : '' }}>Denmark</option>
                        <option value="FI" {{ old('country_code') == 'FI' ? 'selected' : '' }}>Finland</option>
                        <option value="CH" {{ old('country_code') == 'CH' ? 'selected' : '' }}>Switzerland</option>
                        <option value="AT" {{ old('country_code') == 'AT' ? 'selected' : '' }}>Austria</option>
                        <option value="BE" {{ old('country_code') == 'BE' ? 'selected' : '' }}>Belgium</option>
                        <option value="IE" {{ old('country_code') == 'IE' ? 'selected' : '' }}>Ireland</option>
                        <option value="PT" {{ old('country_code') == 'PT' ? 'selected' : '' }}>Portugal</option>
                        <option value="TH" {{ old('country_code') == 'TH' ? 'selected' : '' }}>Thailand</option>
                        <option value="MX" {{ old('country_code') == 'MX' ? 'selected' : '' }}>Mexico</option>
                        <option value="CO" {{ old('country_code') == 'CO' ? 'selected' : '' }}>Colombia</option>
                        <option value="BR" {{ old('country_code') == 'BR' ? 'selected' : '' }}>Brazil</option>
                        <option value="AR" {{ old('country_code') == 'AR' ? 'selected' : '' }}>Argentina</option>
                        <option value="CL" {{ old('country_code') == 'CL' ? 'selected' : '' }}>Chile</option>
                        <option value="PE" {{ old('country_code') == 'PE' ? 'selected' : '' }}>Peru</option>
                        <option value="UY" {{ old('country_code') == 'UY' ? 'selected' : '' }}>Uruguay</option>
                        <option value="EC" {{ old('country_code') == 'EC' ? 'selected' : '' }}>Ecuador</option>
                        <option value="JP" {{ old('country_code') == 'JP' ? 'selected' : '' }}>Japan</option>
                        <option value="KR" {{ old('country_code') == 'KR' ? 'selected' : '' }}>South Korea</option>
                        <option value="SG" {{ old('country_code') == 'SG' ? 'selected' : '' }}>Singapore</option>
                        <option value="MY" {{ old('country_code') == 'MY' ? 'selected' : '' }}>Malaysia</option>
                        <option value="ID" {{ old('country_code') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                        <option value="PH" {{ old('country_code') == 'PH' ? 'selected' : '' }}>Philippines</option>
                        <option value="VN" {{ old('country_code') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                        <option value="IN" {{ old('country_code') == 'IN' ? 'selected' : '' }}>India</option>
                        <option value="ZA" {{ old('country_code') == 'ZA' ? 'selected' : '' }}>South Africa</option>
                        <option value="NZ" {{ old('country_code') == 'NZ' ? 'selected' : '' }}>New Zealand</option>
                        <option value="IL" {{ old('country_code') == 'IL' ? 'selected' : '' }}>Israel</option>
                        <option value="AE" {{ old('country_code') == 'AE' ? 'selected' : '' }}>United Arab Emirates</option>
                        <option value="TR" {{ old('country_code') == 'TR' ? 'selected' : '' }}>Turkey</option>
                        <option value="RU" {{ old('country_code') == 'RU' ? 'selected' : '' }}>Russia</option>
                        <option value="PL" {{ old('country_code') == 'PL' ? 'selected' : '' }}>Poland</option>
                        <option value="CZ" {{ old('country_code') == 'CZ' ? 'selected' : '' }}>Czech Republic</option>
                        <option value="HU" {{ old('country_code') == 'HU' ? 'selected' : '' }}>Hungary</option>
                        <option value="RO" {{ old('country_code') == 'RO' ? 'selected' : '' }}>Romania</option>
                        <option value="BG" {{ old('country_code') == 'BG' ? 'selected' : '' }}>Bulgaria</option>
                        <option value="HR" {{ old('country_code') == 'HR' ? 'selected' : '' }}>Croatia</option>
                        <option value="SI" {{ old('country_code') == 'SI' ? 'selected' : '' }}>Slovenia</option>
                        <option value="SK" {{ old('country_code') == 'SK' ? 'selected' : '' }}>Slovakia</option>
                        <option value="LT" {{ old('country_code') == 'LT' ? 'selected' : '' }}>Lithuania</option>
                        <option value="LV" {{ old('country_code') == 'LV' ? 'selected' : '' }}>Latvia</option>
                        <option value="EE" {{ old('country_code') == 'EE' ? 'selected' : '' }}>Estonia</option>
                        <option value="GR" {{ old('country_code') == 'GR' ? 'selected' : '' }}>Greece</option>
                        <option value="CY" {{ old('country_code') == 'CY' ? 'selected' : '' }}>Cyprus</option>
                        <option value="MT" {{ old('country_code') == 'MT' ? 'selected' : '' }}>Malta</option>
                        <option value="LU" {{ old('country_code') == 'LU' ? 'selected' : '' }}>Luxembourg</option>
                        <option value="IS" {{ old('country_code') == 'IS' ? 'selected' : '' }}>Iceland</option>
                        <option value="LI" {{ old('country_code') == 'LI' ? 'selected' : '' }}>Liechtenstein</option>
                        <option value="MC" {{ old('country_code') == 'MC' ? 'selected' : '' }}>Monaco</option>
                        <option value="SM" {{ old('country_code') == 'SM' ? 'selected' : '' }}>San Marino</option>
                        <option value="VA" {{ old('country_code') == 'VA' ? 'selected' : '' }}>Vatican City</option>
                        <option value="AD" {{ old('country_code') == 'AD' ? 'selected' : '' }}>Andorra</option>
                        <option value="OTHER" {{ old('country_code') == 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Interests -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">What interests you most? (Select all that apply)</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="interests[]" 
                                   value="cities" 
                                   {{ in_array('cities', old('interests', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">🏙️ Cities & Destinations</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="interests[]" 
                                   value="cost_calculator" 
                                   {{ in_array('cost_calculator', old('interests', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">💰 Cost Calculator</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="interests[]" 
                                   value="deals" 
                                   {{ in_array('deals', old('interests', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">🎯 Exclusive Deals</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="interests[]" 
                                   value="articles" 
                                   {{ in_array('articles', old('interests', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">📝 Articles & Tips</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="interests[]" 
                                   value="coworking_spaces" 
                                   {{ in_array('coworking_spaces', old('interests', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">🏢 Coworking Spaces</span>
                        </label>
                    </div>
                </div>

                <!-- Hidden fields for tracking -->
                <input type="hidden" name="source" value="newsletter_page">

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-lg">
                        Subscribe to Newsletter
                    </button>
                </div>

                <!-- Privacy Notice -->
                <div class="text-center text-sm text-gray-500">
                    <p>We respect your privacy. Unsubscribe at any time.</p>
                    <p class="mt-1">
                        <a href="{{ route('newsletter.unsubscribe') }}" class="text-blue-600 hover:text-blue-700">
                            Already subscribed? Unsubscribe here
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Benefits -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="text-4xl mb-4">📧</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Weekly Updates</h3>
                <p class="text-gray-600">Get the latest destination guides and digital nomad tips delivered every week.</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-4">🎯</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Exclusive Deals</h3>
                <p class="text-gray-600">Access special discounts on accommodation, coworking spaces, and travel.</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-4">🌍</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Global Community</h3>
                <p class="text-gray-600">Join thousands of digital nomads sharing experiences and recommendations.</p>
            </div>
        </div>
    </div>
</div>
@endsection
