<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\City;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request)
    {
        $query = Article::with('city.country')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city_id', $request->city);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $articles = $query->paginate(12);
        
        // Get cities for filter dropdown
        $cities = City::where('is_active', true)->orderBy('name')->get();
        
        // Get types
        $types = Article::where('status', 'published')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values();

        return view('articles.index', compact('articles', 'cities', 'types'));
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article)
    {
        // Ensure article is published
        if ($article->status !== 'published') {
            abort(404);
        }

        $article->load('city.country');
        
        // Get related articles
        $relatedArticles = Article::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->where(function ($q) use ($article) {
                $q->where('city_id', $article->city_id)
                  ->orWhere('type', $article->type);
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // Get latest articles
        $latestArticles = Article::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return view('articles.show', compact('article', 'relatedArticles', 'latestArticles'));
    }
}
