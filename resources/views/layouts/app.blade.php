<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Digital Nomad Guide') }} - @yield('title', 'Find Your Next Destination')</title>
    <meta name="description" content="@yield('description', 'Discover the best cities for digital nomads. Find coworking spaces, cost of living data, visa information, and more.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">
                        NomadGuide
                    </a>
                </div>
                
                <div class="flex items-center space-x-8">
                    <a href="{{ route('cities.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Cities</a>
                    <a href="{{ route('calculator.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Calculator</a>
                    <a href="{{ route('deals.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Deals</a>
                    <a href="{{ route('articles.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">Blog</a>
                    
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-600 font-medium">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Sign Up
                        </a>
                    @endauth
                    
                    <a href="/admin" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">NomadGuide</h3>
                    <p class="text-gray-400">Your ultimate resource for digital nomad destinations, coworking spaces, and cost of living data.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">City Guides</a></li>
                        <li><a href="#" class="hover:text-white">Cost Calculator</a></li>
                        <li><a href="#" class="hover:text-white">Visa Information</a></li>
                        <li><a href="#" class="hover:text-white">Coworking Spaces</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Community</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Newsletter</a></li>
                        <li><a href="#" class="hover:text-white">Blog</a></li>
                        <li><a href="#" class="hover:text-white">Deals</a></li>
                        <li><a href="#" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white">Disclaimer</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} NomadGuide. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
