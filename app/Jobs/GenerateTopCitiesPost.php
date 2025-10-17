<?php

namespace App\Jobs;

use App\Services\AiContentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTopCitiesPost implements ShouldQueue
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
     * The year for the top cities post.
     */
    public int $year;

    /**
     * Create a new job instance.
     */
    public function __construct(int $year)
    {
        $this->year = $year;
    }

    /**
     * Execute the job.
     */
    public function handle(AiContentGenerationService $contentService): void
    {
        Log::info('Starting top cities post generation', [
            'year' => $this->year,
        ]);

        try {
            $content = $contentService->generateTopCitiesBlogPost($this->year);

            if ($content) {
                Log::info('Top cities post generated successfully', [
                    'content_id' => $content->id,
                    'title' => $content->title,
                    'year' => $this->year,
                ]);
            } else {
                Log::error('Failed to generate top cities post', [
                    'year' => $this->year,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating top cities post', [
                'year' => $this->year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateTopCitiesPost job failed', [
            'year' => $this->year,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
