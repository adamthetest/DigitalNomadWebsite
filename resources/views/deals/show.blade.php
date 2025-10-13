@extends('layouts.app')

@section('title', $deal->title . ' - Digital Nomad Guide')
@section('description', $deal->description)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Deal Header -->
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
                            <a href="{{ route('deals.index') }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">Deals</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 md:ml-2">{{ Str::limit($deal->title, 50) }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Deal Meta -->
            <div class="flex flex-wrap items-center gap-4 mb-6">
                <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                    {{ ucfirst($deal->category) }}
                </span>
                @if($deal->discount_percentage)
                    <span class="bg-red-100 text-red-800 text-sm font-semibold px-3 py-1 rounded-full">
                        -{{ $deal->discount_percentage }}% OFF
                    </span>
                @endif
                @if($deal->is_featured)
                    <span class="bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1 rounded-full">
                        ⭐ Featured Deal
                    </span>
                @endif
                <time class="text-gray-500">
                    Valid until {{ $deal->valid_until->format('F d, Y') }}
                </time>
            </div>

            <!-- Deal Title -->
            <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ $deal->title }}</h1>

            <!-- Deal Description -->
            @if($deal->description)
                <p class="text-xl text-gray-600 leading-relaxed">{{ $deal->description }}</p>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
                    <!-- Deal Image -->
                    @if($deal->image_url)
                        <div class="mb-8">
                            <img src="{{ $deal->image_url }}" 
                                 alt="{{ $deal->title }}" 
                                 class="w-full h-64 object-cover rounded-lg">
                        </div>
                    @endif

                    <!-- Deal Details -->
                    <div class="prose max-w-none text-gray-700">
                        @if($deal->terms_and_conditions)
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Terms & Conditions</h3>
                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                {!! nl2br(e($deal->terms_and_conditions)) !!}
                            </div>
                        @endif

                        @if($deal->additional_info)
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                            <div class="mb-6">
                                {!! nl2br(e($deal->additional_info)) !!}
                            </div>
                        @endif
                    </div>

                    <!-- Deal Actions -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ $deal->deal_url }}" 
                               target="_blank" 
                               class="w-full bg-green-600 text-white text-center py-3 px-6 rounded-md hover:bg-green-700 transition-colors font-semibold"
                               onclick="trackDealClick({{ $deal->id }})">
                                Get This Deal →
                            </a>
                        </div>
                        
                        <div class="mt-4 text-center text-sm text-gray-500">
                            <p>Deal expires on {{ $deal->valid_until->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Deal Stats -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Deal Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $deal->click_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Views</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $deal->valid_until->diffInDays(now()) }}</div>
                            <div class="text-sm text-gray-600">Days Left</div>
                        </div>
                        <div class="text-center">
                            @if($deal->discount_percentage)
                                <div class="text-2xl font-bold text-green-600">{{ $deal->discount_percentage }}%</div>
                                <div class="text-sm text-gray-600">Savings</div>
                            @else
                                <div class="text-2xl font-bold text-gray-900">Limited</div>
                                <div class="text-sm text-gray-600">Time Offer</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Deal Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Deal Summary</h3>
                    <div class="space-y-4">
                        @if($deal->original_price && $deal->discounted_price)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Original Price</span>
                                <span class="text-lg font-semibold text-gray-900 line-through">${{ $deal->original_price }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Discounted Price</span>
                                <span class="text-2xl font-bold text-green-600">${{ $deal->discounted_price }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">You Save</span>
                                <span class="text-lg font-semibold text-green-600">${{ $deal->original_price - $deal->discounted_price }}</span>
                            </div>
                        @elseif($deal->original_price)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Price</span>
                                <span class="text-2xl font-bold text-green-600">${{ $deal->original_price }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Valid Until</span>
                            <span class="font-semibold text-gray-900">{{ $deal->valid_until->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Category</span>
                            <span class="font-semibold text-gray-900">{{ ucfirst($deal->category) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Related Deals -->
                @if($relatedDeals->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Deals</h3>
                        <div class="space-y-4">
                            @foreach($relatedDeals as $relatedDeal)
                                <a href="{{ route('deals.show', $relatedDeal) }}" 
                                   class="block border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $relatedDeal->title }}</h4>
                                    <div class="flex justify-between items-center text-sm text-gray-500">
                                        <span>{{ ucfirst($relatedDeal->category) }}</span>
                                        @if($relatedDeal->discount_percentage)
                                            <span class="font-semibold text-green-600">-{{ $relatedDeal->discount_percentage }}%</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Latest Deals -->
                @if($latestDeals->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Latest Deals</h3>
                        <div class="space-y-3">
                            @foreach($latestDeals as $latestDeal)
                                <a href="{{ route('deals.show', $latestDeal) }}" 
                                   class="block border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow">
                                    <h4 class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ $latestDeal->title }}</h4>
                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>{{ ucfirst($latestDeal->category) }}</span>
                                        @if($latestDeal->discount_percentage)
                                            <span class="font-semibold text-green-600">-{{ $latestDeal->discount_percentage }}%</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function trackDealClick(dealId) {
    // Track deal clicks for analytics
    fetch('/deals/' + dealId + '/click', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    }).catch(console.error);
}
</script>
@endsection
