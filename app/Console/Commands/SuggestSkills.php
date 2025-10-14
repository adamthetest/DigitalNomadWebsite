<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SuggestSkills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nomads:suggest-skills {--limit=20 : Number of users to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suggest skills for users based on their bio and job title';

    /**
     * Common skills mapping for suggestions
     */
    private array $skillMappings = [
        'developer' => ['JavaScript', 'Python', 'React', 'Node.js', 'Git'],
        'designer' => ['UI/UX Design', 'Figma', 'Adobe Creative Suite', 'Prototyping', 'User Research'],
        'marketing' => ['Digital Marketing', 'SEO', 'Social Media', 'Content Marketing', 'Analytics'],
        'writer' => ['Content Writing', 'Copywriting', 'SEO Writing', 'Technical Writing', 'Editing'],
        'manager' => ['Project Management', 'Team Leadership', 'Agile', 'Scrum', 'Communication'],
        'sales' => ['Sales', 'CRM', 'Lead Generation', 'Negotiation', 'Customer Relations'],
        'data' => ['Data Analysis', 'SQL', 'Python', 'Excel', 'Statistics'],
        'photography' => ['Photography', 'Photo Editing', 'Lightroom', 'Photoshop', 'Videography'],
        'consultant' => ['Consulting', 'Strategy', 'Business Analysis', 'Problem Solving', 'Communication'],
        'entrepreneur' => ['Business Development', 'Startup', 'Fundraising', 'Strategy', 'Leadership'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Suggesting skills for up to {$limit} users...");

        // Get users who have bio or job title but few or no skills
        $users = User::where(function ($query) {
                $query->whereNotNull('bio')
                      ->orWhereNotNull('job_title');
            })
            ->where(function ($query) {
                $query->whereNull('skills')
                      ->orWhereJsonLength('skills', '<=', 2);
            })
            ->limit($limit)
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found that need skill suggestions.');
            return;
        }

        $suggested = 0;

        foreach ($users as $user) {
            try {
                $suggestions = $this->generateSkillSuggestions($user);
                
                if (!empty($suggestions)) {
                    $currentSkills = $user->skills ?? [];
                    $newSkills = array_unique(array_merge($currentSkills, $suggestions));
                    
                    // Limit to 10 skills max
                    $newSkills = array_slice($newSkills, 0, 10);
                    
                    $user->update(['skills' => $newSkills]);
                    $suggested++;
                    
                    $this->line("Suggested skills for {$user->name}: " . implode(', ', $suggestions));
                }
            } catch (\Exception $e) {
                Log::error("Failed to suggest skills for user {$user->id}: " . $e->getMessage());
                $this->error("Failed to suggest skills for {$user->name}: " . $e->getMessage());
            }
        }

        $this->info("Skill suggestions completed. Processed: {$suggested} users");
    }

    /**
     * Generate skill suggestions based on user's bio and job title.
     */
    private function generateSkillSuggestions(User $user): array
    {
        $suggestions = [];
        $text = strtolower(($user->bio ?? '') . ' ' . ($user->job_title ?? '') . ' ' . ($user->company ?? ''));

        foreach ($this->skillMappings as $keyword => $skills) {
            if (str_contains($text, $keyword)) {
                $suggestions = array_merge($suggestions, $skills);
            }
        }

        // Add some general digital nomad skills
        if (str_contains($text, 'nomad') || str_contains($text, 'remote') || str_contains($text, 'travel')) {
            $suggestions = array_merge($suggestions, [
                'Remote Work',
                'Time Management',
                'Communication',
                'Adaptability',
                'Cultural Awareness'
            ]);
        }

        // Remove duplicates and limit suggestions
        return array_unique(array_slice($suggestions, 0, 5));
    }
}
