@extends('layouts.app')

@section('title', 'Dashboard - Digital Nomad Guide')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="mt-2 text-gray-600">Manage your digital nomad journey from your dashboard.</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <span class="text-white text-sm">🌍</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Cities Explored</dt>
                                <dd class="text-lg font-medium text-gray-900">0</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <span class="text-white text-sm">💰</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Cost Calculations</dt>
                                <dd class="text-lg font-medium text-gray-900">0</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <span class="text-white text-sm">📝</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Articles Read</dt>
                                <dd class="text-lg font-medium text-gray-900">0</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                <span class="text-white text-sm">🎯</span>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Deals Used</dt>
                                <dd class="text-lg font-medium text-gray-900">0</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Activity -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Activity</h3>
                    <div class="text-center py-8">
                        <p class="text-gray-500">No recent activity yet.</p>
                        <p class="text-sm text-gray-400 mt-2">Start exploring cities to see your activity here!</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('home') }}" class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            Explore Cities
                        </a>
                        <a href="#" class="block w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                            Cost Calculator
                        </a>
                        <a href="#" class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                            Browse Deals
                        </a>
                        <a href="#" class="block w-full bg-purple-600 text-white text-center py-2 px-4 rounded-md hover:bg-purple-700 transition-colors">
                            Read Articles
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Account Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Profile Information</h4>
                        <p class="text-sm text-gray-600 mb-4">Update your personal information and preferences.</p>
                        <button class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            Edit Profile
                        </button>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Security</h4>
                        <p class="text-sm text-gray-600 mb-4">Manage your password and security settings.</p>
                        <button class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                            Change Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
