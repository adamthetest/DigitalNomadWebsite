<?php

namespace App\Console\Commands;

use App\Jobs\SendNewsletter;
use App\Services\NewsletterAutomationService;
use Illuminate\Console\Command;

class SendNewsletterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:send 
                            {--test-email= : Send test newsletter to specific email}
                            {--queue : Dispatch to queue instead of running immediately}
                            {--stats : Show newsletter statistics}
                            {--add-subscriber= : Add subscriber email}
                            {--remove-subscriber= : Remove subscriber email}
                            {--cleanup : Clean up inactive subscribers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send newsletter to subscribers or manage newsletter subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(NewsletterAutomationService $newsletterService): int
    {
        $testEmail = $this->option('test-email');
        $useQueue = $this->option('queue');
        $showStats = $this->option('stats');
        $addSubscriber = $this->option('add-subscriber');
        $removeSubscriber = $this->option('remove-subscriber');
        $cleanup = $this->option('cleanup');

        if ($showStats) {
            $this->showNewsletterStats($newsletterService);

            return 0;
        }

        if ($addSubscriber) {
            $this->addSubscriber($newsletterService, $addSubscriber);

            return 0;
        }

        if ($removeSubscriber) {
            $this->removeSubscriber($newsletterService, $removeSubscriber);

            return 0;
        }

        if ($cleanup) {
            $this->cleanupInactiveSubscribers($newsletterService);

            return 0;
        }

        $this->info('Starting newsletter sending...');

        try {
            if ($useQueue) {
                SendNewsletter::dispatch($testEmail);
                $this->info('📧 Newsletter job dispatched to queue');
            } else {
                $this->sendNewsletterImmediately($newsletterService, $testEmail);
            }

            $this->info('✅ Newsletter sending completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error sending newsletter: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Send newsletter immediately.
     */
    private function sendNewsletterImmediately(NewsletterAutomationService $newsletterService, ?string $testEmail): void
    {
        if ($testEmail) {
            $this->info("📧 Sending test newsletter to: {$testEmail}");
            $result = $newsletterService->sendTestNewsletter($testEmail);

            $this->displayTestResult($result);
        } else {
            $this->info('📧 Sending newsletter to all subscribers...');
            $result = $newsletterService->generateAndSendNewsletter();

            $this->displayNewsletterResult($result);
        }
    }

    /**
     * Display test newsletter result.
     */
    private function displayTestResult(array $result): void
    {
        if ($result['success']) {
            $this->info('✅ Test newsletter sent successfully!');
            $this->line("• Newsletter ID: {$result['newsletter_id']}");
        } else {
            $this->error("❌ Failed to send test newsletter: {$result['message']}");
        }
    }

    /**
     * Display newsletter result.
     */
    private function displayNewsletterResult(array $result): void
    {
        if ($result['success']) {
            $this->info('✅ Newsletter sent successfully!');
            $this->line("• Subscribers: {$result['subscribers_count']}");
            $this->line("• Sent: {$result['sent_count']}");
            $this->line("• Newsletter ID: {$result['newsletter_id']}");
        } else {
            $this->error("❌ Failed to send newsletter: {$result['message']}");
        }
    }

    /**
     * Show newsletter statistics.
     */
    private function showNewsletterStats(NewsletterAutomationService $newsletterService): void
    {
        $stats = $newsletterService->getNewsletterStats();

        $this->info('📊 Newsletter Statistics:');
        $this->line("• Total subscribers: {$stats['total_subscribers']}");
        $this->line("• Active subscribers: {$stats['active_subscribers']}");
        $this->line("• Inactive subscribers: {$stats['inactive_subscribers']}");
        $this->line("• Recent subscribers (last week): {$stats['recent_subscribers']}");
        $this->line("• Recent unsubscribes (last week): {$stats['recent_unsubscribes']}");
        $this->line("• Growth rate: {$stats['growth_rate']}%");

        if ($stats['last_sent_at']) {
            $this->line("• Last sent: {$stats['last_sent_at']}");
        } else {
            $this->line('• Last sent: Never');
        }
    }

    /**
     * Add subscriber.
     */
    private function addSubscriber(NewsletterAutomationService $newsletterService, string $email): void
    {
        $this->info("📧 Adding subscriber: {$email}");

        $result = $newsletterService->addSubscriber($email);

        if ($result['success']) {
            $this->info('✅ Subscriber added successfully!');
            $this->line("• Subscriber ID: {$result['subscriber_id']}");
        } else {
            $this->error("❌ Failed to add subscriber: {$result['message']}");
        }
    }

    /**
     * Remove subscriber.
     */
    private function removeSubscriber(NewsletterAutomationService $newsletterService, string $email): void
    {
        $this->info("📧 Removing subscriber: {$email}");

        $result = $newsletterService->removeSubscriber($email);

        if ($result['success']) {
            $this->info('✅ Subscriber removed successfully!');
        } else {
            $this->error("❌ Failed to remove subscriber: {$result['message']}");
        }
    }

    /**
     * Cleanup inactive subscribers.
     */
    private function cleanupInactiveSubscribers(NewsletterAutomationService $newsletterService): void
    {
        $this->info('🧹 Cleaning up inactive subscribers...');

        $deletedCount = $newsletterService->cleanupInactiveSubscribers();

        $this->info('✅ Cleanup completed!');
        $this->line("• Inactive subscribers deleted: {$deletedCount}");
    }
}
