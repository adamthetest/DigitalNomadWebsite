@extends('layouts.app')

@section('title', 'Access Denied - Digital Nomad Guide')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <div class="text-6xl mb-4">🚫</div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Access Denied
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                You don't have permission to access the admin panel.
            </p>
        </div>
        
        <div class="mt-8 space-y-4">
            <a href="{{ route('home') }}" 
               class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Go to Homepage
            </a>
            
            @auth
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Logout
                    </button>
                </form>
            @endauth
        </div>
        
        <div class="mt-8 text-xs text-gray-500">
            <p>If you believe this is an error, please contact the administrator.</p>
        </div>
    </div>
</div>
@endsection
