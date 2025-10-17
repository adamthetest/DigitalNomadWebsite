<?php

namespace App\Console\Commands;

use App\Jobs\GenerateCityGuides;
use App\Jobs\GenerateWeeklyContent;
use App\Models\City;
use App\Services\AiContentGenerationService;
use Illuminate\Console\Command;

class GenerateContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:generate 
                            {type : Type of content to generate (weekly, city-guides, top-cities, newsletter, trending)}
                            {--city-id= : Specific city ID for city guide generation}
                            {--year= : Year for top cities post (default: current year)}
                            {--queue : Dispatch to queue instead of running immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI-powered content for the digital nomad website';

    /**
     * Execute the console command.
     */
    public function handle(AiContentGenerationService $contentService): int
    {
        $type = $this->argument('type');
        $useQueue = $this->option('queue');

        $this->info("Generating {$type} content...");

        try {
            switch ($type) {
                case 'weekly':
                    $this->generateWeeklyContent($useQueue);
                    break;
                
                case 'city-guides':
                    $this->generateCityGuides($useQueue);
                    break;
                
                case 'top-cities':
                    $this->generateTopCities($contentService, $useQueue);
                    break;
                
                case 'newsletter':
                    $this->generateNewsletter($contentService);
                    break;
                
                case 'trending':
                    $this->generateTrending($contentService);
                    break;
                
                default:
                    $this->error("Unknown content type: {$type}");
                    $this->line('Available types: weekly, city-guides, top-cities, newsletter, trending');
                    return 1;
            }

            $this->info("✅ {$type} content generation completed successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating {$type} content: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate weekly content.
     */
    private function generateWeeklyContent(bool $useQueue): void
    {
        if ($useQueue) {
            GenerateWeeklyContent::dispatch();
            $this->info('📋 Weekly content generation job dispatched to queue');
        } else {
            $job = new GenerateWeeklyContent();
            $job->handle(app(AiContentGenerationService::class));
            $this->info('📋 Weekly content generated immediately');
        }
    }

    /**
     * Generate city guides.
     */
    private function generateCityGuides(bool $useQueue): void
    {
        $cityId = $this->option('city-id');

        if ($useQueue) {
            GenerateCityGuides::dispatch($cityId);
            $this->info('🏙️ City guides generation job dispatched to queue');
        } else {
            $job = new GenerateCityGuides($cityId);
            $job->handle(app(AiContentGenerationService::class));
            $this->info('🏙️ City guides generated immediately');
        }
    }

    /**
     * Generate top cities blog post.
     */
    private function generateTopCities(AiContentGenerationService $contentService, bool $useQueue): void
    {
        $year = (int) $this->option('year') ?: date('Y');
        
        if ($useQueue) {
            // For queue, we'll create a custom job
            \App\Jobs\GenerateTopCitiesPost::dispatch($year);
            $this->info("📊 Top cities post generation job dispatched to queue for year {$year}");
        } else {
            $content = $contentService->generateTopCitiesBlogPost($year);
            
            if ($content) {
                $this->info("📊 Top cities blog post generated: {$content->title}");
                $this->line("Content ID: {$content->id}");
                $this->line("Status: {$content->status}");
            } else {
                $this->error('Failed to generate top cities blog post');
            }
        }
    }

    /**
     * Generate newsletter.
     */
    private function generateNewsletter(AiContentGenerationService $contentService): void
    {
        $content = $contentService->generateWeeklyNewsletter();
        
        if ($content) {
            $this->info("📧 Newsletter generated: {$content->title}");
            $this->line("Content ID: {$content->id}");
            $this->line("Status: {$content->status}");
        } else {
            $this->error('Failed to generate newsletter');
        }
    }

    /**
     * Generate trending destinations post.
     */
    private function generateTrending(AiContentGenerationService $contentService): void
    {
        $content = $contentService->generateTrendingDestinationsPost();
        
        if ($content) {
            $this->info("📈 Trending destinations post generated: {$content->title}");
            $this->line("Content ID: {$content->id}");
            $this->line("Status: {$content->status}");
        } else {
            $this->error('Failed to generate trending destinations post');
        }
    }
}