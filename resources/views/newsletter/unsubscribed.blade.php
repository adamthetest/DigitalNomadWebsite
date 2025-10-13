@extends('layouts.app')

@section('title', 'Unsubscribed - Digital Nomad Guide')
@section('description', 'You have successfully unsubscribed from our newsletter.')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Message -->
        <div class="text-center mb-12">
            <div class="text-6xl mb-4">✅</div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Successfully Unsubscribed</h1>
            <p class="text-xl text-gray-600">
                You have been unsubscribed from our newsletter. We're sorry to see you go!
            </p>
        </div>

        <!-- Confirmation Details -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">What happens next?</h2>
            <div class="space-y-4 text-gray-600">
                <p>✅ You will no longer receive our weekly newsletter</p>
                <p>✅ You will no longer receive promotional emails</p>
                <p>✅ Your email has been removed from our mailing list</p>
                <p>✅ You can still access all our free content on the website</p>
            </div>
        </div>

        <!-- Re-subscribe Option -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6 text-center">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Changed your mind?</h3>
            <p class="text-blue-800 mb-4">
                You can always resubscribe to our newsletter if you change your mind.
            </p>
            <a href="{{ route('newsletter.index') }}" 
               class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Subscribe Again
            </a>
        </div>

        <!-- Alternative Options -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl mb-3">🌐</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Follow Us Online</h3>
                <p class="text-gray-600 mb-4">Stay connected through our social media channels</p>
                <div class="space-x-4">
                    <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Twitter</a>
                    <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Instagram</a>
                    <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">LinkedIn</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <div class="text-3xl mb-3">📚</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Explore Our Content</h3>
                <p class="text-gray-600 mb-4">Check out our latest articles and guides</p>
                <a href="{{ route('articles.index') }}" 
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                    Read Articles
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
