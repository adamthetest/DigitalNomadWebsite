@extends('layouts.app')

@section('title', $coworkingSpace->name . ' - Coworking Space - Digital Nomad Guide')
@section('description', $coworkingSpace->description)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative h-96 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative h-full flex items-center justify-center">
            <div class="text-center text-white px-4">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">{{ $coworkingSpace->name }}</h1>
                <p class="text-xl md:text-2xl text-blue-100">
                    {{ $coworkingSpace->city->name }}, {{ $coworkingSpace->city->country->name }}
                </p>
                @if($coworkingSpace->is_verified)
                    <span class="inline-block bg-green-400 text-green-900 px-4 py-2 rounded-full text-sm font-semibold mt-4">
                        ✅ Verified Space
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @if($coworkingSpace->wifi_speed_mbps)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                    <div class="text-3xl mb-2">🌐</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $coworkingSpace->wifi_speed_mbps }}</div>
                    <div class="text-gray-600">Mbps WiFi</div>
                </div>
            @endif
            @if($coworkingSpace->monthly_rate)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                    <div class="text-3xl mb-2">💰</div>
                    <div class="text-2xl font-bold text-gray-900">${{ $coworkingSpace->monthly_rate }}</div>
                    <div class="text-gray-600">Monthly Rate</div>
                </div>
            @endif
            @if($coworkingSpace->seating_capacity)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                    <div class="text-3xl mb-2">🪑</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $coworkingSpace->seating_capacity }}</div>
                    <div class="text-gray-600">Seating Capacity</div>
                </div>
            @endif
            @if($coworkingSpace->rating)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                    <div class="text-3xl mb-2">⭐</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $coworkingSpace->rating }}</div>
                    <div class="text-gray-600">Rating</div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About {{ $coworkingSpace->name }}</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($coworkingSpace->description)) !!}
                    </div>
                </div>

                <!-- Amenities -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Amenities & Features</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🔌</span>
                            <span class="text-gray-700">{{ $coworkingSpace->has_power_outlets ? 'Power Outlets' : 'No Power Outlets' }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">❄️</span>
                            <span class="text-gray-700">{{ $coworkingSpace->has_air_conditioning ? 'Air Conditioning' : 'No AC' }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🍳</span>
                            <span class="text-gray-700">{{ $coworkingSpace->has_kitchen ? 'Kitchen' : 'No Kitchen' }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🏢</span>
                            <span class="text-gray-700">{{ $coworkingSpace->has_meeting_rooms ? 'Meeting Rooms' : 'No Meeting Rooms' }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🖨️</span>
                            <span class="text-gray-700">{{ $coworkingSpace->has_printing ? 'Printing' : 'No Printing' }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🕐</span>
                            <span class="text-gray-700">{{ $coworkingSpace->is_24_hours ? '24/7 Access' : 'Limited Hours' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                @if($coworkingSpace->daily_rate || $coworkingSpace->monthly_rate)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Pricing</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($coworkingSpace->daily_rate)
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-3xl font-bold text-gray-900">${{ $coworkingSpace->daily_rate }}</div>
                                    <div class="text-gray-600">Per Day</div>
                                </div>
                            @endif
                            @if($coworkingSpace->monthly_rate)
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <div class="text-3xl font-bold text-blue-600">${{ $coworkingSpace->monthly_rate }}</div>
                                    <div class="text-gray-600">Per Month</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Information</h2>
                    <div class="space-y-3">
                        @if($coworkingSpace->address)
                            <div class="flex items-start space-x-3">
                                <span class="text-gray-500 mt-1">📍</span>
                                <div>
                                    <div class="font-medium text-gray-900">Address</div>
                                    <div class="text-gray-600">{{ $coworkingSpace->address }}</div>
                                </div>
                            </div>
                        @endif
                        @if($coworkingSpace->phone)
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-500">📞</span>
                                <div>
                                    <div class="font-medium text-gray-900">Phone</div>
                                    <div class="text-gray-600">{{ $coworkingSpace->phone }}</div>
                                </div>
                            </div>
                        @endif
                        @if($coworkingSpace->email)
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-500">✉️</span>
                                <div>
                                    <div class="font-medium text-gray-900">Email</div>
                                    <div class="text-gray-600">{{ $coworkingSpace->email }}</div>
                                </div>
                            </div>
                        @endif
                        @if($coworkingSpace->website)
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-500">🌐</span>
                                <div>
                                    <div class="font-medium text-gray-900">Website</div>
                                    <a href="{{ $coworkingSpace->website }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                        {{ $coworkingSpace->website }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Map -->
                @if($coworkingSpace->latitude && $coworkingSpace->longitude)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Location</h3>
                        <div id="spaceMap" style="height: 300px; width: 100%;" class="rounded-lg"></div>
                    </div>
                @endif

                <!-- Related Spaces -->
                @if($relatedSpaces->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">More Spaces in {{ $coworkingSpace->city->name }}</h3>
                        <div class="space-y-4">
                            @foreach($relatedSpaces as $space)
                                <a href="{{ route('coworking-spaces.show', $space) }}" 
                                   class="block border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $space->name }}</h4>
                                    <div class="flex justify-between items-center text-sm text-gray-500">
                                        <span>{{ $space->neighborhood->name ?? 'Downtown' }}</span>
                                        @if($space->monthly_rate)
                                            <span class="font-semibold text-green-600">${{ $space->monthly_rate }}/month</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Latest Spaces -->
                @if($latestSpaces->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Latest Spaces</h3>
                        <div class="space-y-3">
                            @foreach($latestSpaces as $space)
                                <a href="{{ route('coworking-spaces.show', $space) }}" 
                                   class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                    <h4 class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ $space->name }}</h4>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>{{ $space->city->name }}</span>
                                        @if($space->monthly_rate)
                                            <span class="font-semibold text-green-600">${{ $space->monthly_rate }}/month</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($coworkingSpace->latitude && $coworkingSpace->longitude)
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    const map = L.map('spaceMap').setView([{{ $coworkingSpace->latitude }}, {{ $coworkingSpace->longitude }}], 15);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add a marker for the coworking space
    L.marker([{{ $coworkingSpace->latitude }}, {{ $coworkingSpace->longitude }}])
        .addTo(map)
        .bindPopup(`
            <div class="text-center">
                <h3 class="font-bold text-lg">{{ $coworkingSpace->name }}</h3>
                <p class="text-sm text-gray-600">{{ $coworkingSpace->address }}</p>
                @if($coworkingSpace->monthly_rate)
                    <p class="text-sm font-semibold text-green-600 mt-2">${{ $coworkingSpace->monthly_rate }}/month</p>
                @endif
            </div>
        `);
});
</script>
@endif
@endsection
