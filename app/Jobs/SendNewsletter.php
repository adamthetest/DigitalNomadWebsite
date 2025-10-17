<?php

namespace App\Jobs;

use App\Services\NewsletterAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNewsletter implements ShouldQueue
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
     * The email to send test newsletter to (null for all subscribers).
     */
    public ?string $testEmail;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $testEmail = null)
    {
        $this->testEmail = $testEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(NewsletterAutomationService $newsletterService): void
    {
        Log::info('Starting newsletter sending', [
            'test_email' => $this->testEmail ?? 'all_subscribers',
        ]);

        try {
            if ($this->testEmail) {
                // Send test newsletter
                $result = $newsletterService->sendTestNewsletter($this->testEmail);

                Log::info('Test newsletter sent', [
                    'test_email' => $this->testEmail,
                    'result' => $result,
                ]);
            } else {
                // Send newsletter to all subscribers
                $result = $newsletterService->generateAndSendNewsletter();

                Log::info('Newsletter sent to all subscribers', [
                    'subscribers_count' => $result['subscribers_count'],
                    'sent_count' => $result['sent_count'] ?? 0,
                    'newsletter_id' => $result['newsletter_id'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Newsletter sending failed', [
                'test_email' => $this->testEmail ?? 'all_subscribers',
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
        Log::error('SendNewsletter job failed', [
            'test_email' => $this->testEmail ?? 'all_subscribers',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
