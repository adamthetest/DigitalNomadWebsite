<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\City;
use App\Models\Deal;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCities = City::where('is_featured', true)
            ->where('is_active', true)
            ->limit(6)
            ->get();

        $latestArticles = Article::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        $featuredDeals = Deal::where('is_featured', true)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->limit(4)
            ->get();

        return view('home', compact('featuredCities', 'latestArticles', 'featuredDeals'));
    }
}
