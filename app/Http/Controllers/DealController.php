<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Display a listing of deals.
     */
    public function index(Request $request)
    {
        $query = Deal::where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by discount range
        if ($request->filled('discount_min')) {
            $query->where('discount_percentage', '>=', $request->discount_min);
        }

        $deals = $query->paginate(12);
        
        // Get categories
        $categories = Deal::where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('deals.index', compact('deals', 'categories'));
    }

    /**
     * Display the specified deal.
     */
    public function show(Deal $deal)
    {
        // Ensure deal is active and valid
        if (!$deal->is_active || $deal->valid_from > now() || $deal->valid_until < now()) {
            abort(404);
        }
        
        // Get related deals
        $relatedDeals = Deal::where('is_active', true)
            ->where('id', '!=', $deal->id)
            ->where('category', $deal->category)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Get latest deals
        $latestDeals = Deal::where('is_active', true)
            ->where('id', '!=', $deal->id)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('deals.show', compact('deal', 'relatedDeals', 'latestDeals'));
    }

    /**
     * Track deal click for analytics.
     */
    public function trackClick(Deal $deal)
    {
        // Increment click count
        $deal->increment('click_count');
        
        return response()->json(['success' => true]);
    }
}
