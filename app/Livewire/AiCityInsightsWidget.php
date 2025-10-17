<?php

namespace App\Livewire;

use App\Models\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class AiCityInsightsWidget extends Component
{
    public City $city;

    public $aiSummary = null;

    public $aiInsights = null;

    public $loading = false;

    public $error = null;

    public $showFullSummary = false;

    protected $listeners = ['refreshInsights' => 'loadAiData'];

    public function mount(City $city)
    {
        $this->city = $city;
        $this->loadAiData();
    }

    public function loadAiData()
    {
        $this->loading = true;
        $this->error = null;

        try {
            // Check if we have cached AI data
            if ($this->city->ai_summary && $this->city->ai_data_updated_at &&
                $this->city->ai_data_updated_at->isAfter(now()->subDays(7))) {
                $this->aiSummary = $this->city->ai_summary;
                $this->aiInsights = $this->city->ai_tags;
                $this->loading = false;

                return;
            }

            // Generate new AI data via API
            $this->generateAiData();
        } catch (\Exception $e) {
            $this->error = 'Failed to load AI insights. Please try again.';
            $this->loading = false;
        }
    }

    private function generateAiData()
    {
        try {
            // Make API call to get AI summary
            $response = Http::timeout(30)->get(url('/api/v1/ai-advisor/city/'.$this->city->id.'/summary'));

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->aiSummary = $data['data']['ai_summary'];
                    $this->aiInsights = $data['data']['ai_tags'];
                } else {
                    $this->error = $data['message'] ?? 'Failed to generate AI summary';
                }
            } else {
                $this->error = 'API request failed';
            }
        } catch (\Exception $e) {
            $this->error = 'Network error occurred';
        }

        $this->loading = false;
    }

    public function toggleSummary()
    {
        $this->showFullSummary = ! $this->showFullSummary;
    }

    public function getCityRecommendations()
    {
        if (! Auth::check()) {
            $this->error = 'Please login to get personalized recommendations';

            return;
        }

        $this->loading = true;
        $this->error = null;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.Auth::user()->createToken('api')->plainTextToken,
                ])
                ->get(url('/api/v1/ai-advisor/city-recommendations'));

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->dispatch('showRecommendations', $data['data']);
                } else {
                    $this->error = $data['message'] ?? 'Failed to get recommendations';
                }
            } else {
                $this->error = 'API request failed';
            }
        } catch (\Exception $e) {
            $this->error = 'Network error occurred';
        }

        $this->loading = false;
    }

    public function compareWithCity($cityId)
    {
        $this->loading = true;
        $this->error = null;

        try {
            $response = Http::timeout(30)->post(url('/api/v1/ai-advisor/compare-cities'), [
                'city1_id' => $this->city->id,
                'city2_id' => $cityId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->dispatch('showComparison', $data['data']);
                } else {
                    $this->error = $data['message'] ?? 'Failed to compare cities';
                }
            } else {
                $this->error = 'API request failed';
            }
        } catch (\Exception $e) {
            $this->error = 'Network error occurred';
        }

        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.ai-city-insights-widget');
    }
}
