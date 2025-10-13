@extends('layouts.app')

@section('title', 'Unsubscribe - Digital Nomad Guide')
@section('description', 'Unsubscribe from our newsletter if you no longer wish to receive updates.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">📧 Unsubscribe</h1>
            <p class="text-xl text-gray-600">
                We're sorry to see you go! You can unsubscribe from our newsletter below.
            </p>
        </div>

        <!-- Unsubscribe Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <form method="POST" action="{{ route('newsletter.unsubscribe.process') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
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

                <div class="text-center">
                    <button type="submit" 
                            class="bg-red-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors text-lg">
                        Unsubscribe
                    </button>
                </div>

                <div class="text-center text-sm text-gray-500">
                    <p>Changed your mind? 
                        <a href="{{ route('newsletter.index') }}" class="text-blue-600 hover:text-blue-700">
                            Subscribe again
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Feedback -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Help us improve</h3>
            <p class="text-blue-800">
                If you're unsubscribing because of too many emails or irrelevant content, 
                please let us know how we can improve. You can always resubscribe later!
            </p>
        </div>
    </div>
</div>
@endsection
