@extends('layouts.app')

@section('title', $article->title . ' - Digital Nomad Guide')
@section('description', $article->excerpt)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Article Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">Home</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('articles.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">Articles</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 md:ml-2">{{ Str::limit($article->title, 50) }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Article Meta -->
            <div class="flex flex-wrap items-center gap-4 mb-6">
                @if($article->type)
                    <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                        {{ ucfirst($article->type) }}
                    </span>
                @endif
                <time class="text-gray-500">
                    {{ $article->published_at->format('F d, Y') }}
                </time>
                @if($article->city)
                    <span class="text-gray-500">
                        📍 {{ $article->city->name }}, {{ $article->city->country->name }}
                    </span>
                @endif
                @if($article->author)
                    <span class="text-gray-500">
                        By {{ $article->author }}
                    </span>
                @endif
            </div>

            <!-- Article Title -->
            <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ $article->title }}</h1>

            <!-- Article Excerpt -->
            @if($article->excerpt)
                <div class="prose max-w-none text-gray-600 leading-relaxed">
                    {!! $article->parsed_excerpt !!}
                </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <article class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <!-- Featured Image -->
                    @if($article->featured_image)
                        <div class="mb-8">
                            <img src="{{ $article->featured_image }}" 
                                 alt="{{ $article->title }}" 
                                 class="w-full h-64 object-cover rounded-lg">
                        </div>
                    @endif

                    <!-- Article Content -->
                    <div class="prose max-w-none text-gray-700">
                        {!! $article->parsed_content !!}
                    </div>

                    <!-- Article Footer -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-4">
                            @if($article->city)
                                <a href="{{ route('cities.show', $article->city) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Explore {{ $article->city->name }}
                                </a>
                            @endif
                            
                            <div class="flex items-center space-x-4">
                                <button class="text-gray-500 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </button>
                                <button class="text-gray-500 hover:text-blue-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Related Articles -->
                @if($relatedArticles->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Articles</h3>
                        <div class="space-y-4">
                            @foreach($relatedArticles as $relatedArticle)
                                <a href="{{ route('articles.show', $relatedArticle) }}" 
                                   class="block border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $relatedArticle->title }}</h4>
                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">{!! $relatedArticle->parsed_excerpt !!}</p>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>{{ $relatedArticle->published_at->format('M d, Y') }}</span>
                                        @if($relatedArticle->city)
                                            <span>{{ $relatedArticle->city->name }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Latest Articles -->
                @if($latestArticles->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Latest Articles</h3>
                        <div class="space-y-4">
                            @foreach($latestArticles as $latestArticle)
                                <a href="{{ route('articles.show', $latestArticle) }}" 
                                   class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                    <h4 class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ $latestArticle->title }}</h4>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>{{ $latestArticle->published_at->format('M d') }}</span>
                                        @if($latestArticle->city)
                                            <span>{{ $latestArticle->city->name }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Newsletter Signup -->
                <div class="bg-blue-600 rounded-lg p-6 text-white">
                    <h3 class="text-lg font-semibold mb-2">Stay Updated</h3>
                    <p class="text-blue-100 text-sm mb-4">
                        Get the latest digital nomad tips and destination guides delivered to your inbox.
                    </p>
                    <form class="space-y-3">
                        <input type="email" 
                               placeholder="Enter your email" 
                               class="w-full px-3 py-2 rounded text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                        <button type="submit" 
                                class="w-full bg-white text-blue-600 py-2 rounded font-semibold hover:bg-gray-100 transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
