<?php

namespace App\Jobs;

use App\Models\City;
use App\Services\AiContentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCityGuides implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 900; // 15 minutes

    /**
     * The city ID to generate guide for (null for all cities).
     */
    public ?int $cityId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $cityId = null)
    {
        $this->cityId = $cityId;
    }

    /**
     * Execute the job.
     */
    public function handle(AiContentGenerationService $contentService): void
    {
        Log::info('Starting city guides generation', [
            'city_id' => $this->cityId,
        ]);

        $cities = $this->getCitiesToProcess();
        $generatedGuides = [];

        foreach ($cities as $city) {
            try {
                // Check if guide already exists
                $existingGuide = \App\Models\AiGeneratedContent::where('content_type', 'city_guide')
                    ->whereJsonContains('metadata->city_id', $city->id)
                    ->first();

                if ($existingGuide) {
                    Log::info("City guide already exists for {$city->name}", [
                        'city_id' => $city->id,
                        'existing_content_id' => $existingGuide->id,
                    ]);
                    continue;
                }

                $guide = $contentService->generateCityGuide($city);
                
                if ($guide) {
                    $generatedGuides[] = $guide;
                    Log::info("Generated city guide for {$city->name}", [
                        'city_id' => $city->id,
                        'content_id' => $guide->id,
                        'title' => $guide->title,
                    ]);
                } else {
                    Log::warning("Failed to generate city guide for {$city->name}", [
                        'city_id' => $city->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error generating city guide for {$city->name}", [
                    'city_id' => $city->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('City guides generation completed', [
            'processed_cities' => $cities->count(),
            'generated_guides' => count($generatedGuides),
            'content_ids' => collect($generatedGuides)->pluck('id')->toArray(),
        ]);
    }

    /**
     * Get cities to process.
     */
    private function getCitiesToProcess()
    {
        $query = City::where('is_active', true)
            ->where('is_featured', true) // Start with featured cities
            ->with('country');

        if ($this->cityId) {
            $query->where('id', $this->cityId);
        }

        return $query->get();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateCityGuides job failed', [
            'city_id' => $this->cityId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}