@extends('layouts.app')

@section('title', 'Coworking Spaces - Digital Nomad Guide')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">🏢 Coworking Spaces</h1>
        
        @if($coworkingSpaces->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($coworkingSpaces as $space)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $space->name }}</h3>
                        <p class="text-gray-600 mb-4">{{ $space->city->name }}</p>
                        <a href="{{ route('coworking-spaces.show', $space) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            View Details
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600">No coworking spaces found.</p>
        @endif
    </div>
</div>
@endsection