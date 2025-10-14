<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateUserLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nomads:update-locations {--limit=50 : Number of users to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user locations based on IP addresses for active users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Updating locations for up to {$limit} active users...");

        // Get users who have been active recently and don't have precise location set
        $users = User::where('last_active', '>=', now()->subDays(7))
            ->where(function ($query) {
                $query->whereNull('location_current')
                    ->orWhere('location_current', '');
            })
            ->limit($limit)
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found that need location updates.');

            return;
        }

        $updated = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                // In a real implementation, you would get the user's IP from their last activity
                // For now, we'll simulate this with a placeholder
                $location = $this->getLocationFromIP('127.0.0.1'); // Placeholder IP

                if ($location) {
                    $user->update([
                        'location_current' => $location,
                        'last_active' => now(),
                    ]);
                    $updated++;
                    $this->line("Updated location for {$user->name}: {$location}");
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("Failed to update location for user {$user->id}: ".$e->getMessage());
                $this->error("Failed to update location for {$user->name}: ".$e->getMessage());
            }
        }

        $this->info("Location update completed. Updated: {$updated}, Errors: {$errors}");
    }

    /**
     * Get location from IP address using a geolocation service.
     */
    private function getLocationFromIP(string $ip): ?string
    {
        try {
            // Using ipapi.co as a free geolocation service
            $response = Http::timeout(10)->get("http://ipapi.co/{$ip}/json/");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['city']) && isset($data['country_name'])) {
                    return $data['city'].', '.$data['country_name'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Geolocation API error: '.$e->getMessage());
        }

        return null;
    }
}
