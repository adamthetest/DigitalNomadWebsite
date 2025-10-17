<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAiContextData;
use Illuminate\Console\Command;

class ProcessAiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:process 
                            {type : The type of data to process (city, job, user, all)}
                            {--id= : Specific ID to process (optional)}
                            {--queue : Process in background queue}
                            {--force : Force processing even if data is recent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process AI context data for cities, jobs, and users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $id = $this->option('id');
        $useQueue = $this->option('queue');
        $force = $this->option('force');

        if (!in_array($type, ['city', 'job', 'user', 'all'])) {
            $this->error('Invalid type. Must be one of: city, job, user, all');
            return 1;
        }

        $this->info("Processing AI data for type: {$type}");

        if ($useQueue) {
            $this->processInQueue($type, $id);
        } else {
            $this->processSynchronously($type, $id, $force);
        }

        $this->info('AI data processing completed successfully!');
        return 0;
    }

    /**
     * Process data in background queue.
     */
    private function processInQueue(string $type, ?string $id): void
    {
        if ($type === 'all') {
            ProcessAiContextData::dispatch('city', $id ? (int) $id : null);
            ProcessAiContextData::dispatch('job', $id ? (int) $id : null);
            ProcessAiContextData::dispatch('user', $id ? (int) $id : null);
            $this->info('Queued AI processing jobs for all types');
        } else {
            ProcessAiContextData::dispatch($type, $id ? (int) $id : null);
            $this->info("Queued AI processing job for {$type}");
        }
    }

    /**
     * Process data synchronously.
     */
    private function processSynchronously(string $type, ?string $id, bool $force): void
    {
        if ($type === 'all') {
            $this->processType('city', $id, $force);
            $this->processType('job', $id, $force);
            $this->processType('user', $id, $force);
        } else {
            $this->processType($type, $id, $force);
        }
    }

    /**
     * Process a specific type.
     */
    private function processType(string $type, ?string $id, bool $force): void
    {
        $this->info("Processing {$type} data...");

        $job = new ProcessAiContextData($type, $id ? (int) $id : null);
        
        try {
            $job->handle();
            $this->info("✓ {$type} processing completed");
        } catch (\Exception $e) {
            $this->error("✗ {$type} processing failed: " . $e->getMessage());
        }
    }
}