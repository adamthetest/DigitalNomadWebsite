<?php

namespace App\Jobs;

use App\Services\AffiliateLinkValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateAffiliateLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * The category to validate (null for all categories).
     */
    public ?string $category;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $category = null)
    {
        $this->category = $category;
    }

    /**
     * Execute the job.
     */
    public function handle(AffiliateLinkValidationService $validationService): void
    {
        Log::info('Starting affiliate link validation', [
            'category' => $this->category ?? 'all',
        ]);

        try {
            if ($this->category) {
                // Validate links from specific category
                $results = $validationService->validateLinksByCategory($this->category);

                Log::info("Affiliate link validation completed for category: {$this->category}", [
                    'category' => $this->category,
                    'results' => $results,
                ]);
            } else {
                // Validate all links
                $results = $validationService->validateAllLinks();

                Log::info('Affiliate link validation completed for all categories', [
                    'total' => $results['total'],
                    'valid' => $results['valid'],
                    'invalid' => $results['invalid'],
                    'errors' => $results['errors'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Affiliate link validation failed', [
                'category' => $this->category ?? 'all',
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
        Log::error('ValidateAffiliateLinks job failed', [
            'category' => $this->category ?? 'all',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
