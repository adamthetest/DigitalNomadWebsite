<?php

namespace App\Jobs;

use App\Services\AiContentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The content types to generate.
     */
    public array $contentTypes;

    /**
     * Create a new job instance.
     */
    public function __construct(array $contentTypes = ['newsletter', 'trending_destinations'])
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * Execute the job.
     */
    public function handle(AiContentGenerationService $contentService): void
    {
        Log::info('Starting weekly content generation', [
            'content_types' => $this->contentTypes,
        ]);

        $generatedContent = [];

        foreach ($this->contentTypes as $type) {
            try {
                $content = $this->generateContentByType($contentService, $type);

                if ($content) {
                    $generatedContent[] = $content;
                    Log::info("Generated {$type} content", [
                        'content_id' => $content->id,
                        'title' => $content->title,
                    ]);
                } else {
                    Log::warning("Failed to generate {$type} content");
                }
            } catch (\Exception $e) {
                Log::error("Error generating {$type} content", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Weekly content generation completed', [
            'generated_count' => count($generatedContent),
            'content_ids' => collect($generatedContent)->pluck('id')->toArray(),
        ]);
    }

    /**
     * Generate content based on type.
     */
    private function generateContentByType(AiContentGenerationService $contentService, string $type): ?\App\Models\AiGeneratedContent
    {
        return match ($type) {
            'newsletter' => $contentService->generateWeeklyNewsletter(),
            'trending_destinations' => $contentService->generateTrendingDestinationsPost(),
            'top_cities' => $contentService->generateTopCitiesBlogPost(),
            default => null,
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateWeeklyContent job failed', [
            'content_types' => $this->contentTypes,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
