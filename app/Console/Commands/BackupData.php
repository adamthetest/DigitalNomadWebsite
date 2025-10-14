<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:data {--type=all : Type of backup (all, users, cities, countries, neighborhoods, articles, deals, newsletter, favorites, companies, jobs, job_interactions, security_logs, banned_ips, cache, sessions, password_reset_tokens)} {--format=json : Backup format (json, csv, sql)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup website data (users, cities, articles, deals, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $format = $this->option('format');

        $this->info('Starting data backup...');
        $this->info("Type: {$type}");
        $this->info("Format: {$format}");

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupDir = "backups/{$timestamp}";

        // Create backup directory
        Storage::makeDirectory($backupDir);

        try {
            switch ($type) {
                case 'all':
                    $this->backupAll($backupDir, $format);
                    break;
                case 'users':
                    $this->backupUsers($backupDir, $format);
                    break;
                case 'cities':
                    $this->backupCities($backupDir, $format);
                    break;
                case 'articles':
                    $this->backupArticles($backupDir, $format);
                    break;
                case 'deals':
                    $this->backupDeals($backupDir, $format);
                    break;
                case 'newsletter':
                    $this->backupNewsletter($backupDir, $format);
                    break;
                case 'favorites':
                    $this->backupFavorites($backupDir, $format);
                    break;
                case 'companies':
                    $this->backupCompanies($backupDir, $format);
                    break;
                case 'jobs':
                    $this->backupJobs($backupDir, $format);
                    break;
                case 'job_interactions':
                    $this->backupJobInteractions($backupDir, $format);
                    break;
                case 'security_logs':
                    $this->backupSecurityLogs($backupDir, $format);
                    break;
                case 'countries':
                    $this->backupCountries($backupDir, $format);
                    break;
                case 'neighborhoods':
                    $this->backupNeighborhoods($backupDir, $format);
                    break;
                case 'banned_ips':
                    $this->backupBannedIps($backupDir, $format);
                    break;
                case 'cache':
                    $this->backupCache($backupDir, $format);
                    break;
                case 'sessions':
                    $this->backupSessions($backupDir, $format);
                    break;
                case 'password_reset_tokens':
                    $this->backupPasswordResetTokens($backupDir, $format);
                    break;
                default:
                    $this->error('Invalid backup type. Available types: all, users, cities, countries, neighborhoods, articles, deals, newsletter, favorites, companies, jobs, job_interactions, security_logs, banned_ips, cache, sessions, password_reset_tokens');

                    return 1;
            }

            $this->info('✅ Backup completed successfully!');
            $this->info("Backup location: storage/app/{$backupDir}");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Backup failed: '.$e->getMessage());

            return 1;
        }
    }

    private function backupAll($backupDir, $format)
    {
        $this->info('Backing up all data...');

        $this->backupUsers($backupDir, $format);
        $this->backupCities($backupDir, $format);
        $this->backupCountries($backupDir, $format);
        $this->backupNeighborhoods($backupDir, $format);
        $this->backupArticles($backupDir, $format);
        $this->backupDeals($backupDir, $format);
        $this->backupNewsletter($backupDir, $format);
        $this->backupFavorites($backupDir, $format);
        $this->backupCoworkingSpaces($backupDir, $format);
        $this->backupCostItems($backupDir, $format);
        $this->backupVisaRules($backupDir, $format);
        $this->backupAffiliateLinks($backupDir, $format);
        $this->backupCompanies($backupDir, $format);
        $this->backupJobs($backupDir, $format);
        $this->backupJobInteractions($backupDir, $format);
        $this->backupSecurityLogs($backupDir, $format);
        $this->backupBannedIps($backupDir, $format);
        $this->backupCache($backupDir, $format);
        $this->backupSessions($backupDir, $format);
        $this->backupPasswordResetTokens($backupDir, $format);

        // Create backup summary
        $this->createBackupSummary($backupDir);
    }

    private function backupUsers($backupDir, $format)
    {
        $this->info('Backing up users...');

        $users = DB::table('users')
            ->select(
                'id', 'name', 'email', 'email_verified_at', 'password', 'remember_token',
                'bio', 'location', 'profile_image', 'website',
                'twitter', 'linkedin', 'github', 'instagram', 'youtube', 'tiktok',
                'is_public', 'timezone', 'created_at', 'updated_at',
                // Social profile fields
                'tagline', 'job_title', 'company', 'skills', 'work_type', 'availability',
                'location_current', 'location_next', 'travel_timeline', 'behance',
                'id_verified', 'premium_status', 'last_active', 'visibility',
                'location_precise', 'show_social_links'
            )
            ->get();

        // Encrypt sensitive data (passwords and tokens)
        $users = $users->map(function ($user) {
            if ($user->password) {
                $user->password = encrypt($user->password);
            }
            if ($user->remember_token) {
                $user->remember_token = encrypt($user->remember_token);
            }

            return $user;
        });

        $this->saveData($backupDir, 'users', $users, $format);
        $this->info('✅ Users backed up: '.$users->count().' records (passwords encrypted)');
    }

    private function backupCities($backupDir, $format)
    {
        $this->info('Backing up cities...');

        $cities = DB::table('cities')
            ->join('countries', 'cities.country_id', '=', 'countries.id')
            ->select(
                'cities.*',
                'countries.name as country_name',
                'countries.code as country_code'
            )
            ->get();

        $this->saveData($backupDir, 'cities', $cities, $format);
        $this->info('✅ Cities backed up: '.$cities->count().' records');
    }

    private function backupArticles($backupDir, $format)
    {
        $this->info('Backing up articles...');

        $articles = DB::table('articles')
            ->join('cities', 'articles.city_id', '=', 'cities.id')
            ->join('countries', 'cities.country_id', '=', 'countries.id')
            ->select(
                'articles.*',
                'cities.name as city_name',
                'countries.name as country_name'
            )
            ->get();

        $this->saveData($backupDir, 'articles', $articles, $format);
        $this->info('✅ Articles backed up: '.$articles->count().' records');
    }

    private function backupDeals($backupDir, $format)
    {
        $this->info('Backing up deals...');

        $deals = DB::table('deals')->get();
        $this->saveData($backupDir, 'deals', $deals, $format);
        $this->info('✅ Deals backed up: '.$deals->count().' records');
    }

    private function backupNewsletter($backupDir, $format)
    {
        $this->info('Backing up newsletter subscribers...');

        $subscribers = DB::table('newsletter_subscribers')->get();
        $this->saveData($backupDir, 'newsletter_subscribers', $subscribers, $format);
        $this->info('✅ Newsletter subscribers backed up: '.$subscribers->count().' records');
    }

    private function backupFavorites($backupDir, $format)
    {
        $this->info('Backing up favorites...');

        $favorites = DB::table('favorites')
            ->join('users', 'favorites.user_id', '=', 'users.id')
            ->select(
                'favorites.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->get();

        $this->saveData($backupDir, 'favorites', $favorites, $format);
        $this->info('✅ Favorites backed up: '.$favorites->count().' records');
    }

    private function backupCoworkingSpaces($backupDir, $format)
    {
        $this->info('Backing up coworking spaces...');

        $spaces = DB::table('coworking_spaces')
            ->join('cities', 'coworking_spaces.city_id', '=', 'cities.id')
            ->select(
                'coworking_spaces.*',
                'cities.name as city_name'
            )
            ->get();

        $this->saveData($backupDir, 'coworking_spaces', $spaces, $format);
        $this->info('✅ Coworking spaces backed up: '.$spaces->count().' records');
    }

    private function backupCostItems($backupDir, $format)
    {
        $this->info('Backing up cost items...');

        $costItems = DB::table('cost_items')
            ->join('cities', 'cost_items.city_id', '=', 'cities.id')
            ->select(
                'cost_items.*',
                'cities.name as city_name'
            )
            ->get();

        $this->saveData($backupDir, 'cost_items', $costItems, $format);
        $this->info('✅ Cost items backed up: '.$costItems->count().' records');
    }

    private function backupVisaRules($backupDir, $format)
    {
        $this->info('Backing up visa rules...');

        $visaRules = DB::table('visa_rules')
            ->join('countries', 'visa_rules.country_id', '=', 'countries.id')
            ->select(
                'visa_rules.*',
                'countries.name as country_name'
            )
            ->get();

        $this->saveData($backupDir, 'visa_rules', $visaRules, $format);
        $this->info('✅ Visa rules backed up: '.$visaRules->count().' records');
    }

    private function backupAffiliateLinks($backupDir, $format)
    {
        $this->info('Backing up affiliate links...');

        $affiliateLinks = DB::table('affiliate_links')->get();
        $this->saveData($backupDir, 'affiliate_links', $affiliateLinks, $format);
        $this->info('✅ Affiliate links backed up: '.$affiliateLinks->count().' records');
    }

    private function backupCompanies($backupDir, $format)
    {
        $this->info('Backing up companies...');

        $companies = DB::table('companies')->get();
        $this->saveData($backupDir, 'companies', $companies, $format);
        $this->info('✅ Companies backed up: '.$companies->count().' records');
    }

    private function backupJobs($backupDir, $format)
    {
        $this->info('Backing up jobs...');

        $jobs = DB::table('jobs')
            ->join('companies', 'jobs.company_id', '=', 'companies.id')
            ->select(
                'jobs.*',
                'companies.name as company_name',
                'companies.slug as company_slug'
            )
            ->get();

        $this->saveData($backupDir, 'jobs', $jobs, $format);
        $this->info('✅ Jobs backed up: '.$jobs->count().' records');
    }

    private function backupJobInteractions($backupDir, $format)
    {
        $this->info('Backing up job user interactions...');

        $interactions = DB::table('job_user_interactions')
            ->join('users', 'job_user_interactions.user_id', '=', 'users.id')
            ->join('jobs', 'job_user_interactions.job_id', '=', 'jobs.id')
            ->join('companies', 'jobs.company_id', '=', 'companies.id')
            ->select(
                'job_user_interactions.*',
                'users.name as user_name',
                'users.email as user_email',
                'jobs.title as job_title',
                'companies.name as company_name'
            )
            ->get();

        $this->saveData($backupDir, 'job_user_interactions', $interactions, $format);
        $this->info('✅ Job interactions backed up: '.$interactions->count().' records');
    }

    private function backupSecurityLogs($backupDir, $format)
    {
        $this->info('Backing up security logs...');

        $securityLogs = DB::table('security_logs')
            ->leftJoin('users', 'security_logs.user_id', '=', 'users.id')
            ->select(
                'security_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('security_logs.created_at', 'desc')
            ->get();

        $this->saveData($backupDir, 'security_logs', $securityLogs, $format);
        $this->info('✅ Security logs backed up: '.$securityLogs->count().' records');
    }

    private function backupCountries($backupDir, $format)
    {
        $this->info('Backing up countries...');

        $countries = DB::table('countries')->get();

        $this->saveData($backupDir, 'countries', $countries, $format);
        $this->info('✅ Countries backed up: '.$countries->count().' records');
    }

    private function backupNeighborhoods($backupDir, $format)
    {
        $this->info('Backing up neighborhoods...');

        $neighborhoods = DB::table('neighborhoods')
            ->leftJoin('cities', 'neighborhoods.city_id', '=', 'cities.id')
            ->select(
                'neighborhoods.*',
                'cities.name as city_name'
            )
            ->get();

        $this->saveData($backupDir, 'neighborhoods', $neighborhoods, $format);
        $this->info('✅ Neighborhoods backed up: '.$neighborhoods->count().' records');
    }

    private function backupBannedIps($backupDir, $format)
    {
        $this->info('Backing up banned IPs...');

        $bannedIps = DB::table('banned_ips')
            ->leftJoin('users', 'banned_ips.banned_by', '=', 'users.id')
            ->select(
                'banned_ips.*',
                'users.name as banned_by_name',
                'users.email as banned_by_email'
            )
            ->get();

        $this->saveData($backupDir, 'banned_ips', $bannedIps, $format);
        $this->info('✅ Banned IPs backed up: '.$bannedIps->count().' records');
    }

    private function backupCache($backupDir, $format)
    {
        $this->info('Backing up cache...');

        $cache = DB::table('cache')->get();

        $this->saveData($backupDir, 'cache', $cache, $format);
        $this->info('✅ Cache backed up: '.$cache->count().' records');
    }

    private function backupSessions($backupDir, $format)
    {
        $this->info('Backing up sessions...');

        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->get();

        $this->saveData($backupDir, 'sessions', $sessions, $format);
        $this->info('✅ Sessions backed up: '.$sessions->count().' records');
    }

    private function backupPasswordResetTokens($backupDir, $format)
    {
        $this->info('Backing up password reset tokens...');

        $tokens = DB::table('password_reset_tokens')->get();

        $this->saveData($backupDir, 'password_reset_tokens', $tokens, $format);
        $this->info('✅ Password reset tokens backed up: '.$tokens->count().' records');
    }

    private function saveData($backupDir, $tableName, $data, $format)
    {
        switch ($format) {
            case 'json':
                $filename = "{$tableName}.json";
                $content = json_encode($data, JSON_PRETTY_PRINT);
                break;

            case 'csv':
                $filename = "{$tableName}.csv";
                $content = $this->arrayToCsv($data->toArray());
                break;

            case 'sql':
                $filename = "{$tableName}.sql";
                $content = $this->generateSqlInsert($tableName, $data);
                break;

            default:
                throw new \Exception("Unsupported format: {$format}");
        }

        Storage::put("{$backupDir}/{$filename}", $content);
    }

    private function arrayToCsv($data)
    {
        if (empty($data)) {
            return '';
        }

        $csv = '';

        // Add headers
        $headers = array_keys($data[0]);
        $csv .= implode(',', $headers)."\n";

        // Add data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                $value = $row[$header] ?? '';
                // Escape commas and quotes
                $value = str_replace(['"', ','], ['""', '","'], $value);
                $csvRow[] = '"'.$value.'"';
            }
            $csv .= implode(',', $csvRow)."\n";
        }

        return $csv;
    }

    private function generateSqlInsert($tableName, $data)
    {
        if ($data->isEmpty()) {
            return "-- No data for table {$tableName}\n";
        }

        $sql = "-- Backup for table: {$tableName}\n";
        $sql .= '-- Generated on: '.Carbon::now()."\n\n";

        foreach ($data as $row) {
            $columns = array_keys($row);
            $values = array_values($row);

            // Escape values
            $escapedValues = array_map(function ($value) {
                if (is_null($value)) {
                    return 'NULL';
                } elseif (is_string($value)) {
                    return "'".addslashes($value)."'";
                } else {
                    return $value;
                }
            }, $values);

            $sql .= "INSERT INTO {$tableName} (".implode(', ', $columns).') VALUES ('.implode(', ', $escapedValues).");\n";
        }

        return $sql;
    }

    private function createBackupSummary($backupDir)
    {
        $summary = [
            'backup_date' => Carbon::now()->toISOString(),
            'backup_type' => 'all',
            'tables_backed_up' => [
                'users',
                'cities',
                'countries',
                'neighborhoods',
                'articles',
                'deals',
                'newsletter_subscribers',
                'favorites',
                'coworking_spaces',
                'cost_items',
                'visa_rules',
                'affiliate_links',
                'companies',
                'jobs',
                'job_user_interactions',
                'security_logs',
                'banned_ips',
                'cache',
                'sessions',
                'password_reset_tokens',
            ],
            'total_records' => $this->getTotalRecords(),
            'backup_location' => $backupDir,
        ];

        Storage::put("{$backupDir}/backup_summary.json", json_encode($summary, JSON_PRETTY_PRINT));
    }

    private function getTotalRecords()
    {
        $tables = ['users', 'cities', 'articles', 'deals', 'newsletter_subscribers', 'favorites', 'coworking_spaces', 'cost_items', 'visa_rules', 'affiliate_links', 'companies', 'jobs', 'job_user_interactions'];
        $total = 0;

        foreach ($tables as $table) {
            $total += DB::table($table)->count();
        }

        return $total;
    }
}
